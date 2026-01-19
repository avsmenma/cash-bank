<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Column already exists in create table migration
        // Just add foreign key if not exists
        if (!Schema::hasColumn('bank_masuk', 'id_jenis_pembayaran_fk')) {
            Schema::table('bank_masuk', function (Blueprint $table) {
                $table->foreign('id_jenis_pembayaran')
                    ->references('id_jenis_pembayaran')
                    ->on('jenis_pembayarans')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        // No action needed
    }
};
