<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tb_mas_requirements')) {
            Schema::create('tb_mas_requirements', function (Blueprint $table) {
                $table->increments('intID');
                $table->string('name', 255);
                // Accept string types; validation layer will restrict allowed values to: college|shs|grad
                $table->string('type', 32)->index();
                $table->boolean('is_foreign')->default(false)->index();
                $table->timestamps();

                $table->unique('name', 'uq_requirements_name');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_mas_requirements');
    }
};
