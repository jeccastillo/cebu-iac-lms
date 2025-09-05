<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_sys_alerts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title', 255)->nullable();
            $table->text('message');
            // Use ENUM for strict types; adjust if your DB driver doesn't support enums
            $table->enum('type', ['success', 'warning', 'error', 'info'])->default('info');

            // Targeting
            $table->boolean('target_all')->default(false);
            $table->json('role_codes')->nullable();   // e.g., ["admin","registrar"]
            $table->json('campus_ids')->nullable();   // e.g., [1,2,3]

            // Scheduling window
            $table->dateTimeTz('starts_at')->nullable();
            $table->dateTimeTz('ends_at')->nullable();

            // Flags
            $table->tinyInteger('intActive')->default(1);         // 1 = active, 0 = disabled
            $table->tinyInteger('system_generated')->default(0);  // 1 if created by system

            // Audit
            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();

            // Helpful indexes
            $table->index(['intActive']);
            $table->index(['starts_at']);
            $table->index(['ends_at']);
            $table->index(['system_generated']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_sys_alerts');
    }
};
