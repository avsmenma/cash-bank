<?php
// namespace App\Http\Controllers;

namespace App\Http\Controllers;
use App\Models\BankMasuk;
use App\Models\BankTujuan;
use App\Models\SumberDana;
use App\Imports\importMasuk;
use App\Imports\importSheet;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use App\Models\JenisPembayaran;
use App\Models\KategoriKriteria;
use App\Exports\reportMasukExcel;
use App\Imports\importExcelMasukk;
use Illuminate\Support\Facades\DB;
use App\Models\GabunganMasukKeluar;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportExcelBankMasuk;
use App\Imports\importExcelMasukImport;

class BankMasukController extends Controller
{
    private function normalizeReferenceText(?string $value): string
    {
        $value = strtolower(trim((string) $value));
        $value = preg_replace('/\s+/', ' ', $value);

        return $value ?? '';
    }

    private function extractReferenceNumbers(?string $value): array
    {
        preg_match_all('/\b\d[\d\-\/\s]{5,}\d\b/', (string) $value, $matches);

        return collect($matches[0] ?? [])
            ->map(fn($number) => preg_replace('/\D+/', '', $number))
            ->filter(fn($number) => strlen($number) >= 6)
            ->unique()
            ->values()
            ->all();
    }

    private function findReferenceName(array $map, ?string $search): ?string
    {
        $searchText = $this->normalizeReferenceText($search);
        if ($searchText === '') {
            return null;
        }

        $searchNumbers = $this->extractReferenceNumbers($search);
        if (!empty($searchNumbers)) {
            foreach ($map as $name => $id) {
                if (array_intersect($searchNumbers, $this->extractReferenceNumbers($name))) {
                    return $name;
                }
            }
        }

        foreach ($map as $name => $id) {
            $nameText = $this->normalizeReferenceText($name);
            if ($nameText === $searchText || str_contains($nameText, $searchText) || str_contains($searchText, $nameText)) {
                return $name;
            }
        }

        return null;
    }

    public function index(Request $request)
    {
        return view('cash_bank.bankMasuk', [
            'sumberDana' => SumberDana::all(),
            'bankTujuan' => BankTujuan::all(),
            'kategoriKriteria' => KategoriKriteria::where('tipe', 'Masuk')->get(),
            'jenisPembayaran' => JenisPembayaran::all(),
            // Daftar tahun untuk filter export (dari data yang ada)
            'exportYears' => BankMasuk::selectRaw('DISTINCT YEAR(tanggal) as y')
                ->whereNotNull('tanggal')->orderByDesc('y')->pluck('y'),
        ]);
    }

