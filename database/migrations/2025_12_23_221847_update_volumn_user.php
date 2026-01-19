<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Columns already exist in create table migration
        // Foreign key already added in 2025_12_03_020000
    }

    public function down(): void
    {
        // No action needed
    }
};
