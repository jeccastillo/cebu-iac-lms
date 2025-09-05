<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('previous_schools') && Schema::hasColumn('previous_schools', 'grade')) {
            Schema::table('previous_schools', function (Blueprint $table) {
                $table->dropColumn('grade');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('previous_schools') && !Schema::hasColumn('previous_schools', 'grade')) {
            Schema::table('previous_schools', function (Blueprint $table) {
                $table->integer('grade')->nullable();
            });
        }
    }
};
