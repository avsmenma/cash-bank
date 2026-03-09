# INSTRUKSI: Halaman Ringkasan Pembayaran Hierarki — Cash Bank

## GAMBARAN FITUR
Buat halaman baru yang menampilkan ringkasan pembayaran
dalam struktur hierarki 3 level:

```
Kriteria (A. Kebutuhan Gaji, Upah dan Tunjangan)
  └── Golongan (A.1. Gaji dan Tunjangan)
        └── Sub Golongan (A.1. Gaji dan Tunjangan) → nilai
```

Saat user klik baris Golongan atau Sub Golongan,
tampilkan **drawer/panel** di sisi kanan berisi
daftar transaksi detail dari kriteria yang diklik.

---

## DESAIN UI — MODERN & UNIK

### Konsep Visual
- Background halaman: `#F8FAFC` (abu sangat terang)
- Tabel hierarki: card putih dengan shadow tipis
- Header kolom: background `#083E40` (teal gelap), teks putih
- Baris Kriteria utama (level 1): background `#E6F4F1`, font bold, 
  border-left `4px solid #0D9488`
- Baris Golongan (level 2): background `#F0FAF8`, indent 16px,
  border-left `3px solid #14B8A6`, cursor pointer, hover effect
- Baris Sub Golongan (level 3): background white, indent 32px,
  border-left `2px solid #99E6DC`, cursor pointer, hover effect
- Baris Total: background `#083E40`, teks putih, font bold
- Nilai rupiah: rata kanan, font mono, warna `#0D9488`

### Hover Effect pada baris yang bisa diklik
```css
.row-clickable:hover {
  background: #CCEDE8 !important;
  cursor: pointer;
  transition: background 0.15s ease;
}
.row-clickable:hover .nilai {
  color: #065F46;
  font-weight: 700;
}
/* Tambahkan ikon panah kecil di kanan saat hover */
.row-clickable:hover::after {
  content: '→ Lihat Detail';
  font-size: 11px;
  color: #0D9488;
  margin-left: 8px;
}
```

### Collapse/Expand per Kriteria
Setiap blok Kriteria bisa di-collapse/expand dengan klik
pada baris Kriteria. Tambahkan ikon `▼` / `▶` di kiri.

---

## STRUKTUR HALAMAN

### Filter di atas tabel
```html
<div class="filter-bar">
  <!-- Tahun -->
  <select name="tahun">...</select>
  
  <!-- Dari Bulan -->
  <select name="dari_bulan">...</select>
  
  <!-- Sampai Bulan -->
  <select name="sampai_bulan">...</select>
  
  <button type="submit">Filter</button>
  <button type="reset">Reset</button>
  <button id="btn-export-excel">Export Excel</button>
</div>
```

### Tabel Hierarki
Kolom: **URAIAN | GOLONGAN | SUB GOLONGAN | NILAI**

Sesuaikan jumlah kolom bulan jika filter rentang bulan
lebih dari 1 (mirip gambar 4 yang menampilkan Januari, Februari, dst).

---

## BACKEND — CONTROLLER & QUERY

### Route
```php
Route::get('/ringkasan-pembayaran', [RingkasanPembayaranController::class, 'index'])
    ->name('ringkasan.index');

Route::get('/ringkasan-pembayaran/detail', [RingkasanPembayaranController::class, 'detail'])
    ->name('ringkasan.detail'); // AJAX endpoint untuk drawer detail
```

### Query Ringkasan Hierarki
```php
// Ambil dari tabel ringkasan yang sudah ada,
// group/join dengan tabel transaksi untuk hitung total nilai
// Filter berdasarkan tahun dan rentang bulan

$ringkasan = DB::table('nama_tabel_ringkasan') // sesuaikan nama tabel
    ->leftJoin('nama_tabel_transaksi', function($join) use ($request) {
        $join->on('tabel_ringkasan.kriteria_i', '=', 'tabel_transaksi.kriteria_i')
             ->on('tabel_ringkasan.kriteria_ii', '=', 'tabel_transaksi.kriteria_ii');
        
        // Filter periode
        if ($request->tahun) {
            $join->whereYear('tabel_transaksi.tanggal_bayar', $request->tahun);
        }
        if ($request->dari_bulan && $request->sampai_bulan) {
            $join->whereMonth('tabel_transaksi.tanggal_bayar', '>=', $request->dari_bulan)
                 ->whereMonth('tabel_transaksi.tanggal_bayar', '<=', $request->sampai_bulan);
        }
    })
    ->selectRaw('
        tabel_ringkasan.kriteria_i,
        tabel_ringkasan.golongan,
        tabel_ringkasan.sub_golongan,
        SUM(tabel_transaksi.nilai) as total_nilai
    ')
    ->groupBy('tabel_ringkasan.kriteria_i', 'tabel_ringkasan.golongan', 'tabel_ringkasan.sub_golongan')
    ->get();

// Sesuaikan nama field dengan yang ada di database
```

