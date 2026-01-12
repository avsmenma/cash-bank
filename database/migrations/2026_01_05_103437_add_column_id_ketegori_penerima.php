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
        Schema::table('penerimas', function(Blueprint $table){
            $table->unsignedBigInteger('id_kategori_kriteria')->nullable()->after('id_penerima');
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
        //
    }
};
