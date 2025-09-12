<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBillingIdToInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tb_mas_invoices', function (Blueprint $table) {
            $table->unsignedInteger('billing_id')->nullable()->after('intID')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_mas_invoices', function (Blueprint $table) {
            $table->dropColumn('billing_id');
        });
    }
}
