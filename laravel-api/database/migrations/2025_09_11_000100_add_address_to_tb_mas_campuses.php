<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tb_mas_campuses') && !Schema::hasColumn('tb_mas_campuses', 'address')) {
            Schema::table('tb_mas_campuses', function (Blueprint $table) {
                $table->string('address', 255)
                      ->nullable()
                      ->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tb_mas_campuses') && Schema::hasColumn('tb_mas_campuses', 'address')) {
            Schema::table('tb_mas_campuses', function (Blueprint $table) {
                $table->dropColumn('address');
            });
        }
    }
};
