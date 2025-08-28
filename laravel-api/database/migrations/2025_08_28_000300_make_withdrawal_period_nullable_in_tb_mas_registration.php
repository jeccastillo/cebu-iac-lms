<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tb_mas_registration')) {
            return;
        }

        // Ensure the column exists before attempting to alter it
        $row = DB::selectOne("
            SELECT COLUMN_TYPE, IS_NULLABLE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'tb_mas_registration'
              AND COLUMN_NAME = 'withdrawal_period'
        ");

        if (!$row) {
            // Column does not exist; nothing to do
            return;
        }

        // If it's already nullable, skip
        if (isset($row->IS_NULLABLE) && strtoupper($row->IS_NULLABLE) === 'YES') {
            return;
        }

        // Preserve existing column type (varchar/enum/int/etc.) and just drop NOT NULL
        $colType = $row->COLUMN_TYPE; // e.g., "varchar(16)" or "enum('before','start','end')" or "tinyint(1)"
        try {
            DB::statement("ALTER TABLE `tb_mas_registration` MODIFY `withdrawal_period` {$colType} NULL");
        } catch (\Throwable $e) {
            // Fallback: try generic varchar if type-preserving failed
            try {
                DB::statement("ALTER TABLE `tb_mas_registration` MODIFY `withdrawal_period` VARCHAR(32) NULL");
            } catch (\Throwable $e2) {
                // Give up; leave as-is
            }
        }

        // Normalize empty-string values to NULL
        try {
            DB::table('tb_mas_registration')
                ->where('withdrawal_period', '')
                ->update(['withdrawal_period' => null]);
        } catch (\Throwable $e) {
            // ignore
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('tb_mas_registration')) {
            return;
        }

        $row = DB::selectOne("
            SELECT COLUMN_TYPE, IS_NULLABLE, DATA_TYPE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'tb_mas_registration'
              AND COLUMN_NAME = 'withdrawal_period'
        ");

        if (!$row) {
            return;
        }

        // Before making NOT NULL again, replace NULLs with a safe default.
        // If enum -> use 'before' (part of allowed set). Otherwise use a sensible default.
        $defaultValue = 'before';
        if (isset($row->DATA_TYPE)) {
            $dt = strtolower($row->DATA_TYPE);
            if (in_array($dt, ['tinyint', 'smallint', 'int', 'bigint'])) {
                $defaultValue = 0;
            } elseif ($dt === 'varchar' || $dt === 'char' || $dt === 'text' || $dt === 'enum') {
                $defaultValue = 'before';
            }
        }

        try {
            DB::table('tb_mas_registration')
                ->whereNull('withdrawal_period')
                ->update(['withdrawal_period' => $defaultValue]);
        } catch (\Throwable $e) {
            // ignore
        }

        $colType = $row->COLUMN_TYPE;
        try {
            DB::statement("ALTER TABLE `tb_mas_registration` MODIFY `withdrawal_period` {$colType} NOT NULL");
        } catch (\Throwable $e) {
            // Fallback to varchar NOT NULL if type-preserving failed
            try {
                DB::statement("ALTER TABLE `tb_mas_registration` MODIFY `withdrawal_period` VARCHAR(32) NOT NULL");
            } catch (\Throwable $e2) {
                // ignore
            }
        }
    }
};
