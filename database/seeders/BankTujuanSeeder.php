<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BankTujuanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['id_bank_tujuan' => 1, 'nama_tujuan' => '81029155533 - PPPBB'],
            ['id_bank_tujuan' => 2, 'nama_tujuan' => '81029155531 - UGKB'],
            ['id_bank_tujuan' => 3, 'nama_tujuan' => '81029155528 - GUNME'],
            ['id_bank_tujuan' => 4, 'nama_tujuan' => '81029155527 - PAGUN'],
            ['id_bank_tujuan' => 5, 'nama_tujuan' => '81029155526 - GUMAS'],
            ['id_bank_tujuan' => 6, 'nama_tujuan' => '81029155525 - RIMBA'],
            ['id_bank_tujuan' => 7, 'nama_tujuan' => '81029155524 - PARBA'],
            ['id_bank_tujuan' => 8, 'nama_tujuan' => '81029155523 - SINTANG'],
            ['id_bank_tujuan' => 9, 'nama_tujuan' => '81029155522 - NGABANG'],
            ['id_bank_tujuan' => 10, 'nama_tujuan' => '81029155521 - PANGA'],
            ['id_bank_tujuan' => 11, 'nama_tujuan' => '81029155520 - PARINDU'],
            ['id_bank_tujuan' => 12, 'nama_tujuan' => '81029155519 - PAPAR'],
            ['id_bank_tujuan' => 13, 'nama_tujuan' => '81029155518 - BAYAN'],
            ['id_bank_tujuan' => 14, 'nama_tujuan' => '81029155517 - PAKEM'],
            ['id_bank_tujuan' => 15, 'nama_tujuan' => '81029155530 - UGKST'],
            ['id_bank_tujuan' => 16, 'nama_tujuan' => '81029155516 - DASAL'],
            ['id_bank_tujuan' => 17, 'nama_tujuan' => '81029155515 - TAMBA'],
            ['id_bank_tujuan' => 18, 'nama_tujuan' => '81029155514 - PAMUKAN'],
            ['id_bank_tujuan' => 19, 'nama_tujuan' => '81029155513 - PAPAM'],
            ['id_bank_tujuan' => 20, 'nama_tujuan' => '81029155512 - BALIN'],
            ['id_bank_tujuan' => 21, 'nama_tujuan' => '81029155511 - PELAIHARI'],
            ['id_bank_tujuan' => 22, 'nama_tujuan' => '81029155510 - PALAI'],
            ['id_bank_tujuan' => 23, 'nama_tujuan' => '81029155509 - KUMAI'],
            ['id_bank_tujuan' => 24, 'nama_tujuan' => '81029155532 - PRYBB'],
            ['id_bank_tujuan' => 25, 'nama_tujuan' => '81029155529 - UGKT'],
            ['id_bank_tujuan' => 26, 'nama_tujuan' => '81029155508 - TABARA'],
            ['id_bank_tujuan' => 27, 'nama_tujuan' => '81029155507 - TAJATI'],
            ['id_bank_tujuan' => 28, 'nama_tujuan' => '81029155506 - PANDAWA'],
            ['id_bank_tujuan' => 29, 'nama_tujuan' => '81029155505 - PALPI'],
            ['id_bank_tujuan' => 30, 'nama_tujuan' => '81029155504 - PASAM'],
            ['id_bank_tujuan' => 31, 'nama_tujuan' => '81029155503 - LONGKALI'],
            ['id_bank_tujuan' => 32, 'nama_tujuan' => '81029155502 - DEKAN'],
            ['id_bank_tujuan' => 33, 'nama_tujuan' => '81029155501 - RAREN'],
        ];

        foreach ($data as $item) {
            DB::table('bank_tujuan')->updateOrInsert(
                ['id_bank_tujuan' => $item['id_bank_tujuan']],
                array_merge($item, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
