<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use App\Models\PaymentDescription;
use App\Models\StudentDeficiency;
use App\Models\FacultyDepartment;
use App\Services\StudentBillingService;

class DepartmentDeficiencyService
{
     /**
      * List deficiencies with optional filters.
      *
      * @param string|null $studentNumber
      * @param int|null    $studentId
      * @param int|null    $syid
      * @param string|null $departmentCode
      * @param int|null    $campusId
      * @param int         $page
      * @param int         $perPage
      * @param array<int,string> $allowedDepartments optional allow-list (lowercased department codes)
      * @return array{ items: array<int,array>, meta: array<string,int> }
      */
    public function list(?string $studentNumber, ?int $studentId, ?int $syid, ?string $departmentCode, ?int $campusId, int $page = 1, int $perPage = 25, array $allowedDepartments = []): array
    {
        $sid = $this->resolveStudentId($studentNumber, $studentId);

        $q = DB::table('tb_mas_student_deficiencies as d')
            ->select([
                'd.intID as id',
                'd.intStudentID  as student_id',
                'd.syid',
                'd.department_code',
                'd.payment_description_id',
                'd.billing_id',
                'd.amount',
                'd.description',
                'd.remarks',
                'd.posted_at',
                'd.campus_id',
                'd.created_by',
                'd.updated_by',
                'd.created_at',
                'd.updated_at',
                DB::raw('pd.name as payment_description_name'),
                // Student identity
                DB::raw('u.strStudentNumber as student_number'),
                DB::raw('u.strLastname as student_last_name'),
                DB::raw('u.strFirstname as student_first_name'),
                DB::raw('u.strMiddlename as student_middle_name'),
            ])
            ->leftJoin('payment_descriptions as pd', 'pd.intID', '=', 'd.payment_description_id')
            ->leftJoin('tb_mas_users as u', 'u.intID', '=', 'd.intStudentID')
            ->orderBy('d.created_at', 'desc')
            ->orderBy('d.intID', 'desc');

        if ($sid) {
            $q->where('d.intStudentID', $sid);
        }
        if ($syid !== null) {
            $q->where('d.syid', (int) $syid);
        }
        if ($departmentCode !== null && $departmentCode !== '') {
            $q->whereRaw('LOWER(d.department_code) = ?', [strtolower(trim($departmentCode))]);
        }
        if ($campusId !== null) {
            $q->where(function ($qb) use ($campusId) {
                $qb->whereNull('d.campus_id')->orWhere('d.campus_id', $campusId);
            });
        }
        if (!empty($allowedDepartments)) {
            $allowed = array_values(array_unique(array_map(function ($c) {
                return strtolower(trim((string) $c));
            }, $allowedDepartments)));
            $q->whereIn('d.department_code', $allowed);
        }

        $page = max(1, (int) $page);
        $perPage = max(1, (int) $perPage);
        $total = (int) $q->count();
        $rows = $q->forPage($page, $perPage)->get();

        return [
            'items' => array_map(fn($r) => $this->normalizeRow((array) $r), $rows->all()),
            'meta'  => [
                'page'     => $page,
                'per_page' => $perPage,
                'total'    => $total,
            ],
        ];
    }

    /**
     * Get single deficiency by id.
     */
    public function get(int $id): ?array
    {
        $r = DB::table('tb_mas_student_deficiencies as d')
            ->leftJoin('payment_descriptions as pd', 'pd.intID', '=', 'd.payment_description_id')
            ->leftJoin('tb_mas_users as u', 'u.intID', '=', 'd.intStudentID')
            ->select([
                'd.intID as id',
                'd.intStudentID as student_id',
                'd.syid',
                'd.department_code',
                'd.payment_description_id',
                'd.billing_id',
                'd.amount',
                'd.description',
                'd.remarks',
                'd.posted_at',
                'd.campus_id',
                'd.created_by',
                'd.updated_by',
                'd.created_at',
                'd.updated_at',
                DB::raw('pd.name as payment_description_name'),
                // Student identity
                DB::raw('u.strStudentNumber as student_number'),
                DB::raw('u.strLastname as student_last_name'),
                DB::raw('u.strFirstname as student_first_name'),
                DB::raw('u.strMiddlename as student_middle_name'),
            ])
            ->where('d.intID', $id)
            ->first();

        return $r ? $this->normalizeRow((array) $r) : null;
    }

