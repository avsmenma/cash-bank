<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Clean up data first
        DB::statement("UPDATE bank_keluars SET kredit = '0' WHERE kredit IS NULL OR kredit = ''");
        DB::statement("UPDATE bank_keluars SET debet = '0' WHERE debet IS NULL OR debet = ''");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No action needed
    }
};
