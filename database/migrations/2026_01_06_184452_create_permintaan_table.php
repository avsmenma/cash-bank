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
        Schema::create('permintaans', function (Blueprint $table) {
            $table->id('id_permintaan');

            $table->unsignedBigInteger('id_kategori_kriteria')->nullable();
            $table->foreign('id_kategori_kriteria')
            ->references('id_kategori_kriteria')
            ->on('kategori_kriteria')
            ->nullOnDelete();

            $table->unsignedBigInteger('id_sub_kriteria')->nullable();
            $table->foreign('id_sub_kriteria')
                ->references('id_sub_kriteria')
                ->on('sub_kriteria')
                ->nullOnDelete();

            $table->unsignedBigInteger('id_item_sub_kriteria')->nullable();
            $table->foreign('id_item_sub_kriteria')
                ->references('id_item_sub_kriteria')
                ->on('item_sub_kriteria')
                ->nullOnDelete();

            $table->decimal('M1', 38, 2)->default(0);
            $table->decimal('M2', 38, 2)->default(0);
            $table->decimal('M3', 38, 2)->default(0);
            $table->decimal('M4', 38, 2)->default(0);

            $table->tinyInteger('bulan')->nullable(); // 1–12
            $table->year('tahun')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permintaan');
    }
};
