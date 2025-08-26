<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // No-op: tb_mas_user_roles is not used. Roles are assigned via tb_mas_faculty_roles.
    }

    public function down(): void
    {
        // No-op
    }
};
