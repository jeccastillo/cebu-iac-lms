<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tb_mas_faculty_roles')) {
            Schema::create('tb_mas_faculty_roles', function (Blueprint $table) {
                // Legacy-style PK
                $table->increments('intID');

                // Foreign keys (not enforcing constraints for legacy engine compatibility)
                $table->unsignedInteger('intFacultyID');
                $table->unsignedInteger('intRoleID');

                // Indices
                $table->unique(['intFacultyID', 'intRoleID'], 'uniq_faculty_role');
                $table->index('intFacultyID', 'idx_faculty_roles_faculty');
                $table->index('intRoleID', 'idx_faculty_roles_role');

                // No timestamps per legacy style
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_mas_faculty_roles');
    }
};
