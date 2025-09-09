<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected string $table = 'tb_mas_scholarships';

    public function up(): void
    {
        if (!Schema::hasTable($this->table)) {
            return;
        }

        if (!Schema::hasColumn($this->table, 'max_stacks')) {
            Schema::table($this->table, function (Blueprint $table) {
                // Place after status if present; otherwise appended at the end.
                try {
                    $table->unsignedTinyInteger('max_stacks')->default(1)->after('status');
                } catch (\Throwable $e) {
                    // Fallback in case 'status' column doesn't exist
                    $table->unsignedTinyInteger('max_stacks')->default(1);
                }
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable($this->table)) {
            return;
        }

        if (Schema::hasColumn($this->table, 'max_stacks')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->dropColumn('max_stacks');
            });
        }
    }
};