    /**
     * Terapkan filter export (tahun, bulan, kategori, sumber dana) ke query.
     */
    private function applyExportFilter($query, Request $request)
    {
        if ($request->filled('tahun')) {
            $query->whereYear('tanggal', $request->tahun);
        }
        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal', $request->bulan);
        }
        if ($request->filled('kategori')) {
            $query->where('id_kategori_kriteria', $request->kategori);
        }
        if ($request->filled('sumber_dana')) {
            $query->where('id_sumber_dana', $request->sumber_dana);
        }
        return $query;
    }

    /**
     * Hitung jumlah baris sesuai filter export — dipakai modal konfirmasi.
     */
    public function exportCount(Request $request)
    {
        return response()->json([
            'total' => $this->applyExportFilter(BankMasuk::query(), $request)->count(),
        ]);
    }
    public function datatable(Request $request)
    {
        $query = BankMasuk::with([
            'sumberDana:id_sumber_dana,nama_sumber_dana',
            'bankTujuan:id_bank_tujuan,nama_tujuan',
            'kategori:id_kategori_kriteria,nama_kriteria',
            'jenisPembayaran:id_jenis_pembayaran,nama_jenis_pembayaran',
            // Tie-breaker id: banyak baris bertanggal sama — tanpa urutan
            // deterministik, chunk LIMIT/OFFSET bisa duplikat/melewatkan baris.
        ])->orderBy('tanggal', 'desc')->orderBy('id_bank_masuk', 'desc'); // terbaru dulu

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('jenis_pembayaran', function ($row) {
                return $row->jenisPembayaran
                    ? $row->jenisPembayaran->nama_jenis_pembayaran
                    : '-';
            })
            ->addColumn('kategori_kriteria', function ($row) {
                return $row->kategori
                    ? $row->kategori->nama_kriteria
                    : '-';
            })
            ->addColumn('bank_tujuan', function ($row) {
                return $row->bankTujuan
                    ? $row->bankTujuan->nama_tujuan
                    : '-';
            })
            ->addColumn('sumber_dana', function ($row) {
                return $row->sumberDana
                    ? $row->sumberDana->nama_sumber_dana
                    : '-';
            })
            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="checkbox_ids" name="ids[]" value="' . $row->id_bank_masuk . '">';
            })
            ->editColumn('debet', function ($row) {
                return number_format((float) $row->debet, 0, ',', '.');
            })
            ->editColumn('tanggal', function ($row) {
                return \Carbon\Carbon::parse($row->tanggal)->translatedFormat('d F Y');
            })
            // Nilai mentah untuk editor inline (spreadsheet cell)
            ->addColumn('tanggal_raw', function ($row) {
                return $row->tanggal ? \Carbon\Carbon::parse($row->tanggal)->format('Y-m-d') : '';
            })
            ->addColumn('debet_raw', function ($row) {
                return (float) $row->debet;
            })
            ->rawColumns(['checkbox'])
            ->make(true);
    }

    public function store(Request $request)
    {
        // dd($request->method(), $request->all());

        // Debet dikirim dalam format ribuan (mis. "1.000.000"). Buang pemisah titik
        // sebelum divalidasi, agar nilai jutaan (yang punya >1 titik) tidak ditolak
        // aturan `numeric` — is_numeric('1.000.000') = false.
        $request->merge([
            'debet' => str_replace('.', '', (string) $request->debet),
        ]);

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'debet' => 'required|numeric',
        ]);
        // dd($request->tanggal);

        BankMasuk::create([
            'agenda_tahun' => $request->agenda_tahun,
            'id_sumber_dana' => $request->id_sumber_dana,
            'id_bank_tujuan' => $request->id_bank_tujuan,
            'id_kategori_kriteria' => $request->id_kategori_kriteria,
            'id_jenis_pembayaran' => $request->id_jenis_pembayaran,
            'uraian' => $request->uraian,
            'penerima' => $request->penerima,
            'tanggal' => $request->tanggal,
            'debet' => $request->debet ?? 0,
            'kredit' => 0,
            'keterangan' => $request->keterangan,
        ]);

        return back()->with('success', 'Data berhasil disimpan');
    }

    public function report(Request $request)
    {
        /* ================= REQUEST ================= */
        $tahun = $request->tahun;
        $bulan = $request->bulan;
        $bankTujuanId = $request->bankTujuan;
        $sumberDanaIds = $request->sumber_dana ?? [];
        $kategoriIds = $request->kategori ?? [];
        $jenisPembayaranId = $request->jenis_pembayaran;

        /* ================= QUERY DATA ================= */
        $data = BankMasuk::with(['sumberDana', 'bankTujuan', 'kategori', 'jenisPembayaran'])
            ->when($tahun, fn($q) => $q->whereYear('tanggal', $tahun))
            ->when($bulan, fn($q) => $q->whereMonth('tanggal', $bulan))
            ->when($bankTujuanId, fn($q) => $q->where('id_bank_tujuan', $bankTujuanId))
            ->when($jenisPembayaranId, fn($q) => $q->where('id_jenis_pembayaran', $jenisPembayaranId))
            ->when(count($sumberDanaIds), fn($q) => $q->whereIn('id_sumber_dana', $sumberDanaIds))
            ->when(count($kategoriIds), fn($q) => $q->whereIn('id_kategori_kriteria', $kategoriIds))
            ->orderBy('tanggal')
            ->get();

        /* ================= DROPDOWN TERHUBUNG ================= */

        // Bank Tujuan
        $bankTujuanList = DB::table('bank_tujuan')
            ->whereExists(function ($q) use ($tahun, $bulan, $sumberDanaIds, $kategoriIds, $jenisPembayaranId) {
                $q->select(DB::raw(1))
                    ->from('bank_masuk')
                    ->whereColumn('bank_masuk.id_bank_tujuan', 'bank_tujuan.id_bank_tujuan')
                    ->when($tahun, fn($x) => $x->whereYear('tanggal', $tahun))
                    ->when($bulan, fn($x) => $x->whereMonth('tanggal', $bulan))
                    ->when(count($sumberDanaIds), fn($x) => $x->whereIn('id_sumber_dana', $sumberDanaIds))
                    ->when(count($kategoriIds), fn($x) => $x->whereIn('id_kategori_kriteria', $kategoriIds))
                    ->when($jenisPembayaranId, fn($x) => $x->where('id_jenis_pembayaran', $jenisPembayaranId));
            })
            ->orderBy('nama_tujuan')
            ->get();

        // Sumber Dana
        $sumberDanaList = DB::table('sumber_dana')
            ->whereExists(function ($q) use ($tahun, $bulan, $bankTujuanId, $kategoriIds, $jenisPembayaranId) {
                $q->select(DB::raw(1))
                    ->from('bank_masuk')
                    ->whereColumn('bank_masuk.id_sumber_dana', 'sumber_dana.id_sumber_dana')
                    ->when($tahun, fn($x) => $x->whereYear('tanggal', $tahun))
                    ->when($bulan, fn($x) => $x->whereMonth('tanggal', $bulan))
                    ->when($bankTujuanId, fn($x) => $x->where('id_bank_tujuan', $bankTujuanId))
                    ->when($jenisPembayaranId, fn($x) => $x->where('id_jenis_pembayaran', $jenisPembayaranId))
                    ->when(count($kategoriIds), fn($x) => $x->whereIn('id_kategori_kriteria', $kategoriIds));
            })
            ->orderBy('nama_sumber_dana')
            ->get();

        // Kategori
        $kategoriList = DB::table('kategori_kriteria')
            ->whereExists(function ($q) use ($tahun, $bulan, $bankTujuanId, $sumberDanaIds, $jenisPembayaranId) {
                $q->select(DB::raw(1))
                    ->from('bank_masuk')
                    ->whereColumn('bank_masuk.id_kategori_kriteria', 'kategori_kriteria.id_kategori_kriteria')
                    ->when($tahun, fn($x) => $x->whereYear('tanggal', $tahun))
                    ->when($bulan, fn($x) => $x->whereMonth('tanggal', $bulan))
                    ->when($bankTujuanId, fn($x) => $x->where('id_bank_tujuan', $bankTujuanId))
                    ->when($jenisPembayaranId, fn($x) => $x->where('id_jenis_pembayaran', $jenisPembayaranId))
                    ->when(count($sumberDanaIds), fn($x) => $x->whereIn('id_sumber_dana', $sumberDanaIds));

            })
            ->orderBy('nama_kriteria')
            ->get();

        // Jenis Pembayaran
        $jenisPembayaranList = DB::table('jenis_pembayarans')
            ->whereExists(function ($q) use ($tahun, $bulan, $bankTujuanId, $sumberDanaIds, $kategoriIds) {
                $q->select(DB::raw(1))
                    ->from('bank_masuk')
                    ->whereColumn('bank_masuk.id_jenis_pembayaran', 'jenis_pembayarans.id_jenis_pembayaran')
                    ->when($tahun, fn($x) => $x->whereYear('tanggal', $tahun))
                    ->when($bulan, fn($x) => $x->whereMonth('tanggal', $bulan))
                    ->when($bankTujuanId, fn($x) => $x->where('id_bank_tujuan', $bankTujuanId))
                    ->when(count($sumberDanaIds), fn($x) => $x->whereIn('id_sumber_dana', $sumberDanaIds))
                    ->when(count($kategoriIds), fn($x) => $x->whereIn('id_kategori_kriteria', $kategoriIds));
            })
            ->orderBy('nama_jenis_pembayaran')
            ->get();

        $tahunList = BankMasuk::selectRaw('YEAR(tanggal) tahun')->groupBy('tahun')->pluck('tahun');

        return view('cash_bank.reportMasuk', compact(
            'data',
            'tahunList',
            'bankTujuanList',
            'sumberDanaList',
            'kategoriList',
            'jenisPembayaranList'
        ));
    }


    public function importExcel(Request $request)
    {
        $request->validate(['fileExcel' => 'required|file']);
        $ext = strtolower($request->file('fileExcel')->getClientOriginalExtension());
        if (!in_array($ext, ['xlsx', 'xls', 'csv'])) {
            return back()->withErrors(['fileExcel' => 'File harus berformat xlsx, xls, atau csv.']);
        }
        ini_set('memory_limit', '-1');
        set_time_limit(0);

        Excel::import(
            new importSheet,
            $request->file('fileExcel')
        );

        return redirect()
            ->route('bank-masuk.index')
            ->with('success', 'Data berhasil diimport');
    }

    /**
     * PREVIEW: Parse file tanpa simpan, return JSON untuk preview modal
     */
    public function previewImport(Request $request)
    {
        // ── Cek error upload PHP sebelum validasi Laravel ──
        if ($request->hasFile('fileExcel')) {
            $uploadError = $request->file('fileExcel')->getError();
            if ($uploadError !== UPLOAD_ERR_OK) {
                $phpMessages = [
                    UPLOAD_ERR_INI_SIZE   => 'File terlalu besar (melebihi upload_max_filesize=' . ini_get('upload_max_filesize') . '). Hubungi admin server untuk menaikkan limit.',
                    UPLOAD_ERR_FORM_SIZE  => 'File terlalu besar (melebihi MAX_FILE_SIZE form).',
                    UPLOAD_ERR_PARTIAL    => 'File hanya ter-upload sebagian. Coba lagi.',
                    UPLOAD_ERR_NO_FILE    => 'Tidak ada file yang di-upload.',
                    UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ditemukan di server.',
                    UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk server.',
                    UPLOAD_ERR_EXTENSION  => 'Upload dihentikan oleh extension PHP.',
                ];
                $msg = $phpMessages[$uploadError] ?? "Upload gagal (kode error: {$uploadError}).";
                return response()->json(['message' => $msg], 422);
            }
        } else {
            $postMax = ini_get('post_max_size');
            $uploadMax = ini_get('upload_max_filesize');
            return response()->json([
                'message' => "File gagal di-upload. Kemungkinan ukuran file melebihi batas server (post_max_size={$postMax}, upload_max_filesize={$uploadMax}). Hubungi admin server untuk menaikkan limit, atau kompres file Anda."
            ], 422);
        }

        $request->validate(['fileExcel' => 'required|file|max:51200']); // max 50MB
        $ext = strtolower($request->file('fileExcel')->getClientOriginalExtension());
        if (!in_array($ext, ['xlsx', 'xls', 'csv'])) {
            return response()->json(['message' => 'File harus berformat xlsx, xls, atau csv.'], 422);
        }

        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $file = $request->file('fileExcel');

        // Simpan file sementara
        $tempPath = $file->store('import_temp', 'local');
        session(['masuk_import_temp' => $tempPath]);

        // Baca data dari file sebagai array dengan heading row
        $headingImport = new class implements \Maatwebsite\Excel\Concerns\WithHeadingRow {
            public function headingRow(): int { return 1; }
        };
        $rows = Excel::toArray($headingImport, $file);
        $sheetRows = $rows[0] ?? [];


        // Lookup referensi dari DB (cache semuanya)
        $sumberDanaMap  = \App\Models\SumberDana::pluck('id_sumber_dana', 'nama_sumber_dana')->toArray();
        $bankTujuanMap  = \App\Models\BankTujuan::pluck('id_bank_tujuan', 'nama_tujuan')->toArray();
        $kategoriMap    = \App\Models\KategoriKriteria::pluck('id_kategori_kriteria', 'nama_kriteria')->toArray();
        $jenisPembMap   = \App\Models\JenisPembayaran::pluck('id_jenis_pembayaran', 'nama_jenis_pembayaran')->toArray();

        $preview  = [];
        $warnings = 0;

        foreach ($sheetRows as $i => $row) {
            // Ambil nilai kolom utama dulu
            $agendaRaw     = trim((string)($row['agenda_tahun'] ?? $row[0] ?? ''));
            // No Bukti manual dari pembukuan (heading "No. Bukti" → key no_bukti)
            $buktiRaw      = trim((string)($row['no_bukti'] ?? $row['bukti'] ?? ''));
            $tanggalRaw    = $row['tanggal'] ?? $row[1] ?? null;
            $sumberRaw     = trim((string)($row['sumber_dana'] ?? $row[2] ?? ''));
            $bankRaw       = trim((string)($row['bank_tujuan'] ?? $row[3] ?? ''));
            $kategoriRaw   = trim((string)($row['kategori'] ?? $row[4] ?? ''));
            $penerimaRaw   = trim((string)($row['penerima'] ?? $row[5] ?? ''));
            $uraianRaw     = trim((string)($row['uraian'] ?? $row[6] ?? ''));
            $debetRaw      = $row['debet'] ?? $row[7] ?? null;
            $jenisRaw      = trim((string)($row['jenis_pembayaran'] ?? $row[8] ?? ''));

            // Hitung nilai debet numerik untuk filter
            $debetNum = 0;
            if ($debetRaw !== null && $debetRaw !== '') {
                $debetNum = (float) str_replace(['.', ','], ['', '.'], (string)$debetRaw);
            }

            // Skip baris kosong: wajib punya tanggal DAN (debet > 0 ATAU sumber_dana tidak kosong)
            $tanggalStr = trim((string)$tanggalRaw);
            if (empty($tanggalStr) || ($debetNum <= 0 && empty($sumberRaw))) continue;

            // Resolve referensi dengan prioritas cocok nomor rekening.
            $sumberFound = $this->findReferenceName($sumberDanaMap, $sumberRaw);

            $bankFound = $this->findReferenceName($bankTujuanMap, $bankRaw);

            $kategoriFound = $this->findReferenceName($kategoriMap, $kategoriRaw);

            $jenisFound = $this->findReferenceName($jenisPembMap, $jenisRaw);


            // Format tanggal
            $tanggalFormatted = null;
            if ($tanggalRaw) {
                try {
                    if (is_numeric($tanggalRaw)) {
                        $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$tanggalRaw);
                        $tanggalFormatted = \Carbon\Carbon::instance($dt)->format('d/m/Y');
                    } else {
                        $tanggalFormatted = \Carbon\Carbon::parse(str_replace(['-','.'], '/', $tanggalRaw))->format('d/m/Y');
                    }
                } catch (\Exception $e) {
                    $tanggalFormatted = (string)$tanggalRaw;
                }
            }


            $hasWarning = ($sumberRaw && !$sumberFound)
                       || ($bankRaw && !$bankFound)
                       || ($kategoriRaw && !$kategoriFound)
                       || ($jenisRaw && !$jenisFound);

            if ($hasWarning) $warnings++;

            $preview[] = [
                'no'        => $i + 1,
                'agenda'    => $agendaRaw ?: '-',
                'bukti'     => $buktiRaw ?: '-',
                'tanggal'   => $tanggalFormatted,
                'sumber'    => $sumberFound ?? ($sumberRaw ?: '-'),
                'bank'      => $bankFound   ?? ($bankRaw   ?: '-'),
                'kategori'  => $kategoriFound ?? ($kategoriRaw ?: '-'),
                'jenis'     => $jenisFound  ?? ($jenisRaw ?: '-'),
                'penerima'  => $penerimaRaw ?: '-',
                'uraian'    => $uraianRaw   ?: '-',
                'debet'     => number_format($debetNum, 0, ',', '.'),
                'warning'   => $hasWarning,
                'warn_sumber'   => $sumberRaw && !$sumberFound,
                'warn_bank'     => $bankRaw && !$bankFound,
                'warn_kategori' => $kategoriRaw && !$kategoriFound,
                'warn_jenis'    => $jenisRaw && !$jenisFound,
            ];
        }

        return response()->json([
            'rows'     => $preview,
            'total'    => count($preview),
            'warnings' => $warnings,
        ]);
    }


    /**
     * CONFIRM: Eksekusi import dari file temp yang tersimpan di session
     */
    public function confirmImport(Request $request)
    {
        $tempPath = session('masuk_import_temp');

        if (!$tempPath || !\Illuminate\Support\Facades\Storage::disk('local')->exists($tempPath)) {
            return response()->json(['error' => 'File sementara tidak ditemukan. Silakan upload ulang.'], 422);
        }

        ini_set('memory_limit', '-1');
        set_time_limit(0);

        $fullPath = \Illuminate\Support\Facades\Storage::disk('local')->path($tempPath);

        Excel::import(new importMasuk, $fullPath);

        // Hapus file temp & session
        \Illuminate\Support\Facades\Storage::disk('local')->delete($tempPath);
        session()->forget('masuk_import_temp');

        return response()->json(['success' => 'Data berhasil diimport ke database.', 'total' => 0]);
    }

    /**
     * IMPORT MANDIRI — unduh template Excel untuk pencatatan transaksi di Excel.
     * Sheet "Data" tempat user mengisi (kolom referensi ber-dropdown), sheet
     * "Referensi" daftar master yang sah, sheet "Petunjuk" aturan pengisian.
     */
    public function downloadTemplateImport()
    {
        ini_set('memory_limit', '512M');

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // ── Sheet Referensi: kategori dibatasi tipe Masuk (sama dgn aplikasi) ──
        $refLists = [
            ['Sumber Dana', SumberDana::orderBy('nama_sumber_dana')->pluck('nama_sumber_dana')->all()],
            ['Bank Tujuan', BankTujuan::orderBy('nama_tujuan')->pluck('nama_tujuan')->all()],
            ['Kategori', KategoriKriteria::where('tipe', 'Masuk')->orderBy('nama_kriteria')->pluck('nama_kriteria')->all()],
            ['Jenis Pembayaran', JenisPembayaran::orderBy('nama_jenis_pembayaran')->pluck('nama_jenis_pembayaran')->all()],
        ];

        $ref = $spreadsheet->createSheet();
        $ref->setTitle('Referensi');
        foreach ($refLists as $i => [$judul, $list]) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
            $ref->setCellValue($col . '1', $judul);
            $r = 2;
            foreach ($list as $nama) {
                $ref->setCellValue($col . $r++, $nama);
            }
            $ref->getColumnDimension($col)->setWidth(36);
        }
        $ref->getStyle('A1:D1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
        ]);
        $ref->freezePane('A2');

        // ── Sheet Data: tempat user mengisi transaksi ──
        $sheet = $spreadsheet->getSheet(0);
        $sheet->setTitle('Data');
        $headers = ['No Agenda', 'Tanggal', 'No Bukti', 'Sumber Dana', 'Bank Tujuan', 'Kategori',
            'Jenis Pembayaran', 'Dari / Penerima', 'Uraian', 'Debet', 'Keterangan'];
        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:K1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);
        foreach (['A' => 14, 'B' => 13, 'C' => 12, 'D' => 28, 'E' => 28, 'F' => 24,
            'G' => 20, 'H' => 28, 'I' => 45, 'J' => 16, 'K' => 25] as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }
        $sheet->getStyle('B2:B1000')->getNumberFormat()->setFormatCode('dd/mm/yyyy');
        $sheet->getStyle('J2:J1000')->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('A2:A1000')->getNumberFormat()
            ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
        $sheet->getStyle('C2:C1000')->getNumberFormat()
            ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
        $sheet->freezePane('A2');

        // Dropdown kolom referensi (nama pasti cocok dengan master)
        $dropdowns = ['D' => 0, 'E' => 1, 'F' => 2, 'G' => 3];
        foreach ($dropdowns as $dataCol => $i) {
            $count = count($refLists[$i][1]);
            if ($count === 0) {
                continue;
            }
            $refCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
            $rangeName = 'RefBM' . $dataCol;
            $spreadsheet->addNamedRange(new \PhpOffice\PhpSpreadsheet\NamedRange(
                $rangeName, $ref, '$' . $refCol . '$2:$' . $refCol . '$' . ($count + 1)
            ));
            $dv = new \PhpOffice\PhpSpreadsheet\Cell\DataValidation();
            $dv->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)
                ->setAllowBlank(true)
                ->setShowDropDown(true)
                ->setShowErrorMessage(false)
                ->setFormula1($rangeName)
                ->setSqref($dataCol . '2:' . $dataCol . '1000');
            $sheet->setDataValidation($dataCol . '2', $dv);
        }

        // ── Rumus otomatis Bank Tujuan (E) dari Uraian (I) ──
        // Uraian umumnya diawali 11 digit nomor VA (mis. "81029155507|...");
        // 11 karakter pertama dicocokkan ke awalan nama di daftar Bank Tujuan.
        // Memilih manual dari dropdown tetap bisa (menimpa rumus di baris itu).
        if (count($refLists[1][1]) > 0) {
            for ($r = 2; $r <= 1000; $r++) {
                $sheet->setCellValue(
                    "E{$r}",
                    "=IF(I{$r}=\"\",\"\",IFERROR(INDEX(RefBME,MATCH(LEFT(TRIM(I{$r}),11)&\"*\",RefBME,0)),\"\"))"
                );
            }
        }

        // ── Sheet Petunjuk ──
        $ptr = $spreadsheet->createSheet();
        $ptr->setTitle('Petunjuk');
        $petunjuk = [
            'PETUNJUK PENGISIAN TEMPLATE IMPORT BANK MASUK',
            '',
            '1. Isi transaksi pada sheet "Data". Baris 1 (judul kolom) jangan diubah atau dihapus.',
            '2. Tidak semua kolom wajib diisi. Kolom yang kosong tetap ikut terimport,',
            '   tampil "-" di tabel Bank Masuk, dan bisa dilengkapi kemudian lewat tombol edit.',
            '3. Tanggal: gunakan format tanggal Excel biasa atau ketik dd/mm/yyyy (contoh 05/07/2026).',
            '4. Debet: angka nilai transaksi masuk, contoh 1500000 (boleh memakai pemisah ribuan 1.500.000).',
            '5. No Bukti: nomor bukti pembukuan Anda sendiri (manual, TIDAK dibuat otomatis oleh sistem) —',
            '   diambil apa adanya dari file ini saat import.',
            '6. Sumber Dana, Bank Tujuan, Kategori, dan Jenis Pembayaran harus sama persis dengan',
            '   daftar di sheet "Referensi" — gunakan dropdown yang tersedia. Nama yang tidak',
            '   dikenali akan dikosongkan otomatis saat import (data lain tetap masuk).',
            '7. Bank Tujuan terisi OTOMATIS bila Uraian diawali 11 digit nomor VA',
            '   (contoh "81029155507| MCM InhouseTrf ..."). Tidak perlu memilih satu per satu;',
            '   pilihan manual lewat dropdown tetap bisa dan akan menimpa isian otomatisnya.',
            '8. Simpan file, upload lewat tombol Import Excel, periksa hasil baca di pratinjau,',
            '   lalu tekan Konfirmasi Import. Data baru tersimpan setelah konfirmasi.',
        ];
        foreach ($petunjuk as $i => $line) {
            $ptr->setCellValue('A' . ($i + 1), $line);
        }
        $ptr->getStyle('A1')->getFont()->setBold(true);
        $ptr->getColumnDimension('A')->setWidth(100);

        $spreadsheet->setActiveSheetIndex(0);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, 'template_import_bank_masuk.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * IMPORT MANDIRI — parser inti file template. Dipakai previewTemplate
     * (tanpa simpan) dan confirmTemplate (simpan). Kolom boleh kosong; nama
     * referensi dicocokkan exact (case-insensitive).
     * Mengembalikan ['error' => pesan] atau ['inserts', 'warnings', 'preview'].
     */
    private function parseTemplateMasuk(string $path): array
    {
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        try {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($path);
            $spreadsheet = $reader->load($path);
            $sheet = $spreadsheet->getSheetByName('Data') ?? $spreadsheet->getSheet(0);
            $rows = $sheet->toArray(null, true, false, false);
        } catch (\Throwable $e) {
            \Log::error('parseTemplateMasuk gagal membaca file: ' . $e->getMessage());
            return ['error' => 'Gagal membaca file: ' . $e->getMessage()];
        }

        if (count($rows) < 2) {
            return ['error' => 'File tidak berisi baris data.'];
        }

        // ── Peta header → field (urutan kolom bebas, dicocokkan by nama) ──
        $normHeader = fn($v) => preg_replace('/[^a-z]/', '', strtolower((string) $v));
        $aliases = [
            'noagenda' => 'agenda',
            'agenda' => 'agenda',
            'agendatahun' => 'agenda',
            'tanggal' => 'tanggal',
            'nobukti' => 'bukti',
            'bukti' => 'bukti',
            'sumberdana' => 'sumber',
            'banktujuan' => 'bank',
            'kategori' => 'kategori',
            'kriteria' => 'kategori',
            'kategorikriteria' => 'kategori',
            'jenispembayaran' => 'jenis',
            'daripenerima' => 'penerima',
            'dari' => 'penerima',
            'penerima' => 'penerima',
            'uraian' => 'uraian',
            'debet' => 'debet',
            'nilai' => 'debet',
            'nilairupiah' => 'debet',
            'keterangan' => 'keterangan',
        ];
        $colMap = [];
        foreach ($rows[0] as $idx => $judul) {
            $key = $normHeader($judul);
            if (isset($aliases[$key]) && !isset($colMap[$aliases[$key]])) {
                $colMap[$aliases[$key]] = $idx;
            }
        }
        if (!isset($colMap['uraian']) && !isset($colMap['debet'])) {
            return ['error' => 'Judul kolom tidak dikenali. Gunakan template dari tombol "Download Template".'];
        }

        // ── Peta referensi: nama (dinormalkan) → id ──
        $normNama = fn($v) => trim(preg_replace('/\s+/', ' ', strtolower((string) $v)));
        $buildMap = function ($pairs) use ($normNama) {
            $map = [];
            foreach ($pairs as $nama => $id) {
                $map[$normNama($nama)] = $id;
            }
            return $map;
        };
        // Kategori dibatasi tipe Masuk agar nama kriteria Keluar tidak ter-map
        $refMaps = [
            'sumber' => ['id_sumber_dana', 'Sumber Dana', $buildMap(SumberDana::pluck('id_sumber_dana', 'nama_sumber_dana'))],
            'bank' => ['id_bank_tujuan', 'Bank Tujuan', $buildMap(BankTujuan::pluck('id_bank_tujuan', 'nama_tujuan'))],
            'kategori' => ['id_kategori_kriteria', 'Kategori', $buildMap(KategoriKriteria::where('tipe', 'Masuk')->pluck('id_kategori_kriteria', 'nama_kriteria'))],
            'jenis' => ['id_jenis_pembayaran', 'Jenis Pembayaran', $buildMap(JenisPembayaran::pluck('id_jenis_pembayaran', 'nama_jenis_pembayaran'))],
        ];

        // Peta nomor VA (11 digit awalan nama Bank Tujuan) → id,
        // untuk mengisi Bank Tujuan otomatis dari 11 digit pertama Uraian
        $namaBankById = BankTujuan::pluck('nama_tujuan', 'id_bank_tujuan');
        $bankByVa = [];
        foreach ($namaBankById as $idBank => $namaBank) {
            if (preg_match('/^\s*(\d{11})/', (string) $namaBank, $m)) {
                $bankByVa[$m[1]] = $idBank;
            }
        }

        $parseTanggal = function ($v) {
            if ($v === null || $v === '') {
                return null;
            }
            if (is_numeric($v)) {
                try {
                    return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $v)->format('Y-m-d');
                } catch (\Throwable $e) {
                    return null;
                }
            }
            $v = trim((string) $v);
            foreach (['d/m/Y', 'd-m-Y', 'Y-m-d', 'd.m.Y', 'd/m/y'] as $f) {
                $dt = \DateTime::createFromFormat('!' . $f, $v);
                if ($dt !== false) {
                    return $dt->format('Y-m-d');
                }
            }
            try {
                return \Carbon\Carbon::parse($v)->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        };

        $parseAngka = function ($v) {
            if ($v === null || $v === '') {
                return null;
            }
            if (is_numeric($v)) {
                return (float) $v;
            }
            $s = trim(str_ireplace(['rp', ' '], '', (string) $v));
            $s = str_replace(['(', ')'], ['-', ''], $s); // (123) = minus
            if (str_contains($s, ',') && str_contains($s, '.')) {
                $s = str_replace('.', '', $s);
                $s = str_replace(',', '.', $s);
            } elseif (str_contains($s, ',')) {
                $s = preg_match('/,\d{3}(?:,|$)/', $s) ? str_replace(',', '', $s) : str_replace(',', '.', $s);
            } elseif (substr_count($s, '.') > 1 || preg_match('/\.\d{3}$/', $s)) {
                $s = str_replace('.', '', $s); // 1.500.000 = ribuan
            }
            return is_numeric($s) ? (float) $s : null;
        };

        $get = function ($row, $field) use ($colMap) {
            if (!isset($colMap[$field])) {
                return null;
            }
            $v = $row[$colMap[$field]] ?? null;
            if (is_string($v)) {
                $v = trim($v);
            }
            return ($v === '' ? null : $v);
        };

        $now = now();
        $inserts = [];
        $warnings = [];
        $preview = [];
        $skipped = 0;

        foreach (array_slice($rows, 1, null, true) as $rIdx => $row) {
            $rowNum = $rIdx + 1; // nomor baris di Excel

            $adaIsi = false;
            foreach ($colMap as $idx) {
                $v = $row[$idx] ?? null;
                if ($v !== null && trim((string) $v) !== '') {
                    $adaIsi = true;
                    break;
                }
            }
            if (!$adaIsi) {
                $skipped++;
                continue;
            }

            $warnFields = [];

            $data = [
                'agenda_tahun' => $get($row, 'agenda'),
                'no_bukti' => $get($row, 'bukti'),
                'tanggal' => $parseTanggal($get($row, 'tanggal')),
                'penerima' => $get($row, 'penerima'),
                'uraian' => $get($row, 'uraian'),
                'keterangan' => $get($row, 'keterangan'),
                'debet' => $parseAngka($get($row, 'debet')) ?? 0,
                'kredit' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $rawTanggal = $get($row, 'tanggal');
            if ($rawTanggal !== null && $data['tanggal'] === null) {
                $warnings[] = "Baris {$rowNum}: Tanggal \"{$rawTanggal}\" tidak dikenali — dikosongkan";
                $warnFields['tanggal'] = true;
            }
            $rawDebet = $get($row, 'debet');
            if ($rawDebet !== null && $parseAngka($rawDebet) === null) {
                $warnings[] = "Baris {$rowNum}: Debet \"{$rawDebet}\" bukan angka — diisi 0";
                $warnFields['debet'] = true;
            }

            foreach ($refMaps as $field => [$dbCol, $label, $map]) {
                $nama = $get($row, $field);
                // Hasil rumus error di Excel (mis. #N/A) diperlakukan sebagai kosong
                if (is_string($nama) && preg_match('~^#(N/A|REF!|VALUE!|NAME\?|DIV/0!|NULL!|NUM!)$~', $nama)) {
                    $nama = null;
                }
                $data[$dbCol] = null;
                if ($nama !== null) {
                    $id = $map[$normNama($nama)] ?? null;
                    if ($id === null) {
                        $warnings[] = "Baris {$rowNum}: {$label} \"{$nama}\" tidak ada di master — dikosongkan";
                        $warnFields[$field] = true;
                    }
                    $data[$dbCol] = $id;
                }
            }

            // Bank Tujuan otomatis dari Uraian: bila kolom Bank kosong/tidak dikenal,
            // 11 digit pertama Uraian (nomor VA) menentukan bank tujuannya —
            // fallback dari rumus template, tetap jalan meski rumusnya dihapus user.
            $bankOtomatis = false;
            if ($data['id_bank_tujuan'] === null && $data['uraian'] !== null
                && preg_match('/^\s*(\d{11})/', (string) $data['uraian'], $m)
                && isset($bankByVa[$m[1]])) {
                $data['id_bank_tujuan'] = $bankByVa[$m[1]];
                $bankOtomatis = true;
            }

            $inserts[] = $data;

            $preview[] = [
                'baris' => $rowNum,
                'agenda' => $data['agenda_tahun'],
                'bukti' => $data['no_bukti'],
                'tanggal' => $data['tanggal']
                    ? \Carbon\Carbon::parse($data['tanggal'])->format('d/m/Y')
                    : ($rawTanggal !== null ? (string) $rawTanggal : null),
                'sumber' => $get($row, 'sumber'),
                'bank' => $bankOtomatis
                    ? ($namaBankById[$data['id_bank_tujuan']] ?? '') . ' (otomatis)'
                    : $get($row, 'bank'),
                'kategori' => $get($row, 'kategori'),
                'jenis' => $get($row, 'jenis'),
                'penerima' => $data['penerima'],
                'uraian' => $data['uraian'],
                'debet' => number_format($data['debet'], 0, ',', '.'),
                'warn' => $warnFields,
            ];
        }

        return [
            'inserts' => $inserts,
            'warnings' => $warnings,
            'preview' => $preview,
            'skipped' => $skipped,
        ];
    }

    /**
     * IMPORT MANDIRI — PREVIEW: parse file tanpa menyimpan apa pun.
     */
    public function previewTemplate(Request $request)
    {
        $request->validate(['fileTemplate' => 'required|file|max:20480']);
        $ext = strtolower($request->file('fileTemplate')->getClientOriginalExtension());
        if (!in_array($ext, ['xlsx', 'xls', 'csv'])) {
            return response()->json(['message' => 'File harus berformat xlsx, xls, atau csv.'], 422);
        }

        $tempPath = $request->file('fileTemplate')->store('import_temp_masuk', 'local');
        if (!$tempPath) {
            \Log::error('previewTemplate masuk: gagal menyimpan file sementara (cek izin tulis storage/app/private/import_temp_masuk).');
            return response()->json(['message' => 'Gagal menyimpan file sementara di server. Hubungi admin (izin tulis folder storage).'], 500);
        }
        session(['masuk_template_temp' => $tempPath]);

        $hasil = $this->parseTemplateMasuk(
            \Illuminate\Support\Facades\Storage::disk('local')->path($tempPath)
        );
        if (isset($hasil['error'])) {
            return response()->json(['message' => $hasil['error']], 422);
        }

        return response()->json([
            'total' => count($hasil['inserts']),
            'warnings' => count($hasil['warnings']),
            'warning_details' => array_slice($hasil['warnings'], 0, 50),
            'rows' => $hasil['preview'],
        ]);
    }

    /**
     * IMPORT MANDIRI — KONFIRMASI: simpan data dari file yang sudah di-preview.
     */
    public function confirmTemplate(Request $request)
    {
        $tempPath = session('masuk_template_temp');
        if (!$tempPath || !\Illuminate\Support\Facades\Storage::disk('local')->exists($tempPath)) {
            return response()->json(['message' => 'File sementara tidak ditemukan. Silakan upload ulang.'], 422);
        }

        $hasil = $this->parseTemplateMasuk(
            \Illuminate\Support\Facades\Storage::disk('local')->path($tempPath)
        );
        if (isset($hasil['error'])) {
            return response()->json(['message' => $hasil['error']], 422);
        }
        if (empty($hasil['inserts'])) {
            return response()->json(['message' => 'Tidak ada baris berisi data untuk diimport.'], 422);
        }

        try {
            DB::transaction(function () use ($hasil) {
                foreach (array_chunk($hasil['inserts'], 500) as $chunk) {
                    BankMasuk::insert($chunk);
                }
            });
        } catch (\Throwable $e) {
            \Log::error('confirmTemplate masuk gagal menyimpan: ' . $e->getMessage());
            return response()->json(['message' => 'Import gagal saat menyimpan: ' . $e->getMessage()], 500);
        }

        \Illuminate\Support\Facades\Storage::disk('local')->delete($tempPath);
        session()->forget('masuk_template_temp');

        $pesan = count($hasil['inserts']) . ' baris berhasil diimport.';
        if (!empty($hasil['warnings'])) {
            $pesan .= ' (' . count($hasil['warnings']) . ' peringatan — kolom terkait dikosongkan)';
        }

        return response()->json([
            'success' => $pesan,
            'imported' => count($hasil['inserts']),
        ]);
    }


    public function edit(string $id)
    {
        $masuk = BankMasuk::findOrFail($id);
        return view('cash_bank.modal.edit', compact('masuk'));
    }
    public function update(Request $request, string $id)
    {
        $masuk = BankMasuk::findOrFail($id);

        // Normalisasi dari editor inline: '' / '-' pada kolom referensi = null
        $data = $request->except(['_method', '_token']);
        foreach (['id_sumber_dana', 'id_bank_tujuan', 'id_kategori_kriteria', 'id_jenis_pembayaran'] as $f) {
            if (array_key_exists($f, $data) && ($data[$f] === '' || $data[$f] === '-')) {
                $data[$f] = null;
            }
        }
        if (array_key_exists('tanggal', $data) && $data['tanggal'] === '') {
            $data['tanggal'] = null;
        }
        if (array_key_exists('no_bukti', $data) && ($data['no_bukti'] === '' || $data['no_bukti'] === '-')) {
            $data['no_bukti'] = null;
        }

        $masuk->update($data);

        if ($request->ajax()) {
            return response()->json(['success' => 'Data berhasil diperbarui']);
        }
        return redirect()->route('bank-masuk.index')->with('success', 'Data berhasil diperbarui');
    }

    public function destroy(string $id)
    {
        $data = BankMasuk::findOrFail($id);
        $data->delete();

        return redirect()->route('bank-masuk.index')->with('success', 'Data berhasil dihapus');
    }

    public function deleteAll(Request $request)
    {
        // Support JSON body (untuk menghindari batas max_input_vars PHP saat data ribuan)
        $ids = $request->json('ids') ?? $request->input('ids');

        if (empty($ids) || !is_array($ids)) {
            return response()->json(['error' => 'Tidak ada data yang dipilih.'], 422);
        }

        $deleted = BankMasuk::whereIn('id_bank_masuk', $ids)->delete();

        return response()->json([
            'success' => "Berhasil menghapus {$deleted} data Bank Masuk."
        ]);
    }


    public function export_excel(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        return Excel::download(
            new ExportExcelBankMasuk($request->only(['tahun', 'bulan', 'kategori', 'sumber_dana'])),
            'bankMasuk-' . date('Y-m-d') . '.xlsx'
        );
    }

    public function report_export_excel(Request $request)
    {
        return Excel::download(
            new reportMasukExcel($request),
            'report-bank-masuk-' . date('Y-m-d') . '.xlsx'
        );
    }

    public function view_pdf(Request $request)
    {
        // Batas aman halaman cetak: ribuan baris membuat browser/print macet
        $maxRows = 3000;
        $jumlah = $this->applyExportFilter(BankMasuk::query(), $request)->count();
        if ($jumlah > $maxRows) {
            return response(
                '<div style="font-family:sans-serif;max-width:560px;margin:80px auto;text-align:center;">'
                . '<h3 style="color:#c0392b;">Data terlalu banyak untuk PDF</h3>'
                . '<p>Filter saat ini mencakup <strong>' . number_format($jumlah, 0, ',', '.')
                . '</strong> baris (maks. ' . number_format($maxRows, 0, ',', '.') . ').<br>'
                . 'Persempit filter (tahun/bulan/kategori) lalu coba lagi, '
                . 'atau gunakan Download Excel untuk data besar.</p>'
                . '<p><a href="javascript:window.close()">Tutup tab ini</a></p></div>',
                422
            )->header('Content-Type', 'text/html');
        }

        $query = BankMasuk::select(
            'id_bank_masuk',
            'agenda_tahun',
            'tanggal',
            'id_sumber_dana',
            'id_bank_tujuan',
            'id_kategori_kriteria',
            'penerima',
            'uraian',
            'id_jenis_pembayaran',
            'nilai_rupiah',
            'debet',
            'keterangan'
        )
            ->with([
                'sumberDana:id_sumber_dana,nama_sumber_dana',
                'bankTujuan:id_bank_tujuan,nama_tujuan',
                'kategori:id_kategori_kriteria,nama_kriteria',
                'jenisPembayaran:id_jenis_pembayaran,nama_jenis_pembayaran',
            ])
            ->orderBy('tanggal', 'asc')
            ->orderBy('id_bank_masuk');
        $data = $this->applyExportFilter($query, $request)->get();

        // Info filter untuk kop laporan
        $bulanNama = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
        $filterInfo = 'Periode: ' . ($request->filled('bulan') ? ($bulanNama[(int) $request->bulan] ?? '') . ' ' : '')
            . ($request->filled('tahun') ? $request->tahun : 'Semua Tahun');
        if ($request->filled('kategori')) {
            $filterInfo .= ' • Kategori: ' . (optional(KategoriKriteria::find($request->kategori))->nama_kriteria ?? '-');
        }
        if ($request->filled('sumber_dana')) {
            $filterInfo .= ' • Sumber Dana: ' . (optional(SumberDana::find($request->sumber_dana))->nama_sumber_dana ?? '-');
        }

        return view('cash_bank.exportPDF.masukPdf', [
            'data' => $data,
            'filterInfo' => $filterInfo,
        ]);
    }

    public function reportMasukPdf(Request $request)
    {
        /* ================= REQUEST ================= */
        $tahun = $request->tahun;
        $bulan = $request->bulan;
        $bankTujuanId = $request->bankTujuan;
        $sumberDanaIds = $request->sumber_dana ?? [];
        $kategoriIds = $request->kategori ?? [];
        $jenisPembayaranId = $request->jenis_pembayaran;

        /* ================= QUERY DATA ================= */
        $data = BankMasuk::with(['sumberDana', 'bankTujuan', 'kategori', 'jenisPembayaran'])
            ->when($tahun, fn($q) => $q->whereYear('tanggal', $tahun))
            ->when($bulan, fn($q) => $q->whereMonth('tanggal', $bulan))
            ->when($bankTujuanId, fn($q) => $q->where('id_bank_tujuan', $bankTujuanId))
            ->when($jenisPembayaranId, fn($q) => $q->where('id_jenis_pembayaran', $jenisPembayaranId))
            ->when(count($sumberDanaIds), fn($q) => $q->whereIn('id_sumber_dana', $sumberDanaIds))
            ->when(count($kategoriIds), fn($q) => $q->whereIn('id_kategori_kriteria', $kategoriIds))
            ->orderBy('tanggal')
            ->get();

        /* ================= DROPDOWN TERHUBUNG ================= */

        // Bank Tujuan
        $bankTujuanList = DB::table('bank_tujuan')
            ->whereExists(function ($q) use ($tahun, $bulan, $sumberDanaIds, $kategoriIds, $jenisPembayaranId) {
                $q->select(DB::raw(1))
                    ->from('bank_masuk')
                    ->whereColumn('bank_masuk.id_bank_tujuan', 'bank_tujuan.id_bank_tujuan')
                    ->when($tahun, fn($x) => $x->whereYear('tanggal', $tahun))
                    ->when($bulan, fn($x) => $x->whereMonth('tanggal', $bulan))
                    ->when(count($sumberDanaIds), fn($x) => $x->whereIn('id_sumber_dana', $sumberDanaIds))
                    ->when(count($kategoriIds), fn($x) => $x->whereIn('id_kategori_kriteria', $kategoriIds))
                    ->when($jenisPembayaranId, fn($x) => $x->where('id_jenis_pembayaran', $jenisPembayaranId));
            })
            ->orderBy('nama_tujuan')
            ->get();

        // Sumber Dana
        $sumberDanaList = DB::table('sumber_dana')
            ->whereExists(function ($q) use ($tahun, $bulan, $bankTujuanId, $kategoriIds, $jenisPembayaranId) {
                $q->select(DB::raw(1))
                    ->from('bank_masuk')
                    ->whereColumn('bank_masuk.id_sumber_dana', 'sumber_dana.id_sumber_dana')
                    ->when($tahun, fn($x) => $x->whereYear('tanggal', $tahun))
                    ->when($bulan, fn($x) => $x->whereMonth('tanggal', $bulan))
                    ->when($bankTujuanId, fn($x) => $x->where('id_bank_tujuan', $bankTujuanId))
                    ->when($jenisPembayaranId, fn($x) => $x->where('id_jenis_pembayaran', $jenisPembayaranId))
                    ->when(count($kategoriIds), fn($x) => $x->whereIn('id_kategori_kriteria', $kategoriIds));
            })
            ->orderBy('nama_sumber_dana')
            ->get();

        // Kategori
        $kategoriList = DB::table('kategori_kriteria')
            ->whereExists(function ($q) use ($tahun, $bulan, $bankTujuanId, $sumberDanaIds, $jenisPembayaranId) {
                $q->select(DB::raw(1))
                    ->from('bank_masuk')
                    ->whereColumn('bank_masuk.id_kategori_kriteria', 'kategori_kriteria.id_kategori_kriteria')
                    ->when($tahun, fn($x) => $x->whereYear('tanggal', $tahun))
                    ->when($bulan, fn($x) => $x->whereMonth('tanggal', $bulan))
                    ->when($bankTujuanId, fn($x) => $x->where('id_bank_tujuan', $bankTujuanId))
                    ->when($jenisPembayaranId, fn($x) => $x->where('id_jenis_pembayaran', $jenisPembayaranId))
                    ->when(count($sumberDanaIds), fn($x) => $x->whereIn('id_sumber_dana', $sumberDanaIds));

            })
            ->orderBy('nama_kriteria')
            ->get();

        // Jenis Pembayaran
        $jenisPembayaranList = DB::table('jenis_pembayarans')
            ->whereExists(function ($q) use ($tahun, $bulan, $bankTujuanId, $sumberDanaIds, $kategoriIds) {
                $q->select(DB::raw(1))
                    ->from('bank_masuk')
                    ->whereColumn('bank_masuk.id_jenis_pembayaran', 'jenis_pembayarans.id_jenis_pembayaran')
                    ->when($tahun, fn($x) => $x->whereYear('tanggal', $tahun))
                    ->when($bulan, fn($x) => $x->whereMonth('tanggal', $bulan))
                    ->when($bankTujuanId, fn($x) => $x->where('id_bank_tujuan', $bankTujuanId))
                    ->when(count($sumberDanaIds), fn($x) => $x->whereIn('id_sumber_dana', $sumberDanaIds))
                    ->when(count($kategoriIds), fn($x) => $x->whereIn('id_kategori_kriteria', $kategoriIds));
            })
            ->orderBy('nama_jenis_pembayaran')
            ->get();

        $tahunList = BankMasuk::selectRaw('YEAR(tanggal) tahun')->groupBy('tahun')->pluck('tahun');

        return view('cash_bank.exportPDF.reportMasuk', compact(
            'data',
            'tahunList',
            'bankTujuanList',
            'sumberDanaList',
            'kategoriList',
            'jenisPembayaranList'
        ));
    }
}
