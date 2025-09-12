<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add reminder tracking columns to tb_mas_applicant_data table.
     */
    public function up()
    {
        Schema::table('tb_mas_applicant_data', function (Blueprint $table) {
            $table->timestamp('last_inactive_reminder_sent')->nullable()->after('updated_at');
            $table->timestamp('last_reservation_reminder_sent')->nullable()->after('last_inactive_reminder_sent');
            $table->integer('inactive_reminder_count')->default(0)->after('last_reservation_reminder_sent');
            $table->integer('reservation_reminder_count')->default(0)->after('inactive_reminder_count');
        });
    }

    /**
     * Reverse the migration.
     */
    public function down()
    {
        Schema::table('tb_mas_applicant_data', function (Blueprint $table) {
            $table->dropColumn([
                'last_inactive_reminder_sent',
                'last_reservation_reminder_sent', 
                'inactive_reminder_count',
                'reservation_reminder_count'
            ]);
        });
    }
};
