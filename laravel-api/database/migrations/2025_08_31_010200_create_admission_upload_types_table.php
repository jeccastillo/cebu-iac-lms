<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_upload_types', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();     // e.g., 'valid_id', 'psa', 'tor', 'passport', 'payment', 'reservation_fee', etc.
            $table->string('label')->nullable(); // Display label
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_upload_types');
    }
};
