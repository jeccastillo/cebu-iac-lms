<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ScholarshipMEService
{
    /**
     * Normalize a pair (a,b) so that a < b and both are positive ints.
     *
     * @return array{a:int,b:int}
     */
    private function normalizePair(int $id1, int $id2): array
    {
        $a = (int) $id1;
        $b = (int) $id2;
        if ($a <= 0 || $b <= 0) {
            throw new \InvalidArgumentException('Invalid scholarship/discount id(s).');
        }
        if ($a === $b) {
            throw new \InvalidArgumentException('Cannot tag an item as mutually exclusive with itself.');
        }
        if ($a > $b) {
            [$a, $b] = [$b, $a];
        }
        return ['a' => $a, 'b' => $b];
    }

    /**
     * Ensure both scholarship/discount rows exist.
     */
    private function ensureExists(int $id): void
    {
        $exists = DB::table('tb_mas_scholarships')->where('intID', $id)->exists();
        if (!$exists) {
            throw new \InvalidArgumentException('Scholarship/discount not found: ' . $id);
        }
    }

    /**
     * List active mutual-exclusion pairs for a base id.
     *
     * @return array<int, array{id:int,name:string,deduction_type:?string,status:?string}>
     */
    public function list(int $baseId): array
    {
        $baseId = (int) $baseId;
        if ($baseId <= 0) {
            return [];
        }

        $rows = DB::table('tb_mas_scholarship_me as me')
            ->join('tb_mas_scholarships as scA', 'scA.intID', '=', 'me.discount_id_a')
            ->join('tb_mas_scholarships as scB', 'scB.intID', '=', 'me.discount_id_b')
            ->where(function ($q) use ($baseId) {
                $q->where('me.discount_id_a', $baseId)
                  ->orWhere('me.discount_id_b', $baseId);
            })
            ->when(Schema::hasColumn('tb_mas_scholarship_me', 'status'), function ($q) {
                $q->where('me.status', 'active');
            })
            ->select(
                'me.discount_id_a',
                'me.discount_id_b',
                'scA.name as name_a',
                'scA.deduction_type as type_a',
                'scB.name as name_b',
                'scB.deduction_type as type_b'
            )
            ->orderBy('scA.name')
            ->orderBy('scB.name')
            ->get();

        $out = [];
        foreach ($rows as $r) {
            $otherId = ((int) $r->discount_id_a === $baseId) ? (int) $r->discount_id_b : (int) $r->discount_id_a;
            $name    = ((int) $r->discount_id_a === $baseId) ? (string) ($r->name_b ?? '') : (string) ($r->name_a ?? '');
            $type    = ((int) $r->discount_id_a === $baseId) ? (string) ($r->type_b ?? '') : (string) ($r->type_a ?? '');
            $out[] = [
                'id'             => $otherId,
                'name'           => $name,
                'deduction_type' => $type,
                'status'         => 'active',
            ];
        }
        return $out;
    }

    /**
     * Create or activate a mutual-exclusion pair.
     *
     * @return array{created:bool,status:string}
     */
    public function add(int $baseId, int $otherId): array
    {
        $pair = $this->normalizePair($baseId, $otherId);
        $this->ensureExists($pair['a']);
        $this->ensureExists($pair['b']);

        $table = 'tb_mas_scholarship_me';
        $exists = DB::table($table)->where([
            'discount_id_a' => $pair['a'],
            'discount_id_b' => $pair['b'],
        ])->first();

        if (!$exists) {
            DB::table($table)->insert([
                'discount_id_a' => $pair['a'],
                'discount_id_b' => $pair['b'],
                'status'        => 'active',
            ]);
            return ['created' => true, 'status' => 'active'];
        }

        // Reactivate if using soft status
        if (Schema::hasColumn($table, 'status')) {
            DB::table($table)->where([
                'discount_id_a' => $pair['a'],
                'discount_id_b' => $pair['b'],
            ])->update(['status' => 'active']);
            return ['created' => false, 'status' => 'active'];
        }

        return ['created' => false, 'status' => 'active'];
    }

    /**
     * Inactivate (preferred) or delete a mutual-exclusion pair.
     *
     * @return array{deleted:bool}
     */
    public function delete(int $baseId, int $otherId): array
    {
        $pair = $this->normalizePair($baseId, $otherId);
        $table = 'tb_mas_scholarship_me';

        if (Schema::hasColumn($table, 'status')) {
            $updated = DB::table($table)->where([
                'discount_id_a' => $pair['a'],
                'discount_id_b' => $pair['b'],
            ])->update(['status' => 'inactive']);
            return ['deleted' => $updated > 0];
        }

        $deleted = DB::table($table)->where([
            'discount_id_a' => $pair['a'],
            'discount_id_b' => $pair['b'],
        ])->delete();

        return ['deleted' => $deleted > 0];
    }
}
