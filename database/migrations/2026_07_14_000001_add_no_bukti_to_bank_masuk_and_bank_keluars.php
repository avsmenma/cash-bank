<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * No Bukti adalah nomor pembukuan MANUAL milik user (bukan dibuat sistem).
     * Diisi dari file saat import data awal bank masuk/keluar.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('bank_masuk', 'no_bukti')) {
            Schema::table('bank_masuk', function (Blueprint $table) {
                $table->string('no_bukti', 50)->nullable()->after('agenda_tahun');
            });
        }
        if (!Schema::hasColumn('bank_keluars', 'no_bukti')) {
            Schema::table('bank_keluars', function (Blueprint $table) {
                $table->string('no_bukti', 50)->nullable()->after('agenda_tahun');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('bank_masuk', 'no_bukti')) {
            Schema::table('bank_masuk', function (Blueprint $table) {
                $table->dropColumn('no_bukti');
            });
        }
        if (Schema::hasColumn('bank_keluars', 'no_bukti')) {
            Schema::table('bank_keluars', function (Blueprint $table) {
                $table->dropColumn('no_bukti');
            });
        }
    }
};
