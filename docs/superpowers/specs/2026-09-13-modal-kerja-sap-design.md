# Spesifikasi Desain: Halaman Modal Kerja (SAP)

- **Tanggal**: 2026-09-13
- **Halaman**: `/dashboard-modal-kerja-sap`
- **Submenu**: Dashboard > Modal Kerja (SAP)
- **Status**: Disetujui (Ready for Implementation Planning)

---

## 1. Latar Belakang & Tujuan

Aplikasi Cash & Bank saat ini memiliki halaman `/dashboard-modal-kerja` yang menyajikan data perbandingan Rencana Permintaan, Realisasi Dropping, dan Pembayaran (berdasarkan data transaksi lokal/`bank_keluar`). Di sisi lain, modul `/bank-cashflow` memiliki data transaksi arus kas resmi dari SAP (`cashflows` & `cashflow_references`) yang mencakup rincian akun beban secara terstandarisasi (seperti pos *"Pembayaran kas kepada karyawan Gaji dan Tunjangan Karpim"*).

Tujuan fitur ini adalah menyediakan **halaman Modal Kerja versi kedua ("Modal Kerja (SAP)")** yang menampilkan realisasi pengeluaran kas aktual bersumber langsung dari SAP, ditata dalam format hierarki akun Modal Kerja (Gaji & Tunjangan $\rightarrow$ Biaya Eksploitasi $\rightarrow$ Aktivitas Investasi) dengan rincian mingguan (W1..W4) per bulan serta filter per Unit/Kebun.

---

## 2. Ruang Lingkup & Kebutuhan Fungsional

