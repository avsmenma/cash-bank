<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom remember_token agar fitur "Remember Me" di login berfungsi.
     * Tanpa kolom ini, login dengan centang Remember Me gagal (error simpan token).
     */
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'remember_token')) {
            Schema::table('users', function (Blueprint $table) {
                $table->rememberToken()->after('id_bank_tujuan');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'remember_token')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropRememberToken();
            });
        }
    }
};
