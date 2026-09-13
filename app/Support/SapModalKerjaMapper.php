<?php

namespace App\Support;

class SapModalKerjaMapper
{
    public const KAT_GAJI = 'Kebutuhan Gaji, Upah dan Tunjangan';
    public const KAT_OPS  = 'Payment Requirement for Exploitation Activity';
    public const KAT_INV  = 'Kebutuhan Pembayaran Pekerjaan Aktivitas Investasi';
    public const KAT_FIN  = 'Kebutuhan Pembayaran Aktivitas Pendanaan';
    public const KAT_LAIN = 'Akun SAP Lainnya';

    /**
     * Peta langsung dari reference_key_1 ke [Kategori, Sub Kriteria, Item Sub Kriteria]
     * Berdasarkan kamus baku tabel cashflow_references (SAP S/4HANA).
     *
     * @var array<string, array{0: string, 1: string, 2: string}>
     */
    protected static array $directMap = [
        // =========================================================================
        // 1. KEBUTUHAN GAJI, UPAH DAN TUNJANGAN (Parent: A0202)
        // =========================================================================
        // Header
        'A0202000' => [self::KAT_GAJI, 'Karyawan Pelaksana', 'Beban Karyawan Lainnya (A0202000)'],

        // Karyawan Pimpinan (Karpim)
        'A0202001' => [self::KAT_GAJI, 'Karyawan Pimpinan', 'Gaji dan Tunjangan'],
        'A0202006' => [self::KAT_GAJI, 'Karyawan Pimpinan', 'Premi'],
        'A0202008' => [self::KAT_GAJI, 'Karyawan Pimpinan', 'Cuti Tahunan'],
        'A0202010' => [self::KAT_GAJI, 'Karyawan Pimpinan', 'Cuti Panjang'],
        'A0202012' => [self::KAT_GAJI, 'Karyawan Pimpinan', 'T H R'],
        'A0202016' => [self::KAT_GAJI, 'Karyawan Pimpinan', 'Bonus'],
        'A0202020' => [self::KAT_GAJI, 'Karyawan Pimpinan', 'PPh pasal 21'],
        'A0202022' => [self::KAT_GAJI, 'Karyawan Pimpinan', 'Bantuan Anak Sekolah'],
        'A0202024' => [self::KAT_GAJI, 'Karyawan Pimpinan', 'Iuran Dapenbun (Normal)'],
        'A0202026' => [self::KAT_GAJI, 'Karyawan Pimpinan', 'Penghargaan Masa Kerja'],
        'A0202028' => [self::KAT_GAJI, 'Karyawan Pimpinan', 'Iuran BPJS B. Perusahaan'],
        'A0202030' => [self::KAT_GAJI, 'Karyawan Pimpinan', 'SHT (Cicilan)'],

        // Karyawan Pelaksana (Karpel)
        'A0202002' => [self::KAT_GAJI, 'Karyawan Pelaksana', 'Gaji dan Tunjangan'],
        'A0202005' => [self::KAT_GAJI, 'Karyawan Pelaksana', 'Lembur'],
        'A0202007' => [self::KAT_GAJI, 'Karyawan Pelaksana', 'Premi'],
        'A0202009' => [self::KAT_GAJI, 'Karyawan Pelaksana', 'Cuti Tahunan'],
        'A0202011' => [self::KAT_GAJI, 'Karyawan Pelaksana', 'Cuti Panjang'],
        'A0202013' => [self::KAT_GAJI, 'Karyawan Pelaksana', 'T H R'],
        'A0202017' => [self::KAT_GAJI, 'Karyawan Pelaksana', 'Bonus'],
        'A0202021' => [self::KAT_GAJI, 'Karyawan Pelaksana', 'PPh pasal 21'],
        'A0202023' => [self::KAT_GAJI, 'Karyawan Pelaksana', 'Bantuan Anak Sekolah'],
        'A0202025' => [self::KAT_GAJI, 'Karyawan Pelaksana', 'Iuran Dapenbun (Normal)'],
        'A0202027' => [self::KAT_GAJI, 'Karyawan Pelaksana', 'Penghargaan Masa Kerja'],
        'A0202029' => [self::KAT_GAJI, 'Karyawan Pelaksana', 'Iuran BPJS B. Perusahaan'],
        'A0202031' => [self::KAT_GAJI, 'Karyawan Pelaksana', 'SHT (Cicilan)'],
        'A0202034' => [self::KAT_GAJI, 'Karyawan Pelaksana', 'Lainnya'],

        // Karyawan PKWT
        'A0202003' => [self::KAT_GAJI, 'Karyawan PKWT', 'Gaji dan Tunjangan'],
        'A0202014' => [self::KAT_GAJI, 'Karyawan PKWT', 'T H R'],
        'A0202018' => [self::KAT_GAJI, 'Karyawan PKWT', 'Bonus'],
        'A0202032' => [self::KAT_GAJI, 'Karyawan PKWT', 'SHT (Cicilan)'],

        // Gaji Honor
        'A0202004' => [self::KAT_GAJI, 'Gaji Honor', 'Gaji dan Tunjangan'],
        'A0202015' => [self::KAT_GAJI, 'Gaji Honor', 'T H R'],
        'A0202019' => [self::KAT_GAJI, 'Gaji Honor', 'Bonus'],
        'A0202033' => [self::KAT_GAJI, 'Gaji Honor', 'SHT (Cicilan)'],
        'A0202035' => [self::KAT_GAJI, 'Gaji Honor', 'Keamanan'],
        'A0202036' => [self::KAT_GAJI, 'Gaji Honor', 'Guru'],
        'A0202037' => [self::KAT_GAJI, 'Gaji Honor', 'Dokter'],
        'A0202038' => [self::KAT_GAJI, 'Gaji Honor', 'Lainnya'],

        // =========================================================================
        // 2. PAYMENT REQUIREMENT FOR EXPLOITATION ACTIVITY (Parent: A0201, A0203..A0209)
        // =========================================================================
        // TBS (FFB)
        'A0201011' => [self::KAT_OPS, 'TBS (FFB)', 'Pembelian TBS'],

        // Operasional Produksi (A0201, A0203, A0204)
        'A0201000' => [self::KAT_OPS, 'Operasional Produksi', 'Pembayaran kas kepada pemasok'],
        'A0201001' => [self::KAT_OPS, 'Operasional Produksi', 'Pemeliharaan Tanaman Menghasilkan'],
        'A0201002' => [self::KAT_OPS, 'Operasional Produksi', 'Pemupukan (Bahan)'],
        'A0201003' => [self::KAT_OPS, 'Operasional Produksi', 'Pemupukan (Aplikasi)'],
        'A0201004' => [self::KAT_OPS, 'Operasional Produksi', 'Panen & Pengumpulan'],
        'A0201005' => [self::KAT_OPS, 'Operasional Produksi', 'Pengangkutan'],
        'A0201006' => [self::KAT_OPS, 'Operasional Produksi', 'Pengolahan'],
        'A0201012' => [self::KAT_OPS, 'Operasional Produksi', 'Pembelian Bokar'],
        'A0201013' => [self::KAT_OPS, 'Operasional Produksi', 'Pembelian Teh'],
        'A0201015' => [self::KAT_OPS, 'Operasional Produksi', 'Pengadaan Raw Sugar'],
        'A0201016' => [self::KAT_OPS, 'Operasional Produksi', 'Pembelian CPO'],
        'A0201017' => [self::KAT_OPS, 'Operasional Produksi', 'Pembelian Kernel'],
        'A0201018' => [self::KAT_OPS, 'Operasional Produksi', 'Pembelian PKO'],
        'A0201019' => [self::KAT_OPS, 'Operasional Produksi', 'Pembelian PKM'],
        'A0201020' => [self::KAT_OPS, 'Operasional Produksi', 'Pembelian Gula'],
        'A0201021' => [self::KAT_OPS, 'Operasional Produksi', 'Pembelian Tetes'],
        'A0201022' => [self::KAT_OPS, 'Operasional Produksi', 'Pembelian Kopi'],
        'A0201023' => [self::KAT_OPS, 'Operasional Produksi', 'Pembelian Kakao'],
        'A0201024' => [self::KAT_OPS, 'Operasional Produksi', 'Pembelian Tembakau'],
        'A0201025' => [self::KAT_OPS, 'Operasional Produksi', 'Pembelian Bahan Bakar Minyak (BBM)'],
        'A0201026' => [self::KAT_OPS, 'Operasional Produksi', 'Penyaluran ke petani tebu rakyat'],
        'A0201028' => [self::KAT_OPS, 'Operasional Produksi', 'Lainnya'],
        'A0203001' => [self::KAT_OPS, 'Operasional Produksi', 'Angsuran Pinjaman Petani ke Bank'],
        'A0204001' => [self::KAT_OPS, 'Operasional Produksi', 'Penyaluran Dana ke Petani Rakyat'],

        // Biaya Usaha dan lainnya (A0207, A0208, A0209)
        'A0207001' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Pembayaran Tantiem'],
        'A0208001' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Pembayaran Program PKBL'],
        'A0209000' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Pembayaran kas lainnya'],
        'A0209001' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Biaya Pengiriman ke Pelabuhan'],
        'A0209002' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Biaya Sewa Gudang'],
        'A0209003' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Biaya Instalasi Pemompaan'],
        'A0209004' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Biaya Pelabuhan'],
        'A0209005' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Biaya Jasa KPBN'],
        'A0209006' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Biaya Pemasaran Lainnya'],
        'A0209007' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Biaya Pengangkutan, Perjalanan & Penginapan'],
        'A0209008' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Biaya Pemeliharaan Bangunan, Mesin, Jalan dan Instalasi'],
        'A0209009' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Biaya Pemeliharaan Perlengkapan Kantor'],
        'A0209010' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Biaya Pajak dan Retribusi'],
        'A0209011' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Biaya Premi Asuransi'],
        'A0209012' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Biaya Keamanan'],
        'A0209013' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Biaya Mutu (ISO 9000)'],
        'A0209014' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Biaya Pengendalian Lingkungan (ISO 14000)'],
        'A0209015' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Biaya Sistem Manajemen Kesehatan & Keselamatan Kerja'],
        'A0209016' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Biaya Penelitian dan Percobaan'],
        'A0209017' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Biaya Sumbangan, Iuran dan CSR'],
        'A0209018' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Biaya Pendidikan dan Pengembangan SDM'],
        'A0209019' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Biaya Konsultan'],
        'A0209020' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Biaya Audit'],
        'A0209021' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Utilities (Air, Listrik, ATK, Brg Umum, Sewa Kantor)'],
        'A0209022' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Biaya Distrik'],
        'A0209023' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Biaya Institusi Terkait'],
        'A0209024' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Biaya Kantor Perwakilan'],
        'A0209025' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Biaya Komisaris'],
        'A0209026' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Biaya Media'],
        'A0209027' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Biaya Rapat'],
        'A0209028' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Biaya Telekomunikasi dan Ekspedisi'],
        'A0209029' => [self::KAT_OPS, 'Biaya Usaha dan lainnya', 'Lainnya'],

        // Pajak (A0205)
        'A0205000' => [self::KAT_OPS, 'Pajak', 'Pembayaran Pajak'],
        'A0205001' => [self::KAT_OPS, 'Pajak', 'PPh Badan'],
        'A0205002' => [self::KAT_OPS, 'Pajak', 'PBB'],
        'A0205003' => [self::KAT_OPS, 'Pajak', 'PPH Masa'],
        'A0205004' => [self::KAT_OPS, 'Pajak', 'PPN'],

        // =========================================================================
        // 3. KEBUTUHAN PEMBAYARAN PEKERJAAN AKTIVITAS INVESTASI (Parent: B02...)
        // =========================================================================
        // Investasi On Farm
        'B0202001' => [self::KAT_INV, 'Investasi On Farm', 'Pekerjaan TU, TK, TB Tanaman Perkebunan'],
        'B0202002' => [self::KAT_INV, 'Investasi On Farm', 'Pekerjaan Pemeliharaan TBM: Pupuk'],
        'B0202003' => [self::KAT_INV, 'Investasi On Farm', 'Pekerjaan Pemeliharaan TBM: Di Luar Pupuk'],
        'B0205000' => [self::KAT_INV, 'Investasi On Farm', 'Penambahan Pembibitan'],
        'B0205001' => [self::KAT_INV, 'Investasi On Farm', 'Penambahan Pembibitan'],
        'B0207001' => [self::KAT_INV, 'Investasi On Farm', 'Penambahan Tanaman Aneka Kayu'],
        'B0208000' => [self::KAT_INV, 'Investasi On Farm', 'Penambahan Tanaman Semusim'],
        'B0208001' => [self::KAT_INV, 'Investasi On Farm', 'Pekerjaan TU, TK, TB Tanaman Semusim'],
        'B0208002' => [self::KAT_INV, 'Investasi On Farm', 'Tanaman Semusim: Pupuk'],
        'B0208003' => [self::KAT_INV, 'Investasi On Farm', 'Tanaman Semusim: Pemeliharaan TBM diluar Pupuk'],

        // Investasi Off Farm
        'B0201000' => [self::KAT_INV, 'Investasi Off Farm', 'Penambahan Properti Investasi'],
        'B0201001' => [self::KAT_INV, 'Investasi Off Farm', 'Pekerjaan Pembangunan Rumah'],
        'B0201002' => [self::KAT_INV, 'Investasi Off Farm', 'Pekerjaan Pembangunan Perusahaan'],
        'B0203000' => [self::KAT_INV, 'Investasi Off Farm', 'Penambahan Aset Tetap'],
        'B0203001' => [self::KAT_INV, 'Investasi Off Farm', 'Pekerjaan Pembangunan Mesin dan Instalasi'],
        'B0203002' => [self::KAT_INV, 'Investasi Off Farm', 'Pekerjaan Alat Angkutan'],
        'B0203003' => [self::KAT_INV, 'Investasi Off Farm', 'Pekerjaan Pembangunan Jalan, Jembatan dan Saluran Air'],
        'B0203004' => [self::KAT_INV, 'Investasi Off Farm', 'Pekerjaan Inventaris Kecil'],
        'B0203005' => [self::KAT_INV, 'Investasi Off Farm', 'Pekerjaan Investasi Off Farm Lainnya'],
        'B0204000' => [self::KAT_INV, 'Investasi Off Farm', 'Penambahan Aset Tidak Lancar Lainnya'],
        'B0204001' => [self::KAT_INV, 'Investasi Off Farm', 'Penyertaan Modal'],
        'B0204002' => [self::KAT_INV, 'Investasi Off Farm', 'KSO'],
        'B0204003' => [self::KAT_INV, 'Investasi Off Farm', 'Penambahan Aset Tidak Lancar Lainnya'],
        'B0206001' => [self::KAT_INV, 'Investasi Off Farm', 'Penambahan Investasi pada Entitas Asosiasi'],
        'B0209001' => [self::KAT_INV, 'Investasi Off Farm', 'Penambahan Aset Tak Berwujud'],
        'B0213001' => [self::KAT_INV, 'Investasi Off Farm', 'Uang Muka Kontraktor'],
        'B0215001' => [self::KAT_INV, 'Investasi Off Farm', 'Penempatan Deposito'],

        // Pembayaran investasi lainnya
        'B0103024' => [self::KAT_INV, 'Pembayaran investasi lainnya', 'Penerimaan dari Penjualan Aset Tetap'],

        // =========================================================================
        // 4. KEBUTUHAN PEMBAYARAN AKTIVITAS PENDANAAN (Parent: A0206)
        // =========================================================================
        'A0206000' => [self::KAT_FIN, 'Pembayaran Bunga Pinjaman', 'Pembayaran Bunga'],
        'A0206001' => [self::KAT_FIN, 'Pembayaran Bunga Pinjaman', 'Pembayaran bunga ke PTPN I'],
        'A0206002' => [self::KAT_FIN, 'Pembayaran Bunga Pinjaman', 'Pembayaran bunga ke PTPN II'],
        'A0206003' => [self::KAT_FIN, 'Pembayaran Bunga Pinjaman', 'Pembayaran bunga ke PTPN III'],
        'A0206004' => [self::KAT_FIN, 'Pembayaran Bunga Pinjaman', 'Pembayaran bunga ke PTPN IV'],
        'A0206005' => [self::KAT_FIN, 'Pembayaran Bunga Pinjaman', 'Pembayaran bunga ke PTPN V'],
        'A0206006' => [self::KAT_FIN, 'Pembayaran Bunga Pinjaman', 'Pembayaran bunga ke PTPN VI'],
        'A0206007' => [self::KAT_FIN, 'Pembayaran Bunga Pinjaman', 'Pembayaran bunga ke PTPN VII'],
        'A0206008' => [self::KAT_FIN, 'Pembayaran Bunga Pinjaman', 'Pembayaran bunga ke PTPN VIII'],
        'A0206009' => [self::KAT_FIN, 'Pembayaran Bunga Pinjaman', 'Pembayaran bunga ke PTPN IX'],
        'A0206010' => [self::KAT_FIN, 'Pembayaran Bunga Pinjaman', 'Pembayaran bunga ke PTPN X'],
        'A0206011' => [self::KAT_FIN, 'Pembayaran Bunga Pinjaman', 'Pembayaran bunga ke PTPN XI'],
        'A0206012' => [self::KAT_FIN, 'Pembayaran Bunga Pinjaman', 'Pembayaran bunga ke PTPN XII'],
        'A0206013' => [self::KAT_FIN, 'Pembayaran Bunga Pinjaman', 'Pembayaran bunga ke PTPN XIII'],
        'A0206014' => [self::KAT_FIN, 'Pembayaran Bunga Pinjaman', 'Pembayaran bunga ke PTPN XIV'],
        'A0206015' => [self::KAT_FIN, 'Pembayaran Bunga Pinjaman', 'Pembayaran bunga ke PT INL'],
        'A0206016' => [self::KAT_FIN, 'Pembayaran Bunga Pinjaman', 'Pembayaran bunga ke PT KPBN'],
        'A0206017' => [self::KAT_FIN, 'Pembayaran Bunga Pinjaman', 'Pembayaran bunga ke PT RPN'],
        'A0206018' => [self::KAT_FIN, 'Pembayaran Bunga Pinjaman', 'Pembayaran bunga ke PT BIN'],
        'A0206019' => [self::KAT_FIN, 'Pembayaran Bunga Pinjaman', 'Pembayaran bunga ke PT IKN'],
        'A0206020' => [self::KAT_FIN, 'Pembayaran Bunga Pinjaman', 'Pembayaran bunga ke PT SPMN'],
        'A0206021' => [self::KAT_FIN, 'Pembayaran Bunga Pinjaman', 'Pembayaran bunga ke PT KINRA'],
        'A0206022' => [self::KAT_FIN, 'Pembayaran Bunga Pinjaman', 'Pembayaran bunga ke PT LPP'],
        'A0206023' => [self::KAT_FIN, 'Pembayaran Bunga Pinjaman', 'Pembayaran bunga ke PT SGN'],
        'A0206024' => [self::KAT_FIN, 'Pembayaran Bunga Pinjaman', 'Pembayaran bunga ke Mandiri'],
        'A0206025' => [self::KAT_FIN, 'Pembayaran Bunga Pinjaman', 'Pembayaran bunga ke BNI'],
        'A0206026' => [self::KAT_FIN, 'Pembayaran Bunga Pinjaman', 'Pembayaran bunga ke BRI'],
        'A0206027' => [self::KAT_FIN, 'Pembayaran Bunga Pinjaman', 'Pembayaran bunga ke SMBC'],
        'A0206028' => [self::KAT_FIN, 'Pembayaran Bunga Pinjaman', 'Pembayaran bunga ke LPEI'],
        'A0206029' => [self::KAT_FIN, 'Pembayaran Bunga Pinjaman', 'Pembayaran bunga ke DBS'],
        'A0206030' => [self::KAT_FIN, 'Pembayaran Bunga Pinjaman', 'Pembayaran bunga ke BCA'],
        'A0206031' => [self::KAT_FIN, 'Pembayaran Bunga Pinjaman', 'Pembayaran bunga ke ICBC'],
        'A0206032' => [self::KAT_FIN, 'Pembayaran Bunga Pinjaman', 'Pembayaran bunga ke QNB'],
        'A0206033' => [self::KAT_FIN, 'Pembayaran Bunga Pinjaman', 'Pembayaran bunga ke Maybank'],
        'A0206034' => [self::KAT_FIN, 'Pembayaran Bunga Pinjaman', 'Pembayaran bunga ke UOB'],
        'A0206035' => [self::KAT_FIN, 'Pembayaran Bunga Pinjaman', 'Pembayaran bunga ke BSI'],
        'A0206036' => [self::KAT_FIN, 'Pembayaran Bunga Pinjaman', 'Pembayaran bunga ke Permata'],
        'A0206037' => [self::KAT_FIN, 'Pembayaran Bunga Pinjaman', 'Pembayaran bunga ke BRI Agro'],
        'A0206038' => [self::KAT_FIN, 'Pembayaran Bunga Pinjaman', 'Pembayaran bunga ke SMI'],
        'A0206039' => [self::KAT_FIN, 'Pembayaran Bunga Pinjaman', 'Pembayaran bunga ke Victoria'],
        'A0206040' => [self::KAT_FIN, 'Pembayaran Bunga Pinjaman', 'Pembayaran bunga ke Riau Kepri'],
        'A0206041' => [self::KAT_FIN, 'Pembayaran Bunga Pinjaman', 'Pembayaran bunga ke BTPN'],
        'A0206042' => [self::KAT_FIN, 'Pembayaran Bunga Pinjaman', 'Pembayaran bunga ke Bank Jatim'],
        'A0206043' => [self::KAT_FIN, 'Pembayaran Bunga Pinjaman', 'Pembayaran bunga ke Danamon'],
        'A0206044' => [self::KAT_FIN, 'Pembayaran Bunga Pinjaman', 'Pembayaran bunga ke Bank Jateng'],
        'A0206045' => [self::KAT_FIN, 'Pembayaran Bunga Pinjaman', 'Pembayaran bunga ke Muamalat'],
        'A0206046' => [self::KAT_FIN, 'Pembayaran Bunga Pinjaman', 'Pembayaran bunga ke Pihak Lainnya'],
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

        // 1. Cek direct match berdasarkan reference_key_1 baku
        if (isset(self::$directMap[$refKey])) {
            return self::$directMap[$refKey];
        }

        // 2. Heuristik berdasarkan parent_key / uraian teks
        $uLower = strtolower($uraian);
        $isKarpim = str_contains($uLower, 'karpim') || str_contains($uLower, 'pimpinan');
        $isKarpel = str_contains($uLower, 'karpel') || str_contains($uLower, 'pelaksana');
        $isPkwt   = str_contains($uLower, 'pkwt');
        $isHonor  = str_contains($uLower, 'honor');

        // Jika berada di bawah parent A0202 (Pembayaran kas kepada karyawan)
        if ($parentKey === 'A0202' || str_starts_with($refKey, 'A0202')) {
            $sub = $isKarpim ? 'Karyawan Pimpinan' : ($isKarpel ? 'Karyawan Pelaksana' : ($isPkwt ? 'Karyawan PKWT' : ($isHonor ? 'Gaji Honor' : 'Karyawan Pelaksana')));

            if (str_contains($uLower, 'gaji') || str_contains($uLower, 'tunjangan')) {
                return [self::KAT_GAJI, $sub, 'Gaji dan Tunjangan'];
            }
            if (str_contains($uLower, 'lembur')) {
                return [self::KAT_GAJI, 'Karyawan Pelaksana', 'Lembur'];
            }
            if (str_contains($uLower, 'premi')) {
                return [self::KAT_GAJI, $sub, 'Premi'];
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
            if (str_contains($uLower, 'anak sekola')) {
                return [self::KAT_GAJI, $sub, 'Bantuan Anak Sekolah'];
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
            if (str_contains($uLower, 'keamanan')) {
                return [self::KAT_GAJI, 'Gaji Honor', 'Keamanan'];
            }
            if (str_contains($uLower, 'guru')) {
                return [self::KAT_GAJI, 'Gaji Honor', 'Guru'];
            }
            if (str_contains($uLower, 'dokter')) {
                return [self::KAT_GAJI, 'Gaji Honor', 'Dokter'];
            }

            return [self::KAT_GAJI, $sub, $uraian ?: 'Lainnya'];
        }

        // Jika berada di bawah parent A0201 (Pembayaran kas kepada pemasok)
        if ($parentKey === 'A0201' || str_starts_with($refKey, 'A0201')) {
            if (str_contains($uLower, 'tbs')) {
                return [self::KAT_OPS, 'TBS (FFB)', 'Pembelian TBS'];
            }
            return [self::KAT_OPS, 'Operasional Produksi', $uraian ?: 'Operasional Produksi Lainnya'];
        }

        // Jika berada di bawah parent A0209 (Pembayaran kas lainnya / Biaya Usaha)
        if ($parentKey === 'A0209' || str_starts_with($refKey, 'A0209')) {
            return [self::KAT_OPS, 'Biaya Usaha dan lainnya', $uraian ?: 'Biaya Usaha Lainnya'];
        }

        // Jika berada di bawah parent A0205 (Pajak)
        if ($parentKey === 'A0205' || str_starts_with($refKey, 'A0205') || str_contains($uLower, 'pajak')) {
            if (str_contains($uLower, 'badan')) return [self::KAT_OPS, 'Pajak', 'PPh Badan'];
            if (str_contains($uLower, 'pbb')) return [self::KAT_OPS, 'Pajak', 'PBB'];
            if (str_contains($uLower, 'ppn')) return [self::KAT_OPS, 'Pajak', 'PPN'];
            if (str_contains($uLower, 'masa')) return [self::KAT_OPS, 'Pajak', 'PPH Masa'];
            if (str_contains($uLower, 'bphtb')) return [self::KAT_OPS, 'Pajak', 'BPHTB'];
            return [self::KAT_OPS, 'Pajak', $uraian ?: 'Pembayaran Pajak'];
        }

        // Jika berada di bawah parent A0206 (Pembayaran bunga)
        if ($parentKey === 'A0206' || str_starts_with($refKey, 'A0206') || str_contains($uLower, 'bunga')) {
            return [self::KAT_FIN, 'Pembayaran Bunga Pinjaman', $uraian ?: 'Pembayaran Bunga'];
        }

        // Jika aktivitas investasi (Awalan B)
        if (str_starts_with($refKey, 'B') || str_starts_with((string)$parentKey, 'B')) {
            $sub = 'Investasi Off Farm';
            if (str_contains($uLower, 'bibit') || str_contains($uLower, 'tanaman') || str_contains($uLower, 'tbm') || str_contains($uLower, 'on farm') || str_contains($uLower, 'perkebunan') || str_contains($uLower, 'semusim')) {
                $sub = 'Investasi On Farm';
            }
            return [self::KAT_INV, $sub, $uraian ?: ($parentName ?: 'Pekerjaan Investasi')];
        }

        // 3. Fallback: Akun SAP Lainnya (menjamin 100% rupiah transaksi tetap tampil & utuh)
        $fallbackSub = !empty($parentName) ? $parentName : (!empty($parentKey) ? "Grup {$parentKey}" : 'Transaksi Lainnya');
        $fallbackItem = !empty($uraian) ? $uraian : ($refKey ?: 'Pos Belum Terdefinisi');

        return [self::KAT_LAIN, $fallbackSub, $fallbackItem];
    }

    /**
     * Mendapatkan kode SAP standar (reference_key_1) untuk item tertentu
     */
    public static function getStandardCode(string $kategori, string $sub, string $item): ?string
    {
        static $reverseMap = null;
        if ($reverseMap === null) {
            $reverseMap = [];
            foreach (self::$directMap as $k => $v) {
                $combo = $v[0] . '|' . $v[1] . '|' . $v[2];
                $reverseMap[$combo] ??= $k;
            }
        }
        return $reverseMap[$kategori . '|' . $sub . '|' . $item] ?? null;
    }

    /**
     * Mendapatkan kode grup / parent SAP untuk sub-kategori
     */
    public static function getSubParentCode(string $sub): ?string
    {
        return match ($sub) {
            'Karyawan Pimpinan', 'Karyawan Pelaksana', 'Karyawan PKWT', 'Gaji Honor' => 'A0202',
            'TBS (FFB)', 'Operasional Produksi' => 'A0201',
            'Biaya Usaha dan lainnya' => 'A0209',
            'Pajak' => 'A0205',
            'Investasi On Farm' => 'B0202',
            'Investasi Off Farm' => 'B0203',
            'Pembayaran investasi lainnya' => 'B02',
            'Pembayaran Bunga Pinjaman', 'Pembayaran Pokok Pinjaman' => 'A0206',
            default => null,
        };
    }
}
