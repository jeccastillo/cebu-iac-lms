<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ScholarshipService
{
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
                    'id'              => $r->intID ?? null,
                    'name'            => $r->name ?? null,
                    'deduction_type'  => $r->deduction_type ?? null,
                    'deduction_from'  => $r->deduction_from ?? null,
                    'status'          => $r->status ?? null,
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
                'sc.status'
            )
            ->orderBy('sc.deduction_type', 'asc')
            ->orderBy('sc.name', 'asc')
            ->get();

        $scholarships = [];
        $discounts = [];
        foreach ($rows as $r) {
            $item = [
                'id'             => $r->id,
                'syid'           => $r->syid,
                'discount_id'    => $r->discount_id,
                'name'           => $r->name,
                'deduction_type' => $r->deduction_type,
                'deduction_from' => $r->deduction_from,
                'status'         => $r->status,
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
}
