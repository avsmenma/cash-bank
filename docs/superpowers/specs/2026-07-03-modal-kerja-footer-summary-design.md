# Desain: Informasi Ringkasan (Footer) Tabel Modal Kerja

Tanggal: 2026-07-03
Halaman: `/dashboard-modal-kerja`
Berkas acuan: `PMK_2026 (1).xlsx` (sheet `Jan_W`, `Feb_W`, … `Des_W`, baris 216–229)

## 1. Tujuan

Mengisi 14 baris ringkasan di bawah tabel Modal Kerja dengan angka yang **dihitung
oleh sistem** (bukan input manual / bukan hardcode), meniru logika waterfall arus
kas pada workbook PMK 2026.

Saat ini 14 baris tersebut sudah ada di `modalKerjaTable.blade.php` tetapi hampir
semuanya placeholder (`0` / `-`). Hanya 2 yang benar-benar dihitung
(`penerimaan_pengembalian_dana`, `biaya_admin_bank_lainnya`).

## 2. Logika Excel (sumber kebenaran)

Semua nilai dalam **satuan ribuan Rupiah** (sama seperti tabel di atasnya).
Per sheet bulan (mis. `Jan_W`), baris 216–229:

### Blok Modal Sendiri
1. **Saldo Awal (Modal Sendiri)** `R216` = 0 (opening; di-hardcode 0 tiap bulan 2026).
2. **Pembayaran Menggunakan Modal Sendiri** `Z217 = Z215` = total kolom
   "Penggunaan Modal Sendiri" (0 dalam praktik di web).
3. **Penerimaan Pengembalian Dana Kebun-Unit & Angsuran** — dari `bank_masuk`. ✅ sudah ada.
4. **Biaya Admin Bank & Lainnya** — dari `bank_keluar`. ✅ sudah ada.
5. **Saldo Akhir (Modal Sendiri)** `= 1 − 2 + 3 − 4`.

### Blok Modal Kerja / Bank Opex
6. **Saldo Awal Modal Kerja 01** — bulan pertama = posisi opex awal;
   bulan berikutnya = **"Jumlah Saldo Opex" bulan sebelumnya**
   (dikonfirmasi: `Feb_W!R221 = Jan_W!AA229`).
7. **Saldo Awal Bank Opex 01** `= baris1 + baris6`.
8. **Pembayaran Menggunakan Modal Kerja** `= W215 − Z215` = **total Pembayaran** tabel.
9. **Penerimaan Dana Modal Kerja** `= R215` = **total Dropping** tabel.
10. **Sisa Modal Kerja Tersedia** `= baris6 − baris8 + baris9`.
11. **Saldo Akhir Bank Opex** `= baris5 + baris10`.
12. **Posisi Saldo Di Rekg Opex Operasional (Mandiri 408)** = saldo aktual rekening.
13. **Posisi Saldo Di Rekg TBS (Mandiri 200)** = saldo aktual rekening.
14. **Jumlah Saldo Opex** `= baris12 + baris13` → menjadi baris6 bulan berikutnya.

Insight kunci: baris 6, 12, 13, 14 semuanya adalah **running balance dua rekening
opex** (Mandiri 408 & Mandiri TBS 200) pada tanggal cut-off berbeda — persis cara
`DashboardController::bank()` sudah menghitung saldo Rek 408 (pola `9702740`).

## 3. Model Data (sistem)

Sumber data yang dipakai — semuanya sudah ada di aplikasi:

- `Dropping` / `Permintaan` (mingguan M1–M4) → total Dropping per bulan (baris 9).
- `BankKeluar` (kredit, per tanggal) → total Pembayaran per bulan (baris 8),
  dan Biaya Admin (baris 4).
- `BankMasuk` (debet) → Penerimaan Pengembalian Dana (baris 3), dan entri
  "Saldo Awal" sebagai opening opex.
- `sumber_dana.nama_sumber_dana` → identifikasi rekening opex.

### Running balance rekening opex

