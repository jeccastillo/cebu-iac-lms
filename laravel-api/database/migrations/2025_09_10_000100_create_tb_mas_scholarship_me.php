<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected string $table = 'tb_mas_scholarship_me';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable($this->table)) {
            return;
        }

        Schema::create($this->table, function (Blueprint $table) {
            // PK (match legacy style: intID primary key)
            $table->increments('intID');

            // Canonicalized pair: (min, max) of two scholarship/discount ids
            $table->unsignedInteger('discount_id_a');
            $table->unsignedInteger('discount_id_b');

            // Enforcement toggle
            $table->string('status', 16)->default('active')->index('idx_me_status');

            // Unique pair to prevent duplicates
            $table->unique(['discount_id_a', 'discount_id_b'], 'idx_me_pair');

            // Optional: plain indexes for faster lookups by either column
            $table->index('discount_id_a', 'idx_me_a');
            $table->index('discount_id_b', 'idx_me_b');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable($this->table)) {
            return;
        }

        Schema::dropIfExists($this->table);
    }
};
