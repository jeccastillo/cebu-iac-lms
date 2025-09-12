<?php

namespace App\Services;

use App\Models\ClinicHealthRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ClinicHealthService
{
    /**
     * Create or update a health record for a person.
     * Idempotent by (person_type, person_student_id/person_faculty_id).
     *
     * @param array $payload
     * @return ClinicHealthRecord
     */
    public function createOrUpdate(array $payload): ClinicHealthRecord
    {
        $personType = $payload['person_type'] ?? null;
        if (!in_array($personType, ['student', 'faculty'], true)) {
            throw new \InvalidArgumentException('person_type must be student or faculty');
        }

        $studentId = $payload['person_student_id'] ?? null;
        $facultyId = $payload['person_faculty_id'] ?? null;

        if ($personType === 'student') {
            if (!$studentId || !is_numeric($studentId)) {
                throw new \InvalidArgumentException('person_student_id is required for person_type=student');
            }
            $facultyId = null; // normalize
        } else {
            if (!$facultyId || !is_numeric($facultyId)) {
                throw new \InvalidArgumentException('person_faculty_id is required for person_type=faculty');
            }
            $studentId = null; // normalize
        }

        // Normalize JSON array fields
        foreach (['allergies', 'medications', 'immunizations', 'conditions'] as $jsonField) {
            if (isset($payload[$jsonField]) && $payload[$jsonField] !== null) {
                // Accept JSON string or array
                if (is_string($payload[$jsonField])) {
                    $decoded = json_decode($payload[$jsonField], true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $payload[$jsonField] = $decoded;
                    }
                }
                // Enforce array or null
                if (!is_array($payload[$jsonField])) {
                    $payload[$jsonField] = null;
                } else {
                    // Coerce array entries into { name: string, ... } objects where possible.
                    // This allows the SPA to send simple strings or loosely shaped objects.
                    $normalized = array_map(function ($it) {
                        if (is_string($it)) {
                            $n = trim($it);
                            return $n !== '' ? ['name' => $n] : null;
                        }
                        if (is_array($it)) {
                            // Prefer 'name' when present
                            if (isset($it['name'])) {
                                $it['name'] = trim((string) $it['name']);
                                return $it['name'] !== '' ? $it : null;
                            }
                            // Fallback common key
                            if (isset($it['value'])) {
                                $n = trim((string) $it['value']);
                                if ($n === '') return null;
                                unset($it['value']);
                                $it['name'] = $n;
                                return $it;
                            }
                        }
                        return null;
                    }, $payload[$jsonField]);

                    // Filter out null/empty entries and reindex
                    $normalized = array_values(array_filter($normalized, function ($x) { return $x !== null; }));

                    $payload[$jsonField] = count($normalized) ? $normalized : null;
                }
            }
        }

        $bloodType = $payload['blood_type'] ?? null;
        if ($bloodType !== null) {
            $bloodType = strtoupper(trim((string)$bloodType));
            if (!in_array($bloodType, ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'], true)) {
                // allow free-form if needed; else null invalid values
                $bloodType = null;
            }
            $payload['blood_type'] = $bloodType;
        }

        // Upsert key
        $match = [
            'person_type' => $personType,
            'person_student_id' => $studentId,
            'person_faculty_id' => $facultyId,
        ];

        /** @var ClinicHealthRecord $record */
        $record = ClinicHealthRecord::firstOrNew($match);

        $record->fill([
            'blood_type'    => $payload['blood_type'] ?? $record->blood_type,
            'height_cm'     => $payload['height_cm'] ?? $record->height_cm,
            'weight_kg'     => $payload['weight_kg'] ?? $record->weight_kg,
            'allergies'     => $payload['allergies'] ?? $record->allergies,
            'medications'   => $payload['medications'] ?? $record->medications,
            'immunizations' => $payload['immunizations'] ?? $record->immunizations,
            'conditions'    => $payload['conditions'] ?? $record->conditions,
            'notes'         => $payload['notes'] ?? $record->notes,
            'campus_id'     => $payload['campus_id'] ?? $record->campus_id,
            'last_updated_by' => $payload['last_updated_by'] ?? $record->last_updated_by,
        ]);

        $record->save();

        return $record;
    }

    /**
     * Fetch a single record by id.
     *
     * @param int $id
     * @return ClinicHealthRecord|null
     */
    public function get(int $id): ?ClinicHealthRecord
    {
        return ClinicHealthRecord::withCount('visits')->find($id);
    }

    /**
     * Search health records with filters and pagination.
     *
     * Supported filters:
     * - q: free-text name/number (students) or faculty id exact
     * - student_number, last_name, first_name, middle_name
     * - faculty_id
     * - campus_id, program_id, year_level
     * - diagnosis (substring in latest visit diagnosis_codes), medication, allergy
     * - date_from, date_to (visit_date range filter)
     *
     * Note: For MVP we primarily support student linked filters via tb_mas_users.
     *
     * @param array $filters
     * @param int $page
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function search(array $filters, int $page = 1, int $perPage = 20): LengthAwarePaginator
    {
        $page = max(1, $page);
        $perPage = min(max(1, $perPage), 100);

        $q = DB::table('clinic_health_records as hr');

        // Join to students table (optional)
        $q->leftJoin('tb_mas_users as u', function ($join) {
            $join->on('u.intID', '=', 'hr.person_student_id')
                ->where('hr.person_type', '=', 'student');
        });

        // Basic select (include minimal identity projection)
        $q->select(
            'hr.id',
            'hr.person_type',
            'hr.person_student_id',
            'hr.person_faculty_id',
            'hr.blood_type',
            'hr.campus_id',
            'hr.updated_at',
            DB::raw('u.strStudentNumber as student_number'),
            DB::raw('u.strLastname as last_name'),
            DB::raw('u.strFirstname as first_name'),
            DB::raw('u.strMiddlename as middle_name'),
            DB::raw('u.intProgramID as program_id')
        );

        // Identity filters
        $free = isset($filters['q']) ? trim((string)$filters['q']) : '';
        if ($free !== '') {
            $q->where(function ($w) use ($free) {
                $w->where('u.strStudentNumber', 'like', $free . '%')
                  ->orWhere('u.strLastname', 'like', '%' . $free . '%')
                  ->orWhere('u.strFirstname', 'like', '%' . $free . '%')
                  ->orWhere('u.strMiddlename', 'like', '%' . $free . '%');
            });
        }

        if (!empty($filters['student_number'])) {
            $q->where('u.strStudentNumber', 'like', trim((string)$filters['student_number']) . '%');
        }
        if (!empty($filters['last_name'])) {
            $q->where('u.strLastname', 'like', '%' . trim((string)$filters['last_name']) . '%');
        }
        if (!empty($filters['first_name'])) {
            $q->where('u.strFirstname', 'like', '%' . trim((string)$filters['first_name']) . '%');
        }
        if (!empty($filters['middle_name'])) {
            $q->where('u.strMiddlename', 'like', '%' . trim((string)$filters['middle_name']) . '%');
        }
        if (!empty($filters['faculty_id'])) {
            $q->orWhere(function ($w) use ($filters) {
                $w->where('hr.person_type', 'faculty')
                  ->where('hr.person_faculty_id', (int)$filters['faculty_id']);
            });
        }

        // Campus/program/year level filters (students)
        if (!empty($filters['campus_id'])) {
            $q->where(function ($w) use ($filters) {
                $w->where('hr.campus_id', (int)$filters['campus_id'])
                  ->orWhere('u.campus_id', (int)$filters['campus_id']);
            });
        }
        if (!empty($filters['program_id'])) {
            $q->where('u.intProgramID', (int)$filters['program_id']);
        }
        if (!empty($filters['year_level'])) {
            $yl = (int)$filters['year_level'];
            $q->whereExists(function ($sub) use ($yl) {
                $sub->from('tb_mas_registration as r')
                    ->whereColumn('r.intStudentID', 'u.intID')
                    ->where('r.intYearLevel', $yl);
            });
        }

        // Clinical filters: diagnosis/medication/allergy substring by scanning recent visits
        $diagnosis = isset($filters['diagnosis']) ? trim((string)$filters['diagnosis']) : '';
        $medication = isset($filters['medication']) ? trim((string)$filters['medication']) : '';
        $allergy = isset($filters['allergy']) ? trim((string)$filters['allergy']) : '';

        $dateFrom = isset($filters['date_from']) ? trim((string)$filters['date_from']) : '';
        $dateTo = isset($filters['date_to']) ? trim((string)$filters['date_to']) : '';

        if ($diagnosis !== '' || $medication !== '' || $allergy !== '' || $dateFrom !== '' || $dateTo !== '') {
            $q->whereExists(function ($sub) use ($diagnosis, $medication, $allergy, $dateFrom, $dateTo) {
                $sub->from('clinic_visits as v')->whereColumn('v.record_id', 'hr.id');
                if ($dateFrom !== '') {
                    $sub->where('v.visit_date', '>=', $dateFrom);
                }
                if ($dateTo !== '') {
                    $sub->where('v.visit_date', '<=', $dateTo);
                }
                if ($diagnosis !== '') {
                    // JSON CONTAINS fallback: LIKE on serialized json
                    $sub->where('v.diagnosis_codes', 'like', '%' . $diagnosis . '%');
                }
                if ($medication !== '') {
                    $sub->where('v.medications_dispensed', 'like', '%' . $medication . '%');
                }
                if ($allergy !== '') {
                    $sub->whereExists(function ($sub2) use ($allergy) {
                        $sub2->from('clinic_health_records as hr2')
                            ->whereColumn('hr2.id', 'v.record_id')
                            ->where('hr2.allergies', 'like', '%' . $allergy . '%');
                    });
                }
            });
        }

        $q->orderBy('u.strLastname')->orderBy('u.strFirstname')->orderBy('hr.updated_at', 'desc');

        // Pagination
        $total = (clone $q)->count();
        $rows = $q->forPage($page, $perPage)->get();

        // Use Laravel LengthAwarePaginator concrete class
        $items = collect($rows);
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            [
                'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
                'pageName' => 'page',
                'query' => request()->query() ?? []
            ]
        );

        return $paginator;
    }
}
