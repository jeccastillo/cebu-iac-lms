<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tb_mas_student_deficiencies')) {
            return;
        }

        Schema::table('tb_mas_student_deficiencies', function (Blueprint $table) {
            // Core fields expected by service code
            if (!Schema::hasColumn('tb_mas_student_deficiencies', 'amount')) {
                $table->decimal('amount', 12, 2)->nullable()->after('billing_id');
            }
            if (!Schema::hasColumn('tb_mas_student_deficiencies', 'description')) {
                $table->string('description', 255)->nullable()->after('amount');
            }
            if (!Schema::hasColumn('tb_mas_student_deficiencies', 'remarks')) {
                $table->text('remarks')->nullable()->after('description');
            }
            if (!Schema::hasColumn('tb_mas_student_deficiencies', 'posted_at')) {
                $table->dateTime('posted_at')->nullable()->after('remarks');
            }
            if (!Schema::hasColumn('tb_mas_student_deficiencies', 'campus_id')) {
                $table->integer('campus_id')->nullable()->index()->after('posted_at');
            }
            if (!Schema::hasColumn('tb_mas_student_deficiencies', 'created_by')) {
                $table->integer('created_by')->nullable()->index()->after('campus_id');
            }
            if (!Schema::hasColumn('tb_mas_student_deficiencies', 'updated_by')) {
                $table->integer('updated_by')->nullable()->index()->after('created_by');
            }
            // Timestamps if missing
            if (!Schema::hasColumn('tb_mas_student_deficiencies', 'created_at')) {
                $table->timestamp('created_at')->nullable()->after('updated_by');
            }
            if (!Schema::hasColumn('tb_mas_student_deficiencies', 'updated_at')) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('tb_mas_student_deficiencies')) {
            return;
        }

        Schema::table('tb_mas_student_deficiencies', function (Blueprint $table) {
            if (Schema::hasColumn('tb_mas_student_deficiencies', 'updated_at')) {
                $table->dropColumn('updated_at');
            }
            if (Schema::hasColumn('tb_mas_student_deficiencies', 'created_at')) {
                $table->dropColumn('created_at');
            }
            if (Schema::hasColumn('tb_mas_student_deficiencies', 'updated_by')) {
                $table->dropColumn('updated_by');
            }
            if (Schema::hasColumn('tb_mas_student_deficiencies', 'created_by')) {
                $table->dropColumn('created_by');
            }
            if (Schema::hasColumn('tb_mas_student_deficiencies', 'campus_id')) {
                $table->dropColumn('campus_id');
            }
            if (Schema::hasColumn('tb_mas_student_deficiencies', 'posted_at')) {
                $table->dropColumn('posted_at');
            }
            if (Schema::hasColumn('tb_mas_student_deficiencies', 'remarks')) {
                $table->dropColumn('remarks');
            }
            if (Schema::hasColumn('tb_mas_student_deficiencies', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('tb_mas_student_deficiencies', 'amount')) {
                $table->dropColumn('amount');
            }
        });
    }
};
