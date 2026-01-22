<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemSubKriteriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            // Sub Kriteria 1 - Karyawan Pimpinan
            ['id_item_sub_kriteria' => 1, 'id_sub_kriteria' => 1, 'nama_item_sub_kriteria' => 'Gaji dan Tunjangan'],
            ['id_item_sub_kriteria' => 2, 'id_sub_kriteria' => 1, 'nama_item_sub_kriteria' => 'Premi'],
            ['id_item_sub_kriteria' => 3, 'id_sub_kriteria' => 1, 'nama_item_sub_kriteria' => 'Cuti Tahunan'],
            ['id_item_sub_kriteria' => 4, 'id_sub_kriteria' => 1, 'nama_item_sub_kriteria' => 'Cuti Panjang'],
            ['id_item_sub_kriteria' => 5, 'id_sub_kriteria' => 1, 'nama_item_sub_kriteria' => 'THR'],
            ['id_item_sub_kriteria' => 6, 'id_sub_kriteria' => 1, 'nama_item_sub_kriteria' => 'Bonus'],
            ['id_item_sub_kriteria' => 7, 'id_sub_kriteria' => 1, 'nama_item_sub_kriteria' => 'PPh Pasal 21'],
            ['id_item_sub_kriteria' => 8, 'id_sub_kriteria' => 1, 'nama_item_sub_kriteria' => 'Bantuan Anak Sekolah'],
            ['id_item_sub_kriteria' => 9, 'id_sub_kriteria' => 1, 'nama_item_sub_kriteria' => 'Iuran Dapenbun (Normal)'],
            ['id_item_sub_kriteria' => 10, 'id_sub_kriteria' => 1, 'nama_item_sub_kriteria' => 'Iuran Dapenbun (Tambahan)'],
            ['id_item_sub_kriteria' => 11, 'id_sub_kriteria' => 1, 'nama_item_sub_kriteria' => 'Penghargaan Masa Kerja'],
            ['id_item_sub_kriteria' => 12, 'id_sub_kriteria' => 1, 'nama_item_sub_kriteria' => 'Iuran BPJS B. Perusahaan'],
            ['id_item_sub_kriteria' => 13, 'id_sub_kriteria' => 1, 'nama_item_sub_kriteria' => 'SHT (Cicilan)'],
            ['id_item_sub_kriteria' => 14, 'id_sub_kriteria' => 1, 'nama_item_sub_kriteria' => 'Lainnya'],

            // Sub Kriteria 2 - Karyawan Pelaksana
            ['id_item_sub_kriteria' => 15, 'id_sub_kriteria' => 2, 'nama_item_sub_kriteria' => 'Gaji dan Tunjangan'],
            ['id_item_sub_kriteria' => 16, 'id_sub_kriteria' => 2, 'nama_item_sub_kriteria' => 'Lembur'],
            ['id_item_sub_kriteria' => 17, 'id_sub_kriteria' => 2, 'nama_item_sub_kriteria' => 'Premi'],
            ['id_item_sub_kriteria' => 18, 'id_sub_kriteria' => 2, 'nama_item_sub_kriteria' => 'Cuti Tahunan'],
            ['id_item_sub_kriteria' => 19, 'id_sub_kriteria' => 2, 'nama_item_sub_kriteria' => 'Cuti Panjang'],
            ['id_item_sub_kriteria' => 20, 'id_sub_kriteria' => 2, 'nama_item_sub_kriteria' => 'THR'],
            ['id_item_sub_kriteria' => 21, 'id_sub_kriteria' => 2, 'nama_item_sub_kriteria' => 'Bonus'],
            ['id_item_sub_kriteria' => 22, 'id_sub_kriteria' => 2, 'nama_item_sub_kriteria' => 'PPh Pasal 21'],
            ['id_item_sub_kriteria' => 23, 'id_sub_kriteria' => 2, 'nama_item_sub_kriteria' => 'Bantuan Anak Sekolah'],
            ['id_item_sub_kriteria' => 24, 'id_sub_kriteria' => 2, 'nama_item_sub_kriteria' => 'Iuran Dapenbun (Normal)'],
            ['id_item_sub_kriteria' => 25, 'id_sub_kriteria' => 2, 'nama_item_sub_kriteria' => 'Iuran Dapenbun (Tambahan)'],
            ['id_item_sub_kriteria' => 26, 'id_sub_kriteria' => 2, 'nama_item_sub_kriteria' => 'Penghargaan Masa Kerja'],
            ['id_item_sub_kriteria' => 27, 'id_sub_kriteria' => 2, 'nama_item_sub_kriteria' => 'Iuran BPJS B. Perusahaan'],
            ['id_item_sub_kriteria' => 28, 'id_sub_kriteria' => 2, 'nama_item_sub_kriteria' => 'SHT (Cicilan)'],
            ['id_item_sub_kriteria' => 29, 'id_sub_kriteria' => 2, 'nama_item_sub_kriteria' => 'Lainnya'],

            // Sub Kriteria 3 - Gaji Honor
            ['id_item_sub_kriteria' => 30, 'id_sub_kriteria' => 3, 'nama_item_sub_kriteria' => 'Keamanan'],
            ['id_item_sub_kriteria' => 31, 'id_sub_kriteria' => 3, 'nama_item_sub_kriteria' => 'Guru'],
            ['id_item_sub_kriteria' => 32, 'id_sub_kriteria' => 3, 'nama_item_sub_kriteria' => 'Dokter'],
            ['id_item_sub_kriteria' => 33, 'id_sub_kriteria' => 3, 'nama_item_sub_kriteria' => 'PKWT'],
            ['id_item_sub_kriteria' => 34, 'id_sub_kriteria' => 3, 'nama_item_sub_kriteria' => 'Lainnya'],

            // Sub Kriteria 4 - Biaya Usaha dan Lainnya
            ['id_item_sub_kriteria' => 35, 'id_sub_kriteria' => 4, 'nama_item_sub_kriteria' => 'Biaya Pengiriman ke Pelabuhan (CPO)'],
            ['id_item_sub_kriteria' => 36, 'id_sub_kriteria' => 4, 'nama_item_sub_kriteria' => 'Biaya Sewa Gudang'],
            ['id_item_sub_kriteria' => 37, 'id_sub_kriteria' => 4, 'nama_item_sub_kriteria' => 'Biaya Instalasi Pemompaan'],
            ['id_item_sub_kriteria' => 38, 'id_sub_kriteria' => 4, 'nama_item_sub_kriteria' => 'Biaya Pelabuhan'],
            ['id_item_sub_kriteria' => 39, 'id_sub_kriteria' => 4, 'nama_item_sub_kriteria' => 'Biaya Jasa KPBN'],
            ['id_item_sub_kriteria' => 40, 'id_sub_kriteria' => 4, 'nama_item_sub_kriteria' => 'Biaya Pemasaran Lainnya'],
            ['id_item_sub_kriteria' => 41, 'id_sub_kriteria' => 4, 'nama_item_sub_kriteria' => 'Biaya Pengangkutan, Perjalan & Penginapan'],
            ['id_item_sub_kriteria' => 42, 'id_sub_kriteria' => 4, 'nama_item_sub_kriteria' => 'Biaya Pemeliharaan Bangunan, Mesin, Jalan dan Instalasi (DTPL)'],
            ['id_item_sub_kriteria' => 43, 'id_sub_kriteria' => 4, 'nama_item_sub_kriteria' => 'Biaya Pemeliharaan Bangunan, Mesin, Jalan dan Instalasi (DINF)'],
            ['id_item_sub_kriteria' => 44, 'id_sub_kriteria' => 4, 'nama_item_sub_kriteria' => 'Biaya Pemeliharaan Perlengkapan Kantor'],
            ['id_item_sub_kriteria' => 45, 'id_sub_kriteria' => 4, 'nama_item_sub_kriteria' => 'Biaya Pajak dan Retribusi'],
            ['id_item_sub_kriteria' => 46, 'id_sub_kriteria' => 4, 'nama_item_sub_kriteria' => 'Biaya Premi Asuransi'],
            ['id_item_sub_kriteria' => 47, 'id_sub_kriteria' => 4, 'nama_item_sub_kriteria' => 'Biaya Keamanan'],
            ['id_item_sub_kriteria' => 48, 'id_sub_kriteria' => 4, 'nama_item_sub_kriteria' => 'Biaya Sistem Manajemen Kesehatan & Keselamatan Kerja'],
            ['id_item_sub_kriteria' => 49, 'id_sub_kriteria' => 4, 'nama_item_sub_kriteria' => 'Biaya Mutu (ISO 9000)'],
            ['id_item_sub_kriteria' => 50, 'id_sub_kriteria' => 4, 'nama_item_sub_kriteria' => 'Biaya Pengendalian Lingkungan (ISO 14000)'],
            ['id_item_sub_kriteria' => 51, 'id_sub_kriteria' => 4, 'nama_item_sub_kriteria' => 'Biaya Penelitian dan Percobaan'],
            ['id_item_sub_kriteria' => 52, 'id_sub_kriteria' => 4, 'nama_item_sub_kriteria' => 'Biaya Sumbangan dan Iuran'],
            ['id_item_sub_kriteria' => 53, 'id_sub_kriteria' => 4, 'nama_item_sub_kriteria' => 'Biaya CSR'],
            ['id_item_sub_kriteria' => 54, 'id_sub_kriteria' => 4, 'nama_item_sub_kriteria' => 'Biaya Pendidikan dan Pengembangan SDM'],
            ['id_item_sub_kriteria' => 55, 'id_sub_kriteria' => 4, 'nama_item_sub_kriteria' => 'Biaya Konsultan Hukum'],
            ['id_item_sub_kriteria' => 56, 'id_sub_kriteria' => 4, 'nama_item_sub_kriteria' => 'Biaya Konsultan (DSKP)'],
            ['id_item_sub_kriteria' => 57, 'id_sub_kriteria' => 4, 'nama_item_sub_kriteria' => 'Biaya Konsultan (DRPH)'],
            ['id_item_sub_kriteria' => 58, 'id_sub_kriteria' => 4, 'nama_item_sub_kriteria' => 'Biaya Konsultan (DAPN)'],
            ['id_item_sub_kriteria' => 59, 'id_sub_kriteria' => 4, 'nama_item_sub_kriteria' => 'Biaya Konsultan (DSPN)'],
            ['id_item_sub_kriteria' => 60, 'id_sub_kriteria' => 4, 'nama_item_sub_kriteria' => 'Biaya Konsultan (DSSM)'],
            ['id_item_sub_kriteria' => 61, 'id_sub_kriteria' => 4, 'nama_item_sub_kriteria' => 'Biaya Konsultan (DTPL)'],
            ['id_item_sub_kriteria' => 62, 'id_sub_kriteria' => 4, 'nama_item_sub_kriteria' => 'Biaya Audit'],
            ['id_item_sub_kriteria' => 63, 'id_sub_kriteria' => 4, 'nama_item_sub_kriteria' => 'Utilities (Air, Listrik, ATK, Brg Umum, Sewa Kantor)'],
            ['id_item_sub_kriteria' => 64, 'id_sub_kriteria' => 4, 'nama_item_sub_kriteria' => 'Biaya Distrik'],
            ['id_item_sub_kriteria' => 65, 'id_sub_kriteria' => 4, 'nama_item_sub_kriteria' => 'Biaya Institusi Terkait'],
            ['id_item_sub_kriteria' => 66, 'id_sub_kriteria' => 4, 'nama_item_sub_kriteria' => 'Biaya Kantor Perwakilan'],
            ['id_item_sub_kriteria' => 67, 'id_sub_kriteria' => 4, 'nama_item_sub_kriteria' => 'Biaya Komisaris'],
            ['id_item_sub_kriteria' => 68, 'id_sub_kriteria' => 4, 'nama_item_sub_kriteria' => 'Biaya Media'],
            ['id_item_sub_kriteria' => 69, 'id_sub_kriteria' => 4, 'nama_item_sub_kriteria' => 'Biaya Rapat'],
            ['id_item_sub_kriteria' => 70, 'id_sub_kriteria' => 4, 'nama_item_sub_kriteria' => 'Biaya Telekomunikasi dan Ekspedisi'],
            ['id_item_sub_kriteria' => 71, 'id_sub_kriteria' => 4, 'nama_item_sub_kriteria' => 'Biaya Kesehatan'],

            // Sub Kriteria 5 - Purchase Volume
            ['id_item_sub_kriteria' => 72, 'id_sub_kriteria' => 5, 'nama_item_sub_kriteria' => 'CPO'],
            ['id_item_sub_kriteria' => 73, 'id_sub_kriteria' => 5, 'nama_item_sub_kriteria' => 'Kernel'],
            ['id_item_sub_kriteria' => 74, 'id_sub_kriteria' => 5, 'nama_item_sub_kriteria' => 'PKO'],
            ['id_item_sub_kriteria' => 75, 'id_sub_kriteria' => 5, 'nama_item_sub_kriteria' => 'PKM'],
            ['id_item_sub_kriteria' => 76, 'id_sub_kriteria' => 5, 'nama_item_sub_kriteria' => 'TBS (FFB)'],
            ['id_item_sub_kriteria' => 77, 'id_sub_kriteria' => 5, 'nama_item_sub_kriteria' => 'Bokar'],
            ['id_item_sub_kriteria' => 78, 'id_sub_kriteria' => 5, 'nama_item_sub_kriteria' => 'Karet'],
            ['id_item_sub_kriteria' => 79, 'id_sub_kriteria' => 5, 'nama_item_sub_kriteria' => 'Teh'],
            ['id_item_sub_kriteria' => 80, 'id_sub_kriteria' => 5, 'nama_item_sub_kriteria' => 'Tebu'],
            ['id_item_sub_kriteria' => 81, 'id_sub_kriteria' => 5, 'nama_item_sub_kriteria' => 'Raw Sugar'],
            ['id_item_sub_kriteria' => 82, 'id_sub_kriteria' => 5, 'nama_item_sub_kriteria' => 'Gula'],
            ['id_item_sub_kriteria' => 83, 'id_sub_kriteria' => 5, 'nama_item_sub_kriteria' => 'Tetes / Molase'],
            ['id_item_sub_kriteria' => 84, 'id_sub_kriteria' => 5, 'nama_item_sub_kriteria' => 'Coffee'],
            ['id_item_sub_kriteria' => 85, 'id_sub_kriteria' => 5, 'nama_item_sub_kriteria' => 'Cocoa'],
            ['id_item_sub_kriteria' => 86, 'id_sub_kriteria' => 5, 'nama_item_sub_kriteria' => 'Tobacco'],

            // Sub Kriteria 6 - Operasional Produksi
            ['id_item_sub_kriteria' => 87, 'id_sub_kriteria' => 6, 'nama_item_sub_kriteria' => 'Pemeliharaan Tanaman Menghasilkan'],
            ['id_item_sub_kriteria' => 88, 'id_sub_kriteria' => 6, 'nama_item_sub_kriteria' => 'Bahan Pemupukan'],
            ['id_item_sub_kriteria' => 89, 'id_sub_kriteria' => 6, 'nama_item_sub_kriteria' => 'Aplikasi Pemupukan'],
            ['id_item_sub_kriteria' => 90, 'id_sub_kriteria' => 6, 'nama_item_sub_kriteria' => 'Panen & Pengumpulan'],
            ['id_item_sub_kriteria' => 91, 'id_sub_kriteria' => 6, 'nama_item_sub_kriteria' => 'Pengangkutan (TBS)'],
            ['id_item_sub_kriteria' => 92, 'id_sub_kriteria' => 6, 'nama_item_sub_kriteria' => 'Pengolahan'],
            ['id_item_sub_kriteria' => 93, 'id_sub_kriteria' => 6, 'nama_item_sub_kriteria' => 'Pengolahan (Bahan Kimia)'],
            ['id_item_sub_kriteria' => 94, 'id_sub_kriteria' => 6, 'nama_item_sub_kriteria' => 'Pembibitan (Tebu)'],
            ['id_item_sub_kriteria' => 95, 'id_sub_kriteria' => 6, 'nama_item_sub_kriteria' => 'Tebu Giling (Tebu)'],
            ['id_item_sub_kriteria' => 96, 'id_sub_kriteria' => 6, 'nama_item_sub_kriteria' => 'Tebang dan Angkut (Tebu)'],
            ['id_item_sub_kriteria' => 97, 'id_sub_kriteria' => 6, 'nama_item_sub_kriteria' => 'Biaya Pabrik (Tebu)'],
            ['id_item_sub_kriteria' => 98, 'id_sub_kriteria' => 6, 'nama_item_sub_kriteria' => 'Pembelian Bahan Bakar Minyak (BBM)'],
            ['id_item_sub_kriteria' => 99, 'id_sub_kriteria' => 6, 'nama_item_sub_kriteria' => 'Penyaluran ke petani tebu rakyat'],
            ['id_item_sub_kriteria' => 100, 'id_sub_kriteria' => 6, 'nama_item_sub_kriteria' => 'Penyaluran piutang plasma'],

            // Sub Kriteria 7 - Pembayaran Eksploitasi Lainnya
            ['id_item_sub_kriteria' => 101, 'id_sub_kriteria' => 7, 'nama_item_sub_kriteria' => 'Kompensasi Tetap'],
            ['id_item_sub_kriteria' => 102, 'id_sub_kriteria' => 7, 'nama_item_sub_kriteria' => 'Kopi'],
            ['id_item_sub_kriteria' => 103, 'id_sub_kriteria' => 7, 'nama_item_sub_kriteria' => 'Dana TJSL'],
            ['id_item_sub_kriteria' => 104, 'id_sub_kriteria' => 7, 'nama_item_sub_kriteria' => 'Pinbuk ke HO Pendahuluan PPn'],
            ['id_item_sub_kriteria' => 105, 'id_sub_kriteria' => 7, 'nama_item_sub_kriteria' => 'Biaya Eks Konversi Karet'],
            ['id_item_sub_kriteria' => 106, 'id_sub_kriteria' => 7, 'nama_item_sub_kriteria' => 'Bibit Dumai, PSR dan Analisa Produk'],
            ['id_item_sub_kriteria' => 107, 'id_sub_kriteria' => 7, 'nama_item_sub_kriteria' => 'Beban Rumah Sakit'],
            ['id_item_sub_kriteria' => 108, 'id_sub_kriteria' => 7, 'nama_item_sub_kriteria' => 'Kas Lainnya'],
            ['id_item_sub_kriteria' => 109, 'id_sub_kriteria' => 7, 'nama_item_sub_kriteria' => 'BPJS TK'],
            ['id_item_sub_kriteria' => 110, 'id_sub_kriteria' => 7, 'nama_item_sub_kriteria' => 'Iuran Dapenbun Tambahan'],

            // Sub Kriteria 8 - Pajak
            ['id_item_sub_kriteria' => 111, 'id_sub_kriteria' => 8, 'nama_item_sub_kriteria' => 'PBB'],
            ['id_item_sub_kriteria' => 112, 'id_sub_kriteria' => 8, 'nama_item_sub_kriteria' => 'PPN'],
            ['id_item_sub_kriteria' => 113, 'id_sub_kriteria' => 8, 'nama_item_sub_kriteria' => 'PPh 23'],
            ['id_item_sub_kriteria' => 114, 'id_sub_kriteria' => 8, 'nama_item_sub_kriteria' => 'PPh Pasal 4 ayat (2)'],
            ['id_item_sub_kriteria' => 115, 'id_sub_kriteria' => 8, 'nama_item_sub_kriteria' => 'PPh Pasal 25'],
            ['id_item_sub_kriteria' => 116, 'id_sub_kriteria' => 8, 'nama_item_sub_kriteria' => 'BPHTB'],
            ['id_item_sub_kriteria' => 117, 'id_sub_kriteria' => 8, 'nama_item_sub_kriteria' => 'PPh Badan'],
            ['id_item_sub_kriteria' => 118, 'id_sub_kriteria' => 8, 'nama_item_sub_kriteria' => 'PPh Masa'],

            // Sub Kriteria 9 - Investasi On Farm
            ['id_item_sub_kriteria' => 119, 'id_sub_kriteria' => 9, 'nama_item_sub_kriteria' => 'Pekerjaan TU,TK,TB.'],
            ['id_item_sub_kriteria' => 120, 'id_sub_kriteria' => 9, 'nama_item_sub_kriteria' => 'Pekerjaan Pemeliharaan TBM (Pupuk)'],
            ['id_item_sub_kriteria' => 121, 'id_sub_kriteria' => 9, 'nama_item_sub_kriteria' => 'Pekerjaan Pemeliharaan TBM diluar Pupuk'],
            ['id_item_sub_kriteria' => 122, 'id_sub_kriteria' => 9, 'nama_item_sub_kriteria' => 'Pembangunan bibitan'],
            ['id_item_sub_kriteria' => 123, 'id_sub_kriteria' => 9, 'nama_item_sub_kriteria' => 'Pekerjaan Tanaman Ulang (TU)'],
            ['id_item_sub_kriteria' => 124, 'id_sub_kriteria' => 9, 'nama_item_sub_kriteria' => 'Pekerjaan Tanaman Konversi (TK)'],
            ['id_item_sub_kriteria' => 125, 'id_sub_kriteria' => 9, 'nama_item_sub_kriteria' => 'Pekerjaan Tanaman Baru'],

            // Sub Kriteria 10 - Investasi Off Farm
            ['id_item_sub_kriteria' => 126, 'id_sub_kriteria' => 10, 'nama_item_sub_kriteria' => 'Pekerjaan Pembangunan Rumah Perusahaan'],
            ['id_item_sub_kriteria' => 127, 'id_sub_kriteria' => 10, 'nama_item_sub_kriteria' => 'Pekerjaan Bangunan Perusahaan'],
            ['id_item_sub_kriteria' => 128, 'id_sub_kriteria' => 10, 'nama_item_sub_kriteria' => 'Pekerjaaan Pembangunan Mesin dan Instalasi'],
            ['id_item_sub_kriteria' => 129, 'id_sub_kriteria' => 10, 'nama_item_sub_kriteria' => 'Pekerjaan Pembangunan Jalan'],
            ['id_item_sub_kriteria' => 130, 'id_sub_kriteria' => 10, 'nama_item_sub_kriteria' => 'Pekerjaan Pembangunan Jembatan'],
            ['id_item_sub_kriteria' => 131, 'id_sub_kriteria' => 10, 'nama_item_sub_kriteria' => 'Pekerjaan Pembangunan Saluran Air'],
            ['id_item_sub_kriteria' => 132, 'id_sub_kriteria' => 10, 'nama_item_sub_kriteria' => 'Pekerjaan Alat Transportasi (Infrastruktur)'],
            ['id_item_sub_kriteria' => 133, 'id_sub_kriteria' => 10, 'nama_item_sub_kriteria' => 'Pekerjaan Alat Transportasi (Investasi Tanaman)'],
            ['id_item_sub_kriteria' => 134, 'id_sub_kriteria' => 10, 'nama_item_sub_kriteria' => 'Pekerjaan Alat Berat'],
            ['id_item_sub_kriteria' => 135, 'id_sub_kriteria' => 10, 'nama_item_sub_kriteria' => 'Pekerjaan Inventaris Kecil'],
            ['id_item_sub_kriteria' => 136, 'id_sub_kriteria' => 10, 'nama_item_sub_kriteria' => 'Pekerjaan Investasi Off Farm Lainnya (DTPL)'],
            ['id_item_sub_kriteria' => 137, 'id_sub_kriteria' => 10, 'nama_item_sub_kriteria' => 'Pekerjaan Investasi Off Farm Lainnya (DITN)'],
            ['id_item_sub_kriteria' => 138, 'id_sub_kriteria' => 10, 'nama_item_sub_kriteria' => 'Pekerjaan Investasi Off Farm Lainnya (DINF)'],
            ['id_item_sub_kriteria' => 139, 'id_sub_kriteria' => 10, 'nama_item_sub_kriteria' => 'KSO'],
            ['id_item_sub_kriteria' => 140, 'id_sub_kriteria' => 10, 'nama_item_sub_kriteria' => 'Penyertaan Modal'],

            // Sub Kriteria 11 - Pembayaran investasi lainnya
            ['id_item_sub_kriteria' => 141, 'id_sub_kriteria' => 11, 'nama_item_sub_kriteria' => 'Rumah Sakit'],
            ['id_item_sub_kriteria' => 142, 'id_sub_kriteria' => 11, 'nama_item_sub_kriteria' => 'HGU'],

            // Sub Kriteria 12 - Kebutuhan Pembayaran Aktivitas Pendanaan
            ['id_item_sub_kriteria' => 143, 'id_sub_kriteria' => 12, 'nama_item_sub_kriteria' => 'Pembayaran Deviden'],

            // Sub Kriteria 13 - Pembayaran Pokok Pinjaman
            ['id_item_sub_kriteria' => 144, 'id_sub_kriteria' => 13, 'nama_item_sub_kriteria' => 'BNI'],
            ['id_item_sub_kriteria' => 145, 'id_sub_kriteria' => 13, 'nama_item_sub_kriteria' => 'BNI Syariah'],
            ['id_item_sub_kriteria' => 146, 'id_sub_kriteria' => 13, 'nama_item_sub_kriteria' => 'Maybank'],
            ['id_item_sub_kriteria' => 147, 'id_sub_kriteria' => 13, 'nama_item_sub_kriteria' => 'LPEI'],
            ['id_item_sub_kriteria' => 148, 'id_sub_kriteria' => 13, 'nama_item_sub_kriteria' => 'Kepri'],
            ['id_item_sub_kriteria' => 149, 'id_sub_kriteria' => 13, 'nama_item_sub_kriteria' => 'Permata'],
            ['id_item_sub_kriteria' => 150, 'id_sub_kriteria' => 13, 'nama_item_sub_kriteria' => 'DBS'],
            ['id_item_sub_kriteria' => 151, 'id_sub_kriteria' => 13, 'nama_item_sub_kriteria' => 'ICBC'],
            ['id_item_sub_kriteria' => 152, 'id_sub_kriteria' => 13, 'nama_item_sub_kriteria' => 'UOB'],
            ['id_item_sub_kriteria' => 153, 'id_sub_kriteria' => 13, 'nama_item_sub_kriteria' => 'Victoria'],
            ['id_item_sub_kriteria' => 154, 'id_sub_kriteria' => 13, 'nama_item_sub_kriteria' => 'QNB'],
            ['id_item_sub_kriteria' => 155, 'id_sub_kriteria' => 13, 'nama_item_sub_kriteria' => 'SMBC Sindikasi 8'],
            ['id_item_sub_kriteria' => 156, 'id_sub_kriteria' => 13, 'nama_item_sub_kriteria' => 'MTN & Sukuk'],
            ['id_item_sub_kriteria' => 157, 'id_sub_kriteria' => 13, 'nama_item_sub_kriteria' => 'BCA'],
            ['id_item_sub_kriteria' => 158, 'id_sub_kriteria' => 13, 'nama_item_sub_kriteria' => 'BMRI'],

            // Sub Kriteria 14 - Pembayaran Bunga Pinjaman
            ['id_item_sub_kriteria' => 159, 'id_sub_kriteria' => 14, 'nama_item_sub_kriteria' => 'BCA'],
            ['id_item_sub_kriteria' => 160, 'id_sub_kriteria' => 14, 'nama_item_sub_kriteria' => 'BMRI'],
            ['id_item_sub_kriteria' => 161, 'id_sub_kriteria' => 14, 'nama_item_sub_kriteria' => 'BNI'],
            ['id_item_sub_kriteria' => 162, 'id_sub_kriteria' => 14, 'nama_item_sub_kriteria' => 'BNI Syariah'],
            ['id_item_sub_kriteria' => 163, 'id_sub_kriteria' => 14, 'nama_item_sub_kriteria' => 'Maybank'],
            ['id_item_sub_kriteria' => 164, 'id_sub_kriteria' => 14, 'nama_item_sub_kriteria' => 'LPEI'],
            ['id_item_sub_kriteria' => 165, 'id_sub_kriteria' => 14, 'nama_item_sub_kriteria' => 'Kepri'],
            ['id_item_sub_kriteria' => 166, 'id_sub_kriteria' => 14, 'nama_item_sub_kriteria' => 'Permata'],
            ['id_item_sub_kriteria' => 167, 'id_sub_kriteria' => 14, 'nama_item_sub_kriteria' => 'DBS'],
            ['id_item_sub_kriteria' => 168, 'id_sub_kriteria' => 14, 'nama_item_sub_kriteria' => 'ICBC'],
            ['id_item_sub_kriteria' => 169, 'id_sub_kriteria' => 14, 'nama_item_sub_kriteria' => 'UOB'],
            ['id_item_sub_kriteria' => 170, 'id_sub_kriteria' => 14, 'nama_item_sub_kriteria' => 'Victoria'],
            ['id_item_sub_kriteria' => 171, 'id_sub_kriteria' => 14, 'nama_item_sub_kriteria' => 'QNB'],
            ['id_item_sub_kriteria' => 172, 'id_sub_kriteria' => 14, 'nama_item_sub_kriteria' => 'SMBC Sindikasi 8'],
            ['id_item_sub_kriteria' => 173, 'id_sub_kriteria' => 14, 'nama_item_sub_kriteria' => 'MTN & Sukuk'],

            // Sub Kriteria 15 - Pembayaran Kepada Pihak Berelasi
            ['id_item_sub_kriteria' => 174, 'id_sub_kriteria' => 15, 'nama_item_sub_kriteria' => 'Penyaluran SHL Kepada PT SGN'],
            ['id_item_sub_kriteria' => 175, 'id_sub_kriteria' => 15, 'nama_item_sub_kriteria' => 'REGIONAL III'],
            ['id_item_sub_kriteria' => 176, 'id_sub_kriteria' => 15, 'nama_item_sub_kriteria' => 'Pembayaran Kompensasi Tetap dan Variabel ke N8'],
            ['id_item_sub_kriteria' => 177, 'id_sub_kriteria' => 15, 'nama_item_sub_kriteria' => 'Pembayaran DSAA ke PTPN IV'],
            ['id_item_sub_kriteria' => 178, 'id_sub_kriteria' => 15, 'nama_item_sub_kriteria' => 'Pembayaran DSRA ke PTPN IV'],

            // Sub Kriteria 16 - PEN Pembayaran Pokok Pinjaman
            ['id_item_sub_kriteria' => 179, 'id_sub_kriteria' => 16, 'nama_item_sub_kriteria' => 'PEN Tranche A'],
            ['id_item_sub_kriteria' => 180, 'id_sub_kriteria' => 16, 'nama_item_sub_kriteria' => 'PEN Tranche B'],
            ['id_item_sub_kriteria' => 181, 'id_sub_kriteria' => 16, 'nama_item_sub_kriteria' => 'PEN Tranche C'],

            // Sub Kriteria 17 - PEN Pembayaran Bunga Pinjaman
            ['id_item_sub_kriteria' => 182, 'id_sub_kriteria' => 17, 'nama_item_sub_kriteria' => 'PEN Tranche A'],
            ['id_item_sub_kriteria' => 183, 'id_sub_kriteria' => 17, 'nama_item_sub_kriteria' => 'PEN Tranche B'],
            ['id_item_sub_kriteria' => 184, 'id_sub_kriteria' => 17, 'nama_item_sub_kriteria' => 'PEN Tranche C'],

            // Sub Kriteria 18 - Pembayaran Pendanaan Lainnya
            ['id_item_sub_kriteria' => 185, 'id_sub_kriteria' => 18, 'nama_item_sub_kriteria' => 'Biaya Adm Bank'],
        ];

        foreach ($data as $item) {
            DB::table('item_sub_kriteria')->updateOrInsert(
                ['id_item_sub_kriteria' => $item['id_item_sub_kriteria']],
                array_merge($item, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
