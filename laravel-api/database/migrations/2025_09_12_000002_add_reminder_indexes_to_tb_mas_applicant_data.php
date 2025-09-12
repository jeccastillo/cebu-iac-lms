<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add performance indexes for reminder system on tb_mas_applicant_data
     */
    public function up()
    {
        // Add indexes for better reminder query performance
        Schema::table('tb_mas_applicant_data', function (Blueprint $table) {
        	$table->index(['status', 'updated_at'], 'idx_applicant_data_status_updated');
        	$table->index(['last_inactive_reminder_sent', 'last_reservation_reminder_sent'], 'idx_applicant_data_reminder_tracking');
        	$table->index(['user_id', 'status'], 'idx_applicant_data_user_status');
        	$table->index(['created_at', 'status'], 'idx_applicant_data_created_status');
    	});
    }

    /**
     * Remove the indexes
     */
   public function down()
   {
	Schema::table('tb_mas_applicant_data', function (Blueprint $table) {
    		$table->dropIndex('idx_applicant_data_status_updated');
    		$table->dropIndex('idx_applicant_data_reminder_tracking');
    		$table->dropIndex('idx_applicant_data_user_status');
    		$table->dropIndex('idx_applicant_data_created_status');
    	});
   }
};