Untuk tiap rekening opex (pola nama pada `sumber_dana`):
- `opening` = Σ `bank_masuk.debet` dengan `uraian LIKE '%saldo awal%'` (entri saldo awal).
- `movement(bulan m)` = Σ `debet` (non–saldo-awal) − Σ `kredit`, per bulan.
- `posStart(m)` = `opening + Σ movement(bulan < m)` → dipakai baris 6 (Saldo Awal MK).
- `posEnd(m)`   = `opening + Σ movement(bulan ≤ m)` → dipakai baris 12/13 (posisi rekening).
- Semua dibagi 1000 (satuan ribuan).

Pola identifikasi rekening (**perlu divalidasi terhadap data live**):
- Rek Opex 408 → `nama_sumber_dana LIKE '%9702740%'` (dari kode `bank()` yang sudah ada).
- Rek TBS 200 → `nama_sumber_dana LIKE '%200%'` / mengandung `TBS`
  (**asumsi — konfirmasi pola sebenarnya di server**).

## 4. Perhitungan per bulan (di controller)

```
MS_open   = 0
MS_pay    = 0
MS_recv   = penerimaan_pengembalian_dana[b]
MS_admin  = biaya_admin_bank_lainnya[b]
MS_end    = MS_open - MS_pay + MS_recv - MS_admin

MK_open   = posStart_opex(b)              // 408 + 200
Opex_open = MS_open + MK_open
MK_pay    = bayarTot[b] - MS_pay          // total Pembayaran
MK_recv   = dropTot[b]                    // total Dropping
MK_sisa   = MK_open - MK_pay + MK_recv
Opex_end  = MS_end + MK_sisa

pos408    = posEnd_408(b)
pos200    = posEnd_200(b)
jumlahOpex= pos408 + pos200
```

Controller mengirim `$modalKerjaSummary[b]` berisi 14 nilai final; blade hanya
menampilkan (single source of truth di controller).

## 5. Tampilan

Nilai ringkasan adalah **satu angka per bulan**, sedangkan tabel bergrid mingguan.
Keputusan: tiap baris ringkasan dirender sebagai **satu sel gabungan
(`colspan=19`) per blok bulan**, berisi angka bulanan rata-kanan. Kolom identitas
(No + Uraian) tetap sticky. Warna baris mengikuti yang sudah ada
(`row-ui-summary`, `-green`, `-yellow`, `-cyan`, `-orange`, `-softgreen`).

## 6. Acceptance (validasi vs Excel `Jan_W`, satuan ribuan)

| Baris | Nilai Januari 2026 |
|---|---|
| Penerimaan Pengembalian Dana | 500.396 |
| Biaya Admin Bank & Lainnya | 20.955 |
| Saldo Akhir Modal Sendiri | 479.441 |
| Saldo Awal Modal Kerja 01 Jan | 23.242.458 |
| Pembayaran Menggunakan Modal Kerja | 203.934.851 |
| Penerimaan Dana Modal Kerja | 185.363.377 |
| Sisa Modal Kerja Tersedia | 4.670.984 |
| Saldo Akhir Bank Opex Jan | 5.150.425 |
| Posisi Rekg 408 | 5.129.973 |
| Posisi Rekg TBS 200 | 20.452 |
| Jumlah Saldo Opex | 5.150.425 |

Selisih "Saldo Akhir Bank Opex" vs "Jumlah Saldo Opex" adalah rekonsiliasi
(≈0 di Excel).

## 7. Batas / Risiko

- Tidak bisa validasi angka dari mesin lokal (DB remote `47.236.49.229` tidak
  reachable dari sini). Validasi angka dilakukan di lingkungan live terhadap tabel
  Acceptance di atas.
- Pola identifikasi Rek TBS 200 adalah asumsi; harus dikonfirmasi terhadap
  `sumber_dana` di DB live. Titik konfigurasi dibuat terpusat (konstanta) agar mudah
  diperbaiki.
- Bila opening opex tidak tercatat sebagai entri "Saldo Awal" di `bank_masuk`,
  baris 6 & turunannya akan meleset sebesar opening — terdeteksi saat uji vs Januari.
