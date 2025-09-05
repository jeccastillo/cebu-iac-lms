<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_sys_alert_reads', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('alert_id');
            $table->string('user_identifier', 255); // e.g. "username|loginType" (lowercase)
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('login_type', 50)->nullable(); // 'faculty'|'student'|etc
            $table->unsignedBigInteger('campus_id')->nullable();
            $table->dateTimeTz('dismissed_at');

            $table->timestamps();

            // FKs and indexes
            $table->foreign('alert_id')
                ->references('id')->on('tb_sys_alerts')
                ->onDelete('cascade');

            // Ensure stable uniqueness per user per alert
            $table->unique(['alert_id', 'user_identifier'], 'uniq_alert_user');

            $table->index(['user_identifier']);
            $table->index(['user_id']);
            $table->index(['login_type']);
            $table->index(['campus_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_sys_alert_reads');
    }
};
