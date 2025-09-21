<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tb_mas_payee')) {
            Schema::create('tb_mas_payee', function (Blueprint $table) {
                $table->increments('id');
                $table->string('id_number', 40)->unique();
                $table->string('firstname', 99);
                $table->string('lastname', 99);
                $table->string('middlename', 99)->nullable();
                $table->string('tin', 40)->nullable();
                $table->string('address', 255)->nullable();
                $table->string('contact_number', 50)->nullable();
                $table->string('email', 150)->nullable();
                // No timestamps per model definition (public $timestamps = false)
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tb_mas_payee')) {
            Schema::drop('tb_mas_payee');
        }
    }
};