    /**
     * Create deficiency:
     * - Optionally create PaymentDescription inline
     * - Create StudentBilling row (no invoice)
     * - Persist StudentDeficiency linked to billing_id and payment_description_id
     *
     * @param array<string,mixed> $payload
     * @param array{faculty_id:?int,campus_id:?int} $ctx
     * @return array<string,mixed>
     */
    public function store(array $payload, array $ctx = []): array
    {
        $facultyId = isset($ctx['faculty_id']) ? (int) $ctx['faculty_id'] : null;
        $campusId  = isset($ctx['campus_id']) ? (int) $ctx['campus_id'] : null;

        // Resolve student id
        $studentId = $this->resolveStudentId(
            $payload['student_number'] ?? null,
            isset($payload['student_id']) ? (int) $payload['student_id'] : null
        );
        if (!$studentId) {
            throw new \InvalidArgumentException('Invalid or missing student identifier.');
        }

        // Validate department code and authorization via Gate
        $dept = strtolower(trim((string) ($payload['department_code'] ?? '')));
        if ($dept === '') {
            throw new \InvalidArgumentException('department is required.');
        }
        // Authorize by explicitly checking faculty's assigned departments (more deterministic than Gate in header-based contexts)
        $actorFacultyId = $facultyId;
        if (!$actorFacultyId) {
            // Prefer middleware-injected faculty model
            try {
                $facAttr = request()->attributes->get('faculty');
                if ($facAttr && isset($facAttr->intID)) {
                    $actorFacultyId = (int) $facAttr->intID;
                }
            } catch (\Throwable $e) { /* ignore */ }
            // Fallback to header
            if (!$actorFacultyId) {
                $hdr = request()->header('X-Faculty-ID');
                if ($hdr !== null && $hdr !== '' && is_numeric($hdr)) {
                    $actorFacultyId = (int) $hdr;
                }
            }
        }
        if (!$actorFacultyId) {
            throw new \RuntimeException('Forbidden: missing faculty context');
        }
        $allowedDepartments = \App\Models\FacultyDepartment::allowedForFaculty($actorFacultyId, null);
        if (!in_array($dept, $allowedDepartments, true)) {
            throw new \RuntimeException('Forbidden: not authorized for department ' . $dept);
        }

        // Resolve or create PaymentDescription
        $pdId = null;
        $pdName = null;
        if (!empty($payload['payment_description_id'])) {
            $pdId = (int) $payload['payment_description_id'];
            $pd = PaymentDescription::find($pdId);
            if ($pd) {
                $pdName = (string) $pd->name;
            } else {
                throw new \InvalidArgumentException('payment_description_id not found.');
            }
        } elseif (!empty($payload['new_payment_description']) && is_array($payload['new_payment_description'])) {
            $npd = $payload['new_payment_description'];
            $name = trim((string) ($npd['name'] ?? ''));
            if ($name === '') {
                throw new \InvalidArgumentException('new_payment_description.name is required.');
            }
            $pdCampus = isset($npd['campus_id']) && $npd['campus_id'] !== '' ? (int) $npd['campus_id'] : $campusId;
            $pd = PaymentDescription::create([
                'name'      => $name,
                'amount'    => isset($npd['amount']) && $npd['amount'] !== '' ? (float) $npd['amount'] : null,
                'campus_id' => $pdCampus,
            ]);
            $pdId = (int) $pd->intID;
            $pdName = (string) $pd->name;
        }

        // Description and amount
        $desc = $pdName ?? (string) ($payload['description'] ?? '');
        if ($desc === '') {
            throw new \InvalidArgumentException('description is required when no payment description is selected.');
        }
        // Amount: required and non-zero; default to PD amount when not provided
        $amount = $payload['amount'] ?? null;
        if ($amount === null || $amount === '') {
            if (isset($pd) && isset($pd->amount)) {
                $amount = (float) $pd->amount;
            }
        }
        $amount = (float) $amount;
        if (abs($amount) < 0.00001) {
            throw new \InvalidArgumentException('amount must be non-zero.');
        }

        $postedAt = $payload['posted_at'] ?? null;
        $remarksIn = $payload['remarks'] ?? null;
        $syid = (int) ($payload['term'] ?? $payload['syid'] ?? 0);
        if ($syid <= 0) {
            throw new \InvalidArgumentException('term (syid) is required.');
        }

        // Create within transaction: billing then deficiency
        return DB::transaction(function () use ($studentId, $syid, $desc, $amount, $postedAt, $remarksIn, $facultyId, $dept, $pdId, $campusId) {
            /** @var \App\Services\StudentBillingService $billingSvc */
            $billingSvc = app(StudentBillingService::class);

            // Remarks: tag department context
            $remarks = $this->mergeRemarks((string) ($remarksIn ?? ''), 'Deficiency: ' . strtoupper($dept));

            // 1) Create billing (no invoice generation here)
            $billing = $billingSvc->create([
                'intStudentID' => (int) $studentId,
                'syid'         => (int) $syid,
                'description'  => (string) $desc,
                'amount'       => (float) $amount,
                'posted_at'    => $postedAt,
                'remarks'      => $remarks,
            ], $facultyId);

            $billingId = (int) ($billing['id'] ?? 0);
            if ($billingId <= 0) {
                // Fallback: attempt to resolve last insert (should not happen)
                $last = DB::table('tb_mas_student_billing')->where([
                    ['intStudentID', '=', $studentId],
                    ['syid', '=', $syid],
                    ['description', '=', $desc],
                    ['amount', '=', $amount],
                ])->orderBy('intID', 'desc')->first();
                $billingId = $last ? (int) $last->intID : 0;
            }
            if ($billingId <= 0) {
                throw new \RuntimeException('Failed to create billing record.');
            }

            // 2) Create deficiency row
            $nowCampus = $campusId;
            $defId = DB::table('tb_mas_student_deficiencies')->insertGetId([
                'intStudentID'           => (int) $studentId,
                'syid'                   => (int) $syid,
                'department_code'        => strtolower($dept),
                'payment_description_id' => $pdId,
                'billing_id'             => $billingId,
                'amount'                 => round((float) $amount, 2),
                'description'            => (string) $desc,
                'remarks'                => $remarks,
                'posted_at'              => $postedAt,
                'campus_id'              => $nowCampus,
                'created_by'             => $facultyId,
                'updated_by'             => $facultyId,
                'created_at'             => now()->toDateTimeString(),
                'updated_at'             => now()->toDateTimeString(),
            ]);

            $row = DB::table('tb_mas_student_deficiencies')->where('intID', $defId)->first();
            return $this->normalizeRow((array) $row);
        });
    }

