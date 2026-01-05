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
        Schema::create('penerimas', function(Blueprint $table){
            $table->id('id_penerima');
            $table->String('kontrak');
            $table->String('pembeli');
            $table->date('tanggal');
            $table->String('no_reg');
            $table->decimal('volume', 38,2)->default(0);
            $table->decimal('harga', 38,2)->default(0);
            $table->decimal('nilai', 38,2)->default(0);
            $table->decimal('ppn', 38,2)->default(0);
            $table->decimal('potPPH', 38,2)->default(0);
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
