<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Scholarship;
use App\Services\SystemLogService;

class ScholarshipService
{
    /**
     * Resolve primary key column name for tb_mas_student_discount.
     */
    private function sdPk(): string
    {
        return Schema::hasColumn('tb_mas_student_discount', 'intID') ? 'intID' : 'id';
    }

    /**
     * List scholarships with optional filters.
     * Filters:
     * - status: active|inactive
     * - deduction_type: scholarship|discount
     * - deduction_from: in-house|external
     * - q: string (search by name)
     *
     * @return array<int, array{
     *   id:int,
     *   name:string,
     *   deduction_type:string|null,
     *   deduction_from:string|null,
     *   status:string|null
     * }>
     */
    public function list(array $filters = []): array
    {
        $q = DB::table('tb_mas_scholarships');

        if (!empty($filters['status'])) {
            $q->where('status', $filters['status']);
        }
        if (!empty($filters['deduction_type'])) {
            $q->where('deduction_type', $filters['deduction_type']);
        }
        if (!empty($filters['deduction_from'])) {
            $q->where('deduction_from', $filters['deduction_from']);
        }
        if (!empty($filters['q'])) {
            $like = '%' . $filters['q'] . '%';
            $q->where('name', 'like', $like);
        }

        return $q->orderBy('name', 'asc')
            ->get()
            ->map(function ($r) {                
                
                return [
                    'id'                     => $r->intID ?? null,
                    'code'                   => $r->code ?? null,
                    'name'                   => $r->name ?? null,
                    'deduction_type'         => $r->deduction_type ?? null,
                    'deduction_from'         => $r->deduction_from ?? null,
                    'status'                 => $r->status ?? null,                    
                    'description'            => $r->description ?? null,

                    'created_by_id'          => $r->created_by_id ?? null,

                    'tuition_fee_rate'       => $r->tuition_fee_rate ?? null,
                    'tuition_fee_fixed'      => $r->tuition_fee_fixed ?? null,

                    'basic_fee_rate'         => $r->basic_fee_rate ?? null,
                    'basic_fee_fixed'        => $r->basic_fee_fixed ?? null,

                    'misc_fee_rate'          => $r->misc_fee_rate ?? null,
                    'misc_fee_fixed'         => $r->misc_fee_fixed ?? null,

                    'lab_fee_rate'           => $r->lab_fee_rate ?? null,
                    'lab_fee_fixed'          => $r->lab_fee_fixed ?? null,

                    'penalty_fee_rate'       => $r->penalty_fee_rate ?? null,
                    'penalty_fee_fixed'      => $r->penalty_fee_fixed ?? null,

                    'other_fees_rate'        => $r->other_fees_rate ?? null,
                    'other_fees_fixed'       => $r->other_fees_fixed ?? null,

                    'total_assessment_rate'  => $r->total_assessment_rate ?? null,
                    'total_assessment_fixed' => $r->total_assessment_fixed ?? null,
                ];
            })
            ->toArray();
    }

    /**
     * Return assigned scholarships/discounts for a student in a term.
     * One of $studentId or $studentNumber must be provided.
     *
     * @return array{
     *   student: array{id:int, student_number:?string, first_name:?string, last_name:?string}|null,
     *   scholarships: array<int, array{id:int, syid:int, discount_id:int, name:string, deduction_type:string, deduction_from:string, status:string}>,
     *   discounts: array<int, array{id:int, syid:int, discount_id:int, name:string, deduction_type:string, deduction_from:string, status:string}>
     * }
     */
    public function assigned(int $syid, ?int $studentId = null, ?string $studentNumber = null): array
    {
        $student = null;
        if (!empty($studentId)) {
            $student = DB::table('tb_mas_users')->where('intID', $studentId)->first();
        } elseif (!empty($studentNumber)) {
            $student = DB::table('tb_mas_users')->where('strStudentNumber', $studentNumber)->first();
        }

        if (!$student) {
            return [
                'student'      => null,
                'scholarships' => [],
                'discounts'    => [],
            ];
        }

        $rows = DB::table('tb_mas_student_discount as sd')
            ->join('tb_mas_scholarships as sc', 'sc.intID', '=', 'sd.discount_id')
            ->where('sd.syid', $syid)
            ->where('sd.student_id', $student->intID)
            ->select(
                'sd.intID as id',
                'sd.syid',
                'sd.student_id',
                'sd.discount_id',
                'sc.name',
                'sc.deduction_type',
                'sc.deduction_from',
                'sc.status',
                'sd.status as assignment_status'
            )
            ->orderBy('sc.deduction_type', 'asc')
            ->orderBy('sc.name', 'asc')
            ->get();

        $scholarships = [];
        $discounts = [];
        foreach ($rows as $r) {
            $item = [
                'id'                => $r->id,
                'syid'              => $r->syid,
                'discount_id'       => $r->discount_id,
                'name'              => $r->name,
                'deduction_type'    => $r->deduction_type,
                'deduction_from'    => $r->deduction_from,
                'status'            => $r->status,
                'assignment_status' => $r->assignment_status ?? null,
            ];
            if ($r->deduction_type === 'scholarship') {
                $scholarships[] = $item;
            } else {
                $discounts[] = $item;
            }
        }

        $studentInfo = [
            'id'             => $student->intID,
            'student_number' => $student->strStudentNumber ?? null,
            'first_name'     => $student->strFirstname ?? null,
            'last_name'      => $student->strLastname ?? null,
        ];

        return [
            'student'      => $studentInfo,
            'scholarships' => $scholarships,
            'discounts'    => $discounts,
        ];
    }

