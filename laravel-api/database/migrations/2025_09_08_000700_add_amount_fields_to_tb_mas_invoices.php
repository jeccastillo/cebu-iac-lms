<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tb_mas_invoices')) {
            Schema::table('tb_mas_invoices', function (Blueprint $table) {
                // Add columns only if they do not yet exist
                if (!Schema::hasColumn('tb_mas_invoices', 'withholding_tax_percentage')) {
                    $table->integer('withholding_tax_percentage')->nullable()->after('amount_total');
                }
                if (!Schema::hasColumn('tb_mas_invoices', 'invoice_amount')) {
                    // Use float with explicit precision/scale to align with screenshot
                    $table->float('invoice_amount', 10, 2)->nullable()->after('withholding_tax_percentage');
                }
                if (!Schema::hasColumn('tb_mas_invoices', 'invoice_amount_ves')) {
                    $table->double('invoice_amount_ves', 10, 2)->nullable()->after('invoice_amount');
                }
                if (!Schema::hasColumn('tb_mas_invoices', 'invoice_amount_vzrs')) {
                    $table->double('invoice_amount_vzrs', 10, 2)->nullable()->after('invoice_amount_ves');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tb_mas_invoices')) {
            Schema::table('tb_mas_invoices', function (Blueprint $table) {
                // Drop columns if they exist
                if (Schema::hasColumn('tb_mas_invoices', 'invoice_amount_vzrs')) {
                    $table->dropColumn('invoice_amount_vzrs');
                }
                if (Schema::hasColumn('tb_mas_invoices', 'invoice_amount_ves')) {
                    $table->dropColumn('invoice_amount_ves');
                }
                if (Schema::hasColumn('tb_mas_invoices', 'invoice_amount')) {
                    $table->dropColumn('invoice_amount');
                }
                if (Schema::hasColumn('tb_mas_invoices', 'withholding_tax_percentage')) {
                    $table->dropColumn('withholding_tax_percentage');
                }
            });
        }
    }
};
