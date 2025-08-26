<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tb_mas_campuses')) {
            Schema::create('tb_mas_campuses', function (Blueprint $table) {
                $table->increments('id');
                $table->string('campus_name')->unique();
                $table->text('description')->nullable();
                // No timestamps for legacy parity
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tb_mas_campuses')) {
            Schema::dropIfExists('tb_mas_campuses');
        }
    }
};
