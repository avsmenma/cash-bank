<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bank_keluars', function (Blueprint $table) {
            $table->id('id_bank_keluar');
            $table->string('no_agenda')->nullable();
            $table->unsignedBigInteger('id_sumber_dana')->nullable();
            $table->unsignedBigInteger('id_bank_tujuan')->nullable();
            $table->unsignedBigInteger('id_kategori_kriteria')->nullable();
            $table->unsignedBigInteger('id_sub_kriteria')->nullable();
            $table->unsignedBigInteger('id_item_sub_kriteria')->nullable();
            $table->unsignedBigInteger('dokumen_id')->nullable();
            $table->string('agenda_tahun')->nullable();
            $table->text('uraian')->nullable();
            $table->decimal('nilai_rupiah', 20, 2)->nullable();
            $table->string('penerima')->nullable();
            $table->date('tanggal')->nullable();
            $table->unsignedBigInteger('id_jenis_pembayaran')->nullable();
            $table->decimal('debet', 20, 2)->nullable();
            $table->decimal('kredit', 20, 2)->nullable();
            $table->text('keterangan')->nullable();
            $table->string('no_sap')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('id_sumber_dana')->references('id_sumber_dana')->on('sumber_dana')->nullOnDelete();
            $table->foreign('id_bank_tujuan')->references('id_bank_tujuan')->on('bank_tujuan')->nullOnDelete();
            $table->foreign('id_kategori_kriteria')->references('id_kategori_kriteria')->on('kategori_kriteria')->nullOnDelete();
            $table->foreign('id_sub_kriteria')->references('id_sub_kriteria')->on('sub_kriteria')->nullOnDelete();
            $table->foreign('id_item_sub_kriteria')->references('id_item_sub_kriteria')->on('item_sub_kriteria')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_keluars');
    }
};
