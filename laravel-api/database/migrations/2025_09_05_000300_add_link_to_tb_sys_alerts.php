<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tb_sys_alerts', function (Blueprint $table) {
            // Optional URL or route/hash to navigate when clicking the alert
            $table->string('link', 2048)->nullable()->after('message');
            $table->index(['link']);
        });
    }

    public function down(): void
    {
        Schema::table('tb_sys_alerts', function (Blueprint $table) {
            $table->dropIndex(['link']);
            $table->dropColumn('link');
        });
    }
};
