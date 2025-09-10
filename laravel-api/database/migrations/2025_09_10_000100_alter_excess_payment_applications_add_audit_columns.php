<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AlterExcessPaymentApplicationsAddAuditColumns extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('excess_payment_applications')) {
            $self = $this;
            Schema::table('excess_payment_applications', function (Blueprint $table) use ($self) {
                if (!Schema::hasColumn('excess_payment_applications', 'created_by')) {
                    // Use unsignedInteger to align with tb_mas_users.intID typical type
                    $table->unsignedInteger('created_by')->nullable()->after('status');
                }
                if (!Schema::hasColumn('excess_payment_applications', 'reverted_by')) {
                    $table->unsignedInteger('reverted_by')->nullable()->after('created_by');
                }
                if (!Schema::hasColumn('excess_payment_applications', 'reverted_at')) {
                    $table->timestamp('reverted_at')->nullable()->after('reverted_by');
                }
                if (!Schema::hasColumn('excess_payment_applications', 'notes')) {
                    $table->text('notes')->nullable()->after('reverted_at');
                }

                // Indexes for common queries (guard against duplicates)
                if (!$self->indexExists('excess_payment_applications', 'epa_status_idx')) {
                    try { $table->index('status', 'epa_status_idx'); } catch (\Throwable $e) {}
                }
                if (!$self->indexExists('excess_payment_applications', 'epa_created_by_idx')) {
                    try { $table->index('created_by', 'epa_created_by_idx'); } catch (\Throwable $e) {}
                }
                if (!$self->indexExists('excess_payment_applications', 'epa_reverted_by_idx')) {
                    try { $table->index('reverted_by', 'epa_reverted_by_idx'); } catch (\Throwable $e) {}
                }

                // Avoid foreign key constraints to prevent engine/type mismatch issues
                // (keep columns nullable with indexes for lookup)
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('excess_payment_applications')) {
            $self = $this;
            Schema::table('excess_payment_applications', function (Blueprint $table) use ($self) {
                // Drop indexes if they exist
                if ($self->indexExists('excess_payment_applications', 'epa_status_idx')) {
                    try { $table->dropIndex('epa_status_idx'); } catch (\Throwable $e) {}
                }
                if ($self->indexExists('excess_payment_applications', 'epa_created_by_idx')) {
                    try { $table->dropIndex('epa_created_by_idx'); } catch (\Throwable $e) {}
                }
                if ($self->indexExists('excess_payment_applications', 'epa_reverted_by_idx')) {
                    try { $table->dropIndex('epa_reverted_by_idx'); } catch (\Throwable $e) {}
                }

                // Drop columns
                if (Schema::hasColumn('excess_payment_applications', 'notes')) {
                    $table->dropColumn('notes');
                }
                if (Schema::hasColumn('excess_payment_applications', 'reverted_at')) {
                    $table->dropColumn('reverted_at');
                }
                if (Schema::hasColumn('excess_payment_applications', 'reverted_by')) {
                    $table->dropColumn('reverted_by');
                }
                if (Schema::hasColumn('excess_payment_applications', 'created_by')) {
                    $table->dropColumn('created_by');
                }
            });
        }
    }

    /**
     * Check if an index exists on a table (MySQL).
     */
    protected function indexExists(string $table, string $indexName): bool
    {
        try {
            $dbName = DB::getDatabaseName();
            $result = DB::select(
                'SELECT 1 FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ? LIMIT 1',
                [$dbName, $table, $indexName]
            );
            return !empty($result);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