    /**
     * List students who have any scholarship/discount assigned in a term.
     * Optional q: filter by student number or last/first name (LIKE).
     *
     * @return array<int, array{
     *   student_id:int,
     *   student_number:?string,
     *   first_name:?string,
     *   last_name:?string,
     *   scholarships: array<int, string>,
     *   discounts: array<int, string>
     * }>
     */
    public function enrolled(int $syid, ?string $q = null): array
    {
        $query = DB::table('tb_mas_student_discount as sd')
            ->join('tb_mas_users as u', 'u.intID', '=', 'sd.student_id')
            ->join('tb_mas_scholarships as sc', 'sc.intID', '=', 'sd.discount_id')
            ->where('sd.syid', $syid)
            ->select(
                'u.intID as student_id',
                'u.strStudentNumber as student_number',
                'u.strFirstname as first_name',
                'u.strLastname as last_name',
                'sc.name',
                'sc.deduction_type'
            );

        if (!empty($q)) {
            $like = '%' . $q . '%';
            $query->where(function ($sub) use ($like) {
                $sub->where('u.strStudentNumber', 'like', $like)
                    ->orWhere('u.strLastname', 'like', $like)
                    ->orWhere('u.strFirstname', 'like', $like);
            });
        }

        $rows = $query->orderBy('u.strLastname')
            ->orderBy('u.strFirstname')
            ->orderBy('sc.name')
            ->get();

        $byStudent = [];
        foreach ($rows as $r) {
            $sid = (int) $r->student_id;
            if (!isset($byStudent[$sid])) {
                $byStudent[$sid] = [
                    'student_id'     => $sid,
                    'student_number' => $r->student_number,
                    'first_name'     => $r->first_name,
                    'last_name'      => $r->last_name,
                    'scholarships'   => [],
                    'discounts'      => [],
                ];
            }
            if ($r->deduction_type === 'scholarship') {
                $byStudent[$sid]['scholarships'][] = $r->name;
            } else {
                $byStudent[$sid]['discounts'][] = $r->name;
            }
        }

        // De-duplicate names per bucket
        $result = array_values(array_map(function ($s) {
            $s['scholarships'] = array_values(array_unique($s['scholarships']));
            $s['discounts']    = array_values(array_unique($s['discounts']));
            return $s;
        }, $byStudent));

        return $result;
    }

    /**
     * Upsert a scholarship/discount assignment (stub for now).
     *
     * @return array{success:bool, message:string}
     */
    public function upsert(array $payload): array
    {
        return [
            'success' => false,
            'message' => 'Not Implemented',
        ];
    }

