<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DashboardKriteriaMasterSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $gaji = $this->category('Kebutuhan Gaji, Upah dan Tunjangan');
            $eksploitasi = $this->category('Payment Requirement for Exploitation Activity');
            $investasi = $this->category('Kebutuhan Pembayaran Pekerjaan Aktivitas Investasi');

            $pimpinan = $this->ensureSub($gaji, 'Karyawan Pimpinan');
            $pelaksana = $this->ensureSub($gaji, 'Karyawan Pelaksana');

            $this->ensureItems($pimpinan, [
                'Gaji dan Tunjangan',
                'Cuti Tahunan',
                'Cuti Panjang',
                'T H R' => ['THR'],
                'Bonus',
                'PPh pasal 21' => ['PPh Pasal 21'],
                'Iuran Dapenbun (Normal)',
                'Penghargaan Masa Kerja',
                'Iuran BPJS B. Perusahaan',
                'SHT (Cicilan)',
                'Lainnya',
            ]);

            $this->ensureItems($pelaksana, [
                'Gaji dan Tunjangan',
                'Lembur',
                'Premi',
                'Cuti Tahunan',
                'Cuti Panjang',
                'T H R' => ['THR'],
                'Bonus',
                'PPh pasal 21' => ['PPh Pasal 21'],
                'Iuran Dapenbun (Normal)',
                'Iuran Dapenbun (Tambahan)',
                'Penghargaan Masa Kerja',
                'Iuran BPJS B. Perusahaan',
                'SHT (Cicilan)',
                'Lainnya',
            ]);

            $tbs = $this->ensureSub($eksploitasi, 'TBS (FFB)', ['Purchase Volume', 'Pembelian TBS']);
            $operasional = $this->ensureSub($eksploitasi, 'Operasional Produksi');
            $biayaUsaha = $this->ensureSub($eksploitasi, 'Biaya Usaha dan lainnya', ['Biaya Usaha dan Lainnya']);
            $pajak = $this->ensureSub($eksploitasi, 'Pajak');

            $this->ensureItems($tbs, [
                'TBS (FFB)',
            ]);

            $this->ensureItems($operasional, [
                'Pemeliharaan Tanaman Menghasilkan',
                'Pemupukan',
                'Bahan' => ['Bahan Pemupukan'],
                'Aplikasi Pemupukan',
                'Panen & Pengumpulan',
                'Pengangkutan' => ['Pengangkutan (TBS)'],
                'Pengolahan',
                'Pembelian Bahan Bakar Minyak (BBM)',
                'Lainnya',
            ]);

            $this->ensureItems($biayaUsaha, [
                'Biaya Pengiriman ke Pelabuhan' => ['Biaya Pengiriman ke Pelabuhan (CPO)'],
                'Biaya Pelabuhan',
                'Biaya Jasa KPBN',
                'Biaya Pemasaran Lainnya',
                'Biaya Pengangkutan, Perjalanan & Penginapan' => ['Biaya Pengangkutan, Perjalan & Penginapan'],
                'Biaya Pemeliharaan Bangunan, Mesin, Jalan dan Instalasi' => [
                    'Biaya Pemeliharaan Bangunan, Mesin, Jalan dan lainnya',
                    'Biaya Pemeliharaan Bangunan, Mesin, Jalan dan Instalasi (DTPL)',
                    'Biaya Pemeliharaan Bangunan, Mesin, Jalan dan Instalasi (DINF)',
                    'Biaya Pemeliharaan Bangunan, Mesin, Jalan dan Instalasi (DINP)',
                ],
                'Biaya Keamanan',
                'Biaya Pemeliharaan Perlengkapan Kantor',
                'Biaya Pajak dan Retribusi',
                'Biaya Premi Asuransi',
                'Biaya Pengendalian Lingkungan (ISO 14000)' => ['Biaya Pengendalian Lingkungan (ISO/RSPO)'],
                'Biaya Sistem Manajemen Kesehatan & Keselamatan Kerja',
                'Biaya Sumbangan dan Iuran',
                'Biaya CSR',
                'Biaya Pendidikan dan Pengembangan SDM',
                'Biaya Konsultan' => [
                    'Biaya Konsultan Hukum',
                    'Biaya Konsultan (DSKP)',
                    'Biaya Konsultan (DRPH)',
                    'Biaya Konsultan (DAPN)',
                    'Biaya Konsultan (DSPN)',
                    'Biaya Konsultan (DSSM)',
                    'Biaya Konsultan (DTPL)',
                ],
                'Biaya Audit',
                'Utilities (Air, Listrik, ATK, Brg Umum, Sewa Kantor)',
                'Biaya Media',
                'Lainnya',
            ]);

            $this->ensureItems($pajak, [
                'PPh Badan',
                'PBB',
                'PPH Masa' => ['PPh Masa', 'PPh 23', 'PPh Pasal 4 ayat (2)', 'PPh Pasal 25'],
                'PPN',
                'BPHTB',
                'Denda Pajak',
            ]);

            $onFarm = $this->ensureSub($investasi, 'Investasi On Farm');
            $offFarm = $this->ensureSub($investasi, 'Investasi Off Farm');
            $investasiLain = $this->ensureSub($investasi, 'Pembayaran investasi lainnya');

            $this->ensureItems($onFarm, [
                'Pekerjaan TU, TK, TB' => [
                    'Pekerjaan TU,TK,TB.',
                    'Pekerjaan Tanaman Ulang (TU)',
                    'Pekerjaan Tanaman Konversi (TK)',
                    'Pekerjaan Tanaman Baru',
                ],
                'Pekerjaan Pemeliharaan TBM' => ['Pekerjaan Pemeliharaan TBM (Pupuk)'],
                'Pupuk',
                'Pekerjaan Pemeliharaan TBM diluar Pupuk',
                'Pembangunan bibitan',
            ]);

            $this->ensureItems($offFarm, [
                'Pekerjaan Pembangunan Rumah' => ['Pekerjaan Pembangunan Rumah Perusahaan'],
                'Pekerjaan Pembangunan Perusahaan' => ['Pekerjaan Bangunan Perusahaan'],
                'Pekerjaan Pembangunan Mesin dan Instalasi' => ['Pekerjaaan Pembangunan Mesin dan Instalasi'],
                'Pekerjaan Pembangunan Jalan, Jembatan dan Saluran Air' => [
                    'Pekerjaan Pembangunan Jalan',
                    'Pekerjaan Pembangunan Jembatan',
                    'Pekerjaan Pembangunan Saluran Air',
                    'Pekerjaan Pembangunan Jalan/Jembatan/Saluran Air',
                ],
                'Pekerjaan Alat Angkutan' => [
                    'Pekerjaan Alat Transportasi (Infrastruktur)',
                    'Pekerjaan Alat Transportasi (Investasi Tanaman)',
                    'Pekerjaan Alat Berat',
                    'Pekerjaan Alat Transportasi/Alat Berat/Bengkel',
                ],
                'Pekerjaan Inventaris kecil' => ['Pekerjaan Inventaris Kecil', 'Pekerjaan Inventaris Kecil (Non Tanaman)'],
                'Pekerjaan Investasi Off Farm Lainnya' => [
                    'Pekerjaan Investasi Off Farm Lainnya (DTPL)',
                    'Pekerjaan Investasi Off Farm Lainnya (DITN)',
                    'Pekerjaan Investasi Off Farm Lainnya (DINF)',
                    'Pekerjaan Investasi Off Farm Lainnya (Non Tanaman)',
                ],
                'KSO',
                'Penyertaan Modal' => ['Penyertaan Modal (Non Tanaman)'],
            ]);

            $this->ensureItems($investasiLain, [
                'Instalasi Pembibitan',
                'HGU',
                'Pengeluaran Plasma',
                'Pengeluaran biaya PT NB',
                'Pengeluaran biaya PT KMN',
            ]);
        });
    }

    private function category(string $name): object
    {
        $row = DB::table('kategori_kriteria')
            ->whereRaw('LOWER(TRIM(nama_kriteria)) = ?', [$this->norm($name)])
            ->first();

        if (!$row) {
            throw new \RuntimeException("Kategori tidak ditemukan: {$name}");
        }

        return $row;
    }

    private function ensureSub(object $category, string $name, array $aliases = []): int
    {
        $existing = $this->findSub($category->id_kategori_kriteria, $name);
        if ($existing) {
            return (int) $existing->id_sub_kriteria;
        }

        foreach ($aliases as $alias) {
            $aliasRow = $this->findSub($category->id_kategori_kriteria, $alias);
            if ($aliasRow) {
                DB::table('sub_kriteria')
                    ->where('id_sub_kriteria', $aliasRow->id_sub_kriteria)
                    ->update([
                        'nama_sub_kriteria' => $name,
                        'updated_at' => now(),
                    ]);

                return (int) $aliasRow->id_sub_kriteria;
            }
        }

        return (int) DB::table('sub_kriteria')->insertGetId([
            'id_kategori_kriteria' => $category->id_kategori_kriteria,
            'nama_sub_kriteria' => $name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function ensureItems(int $subId, array $items): void
    {
        foreach ($items as $key => $value) {
            $name = is_int($key) ? $value : $key;
            $aliases = is_int($key) ? [] : $value;

            $this->ensureItem($subId, $name, $aliases);
        }
    }

    private function ensureItem(int $subId, string $name, array $aliases = []): int
    {
        $existing = $this->findItem($subId, $name);
        if ($existing) {
            return (int) $existing->id_item_sub_kriteria;
        }

        foreach ($aliases as $alias) {
            $aliasRow = $this->findItem($subId, $alias);
            if ($aliasRow) {
                DB::table('item_sub_kriteria')
                    ->where('id_item_sub_kriteria', $aliasRow->id_item_sub_kriteria)
                    ->update([
                        'nama_item_sub_kriteria' => $name,
                        'updated_at' => now(),
                    ]);

                return (int) $aliasRow->id_item_sub_kriteria;
            }
        }

        return (int) DB::table('item_sub_kriteria')->insertGetId([
            'id_sub_kriteria' => $subId,
            'nama_item_sub_kriteria' => $name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function findSub(int $categoryId, string $name): ?object
    {
        return DB::table('sub_kriteria')
            ->where('id_kategori_kriteria', $categoryId)
            ->whereRaw('LOWER(TRIM(nama_sub_kriteria)) = ?', [$this->norm($name)])
            ->orderBy('id_sub_kriteria')
            ->first();
    }

    private function findItem(int $subId, string $name): ?object
    {
        return DB::table('item_sub_kriteria')
            ->where('id_sub_kriteria', $subId)
            ->whereRaw('LOWER(TRIM(nama_item_sub_kriteria)) = ?', [$this->norm($name)])
            ->orderBy('id_item_sub_kriteria')
            ->first();
    }

    private function norm(string $value): string
    {
        return strtolower(trim($value));
    }
}
