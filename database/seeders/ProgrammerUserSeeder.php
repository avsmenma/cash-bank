<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProgrammerUserSeeder extends Seeder
{
    /**
     * Seed the programmer user.
     */
    public function run(): void
    {
        // Check if programmer user already exists
        $exists = DB::table('users')->where('username', 'programmer')->exists();

        if (!$exists) {
            DB::table('users')->insert([
                'username' => 'programmer',
                'password' => Hash::make('12345678'),
                'role' => 'programmer',
                'id_bank_tujuan' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
