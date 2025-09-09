<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Scholarship;
use App\Services\SystemLogService;
use App\Services\StudentPaymentStatusService;

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
                    'max_stacks'             => isset($r->max_stacks) ? (int) $r->max_stacks : null,
                    'compute_full'           => isset($r->compute_full) ? (bool) $r->compute_full : null,

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
                'sd.referrer',
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
                'referrer'          => $r->referrer ?? null,
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
        // Resolve referrer name (string) from either referrer_student_id or referrer_name
        $referrerName = null;
        if (!empty($payload['referrer_student_id'])) {
            $rid = (int) $payload['referrer_student_id'];
            if ($rid > 0) {
                $u = DB::table('tb_mas_users')
                    ->select('strFirstname', 'strMiddlename', 'strLastname', 'strStudentNumber', 'intID')
                    ->where('intID', $rid)
                    ->first();
                if ($u) {
                    $first  = trim((string) ($u->strFirstname ?? ''));
                    $middle = trim((string) ($u->strMiddlename ?? ''));
                    $last   = trim((string) ($u->strLastname ?? ''));
                    $referrerName = trim(($last !== '' ? ($last . ', ') : '') . $first . ($middle !== '' ? (' ' . $middle) : ''));
                    if ($referrerName === '') {
                        $referrerName = (string) ($u->strStudentNumber ?? '');
                    }
                }
            }
        } elseif (array_key_exists('referrer_name', $payload)) {
            $referrerName = trim((string) $payload['referrer_name']);
            if ($referrerName === '') {
                $referrerName = null;
            }
        }

        if ($studentId <= 0 || $syid <= 0 || $discountId <= 0) {
            throw new \InvalidArgumentException('student_id, syid, and discount_id are required');
        }

        // Validate scholarship/discount id exists
        $existsSch = DB::table('tb_mas_scholarships')->where('intID', $discountId)->exists();
        if (!$existsSch) {
            throw new \InvalidArgumentException('Invalid discount_id');
        }

        // If a referrer is provided, ensure they are fully paid for the same term before proceeding
        if (!empty($payload['referrer_student_id'])) {
            $rid = (int) $payload['referrer_student_id'];
            if ($rid > 0) {
                /** @var StudentPaymentStatusService $paymentStatus */
                $paymentStatus = app(StudentPaymentStatusService::class);
                $status = $paymentStatus->isFullyPaidForTerm($rid, $syid);
                if (empty($status['is_fully_paid'])) {
                    throw new \InvalidArgumentException('The referree you are trying to tag is not yet fully paid');
                }
            }
        }

        // Check duplicate referrer for same term
        if (!empty($payload['referrer_student_id']) && $referrerName !== null && $referrerName !== '') {
            $existsRef = DB::table('tb_mas_student_discount')
                ->where('syid', $syid)
                ->where('referrer', $referrerName)
                ->exists();
            if ($existsRef) {
                throw new \InvalidArgumentException('That student is alreadyt tagged as a referree for this term.');
            }
        }

        // Count existing assignments for the same (student_id, syid, discount_id)
        $existingCount = DB::table('tb_mas_student_discount')
            ->where('student_id', $studentId)
            ->where('syid', $syid)
            ->where('discount_id', $discountId)
            ->count();

        // Determine allowed maximum stacks for this scholarship/discount (default 1)
        $maxStacks = (int) (DB::table('tb_mas_scholarships')->where('intID', $discountId)->value('max_stacks') ?? 1);
        if ($maxStacks < 1) {
            $maxStacks = 1;
        }

        // Mutual-exclusion check for a new assignment attempt
        $conf = $this->detectMutualExclusionConflict($studentId, $syid, $discountId);
        if (!empty($conf['has_conflict'])) {
            $sel = $conf['names']['selected'] ?? 'selected';
            $ex  = $conf['names']['existing'] ?? 'existing';
            throw new \InvalidArgumentException("can not tag {$sel} with {$ex}");
        }

        // Enforce stacking cap
        if ($existingCount >= $maxStacks) {
            throw new \InvalidArgumentException("This scholarship can only be assigned {$maxStacks} time(s).");
        }

        $pk = $this->sdPk();

        // Insert a new row (allow stacking until cap is reached)
        $id = DB::table('tb_mas_student_discount')->insertGetId([
            'student_id'  => $studentId,
            'syid'        => $syid,
            'discount_id' => $discountId,
            'status'      => 'pending',
            'referrer'    => $referrerName !== null ? $referrerName : '',
        ]);

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
                'sd.referrer',
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
                    'referrer'          => $r->referrer ?? null,
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
            'code','name','deduction_type','deduction_from','status','description','max_stacks','compute_full',
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
            'max_stacks'             => $get('max_stacks') !== null ? (int) $get('max_stacks') : null,
            'compute_full'           => $get('compute_full') !== null ? (bool) $get('compute_full') : null,
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

    /**
     * Get assigned discount ids for a student in a term across all statuses.
     *
     * @return array<int>
     */
    private function getAssignedDiscountIds(int $studentId, int $syid, ?int $excludeDiscountId = null): array
    {
        $sid  = (int) $studentId;
        $term = (int) $syid;
        if ($sid <= 0 || $term <= 0) {
            return [];
        }

        $rows = DB::table('tb_mas_student_discount')
            ->where('student_id', $sid)
            ->where('syid', $term)
            ->select('discount_id')
            ->get();

        $ids = [];
        foreach ($rows as $r) {
            $d = (int) ($r->discount_id ?? 0);
            if ($d > 0) {
                if ($excludeDiscountId !== null && $d === (int) $excludeDiscountId) {
                    continue;
                }
                $ids[] = $d;
            }
        }
        return array_values(array_unique($ids));
    }

    /**
     * Find counterpart ids that are mutually exclusive with the given discountId.
     * Only active pairs considered.
     *
     * @return array<int>
     */
    private function findMutualExclusionsFor(int $discountId): array
    {
        $id = (int) $discountId;
        if ($id <= 0) {
            return [];
        }

        $rows = DB::table('tb_mas_scholarship_me')
            ->where('status', 'active')
            ->where(function ($q) use ($id) {
                $q->where('discount_id_a', $id)
                  ->orWhere('discount_id_b', $id);
            })
            ->get();

        $out = [];
        foreach ($rows as $r) {
            $a = (int) ($r->discount_id_a ?? 0);
            $b = (int) ($r->discount_id_b ?? 0);
            if ($a === $id && $b > 0) {
                $out[] = $b;
            }
            if ($b === $id && $a > 0) {
                $out[] = $a;
            }
        }
        // ensure uniqueness
        return array_values(array_unique($out));
    }

    /**
     * Resolve scholarship/discount names by ids.
     *
     * @param array<int> $ids
     * @return array<int, string> id => name
     */
    private function resolveNames(array $ids): array
    {
        $norm = [];
        foreach ($ids as $v) {
            $i = (int) $v;
            if ($i > 0) {
                $norm[] = $i;
            }
        }
        $ids = array_values(array_unique($norm));
        if (empty($ids)) {
            return [];
        }

        $rows = DB::table('tb_mas_scholarships')
            ->whereIn('intID', $ids)
            ->select('intID', 'name')
            ->get();

        $out = [];
        foreach ($rows as $r) {
            $out[(int) ($r->intID ?? 0)] = (string) ($r->name ?? '');
        }
        return $out;
    }

    /**
     * Detect if selectedDiscountId conflicts with any already assigned ids for the student/term.
     *
     * @return array{has_conflict:bool, with_id?:int, names?:array{selected:string, existing:string}}
     */
    private function detectMutualExclusionConflict(int $studentId, int $syid, int $selectedDiscountId): array
    {
        $selectedId = (int) $selectedDiscountId;

        // Collect assigned discount ids for same student and term (pending + applied)
        $assigned = $this->getAssignedDiscountIds($studentId, $syid, null);
        if (empty($assigned)) {
            return ['has_conflict' => false];
        }

        // Find exclusions for the selected id
        $blocked = $this->findMutualExclusionsFor($selectedId);
        if (empty($blocked)) {
            return ['has_conflict' => false];
        }

        // Build set for O(1) lookup
        $assignedSet = [];
        foreach ($assigned as $a) {
            $assignedSet[(int) $a] = true;
        }

        $conflictWith = null;
        foreach ($blocked as $b) {
            $bb = (int) $b;
            if (!empty($assignedSet[$bb])) {
                $conflictWith = $bb;
                break;
            }
        }

        if ($conflictWith === null) {
            return ['has_conflict' => false];
        }

        $names = $this->resolveNames([$selectedId, $conflictWith]);
        $selName = $names[$selectedId] ?? (string) $selectedId;
        $exName  = $names[$conflictWith] ?? (string) $conflictWith;

        return [
            'has_conflict' => true,
            'with_id'      => $conflictWith,
            'names'        => [
                'selected' => $selName,
                'existing' => $exName,
            ],
        ];
    }
}
