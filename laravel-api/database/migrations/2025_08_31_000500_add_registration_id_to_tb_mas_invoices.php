<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tb_mas_invoices')) {
            Schema::table('tb_mas_invoices', function (Blueprint $table) {
                if (!Schema::hasColumn('tb_mas_invoices', 'registration_id')) {
                    $table->unsignedInteger('registration_id')->nullable()->after('syid')->index();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tb_mas_invoices') && Schema::hasColumn('tb_mas_invoices', 'registration_id')) {
            Schema::table('tb_mas_invoices', function (Blueprint $table) {
                $table->dropColumn('registration_id');
            });
        }
    }
};
