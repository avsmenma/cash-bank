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
        Schema::create('penerimas', function (Blueprint $table) {
            $table->id('id_penerima');
            $table->string('kontrak')->nullable();
            $table->unsignedBigInteger('id_kategori_kriteria')->nullable();
            $table->string('pembeli')->nullable();
            $table->date('tanggal')->nullable();
            $table->string('no_reg')->nullable();
            $table->decimal('volume', 38, 2)->default(0);
            $table->decimal('harga', 38, 2)->default(0);
            $table->decimal('nilai', 38, 2)->default(0);
            $table->decimal('ppn', 38, 2)->default(0);
            $table->decimal('potppn', 38, 2)->default(0);
            $table->timestamps();

            $table->foreign('id_kategori_kriteria')
                ->references('id_kategori_kriteria')
                ->on('kategori_kriteria')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penerimas');
    }
};