1. **Submenu Baru di Dashboard**:
   - Menambahkan submenu `Modal Kerja (SAP)` pada sidebar menu `Dashboard` di [`resources/views/layouts/index.blade.php`](file:///D:/Downloads/CASH_BANK/CASH_BANK/resources/views/layouts/index.blade.php#L608-L615).
2. **Filter Interaktif**:
   - **Tahun**: Dropdown tahun transaksi (bersumber dari `Cashflow::distinct()->pluck('tahun')`).
   - **Rentang Bulan**: `Dari Bulan` dan `Sampai Bulan` (Januari s/d Desember).
   - **Cakupan Unit/Kebun**:
     - *Konsolidasi Global (Semua Unit)* (default)
     - *Regional Office (5R00000001)*
     - *Unit/Kebun spesifik* (berdasarkan relasi `bank_tujuan`).
3. **Penyajian Data Mingguan (Weekly Cut-off)**:
   - Nilai dikelompokkan ke W1 (1–7), W2 (8–14), W3 (15–21), W4 (22–akhir bulan), dan Total Bulan.
   - Nilai disajikan dalam **satuan Ribuan Rupiah (Rp '000)** (`abs(amount) / 1000`).
   - Nilai nol disajikan sebagai tanda strip (`-`).
4. **Engine Pemetaan Otomatis (`SapModalKerjaMapper`)**:
   - Memetakan kode `reference_key_1` SAP ke hierarki 3 level:
     - `Kategori` $\rightarrow$ `Sub Kriteria` $\rightarrow$ `Item Sub Kriteria`.
   - Menyediakan grup penampung `Akun SAP Lainnya` untuk akun yang belum terdaftar agar seluruh rupiah transaksi SAP tetap terhitung utuh.
5. **Footer Ringkasan (Bottom Summary)**:
   - Subtotal per Kategori (Gaji, Eksploitasi, Investasi, Lainnya).
   - Grand Total Realisasi Pengeluaran Kas (SAP) per minggu dan per bulan.

---

## 3. Arsitektur & Komponen Teknis

### A. Routing (`routes/web.php`)
Didaftarkan di dalam grup middleware `['auth', 'check_role:admin']`:
```php
Route::get('/dashboard-modal-kerja-sap', [SapModalKerjaController::class, 'index'])
    ->name('dashboard.modal-kerja-sap.index');
Route::get('/dashboard-modal-kerja-sap/data', [SapModalKerjaController::class, 'data'])
    ->name('dashboard.modal-kerja-sap.data');
```
*Audit route wajib dijalankan setelah penambahan: `php artisan route:list --path=dashboard-modal-kerja-sap`.*

### B. Controller (`app/Http/Controllers/SapModalKerjaController.php`)
* **`index(Request $request)`**:
  - Mengambil data filter awal (daftar tahun dari `Cashflow`, daftar bulan, dan daftar unit dari `BankTujuan`).
  - Mengembalikan view shell `cash_bank.modalKerjaSap`.
* **`data(Request $request)`**:
  - Menerima parameter AJAX: `tahun`, `bulan_dari`, `bulan_sampai`, `unit`, `week_ranges`.
  - Melakukan query agregasi pengeluaran kas (`Cashflow::where('amount', '<', 0)` atau kelompok akun pengeluaran `A02...` dan `B02...`).
  - Menerapkan filter periode dan unit.
  - Memanggil `SapModalKerjaMapper` untuk mentransformasi data menjadi matriks grid mingguan.
  - Menghitung subtotal dan grand total footer.
  - Mengembalikan render view parsial `cash_bank.modalKerjaSapTable`.

### C. Mapper Service (`app/Support/SapModalKerjaMapper.php`)
* Berisi kamus pemetaan dari `reference_key_1` SAP ke:
  1. `Kebutuhan Gaji, Upah dan Tunjangan`:
     - `Karyawan Pimpinan`: Gaji & Tunjangan, Cuti Tahunan, Cuti Panjang, THR, Bonus, PPh 21, Dapenbun, BPJS, SHT, Lainnya.
     - `Karyawan Pelaksana`: Gaji & Tunjangan, Lembur, Premi, Cuti Tahunan, Cuti Panjang, THR, Bonus, PPh 21, Dapenbun, BPJS, SHT, Lainnya.
  2. `Payment Requirement for Exploitation Activity`:
     - `TBS (FFB)`, `Operasional Produksi`, `Biaya Usaha dan lainnya`, `Pajak`.
  3. `Kebutuhan Pembayaran Pekerjaan Aktivitas Investasi`:
     - `Investasi On Farm`, `Investasi Off Farm`, `Pembayaran investasi lainnya`.
* Menyediakan fungsi fallback: akun yang tidak memiliki pemetaan khusus dimasukkan ke kategori `Akun SAP Lainnya` dengan sub-kategori nama grup induk dan item nama uraian akun SAP.

### D. Views
1. `resources/views/cash_bank/modalKerjaSap.blade.php`:
   - Shell halaman, filter bar, loader/spinner, script AJAX fetch data, tombol reset, dan event handler.
2. `resources/views/cash_bank/modalKerjaSapTable.blade.php`:
   - Tabel responsive dengan frozen/sticky columns di sebelah kiri (No & Nama Akun).
   - Dynamic headers per bulan (W1..W4 + Total Bulan) dan Total Keseluruhan.
   - Baris hierarki akordeon/indentasi.
   - Footer baris rekapitulasi ringkasan pengeluaran.
3. `resources/views/layouts/index.blade.php`:
   - Submenu sidebar `Modal Kerja (SAP)` dengan status aktif dinamis `request()->routeIs('dashboard.modal-kerja-sap.*')`.

---

## 4. Alur Data & Perhitungan

1. **Pengambilan Data SAP**:
   ```sql
   SELECT reference_key_1, posting_date, amount, id_bank_tujuan, profit_center
   FROM cashflows
   WHERE tahun = :tahun
     AND bulan BETWEEN :bulan_dari AND :bulan_sampai
     AND (reference_key_1 LIKE 'A02%' OR reference_key_1 LIKE 'B02%' OR amount < 0)
   ```
2. **Kalkulasi Minggu**:
   - Jika `DAY(posting_date) <= 7` $\rightarrow$ `W1`
   - Jika `DAY(posting_date) <= 14` $\rightarrow$ `W2`
   - Jika `DAY(posting_date) <= 21` $\rightarrow$ `W3`
   - Else $\rightarrow$ `W4`
3. **Agregasi**:
   - Nilai: `abs(amount) / 1000`
   - Dijumlahkan ke dalam array:
     `$data[kategori][sub_kriteria][item_kriteria][bulan]['w' . $week] += $nilai`
     `$data[kategori][sub_kriteria][item_kriteria][bulan]['total'] += $nilai`
4. **Footer**:
   - Menghitung akumulasi vertikal untuk tiap sub-kategori, kategori, dan seluruh baris pengeluaran kas.

---

## 5. Rencana Pengujian & Validasi (Playwright E2E & Server-Side)

Sesuai aturan kerja proyek:
1. **Routing Verification**:
   - Jalankan `php artisan route:list --path=dashboard-modal-kerja-sap` untuk memastikan route terdaftar tanpa tabrakan.
2. **Autonomous Playwright E2E Testing**:
   - **Background Monitoring**: Pastikan tidak ada console error JavaScript atau request API yang gagal (4xx/5xx).
   - **Filter Testing**: Uji filter perubahan tahun, rentang bulan, dan perubahan pilihan Unit/Kebun.
   - **State Persistence**: Uji reload halaman (F5) setelah aksi filter.
   - **Modern UI/UX**: Uji sticky column header dan horizontal scroll agar tidak ada overlap atau clipping.
   - **Unhappy Paths**: Uji filter periode kosong / di luar rentang.
3. **Commit & Deploy Protocol**:
   - Git add & commit per file secara atomik dengan deskripsi jelas.
   - Git push ke remote repository.
   - Deploy via SSH ke VPS (`163.61.58.92`), jalankan `git pull && php artisan cache:clear && php artisan route:clear`.
   - Bersihkan semua screenshot hasil testing Playwright.
