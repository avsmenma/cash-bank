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
        Schema::create('cashflow_locks', function (Blueprint $table) {
            $table->id();
            $table->integer('tahun')->index();
            $table->integer('bulan')->index();
            $table->boolean('is_locked')->default(false);
            $table->string('keterangan')->nullable();
            $table->string('locked_by')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->timestamps();

            $table->unique(['tahun', 'bulan'], 'unique_tahun_bulan_lock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashflow_locks');
    }
};
