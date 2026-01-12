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
        Schema::create('rencana_droppings', function (Blueprint $table) {
           $table->id('id_rencana_dropping');
           $table->unsignedBigInteger('id_kategori_kriteria')->nullable();
            $table->foreign('id_kategori_kriteria')
            ->references('id_kategori_kriteria')
            ->on('kategori_kriteria')
            ->nullOnDelete();
            $table->decimal('januari',38,2)->default(0);
            $table->decimal('februari',38,2)->default(0);
            $table->decimal('maret',38,2)->default(0);
            $table->decimal('april',38,2)->default(0);
            $table->decimal('mei',38,2)->default(0);
            $table->decimal('juni',38,2)->default(0);
            $table->decimal('juli',38,2)->default(0);
            $table->decimal('agustus',38,2)->default(0);
            $table->decimal('september',38,2)->default(0);
            $table->decimal('oktober',38,2)->default(0);
            $table->decimal('november',38,2)->default(0);
            $table->decimal('desember',38,2)->default(0);
            $table->YEAR('tahun');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rencana_dropping');
    }
};
