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
        Schema::table('tb_mas_prerequisites', function (Blueprint $table) {
            $table->decimal('required_grade', 3, 2)
                  ->nullable()
                  ->after('program')
                  ->comment('Minimum grade required (e.g., 2.0). NULL means no specific grade requirement.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_mas_prerequisites', function (Blueprint $table) {
            $table->dropColumn('required_grade');
        });
    }
};