    /**
     * Update deficiency (amount/posted_at/remarks) and reflect to linked billing row.
     *
     * @param int $id
     * @param array<string,mixed> $payload
     * @param int|null $actorFacultyId
     * @return array<string,mixed>|null
     */
    public function update(int $id, array $payload, ?int $actorFacultyId = null): ?array
    {
        $existing = DB::table('tb_mas_student_deficiencies')->where('intID', $id)->first();
        if (!$existing) {
            return null;
        }
        $update = [];
        $billingUpdate = [];

        if (array_key_exists('amount', $payload)) {
            $amt = (float) $payload['amount'];
            if (abs($amt) < 0.00001) {
                throw new \InvalidArgumentException('amount must be non-zero.');
            }
            $update['amount'] = round($amt, 2);
            $billingUpdate['amount'] = round($amt, 2);
        }
        if (array_key_exists('posted_at', $payload)) {
            $update['posted_at'] = $payload['posted_at'];
            $billingUpdate['posted_at'] = $payload['posted_at'];
        }
        if (array_key_exists('remarks', $payload)) {
            $update['remarks'] = (string) $payload['remarks'];
            $billingUpdate['remarks'] = (string) $payload['remarks'];
        }

        if (empty($update)) {
            return $this->get($id);
        }

        $update['updated_by'] = $actorFacultyId;
        $update['updated_at'] = now()->toDateTimeString();

        DB::transaction(function () use ($id, $update, $billingUpdate, $existing, $actorFacultyId) {
            DB::table('tb_mas_student_deficiencies')->where('intID', $id)->update($update);

            $billingId = (int) ($existing->billing_id ?? 0);
            if ($billingId > 0 && !empty($billingUpdate)) {
                // Use billing service for audit fields
                /** @var \App\Services\StudentBillingService $billingSvc */
                $billingSvc = app(StudentBillingService::class);
                $billingSvc->update($billingId, $billingUpdate, $actorFacultyId);
            }
        });

        return $this->get($id);
    }

