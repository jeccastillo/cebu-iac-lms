<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add performance indexes for reminder system on tb_mas_applicant_data
     */
    public function up()
    {
        // Add indexes for better reminder query performance
        DB::statement('CREATE INDEX IF NOT EXISTS idx_applicant_data_status_updated ON tb_mas_applicant_data(status, updated_at)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_applicant_data_reminder_tracking ON tb_mas_applicant_data(last_inactive_reminder_sent, last_reservation_reminder_sent)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_applicant_data_user_status ON tb_mas_applicant_data(user_id, status)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_applicant_data_created_status ON tb_mas_applicant_data(created_at, status)');
    }

    /**
     * Remove the indexes
     */
    public function down()
    {
        DB::statement('DROP INDEX IF EXISTS idx_applicant_data_status_updated ON tb_mas_applicant_data');
        DB::statement('DROP INDEX IF EXISTS idx_applicant_data_reminder_tracking ON tb_mas_applicant_data');
        DB::statement('DROP INDEX IF EXISTS idx_applicant_data_user_status ON tb_mas_applicant_data');
        DB::statement('DROP INDEX IF EXISTS idx_applicant_data_created_status ON tb_mas_applicant_data');
    }
};
