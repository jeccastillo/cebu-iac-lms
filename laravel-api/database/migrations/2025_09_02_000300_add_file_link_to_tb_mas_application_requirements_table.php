<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tb_mas_application_requirements', function (Blueprint $table) {
            if (!Schema::hasColumn('tb_mas_application_requirements', 'file_link')) {
                $table->string('file_link')->nullable()->after('submitted_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tb_mas_application_requirements', function (Blueprint $table) {
            if (Schema::hasColumn('tb_mas_application_requirements', 'file_link')) {
                $table->dropColumn('file_link');
            }
        });
    }
};
