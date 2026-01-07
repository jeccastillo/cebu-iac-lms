<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_mas_reservation_vehicle', function (Blueprint $table) {
            $table->id('intReservationVehicleID');
            $table->unsignedBigInteger('intVehicleID');
            $table->unsignedBigInteger('intFacultyID');
            $table->string('strPurpose', 255);
            $table->string('strDestination', 255);
            $table->date('dteReservationDate');
            $table->time('dteStartTime');
            $table->time('dteEndTime');
            $table->datetime('dteActualReturn')->nullable();
            $table->enum('enumStatus', ['pending', 'approved', 'rejected', 'in_use', 'completed', 'cancelled'])->default('pending');
            $table->unsignedBigInteger('intDriverID')->nullable();
            $table->text('strRemarks')->nullable();
            $table->datetime('dteCreated')->useCurrent();
            $table->unsignedBigInteger('intApprovedBy')->nullable();
            $table->datetime('dteApproved')->nullable();
            $table->unsignedBigInteger('intCreatedBy');
            $table->datetime('dteUpdated')->nullable();
            
            $table->index(['intVehicleID', 'dteReservationDate']);
            $table->index('enumStatus');
            $table->index('intFacultyID');
            
            $table->foreign('intVehicleID')->references('intVehicleID')->on('tb_mas_vehicles')->onDelete('cascade');
            $table->foreign('intFacultyID')->references('intID')->on('tb_mas_faculty')->onDelete('cascade');
            $table->foreign('intDriverID')->references('intID')->on('tb_mas_faculty')->onDelete('set null');
            $table->foreign('intApprovedBy')->references('intID')->on('tb_mas_faculty')->onDelete('set null');
            $table->foreign('intCreatedBy')->references('intID')->on('tb_mas_faculty')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_mas_reservation_vehicle');
    }
};
