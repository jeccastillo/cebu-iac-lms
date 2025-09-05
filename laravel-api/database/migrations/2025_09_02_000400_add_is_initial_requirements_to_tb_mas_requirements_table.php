<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tb_mas_requirements', function (Blueprint $table) {
            if (!Schema::hasColumn('tb_mas_requirements', 'is_initial_requirements')) {
                $table->boolean('is_initial_requirements')->default(false)->index()->after('is_foreign');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tb_mas_requirements', function (Blueprint $table) {
            if (Schema::hasColumn('tb_mas_requirements', 'is_initial_requirements')) {
                // Drop the index explicitly by its conventional name, then drop the column
                $table->dropIndex('tb_mas_requirements_is_initial_requirements_index');
                $table->dropColumn('is_initial_requirements');
            }
        });
    }
};
