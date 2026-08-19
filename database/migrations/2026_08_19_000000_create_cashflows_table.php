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
        Schema::create('cashflow_references', function (Blueprint $table) {
            $table->id();
            $table->string('reference_key')->unique();
            $table->string('parent_key')->nullable();
            $table->string('parent_name')->nullable();
            $table->string('uraian');
            $table->text('nature')->nullable();
            $table->timestamps();
        });

        Schema::create('cashflows', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_bank_tujuan')->nullable()->index();
            $table->string('profit_center')->nullable()->index();
            $table->string('nama_profit_center')->nullable();
            $table->string('document_number')->nullable()->index();
            $table->date('posting_date')->nullable();
            $table->integer('posting_period')->nullable();
            $table->integer('bulan')->nullable()->index();
            $table->integer('tahun')->nullable()->index();
            $table->string('account')->nullable();
            $table->string('offsetting_account')->nullable();
            $table->string('name_of_offsetting_account')->nullable();
            $table->string('posting_key')->nullable();
            $table->decimal('amount', 18, 2)->default(0);
            $table->text('text')->nullable();
            $table->string('gl_account_desc')->nullable();
            $table->string('reference_key')->nullable();
            $table->string('reference_key_1')->nullable()->index();
            $table->string('reference_key_3')->nullable();
            $table->string('cost_center')->nullable();
            $table->string('uraian')->nullable();
            $table->timestamps();

            $table->foreign('id_bank_tujuan')
                ->references('id_bank_tujuan')
                ->on('bank_tujuan')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashflows');
        Schema::dropIfExists('cashflow_references');
    }
};
