<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tb_mas_roles')) {
            Schema::create('tb_mas_roles', function (Blueprint $table) {
                // Legacy-style primary key naming
                $table->increments('intRoleID');
                $table->string('strCode', 64)->unique();
                $table->string('strName', 128);
                $table->text('strDescription')->nullable();
                $table->tinyInteger('intActive')->default(1);

                // No timestamps to match legacy style
                $table->index(['intActive'], 'idx_roles_active');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_mas_roles');
    }
};
