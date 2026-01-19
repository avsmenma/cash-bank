<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Note: This migration is no longer needed as columns are already nullable in create table migration
     */
    public function up()
    {
        // Columns already nullable in create table migration
    }

    public function down()
    {
        // No action needed
    }
};
