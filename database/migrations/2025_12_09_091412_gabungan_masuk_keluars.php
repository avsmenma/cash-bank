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
        Schema::create('gabungan_masuk_keluars', function (Blueprint $table) {
            $table->bigIncrements('id_gabungan');
            $table->dateTime('tanggal')->useCurrent();
            $table->unsignedBigInteger('nomor_agenda')->nullable();
            $table->unsignedBigInteger('dokumen_id')->nullable();
            $table->string('agenda_tahun')->nullable();
            $table->unsignedBigInteger('id_sumber_dana')->nullable();
            $table->unsignedBigInteger('id_bank_tujuan')->nullable();
            $table->unsignedBigInteger('id_kategori_kriteria')->nullable();
            $table->unsignedBigInteger('id_sub_kriteria')->nullable();
            $table->unsignedBigInteger('id_item_sub_kriteria')->nullable();
            $table->string('penerima')->nullable();
            $table->string('uraian')->nullable();
            $table->decimal('nilai_rupiah', 15, 2)->default(0);
            $table->decimal('debet', 15, 2)->default(0);
            $table->decimal('kredit', 15, 2)->default(0);
            $table->enum('jenis', ['Masuk', 'Keluar']);
            $table->string('keterangan')->nullable();
            $table->timestamps();

            // Foreign keys (removed dokumens FK as table doesn't exist)
            $table->foreign('id_sumber_dana')
                ->references('id_sumber_dana')
                ->on('sumber_dana')
                ->nullOnDelete();

            $table->foreign('id_bank_tujuan')
                ->references('id_bank_tujuan')
                ->on('bank_tujuan')
                ->nullOnDelete();

            $table->foreign('id_kategori_kriteria')
                ->references('id_kategori_kriteria')
                ->on('kategori_kriteria')
                ->nullOnDelete();

            $table->foreign('id_sub_kriteria')
                ->references('id_sub_kriteria')
                ->on('sub_kriteria')
                ->nullOnDelete();

            $table->foreign('id_item_sub_kriteria')
                ->references('id_item_sub_kriteria')
                ->on('item_sub_kriteria')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gabungan_masuk_keluars');
    }
};
