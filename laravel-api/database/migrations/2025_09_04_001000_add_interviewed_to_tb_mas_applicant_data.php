<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add interviewed boolean flag to tb_mas_applicant_data (default false).
     * This indicates whether the applicant has been interviewed.
     */
    public function up(): void
    {
        if (!Schema::hasTable('tb_mas_applicant_data')) {
            return;
        }

        if (!Schema::hasColumn('tb_mas_applicant_data', 'interviewed')) {
            // Try to position after status, else after data; otherwise append
            $afterBase = null;
            if (Schema::hasColumn('tb_mas_applicant_data', 'status')) {
                $afterBase = 'status';
            } elseif (Schema::hasColumn('tb_mas_applicant_data', 'data')) {
                $afterBase = 'data';
            }

            Schema::table('tb_mas_applicant_data', function (Blueprint $table) use ($afterBase) {
                $col = $table->boolean('interviewed')->default(false);
                if ($afterBase) {
                    $col->after($afterBase);
                }
            });
        }
    }

    /**
     * Drop the interviewed flag (if present).
     */
    public function down(): void
    {
        if (!Schema::hasTable('tb_mas_applicant_data')) {
            return;
        }

        if (Schema::hasColumn('tb_mas_applicant_data', 'interviewed')) {
            Schema::table('tb_mas_applicant_data', function (Blueprint $table) {
                $table->dropColumn('interviewed');
            });
        }
    }
};
