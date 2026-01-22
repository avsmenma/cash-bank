<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubKriteriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['id_sub_kriteria' => 1, 'id_kategori_kriteria' => 1, 'nama_sub_kriteria' => 'Karyawan Pimpinan'],
            ['id_sub_kriteria' => 2, 'id_kategori_kriteria' => 1, 'nama_sub_kriteria' => 'Karyawan Pelaksana'],
            ['id_sub_kriteria' => 3, 'id_kategori_kriteria' => 1, 'nama_sub_kriteria' => 'Gaji Honor'],
            ['id_sub_kriteria' => 4, 'id_kategori_kriteria' => 2, 'nama_sub_kriteria' => 'Biaya Usaha dan Lainnya'],
            ['id_sub_kriteria' => 5, 'id_kategori_kriteria' => 2, 'nama_sub_kriteria' => 'Purchase Volume'],
            ['id_sub_kriteria' => 6, 'id_kategori_kriteria' => 2, 'nama_sub_kriteria' => 'Operasional Produksi'],
            ['id_sub_kriteria' => 7, 'id_kategori_kriteria' => 2, 'nama_sub_kriteria' => 'Pembayaran Eksploitasi Lainnya'],
            ['id_sub_kriteria' => 8, 'id_kategori_kriteria' => 3, 'nama_sub_kriteria' => 'Pajak'],
            ['id_sub_kriteria' => 9, 'id_kategori_kriteria' => 3, 'nama_sub_kriteria' => 'Investasi On Farm'],
            ['id_sub_kriteria' => 10, 'id_kategori_kriteria' => 3, 'nama_sub_kriteria' => 'Investasi Off Farm'],
            ['id_sub_kriteria' => 11, 'id_kategori_kriteria' => 3, 'nama_sub_kriteria' => 'Pembayaran investasi lainnya'],
            ['id_sub_kriteria' => 12, 'id_kategori_kriteria' => 4, 'nama_sub_kriteria' => 'Kebutuhan Pembayaran Aktivitas Pendanaann'],
            ['id_sub_kriteria' => 13, 'id_kategori_kriteria' => 4, 'nama_sub_kriteria' => 'Pembayaran Pokok Pinjaman'],
            ['id_sub_kriteria' => 14, 'id_kategori_kriteria' => 4, 'nama_sub_kriteria' => 'Pembayaran Bunga Pinjaman'],
            ['id_sub_kriteria' => 15, 'id_kategori_kriteria' => 4, 'nama_sub_kriteria' => 'Pembayaran Kepada Pihak Berelasi'],
            ['id_sub_kriteria' => 16, 'id_kategori_kriteria' => 4, 'nama_sub_kriteria' => 'PEN Pembayaran Pokok Pinjaman'],
            ['id_sub_kriteria' => 17, 'id_kategori_kriteria' => 4, 'nama_sub_kriteria' => 'PEN Pembayaran Bunga Pinjaman'],
            ['id_sub_kriteria' => 18, 'id_kategori_kriteria' => 4, 'nama_sub_kriteria' => 'Pembayaran Pendanaan Lainnya'],
        ];

        foreach ($data as $item) {
            DB::table('sub_kriteria')->updateOrInsert(
                ['id_sub_kriteria' => $item['id_sub_kriteria']],
                array_merge($item, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
