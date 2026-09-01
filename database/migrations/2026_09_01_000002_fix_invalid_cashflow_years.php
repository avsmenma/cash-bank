<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Perbaiki tahun berdasarkan posting_date jika tersedia
        DB::statement("
            UPDATE cashflows
            SET tahun = YEAR(posting_date)
            WHERE posting_date IS NOT NULL
              AND (tahun < 2000 OR tahun > 2100 OR tahun IS NULL)
        ");

        // 2. Fallback sisa data yang tahun-nya masih tidak valid ke tahun 2026
        DB::statement("
            UPDATE cashflows
            SET tahun = 2026
            WHERE tahun < 2000 OR tahun > 2100 OR tahun IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak perlu rollback perubahan data yang sudah dinormalisasi
    }
};
