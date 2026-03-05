# Prompt: Implementasi Sync Cash Bank → Agenda Online

## Konteks Project

Saya memiliki dua project terpisah yang berbagi data dokumen:

- **Agenda Online (AO)** – sistem manajemen dokumen utama
- **Cash Bank (CB)** – sistem pembayaran yang terhubung dengan dokumen dari Agenda Online

### Koneksi Database

| Project | Koneksi ke Project Lain | Nama Koneksi |
|---|---|---|
| Agenda Online | → Cash Bank | `cash_bank_new` |
| Cash Bank | → Agenda Online | `mysql_agenda_online` |

---

## Apa yang Sudah Diimplementasi (AO → CB)

Di project **Agenda Online**, saya sudah mengimplementasi sync langsung di controller (`BagianDokumenController.update()` dan `DokumenController.update()`).

Saat dokumen diedit di AO, field berikut **otomatis ter-sync ke Cash Bank** (`bank_keluars` table):

### Field Mapping AO → CB (sudah berfungsi)

| Dokumen (AO) | bank_keluars (CB) | Mekanisme |
|---|---|---|
| `uraian_spp` | `uraian` | Direct mapping |
| `nilai_rupiah` | `nilai_rupiah` + `kredit` | Direct mapping (kedua kolom) |
| `nomor_agenda` | `no_agenda` | Direct mapping |
| [kategori](file:///c:/Users/ASUS/Downloads/agenda_2026/agenda-online-PTPN/app/Models/SubKriteria.php#20-25) (text) | `id_kategori_kriteria` (ID) | ID langsung dari request dropdown |
| `jenis_dokumen` (text) | `id_sub_kriteria` (ID) | ID langsung dari request dropdown |
| `jenis_sub_pekerjaan` (text) | `id_item_sub_kriteria` (ID) | ID langsung dari request dropdown |
| `jenis_pembayaran` (text) | `id_jenis_pembayaran` (ID) | Name→ID lookup via [JenisPembayaran](file:///c:/Users/ASUS/Downloads/agenda_2026/agenda-online-PTPN/app/Models/JenisPembayaran.php#7-51) model |

### Cara Pencarian Record di CB

Sync AO → CB mencari record `bank_keluars` dengan query:
```php
->where(function ($q) use ($dokumen, $agendaKey) {
    $q->where('dokumen_id', $dokumen->id)
      ->orWhere('no_agenda', $agendaKey)
      ->orWhere('agenda_tahun', $agendaKey);
})
```

> **PENTING:** Kolom `no_agenda` di `bank_keluars` sering NULL. Data agenda tersimpan di kolom `agenda_tahun` (format: `0007_2026`).

---

## Yang Perlu Diimplementasi (CB → AO)

Di project **Cash Bank**, saat data di `bank_keluars` diedit, field berikut harus otomatis ter-sync ke **Agenda Online** (`dokumens` table via koneksi `mysql_agenda_online`):

### Field Mapping CB → AO (perlu dibuat)

| bank_keluars (CB) | dokumens (AO) | Mekanisme |
|---|---|---|
| `uraian` | `uraian_spp` | Direct mapping |
| `nilai_rupiah` atau `kredit` | `nilai_rupiah` + [dibayar](file:///c:/Users/ASUS/Downloads/agenda_2026/agenda-online-PTPN/app/Models/Dokumen.php#195-199) | Direct mapping |
| `penerima` | `dibayar_kepada` | Direct mapping |
| `tanggal` | `tanggal_dibayar` | Direct mapping |
| `id_kategori_kriteria` (ID) | [kategori](file:///c:/Users/ASUS/Downloads/agenda_2026/agenda-online-PTPN/app/Models/SubKriteria.php#20-25) (text) | ID→Name lookup via `kategori_kriteria` table |
| `id_sub_kriteria` (ID) | `jenis_dokumen` (text) | ID→Name lookup via `sub_kriteria` table |
| `id_item_sub_kriteria` (ID) | `jenis_sub_pekerjaan` (text) | ID→Name lookup via `item_sub_kriteria` table |
| `id_jenis_pembayaran` (ID) | `jenis_pembayaran` (text) | ID→Name lookup via `jenis_pembayarans` table |

### Lookup Tables (di database Cash Bank)

```
kategori_kriteria:      id_kategori_kriteria, nama_kriteria, tipe
sub_kriteria:           id_sub_kriteria, id_kategori_kriteria, nama_sub_kriteria
item_sub_kriteria:      id_item_sub_kriteria, id_sub_kriteria, nama_item_sub_kriteria
jenis_pembayarans:      id_jenis_pembayaran, nama_jenis_pembayaran
```

### Cara Pencarian Record di AO

Untuk mencari dokumen di AO dari CB, gunakan:
```php
$dokumen = DB::connection('mysql_agenda_online')
    ->table('dokumens')
    ->where('nomor_agenda', $bankKeluar->agenda_tahun)
    ->first();

// Atau jika dokumen_id tersedia:
// ->where('id', $bankKeluar->dokumen_id)
```

### Kode yang Sudah Ada di CB (referensi)

Cash Bank sudah memiliki kode untuk mengupdate beberapa field di AO. Berikut yang sudah ada:
```php
if (is_numeric($input)) {
    $dokumen = DB::connection('mysql_agenda_online')
        ->table('dokumens')
        ->find($input);
    if ($dokumen) {
        DB::connection('mysql_agenda_online')
            ->table('dokumens')
            ->where('id', $dokumen->id)
            ->update([
                'uraian_spp'        => $request->uraian,
                'nilai_rupiah'      => $request->nilai_rupiah,
                'dibayar'           => $request->nilai_rupiah,
                'dibayar_kepada'    => $request->penerima,
                'status_pembayaran' => 'sudah_dibayar',
                'tanggal_dibayar'   => now(),
            ]);
    }
}
```

### Implementasi yang Diharapkan

1. **Di controller yang meng-update `bank_keluars`** (misalnya `BankKeluarController.update()`):
   - Setelah `DB::commit()`, tambahkan blok sync langsung ke AO
   - Cari dokumen di AO berdasarkan `agenda_tahun` atau `dokumen_id`
   - Build payload dengan mapping field di atas
   - Untuk field kategori (ID→Name), lakukan lookup dari table lokal CB

2. **Pola kode** (mirip dengan yang sudah diimplementasi di AO):
```php
DB::commit();

// === Direct Sync ke Agenda Online ===
try {
    $agendaKey = $bankKeluar->agenda_tahun;
    if ($agendaKey) {
        $syncPayload = [];

        // Map basic fields
        $syncPayload['uraian_spp'] = $bankKeluar->uraian;
        $syncPayload['nilai_rupiah'] = $bankKeluar->kredit;
        $syncPayload['dibayar'] = $bankKeluar->kredit;
        $syncPayload['dibayar_kepada'] = $bankKeluar->penerima;

        // Lookup kategori ID → nama
        if ($bankKeluar->id_kategori_kriteria) {
            $kategori = DB::table('kategori_kriteria')
                ->where('id_kategori_kriteria', $bankKeluar->id_kategori_kriteria)
                ->first();
            if ($kategori) {
                $syncPayload['kategori'] = $kategori->nama_kriteria;
            }
        }

        if ($bankKeluar->id_sub_kriteria) {
            $sub = DB::table('sub_kriteria')
                ->where('id_sub_kriteria', $bankKeluar->id_sub_kriteria)
                ->first();
            if ($sub) {
                $syncPayload['jenis_dokumen'] = $sub->nama_sub_kriteria;
            }
        }

        if ($bankKeluar->id_item_sub_kriteria) {
            $item = DB::table('item_sub_kriteria')
                ->where('id_item_sub_kriteria', $bankKeluar->id_item_sub_kriteria)
                ->first();
            if ($item) {
                $syncPayload['jenis_sub_pekerjaan'] = $item->nama_item_sub_kriteria;
            }
        }

        if ($bankKeluar->id_jenis_pembayaran) {
            $jp = DB::table('jenis_pembayarans')
                ->where('id_jenis_pembayaran', $bankKeluar->id_jenis_pembayaran)
                ->first();
            if ($jp) {
                $syncPayload['jenis_pembayaran'] = $jp->nama_jenis_pembayaran;
            }
        }

        // Update dokumen di Agenda Online
        $affected = DB::connection('mysql_agenda_online')
            ->table('dokumens')
            ->where(function ($q) use ($bankKeluar) {
                if ($bankKeluar->dokumen_id) {
                    $q->where('id', $bankKeluar->dokumen_id);
                }
                if ($bankKeluar->agenda_tahun) {
                    $q->orWhere('nomor_agenda', $bankKeluar->agenda_tahun);
                }
            })
            ->update($syncPayload);

        \Log::info('[CBSync] Direct sync CB → AO berhasil.', [
            'bank_keluar_id' => $bankKeluar->id_bank_keluar,
            'agenda_key'     => $agendaKey,
            'rows_affected'  => $affected,
        ]);
    }
} catch (\Throwable $e) {
    \Log::error('[CBSync] Direct sync CB → AO GAGAL.', [
        'bank_keluar_id' => $bankKeluar->id_bank_keluar,
        'error'          => $e->getMessage(),
    ]);
}
```

3. **Error handling:** Wrap sync dalam try-catch — sync gagal TIDAK boleh menghentikan alur utama CB.

### Skema Table `dokumens` (di AO)

Field yang relevan untuk sync:
```
id, nomor_agenda, uraian_spp, nilai_rupiah, dibayar, dibayar_kepada,
kategori, jenis_dokumen, jenis_sub_pekerjaan, jenis_pembayaran,
status_pembayaran, tanggal_dibayar
```
