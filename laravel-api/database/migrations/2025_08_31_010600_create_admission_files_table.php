<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_files', function (Blueprint $table) {
            $table->id();
            $table->string('filename');       // stored filename without extension (timestamp)
            $table->string('orig_filename')->nullable(); // original client filename
            $table->string('filetype');       // extension
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_files');
    }
};
