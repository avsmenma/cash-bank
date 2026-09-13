<?php

namespace App\Support;

class SapModalKerjaMapper
{
    public const KAT_GAJI = 'Kebutuhan Gaji, Upah dan Tunjangan';
    public const KAT_OPS  = 'Payment Requirement for Exploitation Activity';
    public const KAT_INV  = 'Kebutuhan Pembayaran Pekerjaan Aktivitas Investasi';
    public const KAT_LAIN = 'Akun SAP Lainnya';

    /**
     * Peta langsung dari reference_key_1 ke [Kategori, Sub Kriteria, Item Sub Kriteria]
     *
     * @var array<string, array{0: string, 1: string, 2: string}>
     */
    protected static array $directMap = [
        // =========================================================================
        // 1. KEBUTUHAN GAJI, UPAH DAN TUNJANGAN (Parent: A0202)
        // =========================================================================
        // Karyawan Pimpinan (Karpim)
        'A0202001' => [self::KAT_GAJI, 'Karyawan Pimpinan', 'Gaji dan Tunjangan'],
        'A0202002' => [self::KAT_GAJI, 'Karyawan Pimpinan', 'Cuti Tahunan'],
        'A0202003' => [self::KAT_GAJI, 'Karyawan Pimpinan', 'Cuti Panjang'],
        'A0202004' => [self::KAT_GAJI, 'Karyawan Pimpinan', 'T H R'],
        'A0202005' => [self::KAT_GAJI, 'Karyawan Pimpinan', 'Bonus'],
        'A0202006' => [self::KAT_GAJI, 'Karyawan Pimpinan', 'PPh pasal 21'],
        'A0202007' => [self::KAT_GAJI, 'Karyawan Pimpinan', 'Iuran Dapenbun (Normal)'],
        'A0202008' => [self::KAT_GAJI, 'Karyawan Pimpinan', 'Penghargaan Masa Kerja'],
        'A0202009' => [self::KAT_GAJI, 'Karyawan Pimpinan', 'Iuran BPJS B. Perusahaan'],
        'A0202010' => [self::KAT_GAJI, 'Karyawan Pimpinan', 'SHT (Cicilan)'],
        'A0202011' => [self::KAT_GAJI, 'Karyawan Pimpinan', 'Lainnya'],

        // Karyawan Pelaksana (Karpel)
        'A0202012' => [self::KAT_GAJI, 'Karyawan Pelaksana', 'Gaji dan Tunjangan'],
        'A0202013' => [self::KAT_GAJI, 'Karyawan Pelaksana', 'Lembur'],
        'A0202014' => [self::KAT_GAJI, 'Karyawan Pelaksana', 'Premi'],
        'A0202015' => [self::KAT_GAJI, 'Karyawan Pelaksana', 'Cuti Tahunan'],
        'A0202016' => [self::KAT_GAJI, 'Karyawan Pelaksana', 'Cuti Panjang'],
        'A0202017' => [self::KAT_GAJI, 'Karyawan Pelaksana', 'T H R'],
        'A0202018' => [self::KAT_GAJI, 'Karyawan Pelaksana', 'Bonus'],
        'A0202019' => [self::KAT_GAJI, 'Karyawan Pelaksana', 'PPh pasal 21'],
        'A0202020' => [self::KAT_GAJI, 'Karyawan Pelaksana', 'Iuran Dapenbun (Normal)'],
        'A0202021' => [self::KAT_GAJI, 'Karyawan Pelaksana', 'Iuran Dapenbun (Tambahan)'],
        'A0202022' => [self::KAT_GAJI, 'Karyawan Pelaksana', 'Penghargaan Masa Kerja'],
        'A0202023' => [self::KAT_GAJI, 'Karyawan Pelaksana', 'Iuran BPJS B. Perusahaan'],
        'A0202024' => [self::KAT_GAJI, 'Karyawan Pelaksana', 'SHT (Cicilan)'],
        'A0202025' => [self::KAT_GAJI, 'Karyawan Pelaksana', 'Lainnya'],

        // =========================================================================
        // 2. PAYMENT REQUIREMENT FOR EXPLOITATION ACTIVITY (Parent: A0201, A0203..)
        // =========================================================================
        // TBS (FFB)
        'A0201001' => [self::KAT_OPS, 'TBS (FFB)', 'TBS (FFB)'],

        // Operasional Produksi
        'A0201002' => [self::KAT_OPS, 'Operasional Produksi', 'Pemeliharaan Tanaman Menghasilkan'],
        'A0201003' => [self::KAT_OPS, 'Operasional Produksi', 'Pemupukan'],
        'A0201004' => [self::KAT_OPS, 'Operasional Produksi', 'Bahan'],
        'A0201005' => [self::KAT_OPS, 'Operasional Produksi', 'Aplikasi Pemupukan'],
        'A0201006' => [self::KAT_OPS, 'Operasional Produksi', 'Panen & Pengumpulan'],
        'A0201007' => [self::KAT_OPS, 'Operasional Produksi', 'Pengangkutan'],
        'A0201008' => [self::KAT_OPS, 'Operasional Produksi', 'Pengolahan'],
        'A0201009' => [self::KAT_OPS, 'Operasional Produksi', 'Pembelian Bahan Bakar Minyak (BBM)'],
        'A0201010' => [self::KAT_OPS, 'Operasional Produksi', 'Lainnya'],

        // Biaya Usaha dan lainnya
        'A0201011' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Biaya Pengiriman ke Pelabuhan'],
        'A0201012' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Biaya Pelabuhan'],
        'A0201013' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Biaya Jasa KPBN'],
        'A0201014' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Biaya Pemasaran Lainnya'],
        'A0201015' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Biaya Pengangkutan, Perjalanan & Penginapan'],
        'A0201016' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Biaya Pemeliharaan Bangunan, Mesin, Jalan dan Instalasi'],
        'A0201017' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Biaya Keamanan'],
        'A0201018' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Biaya Pemeliharaan Perlengkapan Kantor'],
        'A0201019' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Biaya Pajak dan Retribusi'],
        'A0201020' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Biaya Premi Asuransi'],
        'A0201021' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Biaya Pengendalian Lingkungan (ISO 14000)'],
        'A0201022' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Biaya Sistem Manajemen Kesehatan & Keselamatan Kerja'],
        'A0201023' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Biaya Sumbangan dan Iuran'],
        'A0201024' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Biaya CSR'],
        'A0201025' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Biaya Pendidikan dan Pengembangan SDM'],
        'A0201026' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Biaya Konsultan'],
        'A0201027' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Biaya Audit'],
        'A0201028' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Utilities (Air, Listrik, ATK, Brg Umum, Sewa Kantor)'],
        'A0201029' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Biaya Media'],
        'A0201030' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Lainnya'],

        // Pajak
        'A0204001' => [self::KAT_OPS, 'Pajak', 'PPh Badan'],
        'A0204002' => [self::KAT_OPS, 'Pajak', 'PBB'],
        'A0204003' => [self::KAT_OPS, 'Pajak', 'PPH Masa'],
        'A0204004' => [self::KAT_OPS, 'Pajak', 'PPN'],
        'A0204005' => [self::KAT_OPS, 'Pajak', 'BPHTB'],
        'A0204006' => [self::KAT_OPS, 'Pajak', 'Denda Pajak'],

        // =========================================================================
        // 3. KEBUTUHAN PEMBAYARAN PEKERJAAN AKTIVITAS INVESTASI (Parent: B02...)
        // =========================================================================
        // Investasi On Farm
        'B0205001' => [self::KAT_INV, 'Investasi On Farm', 'Pekerjaan TU, TK, TB'],
        'B0205002' => [self::KAT_INV, 'Investasi On Farm', 'Pekerjaan Pemeliharaan TBM'],
        'B0205003' => [self::KAT_INV, 'Investasi On Farm', 'Pupuk'],
        'B0205004' => [self::KAT_INV, 'Investasi On Farm', 'Pekerjaan Pemeliharaan TBM diluar Pupuk'],
        'B0205005' => [self::KAT_INV, 'Investasi On Farm', 'Pembangunan bibitan'],

        // Investasi Off Farm
        'B0203001' => [self::KAT_INV, 'Investasi Off Farm', 'Pekerjaan Pembangunan Rumah'],
        'B0203002' => [self::KAT_INV, 'Investasi Off Farm', 'Pekerjaan Pembangunan Perusahaan'],
        'B0203003' => [self::KAT_INV, 'Investasi Off Farm', 'Pekerjaan Pembangunan Mesin dan Instalasi'],
        'B0203004' => [self::KAT_INV, 'Investasi Off Farm', 'Pekerjaan Pembangunan Jalan, Jembatan dan Saluran Air'],
        'B0203005' => [self::KAT_INV, 'Investasi Off Farm', 'Pekerjaan Alat Angkutan'],
        'B0203006' => [self::KAT_INV, 'Investasi Off Farm', 'Pekerjaan Inventaris kecil'],
        'B0203007' => [self::KAT_INV, 'Investasi Off Farm', 'Pekerjaan Investasi Off Farm Lainnya'],
        'B0203008' => [self::KAT_INV, 'Investasi Off Farm', 'KSO'],
        'B0203009' => [self::KAT_INV, 'Investasi Off Farm', 'Penyertaan Modal'],

        // Pembayaran investasi lainnya
        'B0203010' => [self::KAT_INV, 'Pembayaran investasi lainnya', 'Instalasi Pembibitan'],
        'B0203011' => [self::KAT_INV, 'Pembayaran investasi lainnya', 'HGU'],
        'B0203012' => [self::KAT_INV, 'Pembayaran investasi lainnya', 'Pengeluaran Plasma'],
        'B0203013' => [self::KAT_INV, 'Pembayaran investasi lainnya', 'Pengeluaran biaya PT NB'],
        'B0203014' => [self::KAT_INV, 'Pembayaran investasi lainnya', 'Pengeluaran biaya PT KMN'],
    ];

