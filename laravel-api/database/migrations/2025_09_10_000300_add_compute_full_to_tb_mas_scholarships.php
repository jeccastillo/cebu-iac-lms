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

        if (!Schema::hasColumn($this->table, 'compute_full')) {
            Schema::table($this->table, function (Blueprint $table) {
                // Try to position after max_stacks or status if available; otherwise append.
                try {
                    if (Schema::hasColumn($this->table, 'max_stacks')) {
                        $table->boolean('compute_full')->default(true)->after('max_stacks');
                        return;
                    }
                    if (Schema::hasColumn($this->table, 'status')) {
                        $table->boolean('compute_full')->default(true)->after('status');
                        return;
                    }
                } catch (\Throwable $e) {
                    // fall through to append
                }

                // Fallback append at the end
                $table->boolean('compute_full')->default(true);
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable($this->table)) {
            return;
        }

        if (Schema::hasColumn($this->table, 'compute_full')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->dropColumn('compute_full');
            });
        }
    }
};
