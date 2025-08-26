<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tb_mas_system_log')) {
            Schema::create('tb_mas_system_log', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('entity', 100);
                $table->unsignedBigInteger('entity_id')->nullable()->index();
                $table->string('action', 50);
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->string('method', 10)->nullable();
                $table->string('path', 255)->nullable();
                $table->timestamps();

                $table->index(['entity', 'entity_id'], 'idx_entity_entity_id');
                $table->index(['created_at'], 'idx_created_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tb_mas_system_log')) {
            Schema::dropIfExists('tb_mas_system_log');
        }
    }
};
