<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payment_descriptions') && !Schema::hasColumn('payment_descriptions', 'amount')) {
            Schema::table('payment_descriptions', function (Blueprint $table) {
                $table->decimal('amount', 12, 2)->nullable()->after('name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('payment_descriptions') && Schema::hasColumn('payment_descriptions', 'amount')) {
            Schema::table('payment_descriptions', function (Blueprint $table) {
                $table->dropColumn('amount');
            });
        }
    }
};