    /**
     * Create a pending assignment row if it doesn't exist for (student_id, syid, discount_id).
     * Returns the joined normalized item: { id, syid, discount_id, name, deduction_type, deduction_from, status, assignment_status }
     */
    public function assignmentUpsert(array $payload): array
    {
        $studentId  = isset($payload['student_id']) ? (int) $payload['student_id'] : 0;
        $syid       = isset($payload['syid']) ? (int) $payload['syid'] : 0;
        $discountId = isset($payload['discount_id']) ? (int) $payload['discount_id'] : 0;
        $referrer =   isset($payload['referrer']) ? (int) $payload['referrer'] : "na";

        if ($studentId <= 0 || $syid <= 0 || $discountId <= 0) {
            throw new \InvalidArgumentException('student_id, syid, and discount_id are required');
        }

        // Validate scholarship/discount id exists
        $existsSch = DB::table('tb_mas_scholarships')->where('intID', $discountId)->exists();
        if (!$existsSch) {
            throw new \InvalidArgumentException('Invalid discount_id');
        }

        // Idempotent check
        $existing = DB::table('tb_mas_student_discount')
            ->where('student_id', $studentId)
            ->where('syid', $syid)
            ->where('discount_id', $discountId)
            ->first();

        $pk = $this->sdPk();

        if (!$existing) {
            $id = DB::table('tb_mas_student_discount')->insertGetId([
                'student_id'  => $studentId,
                'syid'        => $syid,
                'discount_id' => $discountId,
                'status'      => 'pending',
                'referrer'    => $referrer
            ]);
        } else {
            $id = (int) ($existing->$pk ?? 0);
        }

        // Return normalized joined row
        $row = DB::table('tb_mas_student_discount as sd')
            ->join('tb_mas_scholarships as sc', 'sc.intID', '=', 'sd.discount_id')
            ->where('sd.' . $pk, $id)
            ->select(
                DB::raw('sd.' . $pk . ' as id'),
                'sd.syid',
                'sd.student_id',
                'sd.discount_id',
                'sd.status as assignment_status',
                'sc.name',
                'sc.deduction_type',
                'sc.deduction_from',
                'sc.status'
            )
            ->first();

        if (!$row) {
            throw new \RuntimeException('Failed to fetch assignment after upsert');
        }

        $out = [
            'id'                => (int) $row->id,
            'syid'              => (int) $row->syid,
            'student_id'        => (int) $row->student_id,
            'discount_id'       => (int) $row->discount_id,
            'name'              => $row->name,
            'deduction_type'    => $row->deduction_type,
            'deduction_from'    => $row->deduction_from,
            'status'            => $row->status, // catalog status
            'assignment_status' => $row->assignment_status ?? null,
        ];

        // System log: assign scholarship/discount (create assignment)
        try {
            SystemLogService::log('create', 'StudentDiscount', (int) $out['id'], null, $out, request());
        } catch (\Throwable $e) {}

        return $out;
    }

    /**
     * List assignment rows for a term; optionally filter by student_id or query by student name/number.
     *
     * @param array $filters ['syid'=>int, 'student_id'=>?int, 'q'=>?string]
     * @return array<int, array{id:int, syid:int, discount_id:int, name:string, deduction_type:string, deduction_from:string, status:?string, assignment_status:?string}>
     */
    public function listAssignments(array $filters = []): array
    {
        $syid = isset($filters['syid']) ? (int) $filters['syid'] : 0;
        if ($syid <= 0) {
            return [];
        }

        $pk = $this->sdPk();

        $q = DB::table('tb_mas_student_discount as sd')
            ->join('tb_mas_scholarships as sc', 'sc.intID', '=', 'sd.discount_id')
            ->where('sd.syid', $syid)
            ->select(
                DB::raw('sd.' . $pk . ' as id'),
                'sd.syid',
                'sd.student_id',
                'sd.discount_id',
                'sd.status as assignment_status',
                'sc.name',
                'sc.deduction_type',
                'sc.deduction_from',
                'sc.status'
            );

        if (!empty($filters['student_id'])) {
            $q->where('sd.student_id', (int) $filters['student_id']);
        } elseif (!empty($filters['q'])) {
            $like = '%' . $filters['q'] . '%';
            $q->join('tb_mas_users as u', 'u.intID', '=', 'sd.student_id')
                ->where(function ($sub) use ($like) {
                    $sub->where('u.strStudentNumber', 'like', $like)
                        ->orWhere('u.strLastname', 'like', $like)
                        ->orWhere('u.strFirstname', 'like', $like);
                });
        }

        return $q->orderBy('sc.deduction_type', 'asc')
            ->orderBy('sc.name', 'asc')
            ->get()
            ->map(function ($r) {
                return [
                    'id'                => (int) $r->id,
                    'syid'              => (int) $r->syid,
                    'student_id'        => (int) $r->student_id,
                    'discount_id'       => (int) $r->discount_id,
                    'name'              => $r->name,
                    'deduction_type'    => $r->deduction_type,
                    'deduction_from'    => $r->deduction_from,
                    'status'            => $r->status, // catalog status
                    'assignment_status' => $r->assignment_status ?? null,
                ];
            })
            ->toArray();
    }

