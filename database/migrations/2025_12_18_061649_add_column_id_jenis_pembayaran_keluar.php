<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Column already exists in create table migration
        // Just add FK if needed
        Schema::table('bank_keluars', function (Blueprint $table) {
            $table->foreign('id_jenis_pembayaran')
                ->references('id_jenis_pembayaran')
                ->on('jenis_pembayarans')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bank_keluars', function (Blueprint $table) {
            $table->dropForeign(['id_jenis_pembayaran']);
        });
    }
};