    /**
     * Memetakan reference_key_1 SAP ke hierarki [Kategori, Sub Kriteria, Item Sub Kriteria]
     *
     * @param string|null $refKey
     * @param string|null $uraian
     * @param string|null $parentKey
     * @param string|null $parentName
     * @return array{0: string, 1: string, 2: string}
     */
    public static function map(?string $refKey, ?string $uraian = null, ?string $parentKey = null, ?string $parentName = null): array
    {
        $refKey = trim((string) $refKey);
        $uraian = trim((string) $uraian);

        // 1. Cek direct match berdasarkan reference_key_1
        if (isset(self::$directMap[$refKey])) {
            return self::$directMap[$refKey];
        }

        // 2. Heuristik berdasarkan parent_key / uraian teks
        $uLower = strtolower($uraian);
        $isKarpim = str_contains($uLower, 'karpim') || str_contains($uLower, 'pimpinan');
        $isKarpel = str_contains($uLower, 'karpel') || str_contains($uLower, 'pelaksana');

        // Jika berada di bawah parent A0202 (Pembayaran kas kepada karyawan)
        if ($parentKey === 'A0202' || str_starts_with($refKey, 'A0202') || str_contains($uLower, 'karyawan')) {
            $sub = $isKarpim ? 'Karyawan Pimpinan' : ($isKarpel ? 'Karyawan Pelaksana' : 'Karyawan Pimpinan');

            if (str_contains($uLower, 'gaji') || str_contains($uLower, 'tunjangan')) {
                return [self::KAT_GAJI, $sub, 'Gaji dan Tunjangan'];
            }
            if (str_contains($uLower, 'lembur')) {
                return [self::KAT_GAJI, 'Karyawan Pelaksana', 'Lembur'];
            }
            if (str_contains($uLower, 'premi')) {
                return [self::KAT_GAJI, 'Karyawan Pelaksana', 'Premi'];
            }
            if (str_contains($uLower, 'cuti tahunan')) {
                return [self::KAT_GAJI, $sub, 'Cuti Tahunan'];
            }
            if (str_contains($uLower, 'cuti panjang')) {
                return [self::KAT_GAJI, $sub, 'Cuti Panjang'];
            }
            if (str_contains($uLower, 'thr') || str_contains($uLower, 't h r')) {
                return [self::KAT_GAJI, $sub, 'T H R'];
            }
            if (str_contains($uLower, 'bonus')) {
                return [self::KAT_GAJI, $sub, 'Bonus'];
            }
            if (str_contains($uLower, 'pph') || str_contains($uLower, 'pasal 21')) {
                return [self::KAT_GAJI, $sub, 'PPh pasal 21'];
            }
            if (str_contains($uLower, 'dapenbun') || str_contains($uLower, 'iuran pensiun')) {
                return [self::KAT_GAJI, $sub, 'Iuran Dapenbun (Normal)'];
            }
            if (str_contains($uLower, 'bpjs') || str_contains($uLower, 'jamsostek')) {
                return [self::KAT_GAJI, $sub, 'Iuran BPJS B. Perusahaan'];
            }
            if (str_contains($uLower, 'sht') || str_contains($uLower, 'santunan')) {
                return [self::KAT_GAJI, $sub, 'SHT (Cicilan)'];
            }
            if (str_contains($uLower, 'masa kerja')) {
                return [self::KAT_GAJI, $sub, 'Penghargaan Masa Kerja'];
            }

            return [self::KAT_GAJI, $sub, $uraian ?: 'Lainnya'];
        }

        // Jika berada di bawah parent A0204 (Pajak)
        if ($parentKey === 'A0204' || str_starts_with($refKey, 'A0204') || str_contains($uLower, 'pajak')) {
            if (str_contains($uLower, 'badan')) return [self::KAT_OPS, 'Pajak', 'PPh Badan'];
            if (str_contains($uLower, 'pbb')) return [self::KAT_OPS, 'Pajak', 'PBB'];
            if (str_contains($uLower, 'ppn')) return [self::KAT_OPS, 'Pajak', 'PPN'];
            if (str_contains($uLower, 'masa')) return [self::KAT_OPS, 'Pajak', 'PPH Masa'];
            if (str_contains($uLower, 'bphtb')) return [self::KAT_OPS, 'Pajak', 'BPHTB'];
            return [self::KAT_OPS, 'Pajak', $uraian ?: 'Pajak Lainnya'];
        }

        // Jika aktivitas investasi (Awalan B)
        if (str_starts_with($refKey, 'B') || str_starts_with((string)$parentKey, 'B')) {
            $sub = 'Investasi Off Farm';
            if (str_contains($uLower, 'bibit') || str_contains($uLower, 'tanaman') || str_contains($uLower, 'tbm') || str_contains($uLower, 'on farm')) {
                $sub = 'Investasi On Farm';
            }
            return [self::KAT_INV, $sub, $uraian ?: ($parentName ?: 'Pekerjaan Investasi')];
        }

        // 3. Fallback: Akun SAP Lainnya (menjamin 100% rupiah transaksi tetap tampil & utuh)
        $fallbackSub = !empty($parentName) ? $parentName : (!empty($parentKey) ? "Grup {$parentKey}" : 'Transaksi Lainnya');
        $fallbackItem = !empty($uraian) ? $uraian : ($refKey ?: 'Pos Belum Terdefinisi');

        return [self::KAT_LAIN, $fallbackSub, $fallbackItem];
    }
}