    /**
     * Bulk-apply assignments by IDs. Returns { updated: count }.
     */
    public function applyAssignments(array $ids, ?int $actorId = null): array
    {
        // Normalize ids to unique positive ints
        $ids = array_values(array_unique(array_filter(array_map(function ($v) {
            $i = (int) $v;
            return $i > 0 ? $i : null;
        }, $ids))));

        if (empty($ids)) {
            return ['updated' => 0];
        }
 
        $pk = $this->sdPk();
        $updated = DB::table('tb_mas_student_discount')
            ->whereIn($pk, $ids)
            ->where('status', '<>', 'applied')
            ->update(['status' => 'applied']);
 
        return ['updated' => (int) $updated];
    }

    /**
     * Delete an assignment row by ID. Allowed only when status != 'applied'.
     * Returns { deleted: bool }.
     */
    public function deleteAssignment(int $id): array
    {
        $pk = $this->sdPk();

        $row = DB::table('tb_mas_student_discount')->where($pk, $id)->first();
        if (!$row) {
            return ['deleted' => false];
        }

        // Allow deletion regardless of current status (including 'applied')
        $before = is_object($row) ? (array) $row : (array) $row;

        $deleted = DB::table('tb_mas_student_discount')->where($pk, $id)->delete();

        // System log: delete scholarship/discount assignment
        try {
            SystemLogService::log('delete', 'StudentDiscount', (int) $id, $before, null, request());
        } catch (\Throwable $e) {}

        return ['deleted' => $deleted > 0];
    }

    /**
     * Get a scholarship by id.
     *
     * @return array|null
     */
    public function get(int $id): ?array
    {
        $m = Scholarship::find($id);
        if (!$m) {
            return null;
        }
        return $this->mapModel($m);
    }

    /**
     * Create a scholarship row.
     *
     * @param array $data
     * @return array
     */
    public function create(array $data): array
    {
        $payload = $this->normalizePayload($data);
        $m = Scholarship::create($payload);
        return $this->mapModel($m);
    }

    /**
     * Update a scholarship row.
     *
     * @param int $id
     * @param array $data
     * @return array
     */
    public function update(int $id, array $data): array
    {
        $m = Scholarship::findOrFail($id);
        $payload = $this->normalizePayload($data, $m);
        $m->fill($payload);
        $m->save();

        return $this->mapModel($m->fresh());
    }

    /**
     * Soft delete (set status = inactive) if status column exists; otherwise hard delete as fallback.
     *
     * @param int $id
     * @return array
     */
    public function softDelete(int $id): array
    {
        $m = Scholarship::findOrFail($id);

        if (Schema::hasColumn($m->getTable(), 'status')) {
            // idempotent: keep inactive if already inactive
            $m->status = 'inactive';
            $m->save();
            return $this->mapModel($m->fresh());
        }

        // Fallback to hard delete if no status column
        $snapshot = $this->mapModel($m);
        $m->delete();
        return $snapshot;
    }

    /**
     * Restore (set status = active) where supported.
     *
     * @param int $id
     * @return array
     */
    public function restore(int $id): array
    {
        $m = Scholarship::findOrFail($id);

        if (Schema::hasColumn($m->getTable(), 'status')) {
            $m->status = 'active';
            $m->save();
            return $this->mapModel($m->fresh());
        }

        // If no status column, return the current snapshot
        return $this->mapModel($m);
    }

