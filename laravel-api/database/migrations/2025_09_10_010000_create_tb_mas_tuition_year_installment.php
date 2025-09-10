<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tb_mas_tuition_year_installment')) {
            Schema::create('tb_mas_tuition_year_installment', function (Blueprint $table) {
                $table->increments('id');
                // Link to tuition year (legacy PK is intID on tb_mas_tuition_year)
                $table->unsignedInteger('tuitionyear_id')->index();
                $table->string('code', 32);   // unique per tuitionyear
                $table->string('label', 64);
                $table->enum('dp_type', ['percent', 'fixed']);
                $table->decimal('dp_value', 10, 2)->default(0);
                $table->decimal('increase_percent', 5, 2)->default(0);
                $table->unsignedTinyInteger('installment_count')->default(5);
                $table->unsignedTinyInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                // Scope (optional): college, shs, both
                $table->string('level', 16)->nullable();
                $table->timestamps();

                $table->unique(['tuitionyear_id', 'code'], 'uniq_tuitionyear_code');
            });
        }

        // Seed default plans (standard, dp50, dp30) for existing tuition years
        try {
            $years = DB::table('tb_mas_tuition_year')->select('intID', 'installmentDP', 'installmentFixed', 'installmentIncrease')->get();
            foreach ($years as $ty) {
                $tid = (int) $ty->intID;
                // Skip if already has at least one plan
                $exists = DB::table('tb_mas_tuition_year_installment')->where('tuitionyear_id', $tid)->exists();
                if ($exists) {
                    continue;
                }

                $dpPercent = is_null($ty->installmentDP) ? 0 : (float) $ty->installmentDP;
                $dpFixed   = is_null($ty->installmentFixed) ? 0 : (float) $ty->installmentFixed;
                $incPct    = is_null($ty->installmentIncrease) ? 0 : (float) $ty->installmentIncrease;

                // Standard plan: percent DP unless fixed configured
                DB::table('tb_mas_tuition_year_installment')->insert([
                    'tuitionyear_id'   => $tid,
                    'code'             => 'standard',
                    'label'            => 'Standard',
                    'dp_type'          => ($dpFixed > 0 ? 'fixed' : 'percent'),
                    'dp_value'         => ($dpFixed > 0 ? $dpFixed : $dpPercent),
                    'increase_percent' => $incPct,
                    'installment_count'=> 5,
                    'sort_order'       => 0,
                    'is_active'        => 1,
                    'level'            => null,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);

                // 50% plan
                DB::table('tb_mas_tuition_year_installment')->insert([
                    'tuitionyear_id'   => $tid,
                    'code'             => 'dp50',
                    'label'            => '50% Down Payment',
                    'dp_type'          => 'percent',
                    'dp_value'         => 50.00,
                    'increase_percent' => 9.00,
                    'installment_count'=> 5,
                    'sort_order'       => 1,
                    'is_active'        => 1,
                    'level'            => null,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);

                // 30% plan
                DB::table('tb_mas_tuition_year_installment')->insert([
                    'tuitionyear_id'   => $tid,
                    'code'             => 'dp30',
                    'label'            => '30% Down Payment',
                    'dp_type'          => 'percent',
                    'dp_value'         => 30.00,
                    'increase_percent' => 15.00,
                    'installment_count'=> 5,
                    'sort_order'       => 2,
                    'is_active'        => 1,
                    'level'            => null,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }
        } catch (\Throwable $e) {
            // Silent fail on seed to avoid migration hard-failure in environments with unexpected schema
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tb_mas_tuition_year_installment')) {
            Schema::dropIfExists('tb_mas_tuition_year_installment');
        }
    }
};
