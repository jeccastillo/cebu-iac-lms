<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tb_mas_applicant_data')) {
            return;
        }

        Schema::table('tb_mas_applicant_data', function (Blueprint $table) {
            if (!Schema::hasColumn('tb_mas_applicant_data', 'hash')) {
                // Try to place after status -> data when present
                $after = null;
                if (Schema::hasColumn('tb_mas_applicant_data', 'status')) {
                    $after = 'status';
                } elseif (Schema::hasColumn('tb_mas_applicant_data', 'data')) {
                    $after = 'data';
                }

                $col = $table->string('hash', 64)->nullable()->unique('tb_mas_applicant_data_hash_unique');
                if ($after) {
                    $col->after($after);
                }
            }
        });

        // Backfill unique hashes for existing rows where null
        try {
            DB::table('tb_mas_applicant_data')
                ->select('id')
                ->whereNull('hash')
                ->orderBy('id')
                ->chunkById(500, function ($rows) {
                    foreach ($rows as $row) {
                        $hash = null;
                        $tries = 0;
                        do {
                            $hash = Str::random(40);
                            $exists = DB::table('tb_mas_applicant_data')->where('hash', $hash)->exists();
                            $tries++;
                        } while ($exists && $tries < 5);

                        DB::table('tb_mas_applicant_data')->where('id', $row->id)->update(['hash' => $hash]);
                    }
                }, 'id');
        } catch (\Throwable $e) {
            // Ignore backfill errors to avoid migration failure in constrained environments
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('tb_mas_applicant_data')) {
            return;
        }

        Schema::table('tb_mas_applicant_data', function (Blueprint $table) {
            if (Schema::hasColumn('tb_mas_applicant_data', 'hash')) {
                try {
                    $table->dropUnique('tb_mas_applicant_data_hash_unique');
                } catch (\Throwable $e) {
                    // ignore if unique not present
                }
                $table->dropColumn('hash');
            }
        });
    }
};