    /**
     * Normalize incoming payload to actual table columns that exist.
     *
     * - Maps percent/percentage to whichever column exists.
     * - Maps fixed_amount/amount to whichever column exists.
     * - Whitelists allowed top-level fields.
     *
     * @param array $data
     * @param \App\Models\Scholarship|null $existing
     * @return array
     */
    protected function normalizePayload(array $data, ?Scholarship $existing = null): array
    {
        // Base whitelisted fields
        $out = [];
        $allow = [
            'code','name','deduction_type','deduction_from','status','description',
            'created_by_id',
            'tuition_fee_rate','tuition_fee_fixed',
            'basic_fee_rate','basic_fee_fixed',
            'misc_fee_rate','misc_fee_fixed',
            'lab_fee_rate','lab_fee_fixed',
            'penalty_fee_rate','penalty_fee_fixed',
            'other_fees_rate','other_fees_fixed',
            'total_assessment_rate','total_assessment_fixed',
        ];

        foreach ($allow as $k) {
            if (array_key_exists($k, $data)) {
                $out[$k] = $data[$k];
            }
        }

        // Percent/Percentage mapping
        $percentVal = null;
        if (array_key_exists('percent', $data)) {
            $percentVal = $data['percent'];
        } elseif (array_key_exists('percentage', $data)) {
            $percentVal = $data['percentage'];
        }

        if ($percentVal !== null) {
            $table = (new Scholarship())->getTable();
            if (Schema::hasColumn($table, 'percent')) {
                $out['percent'] = $percentVal;
            } elseif (Schema::hasColumn($table, 'percentage')) {
                $out['percentage'] = $percentVal;
            }
        }

        // Fixed amount/amount mapping
        $fixedVal = null;
        if (array_key_exists('fixed_amount', $data)) {
            $fixedVal = $data['fixed_amount'];
        } elseif (array_key_exists('amount', $data)) {
            $fixedVal = $data['amount'];
        }

        if ($fixedVal !== null) {
            $table = (new Scholarship())->getTable();
            if (Schema::hasColumn($table, 'fixed_amount')) {
                $out['fixed_amount'] = $fixedVal;
            } elseif (Schema::hasColumn($table, 'amount')) {
                $out['amount'] = $fixedVal;
            }
        }

        return $out;
    }

    /**
     * Map model/row to standardized array used by API.
     *
     * @param \App\Models\Scholarship|\stdClass|array $m
     * @return array
     */
    private function mapModel($m): array
    {
        // handle both Eloquent model and stdClass/array rows
        $get = function (string $key) use ($m) {
            if (is_array($m)) {
                return $m[$key] ?? null;
            }
            if (is_object($m)) {
                return $m->$key ?? null;
            }
            return null;
        };

        $percent = $get('percent') ?? $get('percentage');
        $fixed   = $get('fixed_amount') ?? $get('amount');

        return [
            'id'                     => $get('intID') ?? $get('id'),
            'code'                   => $get('code'),
            'name'                   => $get('name') ?? $get('strName'),
            'deduction_type'         => $get('deduction_type'),
            'deduction_from'         => $get('deduction_from'),
            'status'                 => $get('status'),
            'percent'                => $percent !== null ? (float) $percent : null,
            'fixed_amount'           => $fixed !== null ? (float) $fixed : null,
            'description'            => $get('description'),

            'created_by_id'          => $get('created_by_id'),

            'tuition_fee_rate'       => $get('tuition_fee_rate') !== null ? (int) $get('tuition_fee_rate') : null,
            'tuition_fee_fixed'      => $get('tuition_fee_fixed') !== null ? (float) $get('tuition_fee_fixed') : null,

            'basic_fee_rate'         => $get('basic_fee_rate') !== null ? (int) $get('basic_fee_rate') : null,
            'basic_fee_fixed'        => $get('basic_fee_fixed') !== null ? (float) $get('basic_fee_fixed') : null,

            'misc_fee_rate'          => $get('misc_fee_rate') !== null ? (int) $get('misc_fee_rate') : null,
            'misc_fee_fixed'         => $get('misc_fee_fixed') !== null ? (float) $get('misc_fee_fixed') : null,

            'lab_fee_rate'           => $get('lab_fee_rate') !== null ? (int) $get('lab_fee_rate') : null,
            'lab_fee_fixed'          => $get('lab_fee_fixed') !== null ? (float) $get('lab_fee_fixed') : null,

            'penalty_fee_rate'       => $get('penalty_fee_rate') !== null ? (int) $get('penalty_fee_rate') : null,
            'penalty_fee_fixed'      => $get('penalty_fee_fixed') !== null ? (float) $get('penalty_fee_fixed') : null,

            'other_fees_rate'        => $get('other_fees_rate') !== null ? (int) $get('other_fees_rate') : null,
            'other_fees_fixed'       => $get('other_fees_fixed') !== null ? (float) $get('other_fees_fixed') : null,

            'total_assessment_rate'  => $get('total_assessment_rate') !== null ? (int) $get('total_assessment_rate') : null,
            'total_assessment_fixed' => $get('total_assessment_fixed') !== null ? (float) $get('total_assessment_fixed') : null,
        ];

    }
}
