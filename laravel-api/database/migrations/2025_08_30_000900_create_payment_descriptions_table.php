<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('payment_descriptions')) {
            Schema::create('payment_descriptions', function (Blueprint $table) {
                // Primary key using legacy-style integer key naming
                $table->increments('intID');
                // Name field (unique), per requirement only two fields
                $table->string('name', 128)->unique();
                // No timestamps and no soft deletes to keep schema minimal
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_descriptions');
    }
};
