<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tb_mas_campuses') && !Schema::hasColumn('tb_mas_campuses', 'status')) {
            Schema::table('tb_mas_campuses', function (Blueprint $table) {
                $table->enum('status', ['active', 'inactive'])
                      ->default('active')
                      ->after('description');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tb_mas_campuses') && Schema::hasColumn('tb_mas_campuses', 'status')) {
            Schema::table('tb_mas_campuses', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
