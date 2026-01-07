<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_mas_vehicles', function (Blueprint $table) {
            $table->id('intVehicleID');
            $table->string('strPlateNumber', 20)->unique();
            $table->string('strVehicleName', 100);
            $table->string('strBrand', 50);
            $table->string('strModel', 50);
            $table->integer('intYear');
            $table->enum('enumType', ['sedan', 'suv', 'van', 'pickup', 'minivan', 'coaster', 'bus', 'motorcycle', 'other'])->default('sedan');
            $table->integer('intCapacity')->default(4);
            $table->enum('enumTransmission', ['manual', 'automatic'])->default('automatic');
            $table->enum('enumFuelType', ['gasoline', 'diesel', 'electric', 'hybrid'])->default('gasoline');
            $table->string('strColor', 30)->nullable();
            $table->enum('enumStatus', ['available', 'in_use', 'maintenance', 'retired'])->default('available');
            $table->string('strLocation', 100)->nullable();
            $table->decimal('decCostPerDay', 10, 2)->default(0.00);
            $table->text('strNotes')->nullable();
            $table->datetime('dteCreated')->useCurrent();
            $table->datetime('dteUpdated')->nullable();
            $table->unsignedBigInteger('intCreatedBy');
            
            $table->index('enumStatus');
            $table->index('enumType');
            $table->foreign('intCreatedBy')->references('intID')->on('tb_mas_faculty')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_mas_vehicles');
    }
};