### Query Detail Transaksi (AJAX)
```php
public function detail(Request $request)
{
    // Menerima parameter: kriteria_i, kriteria_ii (golongan/sub golongan)
    // plus filter tahun dan bulan yang sama

    $transaksi = DB::table('nama_tabel_transaksi')
        ->where('kriteria_i', $request->kriteria_i)
        ->where('kriteria_ii', $request->kriteria_ii)
        ->whereYear('tanggal_bayar', $request->tahun)
        ->whereMonth('tanggal_bayar', '>=', $request->dari_bulan)
        ->whereMonth('tanggal_bayar', '<=', $request->sampai_bulan)
        ->select([
            'tanggal_bayar',
            'uraian',
            'dibayarkan_kepada',
            'nilai',
            'jenis_bayar',
            'kebun_unit',
            'no_bank',
            // tambahkan kolom lain yang relevan
        ])
        ->orderBy('tanggal_bayar')
        ->get();

    return response()->json($transaksi);
}
```

---

## DRAWER DETAIL TRANSAKSI

Saat user klik baris Golongan atau Sub Golongan,
tampilkan drawer (panel geser dari kanan) berisi detail transaksi.

### HTML Drawer
```html
<div id="drawer-detail" class="drawer" style="display:none;">
  <div class="drawer-header">
    <div>
      <h3 id="drawer-title">Detail Transaksi</h3>
      <p id="drawer-subtitle" style="font-size:12px; color:#94a3b8;"></p>
    </div>
    <button id="drawer-close">✕</button>
  </div>
  
  <div class="drawer-stats">
    <div class="stat">
      <span>Total Transaksi</span>
      <strong id="drawer-count">-</strong>
    </div>
    <div class="stat">
      <span>Total Nilai</span>
      <strong id="drawer-total">-</strong>
    </div>
  </div>

  <div class="drawer-body">
    <table id="drawer-table">
      <thead>
        <tr>
          <th>Tanggal</th>
          <th>Uraian</th>
          <th>Dibayarkan Kepada</th>
          <th>Kebun/Unit</th>
          <th>Nilai</th>
        </tr>
      </thead>
      <tbody id="drawer-tbody">
        <!-- diisi via JavaScript -->
      </tbody>
    </table>
  </div>
</div>

<!-- Overlay -->
<div id="drawer-overlay" style="display:none;"></div>
```

### CSS Drawer
```css
.drawer {
  position: fixed;
  top: 0;
  right: -600px;
  width: 600px;
  height: 100vh;
  background: white;
  z-index: 1000;
  box-shadow: -4px 0 24px rgba(0,0,0,0.12);
  transition: right 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex !important;
  flex-direction: column;
}
.drawer.open {
  right: 0;
}
.drawer-header {
  background: #083E40;
  padding: 20px 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  color: white;
}
.drawer-header button {
  background: rgba(255,255,255,0.15);
  border: none;
  color: white;
  width: 32px; height: 32px;
  border-radius: 50%;
  cursor: pointer;
  font-size: 16px;
}
.drawer-stats {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1px;
  background: #E2E8F0;
  border-bottom: 1px solid #E2E8F0;
}
.drawer-stats .stat {
  background: #F8FAFC;
  padding: 12px 20px;
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.drawer-stats .stat span { font-size: 11px; color: #94a3b8; }
.drawer-stats .stat strong { font-size: 18px; color: #0D9488; }
.drawer-body {
  flex: 1;
  overflow-y: auto;
  padding: 16px;
}
#drawer-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 12px;
}
#drawer-table th {
  background: #F1F5F9;
  padding: 8px 10px;
  text-align: left;
  font-size: 10px;
  text-transform: uppercase;
  color: #64748b;
  letter-spacing: 0.5px;
  position: sticky;
  top: 0;
}
#drawer-table td {
  padding: 8px 10px;
  border-bottom: 1px solid #F1F5F9;
  vertical-align: top;
}
#drawer-table tr:hover td {
  background: #F8FAFC;
}
#drawer-table .nilai {
  text-align: right;
  font-weight: 600;
  color: #0D9488;
  white-space: nowrap;
}
.drawer-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.3);
  z-index: 999;
}
```

