<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Note: saldo_awal column already exists in create table migration
     */
    public function up()
    {
        // Column already added in create table migration
    }

    public function down()
    {
        // No action needed
    }
};
