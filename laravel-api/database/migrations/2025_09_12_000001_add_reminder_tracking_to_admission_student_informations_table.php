<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('admission_student_informations', function (Blueprint $table) {
            $table->timestamp('last_inactive_reminder_sent')->nullable()->after('acceptance_letter_sent_date');
            $table->timestamp('last_reservation_reminder_sent')->nullable()->after('last_inactive_reminder_sent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admission_student_informations', function (Blueprint $table) {
            $table->dropColumn(['last_inactive_reminder_sent', 'last_reservation_reminder_sent']);
        });
    }
};