### JavaScript Drawer
```javascript
document.querySelectorAll('.row-clickable').forEach(row => {
  row.addEventListener('click', function() {
    const kriteria1 = this.dataset.kriteria1;
    const kriteria2 = this.dataset.kriteria2;
    const label     = this.dataset.label;
    
    // Update drawer title
    document.getElementById('drawer-title').textContent = label;
    document.getElementById('drawer-subtitle').textContent = 
      `${kriteria1} › ${kriteria2}`;
    
    // Show drawer & overlay
    document.getElementById('drawer-detail').classList.add('open');
    document.getElementById('drawer-overlay').style.display = 'block';
    
    // Show loading state
    document.getElementById('drawer-tbody').innerHTML = 
      '<tr><td colspan="5" style="text-align:center;padding:40px;color:#94a3b8;">Memuat data...</td></tr>';
    
    // Fetch detail via AJAX
    const params = new URLSearchParams({
      kriteria_i:   kriteria1,
      kriteria_ii:  kriteria2,
      tahun:        document.querySelector('[name=tahun]').value,
      dari_bulan:   document.querySelector('[name=dari_bulan]').value,
      sampai_bulan: document.querySelector('[name=sampai_bulan]').value,
    });
    
    fetch(`/ringkasan-pembayaran/detail?${params}`)
      .then(res => res.json())
      .then(data => {
        // Update stats
        const total = data.reduce((sum, d) => sum + parseFloat(d.nilai || 0), 0);
        document.getElementById('drawer-count').textContent = 
          data.length.toLocaleString('id-ID') + ' transaksi';
        document.getElementById('drawer-total').textContent = 
          'Rp ' + total.toLocaleString('id-ID');
        
        // Render rows
        if (data.length === 0) {
          document.getElementById('drawer-tbody').innerHTML = 
            '<tr><td colspan="5" style="text-align:center;padding:40px;color:#94a3b8;">Tidak ada transaksi</td></tr>';
          return;
        }
        
        document.getElementById('drawer-tbody').innerHTML = data.map(d => `
          <tr>
            <td style="white-space:nowrap">${d.tanggal_bayar ?? '-'}</td>
            <td>${d.uraian ?? '-'}</td>
            <td>${d.dibayarkan_kepada ?? '-'}</td>
            <td>${d.kebun_unit ?? '-'}</td>
            <td class="nilai">Rp ${parseFloat(d.nilai || 0).toLocaleString('id-ID')}</td>
          </tr>
        `).join('');
      })
      .catch(() => {
        document.getElementById('drawer-tbody').innerHTML = 
          '<tr><td colspan="5" style="text-align:center;padding:40px;color:#EF4444;">Gagal memuat data</td></tr>';
      });
  });
});

// Tutup drawer
document.getElementById('drawer-close').addEventListener('click', closeDrawer);
document.getElementById('drawer-overlay').addEventListener('click', closeDrawer);
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDrawer(); });

function closeDrawer() {
  document.getElementById('drawer-detail').classList.remove('open');
  document.getElementById('drawer-overlay').style.display = 'none';
}
```

---

## TAMBAHKAN KE SIDEBAR NAVIGASI

Tambahkan menu baru di sidebar project Cash Bank:
```html
<a href="{{ route('ringkasan.index') }}" 
   class="{{ request()->routeIs('ringkasan.*') ? 'active' : '' }}">
  📊 Ringkasan Pembayaran
</a>
```

---

## CHECKLIST VERIFIKASI
- [ ] Halaman menampilkan hierarki 3 level: Kriteria → Golongan → Sub Golongan
- [ ] Filter tahun + rentang bulan berfungsi
- [ ] Collapse/expand per blok Kriteria berfungsi
- [ ] Klik baris Golongan/Sub Golongan membuka drawer dari kanan
- [ ] Drawer menampilkan daftar transaksi yang sesuai kriteria diklik
- [ ] Drawer menampilkan total transaksi dan total nilai di atas
- [ ] Drawer bisa ditutup via tombol X, klik overlay, atau tekan Escape
- [ ] Nama field pada query disesuaikan dengan nama field di database
- [ ] Menu di sidebar mengarah ke halaman ini
- [ ] Tidak ada perubahan pada halaman atau fitur lain

---

## CATATAN PENTING
- Sesuaikan semua nama tabel dan nama field dengan
  yang benar-benar ada di database project ini
- Cek nama field kolom KRITERIA I dan KRITERIA II
  di tabel transaksi sebelum menulis query
- Jika ada CSRF issue pada AJAX request, tambahkan
  header `X-CSRF-TOKEN` dari meta tag
