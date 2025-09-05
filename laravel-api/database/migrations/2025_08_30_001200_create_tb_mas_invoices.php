<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tb_mas_invoices')) {
            Schema::create('tb_mas_invoices', function (Blueprint $table) {
                // Primary key (legacy naming parity)
                $table->increments('intID');

                // Foreign-like references
                $table->unsignedInteger('intStudentID')->index();  // tb_mas_users.intID
                $table->unsignedInteger('syid')->index();          // school year id (AY)
                $table->unsignedInteger('campus_id')->nullable()->index();
                $table->unsignedInteger('cashier_id')->nullable()->index(); // optional link to tb_mas_cashiers.intID

                // Invoice classification and status
                $table->string('type', 64)->index();               // tuition | billing | other
                $table->string('status', 32)->default('Draft')->index(); // Draft | Issued | Paid | Void

                // Number (assigned by cashier flow when paid/issued via ranges)
                $table->unsignedBigInteger('invoice_number')->nullable()->unique();

                // Amounts and timing
                $table->decimal('amount_total', 12, 2)->default(0);
                $table->dateTime('posted_at')->nullable(); // issuance time
                $table->date('due_at')->nullable();

                // Additional info
                $table->text('remarks')->nullable();
                // Snapshot of items and metadata (JSON)
                $table->json('payload')->nullable();

                // Audit trail
                $table->unsignedInteger('created_by')->nullable();
                $table->unsignedInteger('updated_by')->nullable();

                $table->timestamps();

                // Helpful composite indexes
                $table->index(['intStudentID', 'syid'], 'idx_invoices_student_syid');
                $table->index(['intStudentID', 'syid', 'type'], 'idx_invoices_student_syid_type');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_mas_invoices');
    }
};
