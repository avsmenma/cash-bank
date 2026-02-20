<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('penerimas', function (Blueprint $table) {
            $table->decimal('nilai_inc_ppn', 20, 2)->default(0)->after('potppn');
        });
    }

    public function down(): void
    {
        Schema::table('penerimas', function (Blueprint $table) {
            $table->dropColumn('nilai_inc_ppn');
        });
    }
};
