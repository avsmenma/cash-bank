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
        Schema::create('daftarrekenings', function (Blueprint $table) {
            $table->id('id_rekening');
            $table->foreignId('id_daftar_bank')
                ->constrained('daftarbanks', 'id_daftar_bank')
                ->onDelete('cascade');
            $table->string('nomor_rekening');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daftarrekenings');
    }
};
