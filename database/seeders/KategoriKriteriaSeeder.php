<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriKriteriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['id_kategori_kriteria' => 1, 'nama_kriteria' => 'Kebutuhan Gaji, Upah dan Tunjangan', 'tipe' => 'Keluar'],
            ['id_kategori_kriteria' => 2, 'nama_kriteria' => 'Payment Requirement for Exploitation Activity', 'tipe' => 'Keluar'],
            ['id_kategori_kriteria' => 3, 'nama_kriteria' => 'Kebutuhan Pembayaran Pekerjaan Aktivitas Investasi', 'tipe' => 'Keluar'],
            ['id_kategori_kriteria' => 4, 'nama_kriteria' => 'Kebutuhan Pembayaran Aktivitas Pendanaan', 'tipe' => 'Keluar'],
            ['id_kategori_kriteria' => 5, 'nama_kriteria' => 'Lain-Lain', 'tipe' => 'Masuk'],
            ['id_kategori_kriteria' => 6, 'nama_kriteria' => 'Return Transaksi', 'tipe' => 'Masuk'],
            ['id_kategori_kriteria' => 7, 'nama_kriteria' => 'Jasa Giro Bank', 'tipe' => 'Masuk'],
            ['id_kategori_kriteria' => 8, 'nama_kriteria' => 'Dropping', 'tipe' => 'Masuk'],
            ['id_kategori_kriteria' => 9, 'nama_kriteria' => 'Penjualan CPO', 'tipe' => 'Penerima'],
            ['id_kategori_kriteria' => 10, 'nama_kriteria' => 'Penjualan Kernel', 'tipe' => 'Penerima'],
            ['id_kategori_kriteria' => 11, 'nama_kriteria' => 'Hapor', 'tipe' => 'Penerima'],
            ['id_kategori_kriteria' => 12, 'nama_kriteria' => 'Penjualan Cangkang', 'tipe' => 'Penerima'],
            ['id_kategori_kriteria' => 13, 'nama_kriteria' => 'Penjualan TBS', 'tipe' => 'Penerima'],
            ['id_kategori_kriteria' => 14, 'nama_kriteria' => 'Penjualan Karet', 'tipe' => 'Penerima'],
            ['id_kategori_kriteria' => 15, 'nama_kriteria' => 'Lainnya', 'tipe' => 'Penerima'],
            ['id_kategori_kriteria' => 16, 'nama_kriteria' => 'KSO (Titip Olah/Revenue Sharing)', 'tipe' => 'Penerima'],
            ['id_kategori_kriteria' => 17, 'nama_kriteria' => 'KSU Batubara', 'tipe' => 'Penerima'],
        ];

        foreach ($data as $item) {
            DB::table('kategori_kriteria')->updateOrInsert(
                ['id_kategori_kriteria' => $item['id_kategori_kriteria']],
                array_merge($item, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
