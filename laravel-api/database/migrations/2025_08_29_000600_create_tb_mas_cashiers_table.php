<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tb_mas_cashiers')) {
            Schema::create('tb_mas_cashiers', function (Blueprint $table) {
                // Legacy PK naming to match Eloquent model (intID)
                $table->increments('intID');

                // References
                $table->unsignedBigInteger('user_id')->nullable()->index();   // joins to users.id
                $table->unsignedInteger('campus_id')->nullable()->index();    // optional campus scope

                // OR range and current pointer
                $table->unsignedBigInteger('or_start')->nullable();
                $table->unsignedBigInteger('or_end')->nullable();
                $table->unsignedBigInteger('or_current')->nullable();

                // Invoice range and current pointer
                $table->unsignedBigInteger('invoice_start')->nullable();
                $table->unsignedBigInteger('invoice_end')->nullable();
                $table->unsignedBigInteger('invoice_current')->nullable();

                // Optional legacy counters (kept for compatibility)
                $table->unsignedBigInteger('or_used')->nullable();
                $table->unsignedBigInteger('invoice_used')->nullable();

                // Flags
                $table->tinyInteger('temporary_admin')->default(0);

                // Optional timestamps (model has timestamps=false; leaving nullable for legacy)
                $table->nullableTimestamps();

                // Helpful composite indexes
                $table->index(['or_start', 'or_end']);
                $table->index(['invoice_start', 'invoice_end']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_mas_cashiers');
    }
};
