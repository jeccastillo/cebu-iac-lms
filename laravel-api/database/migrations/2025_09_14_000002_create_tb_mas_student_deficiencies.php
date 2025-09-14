<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tb_mas_student_deficiencies')) {
            Schema::create('tb_mas_student_deficiencies', function (Blueprint $table) {
                $table->increments('intID');
                $table->integer('intStudentID')->index(); // references tb_mas_users.intID (no FK to keep env-agnostic)
                $table->integer('syid')->index();
                $table->string('department_code', 64)->index();
                $table->integer('payment_description_id')->nullable()->index(); // payment_descriptions.intID
                $table->integer('billing_id')->index(); // tb_mas_student_billing.intID (required linkage)
                $table->decimal('amount', 12, 2);
                $table->string('description', 255);
                $table->text('remarks')->nullable();
                $table->dateTime('posted_at')->nullable();
                $table->integer('campus_id')->nullable()->index();
                $table->integer('created_by')->nullable()->index(); // faculty intID
                $table->integer('updated_by')->nullable()->index(); // faculty intID
                $table->timestamps();

                $table->index(['intStudentID', 'syid'], 'idx_sd_student_term');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_mas_student_deficiencies');
    }
};
