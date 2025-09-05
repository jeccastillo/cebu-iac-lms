<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('payment_modes')) {
            Schema::create('payment_modes', function (Blueprint $table) {
                // id: int(11) unsigned auto-increment primary key
                $table->increments('id');

                $table->string('name', 64);
                $table->text('image_url')->nullable();
                $table->string('type', 12);
                $table->float('charge')->default(0);
                $table->boolean('is_active')->default(1);
                $table->string('pchannel', 32);
                $table->string('pmethod', 32);
                $table->boolean('is_nonbank')->default(0);

                // created_at, updated_at
                $table->timestamps();

                // deleted_at
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_modes');
    }
};
