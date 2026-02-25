<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class VAUserSeeder extends Seeder
{
    /**
     * Seed VA user accounts — one per Virtual Account.
     * Each user can login with their bank number (username) or VA name.
     * Password for all: 12345678
     */
    public function run(): void
    {
        $vaList = [
            ['id_bank_tujuan' => 1, 'username' => '81029155533'],
            ['id_bank_tujuan' => 2, 'username' => '81029155531'],
            ['id_bank_tujuan' => 3, 'username' => '81029155528'],
            ['id_bank_tujuan' => 4, 'username' => '81029155527'],
            ['id_bank_tujuan' => 5, 'username' => '81029155526'],
            ['id_bank_tujuan' => 6, 'username' => '81029155525'],
            ['id_bank_tujuan' => 7, 'username' => '81029155524'],
            ['id_bank_tujuan' => 8, 'username' => '81029155523'],
            ['id_bank_tujuan' => 9, 'username' => '81029155522'],
            ['id_bank_tujuan' => 10, 'username' => '81029155521'],
            ['id_bank_tujuan' => 11, 'username' => '81029155520'],
            ['id_bank_tujuan' => 12, 'username' => '81029155519'],
            ['id_bank_tujuan' => 13, 'username' => '81029155518'],
            ['id_bank_tujuan' => 14, 'username' => '81029155517'],
            ['id_bank_tujuan' => 15, 'username' => '81029155530'],
            ['id_bank_tujuan' => 16, 'username' => '81029155516'],
            ['id_bank_tujuan' => 17, 'username' => '81029155515'],
            ['id_bank_tujuan' => 18, 'username' => '81029155514'],
            ['id_bank_tujuan' => 19, 'username' => '81029155513'],
            ['id_bank_tujuan' => 20, 'username' => '81029155512'],
            ['id_bank_tujuan' => 21, 'username' => '81029155511'],
            ['id_bank_tujuan' => 22, 'username' => '81029155510'],
            ['id_bank_tujuan' => 23, 'username' => '81029155509'],
            ['id_bank_tujuan' => 24, 'username' => '81029155532'],
            ['id_bank_tujuan' => 25, 'username' => '81029155529'],
            ['id_bank_tujuan' => 26, 'username' => '81029155508'],
            ['id_bank_tujuan' => 27, 'username' => '81029155507'],
            ['id_bank_tujuan' => 28, 'username' => '81029155506'],
            ['id_bank_tujuan' => 29, 'username' => '81029155505'],
            ['id_bank_tujuan' => 30, 'username' => '81029155504'],
            ['id_bank_tujuan' => 31, 'username' => '81029155503'],
            ['id_bank_tujuan' => 32, 'username' => '81029155502'],
            ['id_bank_tujuan' => 33, 'username' => '81029155501'],
        ];

        $password = Hash::make('12345678');

        foreach ($vaList as $va) {
            DB::table('users')->updateOrInsert(
                ['username' => $va['username']],
                [
                    'password' => $password,
                    'role' => 'va',
                    'id_bank_tujuan' => $va['id_bank_tujuan'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