    /**
     * Delete deficiency entry (does not delete linked billing).
     */
    public function destroy(int $id): void
    {
        DB::table('tb_mas_student_deficiencies')->where('intID', $id)->delete();
    }

    // --------------------------
    // Helpers
    // --------------------------

    protected function resolveStudentId(?string $studentNumber, ?int $studentId): ?int
    {
        if (!empty($studentId)) {
            return (int) $studentId;
        }
        if (!empty($studentNumber)) {
            $u = DB::table('tb_mas_users')->where('strStudentNumber', $studentNumber)->first();
            return $u ? (int) $u->intID : null;
        }
        return null;
    }

    protected function normalizeRow(array $r): array
    {
        $studentId = isset($r['student_id']) ? (int)$r['student_id'] : (int)($r['intStudentID'] ?? 0);
        $sn = isset($r['student_number']) ? (string)$r['student_number'] : null;
        $ln = isset($r['student_last_name']) ? trim((string)$r['student_last_name']) : '';
        $fn = isset($r['student_first_name']) ? trim((string)$r['student_first_name']) : '';
        $mn = isset($r['student_middle_name']) ? trim((string)$r['student_middle_name']) : '';
        $full = trim(implode(' ', array_filter([
            $ln !== '' ? ($ln . ',') : '',
            $fn,
            $mn
        ])));

        return [
            'id'                        => isset($r['id']) ? (int) $r['id'] : (int) ($r['intID'] ?? 0),
            'student_id'                => $studentId,
            'student_number'            => $sn,
            'student_first_name'        => $fn,
            'student_middle_name'       => $mn,
            'student_last_name'         => $ln,
            'student_full_name'         => $full !== '' ? $full : null,
            'syid'                      => isset($r['syid']) ? (int) $r['syid'] : null,
            'department_code'           => isset($r['department_code']) ? (string) $r['department_code'] : null,
            'payment_description_id'    => isset($r['payment_description_id']) ? (int) $r['payment_description_id'] : null,
            'payment_description_name'  => isset($r['payment_description_name']) ? (string) $r['payment_description_name'] : null,
            'billing_id'                => isset($r['billing_id']) ? (int) $r['billing_id'] : null,
            'amount'                    => isset($r['amount']) ? round((float) $r['amount'], 2) : null,
            'description'               => isset($r['description']) ? (string) $r['description'] : null,
            'remarks'                   => isset($r['remarks']) ? (string) $r['remarks'] : null,
            'posted_at'                 => isset($r['posted_at']) ? (string) $r['posted_at'] : null,
            'campus_id'                 => isset($r['campus_id']) ? (int) $r['campus_id'] : null,
            'created_by'                => isset($r['created_by']) ? (int) $r['created_by'] : null,
            'updated_by'                => isset($r['updated_by']) ? (int) $r['updated_by'] : null,
            'created_at'                => isset($r['created_at']) ? (string) $r['created_at'] : null,
            'updated_at'                => isset($r['updated_at']) ? (string) $r['updated_at'] : null,
        ];
    }

    protected function mergeRemarks(string $existing, string $tag): string
    {
        $existing = trim($existing);
        $tag = trim($tag);
        if ($existing === '') return $tag;
        if (stripos($existing, $tag) !== false) return $existing;
        return $existing . ' | ' . $tag;
        }
}
