<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create attendance tables for classlists:
     * - tb_mas_classlist_attendance_date: one row per classlist per date
     * - tb_mas_classlist_attendance: one row per student per date
     *
     * Notes:
     * - Defaults to legacy-style PK names (intID) and integer columns to align with tb_mas_* tables.
     * - No foreign keys are enforced to remain compatible with mixed legacy schemas; indexes provided for performance.
     */
    public function up(): void
    {
        // Per-classlist dates
        Schema::create('tb_mas_classlist_attendance_date', function (Blueprint $table) {
            $table->increments('intID');
            $table->unsignedInteger('intClassListID');
            $table->date('attendance_date');
            $table->string('period', 16); // 'midterm' or 'finals'
            $table->unsignedInteger('created_by')->nullable();
            $table->dateTime('created_at')->nullable();

            // Indexes
            $table->unique(['intClassListID', 'attendance_date', 'period'], 'ux_classlist_date');
            $table->index('intClassListID', 'ix_attd_date_classlist');
        });

        // Per-student marks for a given date
        Schema::create('tb_mas_classlist_attendance', function (Blueprint $table) {
            $table->increments('intID');
            $table->unsignedInteger('intAttendanceDateID'); // FK -> tb_mas_classlist_attendance_date.intID (not enforced)
            $table->unsignedInteger('intClassListID');      // Redundant for fast filtering
            $table->unsignedInteger('intCSID');             // FK -> tb_mas_classlist_student.intCSID (not enforced)
            $table->unsignedInteger('intStudentID');        // FK -> tb_mas_users.intID (not enforced)

            // Null = unset (default on date creation), true = present, false = absent
            $table->boolean('is_present')->nullable();

            // Optional remarks, typically used when absent
            $table->string('remarks', 255)->nullable();

            // Audit
            $table->unsignedInteger('marked_by')->nullable();
            $table->dateTime('marked_at')->nullable();

            // Indexes
            $table->unique(['intAttendanceDateID', 'intCSID'], 'ux_attd_date_csid');
            $table->index('intAttendanceDateID', 'ix_attd_date');
            $table->index('intClassListID', 'ix_attd_classlist');
            $table->index('intCSID', 'ix_attd_csid');
            $table->index('intStudentID', 'ix_attd_student');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_mas_classlist_attendance');
        Schema::dropIfExists('tb_mas_classlist_attendance_date');
    }
};
