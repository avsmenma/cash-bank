<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('bank_tujuan', 'sap')) {
            return;
        }

        Schema::table('bank_tujuan', function (Blueprint $table) {
            $table->string('sap')->nullable()->after('nama_tujuan');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('bank_tujuan', 'sap')) {
            return;
        }

        Schema::table('bank_tujuan', function (Blueprint $table) {
            $table->dropColumn('sap');
        });
    }
};
