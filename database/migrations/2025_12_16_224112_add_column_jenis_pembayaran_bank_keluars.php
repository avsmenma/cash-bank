<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Note: jenis_pembayaran column already exists as id_jenis_pembayaran in create table
     */
    public function up(): void
    {
        if (!Schema::hasColumn('bank_keluars', 'jenis_pembayaran')) {
            Schema::table('bank_keluars', function (Blueprint $table) {
                $table->string('jenis_pembayaran')->nullable()->after('debet');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_keluars', function (Blueprint $table) {
            $table->dropColumn('jenis_pembayaran');
        });
    }
};
