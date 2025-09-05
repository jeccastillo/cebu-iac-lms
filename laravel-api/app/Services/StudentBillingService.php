<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class StudentBillingService
{
    /**
     * List student billing items for a student+term.
     * Supply either $studentId or $studentNumber (service resolves to intID).
     *
     * @return array<int, array{
     *   id:int, student_id:int, syid:int, description:string, amount:float,
     *   posted_at:?string, remarks:?string, created_at:?string, updated_at:?string
     * }>
     */
    public function list(?string $studentNumber, ?int $studentId, int $syid): array
    {
        $sid = $this->resolveStudentId($studentNumber, $studentId);
        if (!$sid) {
            return [];
        }

        $rows = DB::table('tb_mas_student_billing')
            ->where('intStudentID', $sid)
            ->where('syid', $syid)
            ->orderBy('posted_at', 'desc')
            ->orderBy('intID', 'desc')
            ->get();

        return $rows->map(function ($r) {
            return $this->normalizeRow((array) $r);
        })->toArray();
    }

    /**
     * Get single billing row by id.
     */
    public function get(int $id): ?array
    {
        $row = DB::table('tb_mas_student_billing')->where('intID', $id)->first();
        return $row ? $this->normalizeRow((array) $row) : null;
    }

    /**
     * Create a billing row.
     * $data expects keys: intStudentID, syid, description, amount, posted_at?, remarks?
     */
    public function create(array $data, ?int $actorId = null): array
    {
        $now = now()->toDateTimeString();
        $insert = [
            'intStudentID' => (int) $data['intStudentID'],
            'syid'         => (int) $data['syid'],
            'description'  => (string) $data['description'],
            'amount'       => (float) $data['amount'],
            'posted_at'    => $data['posted_at'] ?? null,
            'remarks'      => $data['remarks'] ?? null,
            'created_by'   => $actorId,
            'updated_by'   => $actorId,
            'created_at'   => $now,
            'updated_at'   => $now,
        ];

        $id = DB::table('tb_mas_student_billing')->insertGetId($insert);

        return $this->normalizeRow((array) DB::table('tb_mas_student_billing')->where('intID', $id)->first());
    }

    /**
     * Update a billing row.
     * $data allows: description, amount, posted_at, remarks
     */
    public function update(int $id, array $data, ?int $actorId = null): ?array
    {
        $allowed = ['description', 'amount', 'posted_at', 'remarks'];
        $update = [];
        foreach ($allowed as $k) {
            if (array_key_exists($k, $data)) {
                $update[$k] = $data[$k];
            }
        }
        if (count($update) === 0) {
            return $this->get($id);
        }

        $update['updated_by'] = $actorId;
        $update['updated_at'] = now()->toDateTimeString();

        DB::table('tb_mas_student_billing')->where('intID', $id)->update($update);

        $row = DB::table('tb_mas_student_billing')->where('intID', $id)->first();
        return $row ? $this->normalizeRow((array) $row) : null;
    }

    /**
     * Delete a billing row.
     */
    public function delete(int $id): void
    {
        DB::table('tb_mas_student_billing')->where('intID', $id)->delete();
    }

    /**
     * Resolve student intID via explicit $studentId or by $studentNumber.
     */
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

    /**
     * Normalize DB row to API shape.
     */
    protected function normalizeRow(array $r): array
    {
        return [
            'id'          => (int) ($r['intID'] ?? 0),
            'student_id'  => (int) ($r['intStudentID'] ?? 0),
            'syid'        => (int) ($r['syid'] ?? 0),
            'description' => (string) ($r['description'] ?? ''),
            'amount'      => round((float) ($r['amount'] ?? 0), 2),
            'posted_at'   => isset($r['posted_at']) ? (string) $r['posted_at'] : null,
            'remarks'     => isset($r['remarks']) ? (string) $r['remarks'] : null,
            'created_at'  => isset($r['created_at']) ? (string) $r['created_at'] : null,
            'updated_at'  => isset($r['updated_at']) ? (string) $r['updated_at'] : null,
        ];
    }
}
