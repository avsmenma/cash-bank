<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Dokumen;
use App\Models\BankKeluar;
use App\Models\BankTujuan;
use App\Models\SumberDana;
use App\Models\SubKriteria;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Imports\importKeluar;
use App\Models\DokumenAgenda;
use App\Imports\EmployeeImport;
use App\Models\ItemSubKriteria;
use App\Models\JenisPembayaran;
use App\Exports\excelBankKeluar;
use App\Models\KategoriKriteria;
use App\Exports\reportKeluarExcel;
use App\Imports\importSheetKeluar;
use Illuminate\Support\Facades\DB;
use App\Models\GabunganMasukKeluar;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Cache;
use Yajra\DataTables\Facades\DataTables;
// use Maatwebsite\Excel\Excel;

class BankKeluarController extends Controller
{

    public function index(Request $request)
    {

        // ->paginate(25)
        // ->withQueryString();
        // DB::raw("CONCAT(nomor_agenda,'_',tahun) as agenda_tahun"),

        /* ================= DATA AGENDA================= */
        // $agenda = DB::connection('mysql_agenda_online')
        //     ->table('dokumens')
        //     ->select(
        //         'id as dokumen_id',
        //         'nomor_agenda as agenda_tahun',
        //         'uraian_spp as uraian',
        //         'nilai_rupiah',
        //         'dibayar_kepada as penerima',
        //         'jenis_pembayaran'
        //     )
        //     // ->where('current_handler', 'pembayaran')
        //     ->where('status_pembayaran', '!=','sudah_dibayar')
        //     ->whereNull('status_pembayaran')
        //     ->orderBy('tanggal_masuk', 'asc')
        //     ->get();
        $agenda = DB::connection('mysql_agenda_online')
            ->table('dokumens')
            ->select(
                'id as dokumen_id',
                'nomor_agenda as agenda_tahun',
                'uraian_spp as uraian',
                'nilai_rupiah',
                'dibayar_kepada as penerima',
                'jenis_pembayaran'
            )
            ->where(function ($q) {
                $q->where('status_pembayaran', '!=', 'sudah_dibayar')
                    ->orWhereNull('status_pembayaran');
            })
            ->orderBy('tanggal_masuk', 'asc')
            ->get();


        // ->where('status_pembayaran', 'SIAP DIBAYAR')
        /* ================= CACHE DATA MASTER ================= */
        $sumberDana = Cache::remember('sumber_dana', 3600, fn() => SumberDana::all());
        $bankTujuan = Cache::remember('bank_tujuan', 3600, fn() => BankTujuan::all());
        $kategoriKriteria = Cache::remember(
            'kategori_keluar',
            3600,
            fn() => KategoriKriteria::where('tipe', 'Keluar')->get()
        );
        $subKriteria = Cache::remember('sub_kriteria', 3600, fn() => SubKriteria::orderByRaw("CASE nama_sub_kriteria
                WHEN 'Karyawan Pimpinan' THEN 1
                WHEN 'Karyawan Pelaksana' THEN 2
                WHEN 'Gaji Honor' THEN 3
                WHEN 'Purchase Volume' THEN 4
                WHEN 'Biaya Usaha dan Lainnya' THEN 5
                WHEN 'Pajak' THEN 6
                WHEN 'Operasional Produksi' THEN 7
                ELSE 999
            END")
            ->orderBy('id_sub_kriteria')
            ->get());
        $itemSubKriteria = Cache::remember('item_sub_kriteria', 3600, fn() => ItemSubKriteria::all());
        $jenisPembayaran = Cache::remember('jenis_pembayaran', 3600, fn() => JenisPembayaran::all());


        // Daftar tahun untuk filter export (dari data yang ada)
        $exportYears = BankKeluar::selectRaw('DISTINCT YEAR(tanggal) as y')
            ->whereNotNull('tanggal')->orderByDesc('y')->pluck('y');

        return view('cash_bank.bankKeluar', compact(
            'agenda',
            'sumberDana',
            'bankTujuan',
            'kategoriKriteria',
            'subKriteria',
            'itemSubKriteria',
            'jenisPembayaran',
            'exportYears'
        ));
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
            'total' => $this->applyExportFilter(BankKeluar::query(), $request)->count(),
        ]);
    }

    public function datatable(Request $request)
    {
        $query = BankKeluar::with([
            'sumberDana:id_sumber_dana,nama_sumber_dana',
            'bankTujuan:id_bank_tujuan,nama_tujuan',
            'kategori:id_kategori_kriteria,nama_kriteria',
            'subKriteria:id_sub_kriteria,nama_sub_kriteria',
            'itemSubKriteria:id_item_sub_kriteria,nama_item_sub_kriteria',
            'jenisPembayaran:id_jenis_pembayaran,nama_jenis_pembayaran',
            // Tie-breaker id: banyak baris bertanggal sama — tanpa urutan
            // deterministik, chunk LIMIT/OFFSET bisa duplikat/melewatkan baris.
        ])->orderBy('tanggal', 'desc')->orderBy('id_bank_keluar', 'desc'); // terbaru dulu

        // Date range filter from header popup
        if ($request->filled('filter_tgl_dari')) {
            $query->where('tanggal', '>=', $request->filter_tgl_dari);
        }
        if ($request->filled('filter_tgl_sampai')) {
            $query->where('tanggal', '<=', $request->filter_tgl_sampai);
        }

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
            ->addColumn('sub_kriteria', function ($row) {
                return $row->subKriteria
                    ? $row->subKriteria->nama_sub_kriteria
                    : '-';
            })
            ->addColumn('item_sub_kriteria', function ($row) {
                return $row->itemSubKriteria
                    ? $row->itemSubKriteria->nama_item_sub_kriteria
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
            ->addColumn('tanggal_raw', function ($row) {
                return $row->tanggal
                    ? \Carbon\Carbon::parse($row->tanggal)->format('Y-m-d')
                    : '';
            })
            ->addColumn('kredit_raw', function ($row) {
                return (float) $row->kredit;
            })
            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="checkbox_ids" name="ids[]" value="' . $row->id_bank_keluar . '">';
            })
            ->editColumn('kredit', function ($row) {
                return number_format((float) $row->kredit, 0, ',', '.');
            })
            ->editColumn('tanggal', function ($row) {
                return \Carbon\Carbon::parse($row->tanggal)->translatedFormat('d F Y');
            })
            ->filterColumn('tanggal', function ($query, $keyword) {
                // Support searching by date string (e.g. "2026-01-15" or "01/2026" or "January")
                $query->whereRaw("DATE_FORMAT(tanggal, '%Y-%m-%d') LIKE ?", ["%{$keyword}%"]);
            })
            ->filterColumn('sumber_dana', function ($query, $keyword) {
                $query->whereHas('sumberDana', function ($q) use ($keyword) {
                    $q->where('nama_sumber_dana', 'LIKE', "%{$keyword}%");
                });
            })
            ->filterColumn('bank_tujuan', function ($query, $keyword) {
                $query->whereHas('bankTujuan', function ($q) use ($keyword) {
                    $q->where('nama_tujuan', 'LIKE', "%{$keyword}%");
                });
            })
            ->filterColumn('kategori_kriteria', function ($query, $keyword) {
                $query->whereHas('kategori', function ($q) use ($keyword) {
                    $q->where('nama_kriteria', 'LIKE', "%{$keyword}%");
                });
            })
            ->filterColumn('sub_kriteria', function ($query, $keyword) {
                $query->whereHas('subKriteria', function ($q) use ($keyword) {
                    $q->where('nama_sub_kriteria', 'LIKE', "%{$keyword}%");
                });
            })
            ->filterColumn('item_sub_kriteria', function ($query, $keyword) {
                $query->whereHas('itemSubKriteria', function ($q) use ($keyword) {
                    $q->where('nama_item_sub_kriteria', 'LIKE', "%{$keyword}%");
                });
            })
            ->filterColumn('jenis_pembayaran', function ($query, $keyword) {
                $query->whereHas('jenisPembayaran', function ($q) use ($keyword) {
                    $q->where('nama_jenis_pembayaran', 'LIKE', "%{$keyword}%");
                });
            })
            ->filterColumn('kredit', function ($query, $keyword) {
                $normalized = preg_replace('/[^\d]/', '', $keyword);
                if ($normalized !== '') {
                    $query->where('kredit', 'LIKE', "%{$normalized}%");
                }
            })
            ->rawColumns(['checkbox'])
            ->make(true);
    }



    public function store(Request $request)
    {
        $validated = $request->validate([
            'agenda_tahun' => 'nullable|string',
            'id_sumber_dana' => 'nullable|exists:sumber_dana,id_sumber_dana',
            'id_bank_tujuan' => 'nullable|exists:bank_tujuan,id_bank_tujuan',
            'id_kategori_kriteria' => 'nullable|exists:kategori_kriteria,id_kategori_kriteria',
            'id_sub_kriteria' => 'nullable|exists:sub_kriteria,id_sub_kriteria',
            'id_item_sub_kriteria' => 'nullable|exists:item_sub_kriteria,id_item_sub_kriteria',
            'uraian' => 'nullable|string',
            'jenis_pembayaran' => 'nullable|string',
            'nilai_rupiah' => 'nullable|numeric|min:0',
            'penerima' => 'nullable|string',
            'tanggal' => 'nullable|date',
            'debet' => 'nullable|numeric|min:0',
            'kredit' => 'nullable|numeric|min:0',
            'keterangan' => 'nullable|string',

            'split.kategori.*' => 'sometimes|required|exists:kategori_kriteria,id_kategori_kriteria',
            'split.sub_kriteria.*' => 'sometimes|required|exists:sub_kriteria,id_sub_kriteria',
            'split.item.*' => 'sometimes|required|exists:item_sub_kriteria,id_item_sub_kriteria',
            'split.kredit.*' => 'sometimes|required|numeric|min:0',
        ]);

        $validated['debet'] = $validated['debet'] ?? 0;
        $validated['kredit'] = $validated['kredit'] ?? 0;

        DB::beginTransaction();

        $input = $request->agenda_tahun;
        $dokumen_id = null;
        $no_agenda = null;
        $agenda_tahun = $input;

        if (is_numeric($input)) {
            $dokumen = DB::connection('mysql_agenda_online')
                ->table('dokumens')
                ->find($input);

            if ($dokumen) {
                $dokumen_id = $dokumen->id;
                $agenda_tahun = $dokumen->nomor_agenda;
                // $agenda_tahun = $dokumen->nomor_agenda . '_' . $dokumen->tahun;

                // Build sync payload with basic fields
                $syncPayload = [
                    'uraian_spp' => $request->uraian,
                    'nilai_rupiah' => $request->nilai_rupiah,
                    'dibayar' => $request->nilai_rupiah,
                    'dibayar_kepada' => $request->penerima,
                    'status_pembayaran' => 'sudah_dibayar',
                    'tanggal_dibayar' => $request->tanggal,
                ];

                // Lookup kategori ID → nama
                if ($request->id_kategori_kriteria) {
                    $kategori = DB::table('kategori_kriteria')
                        ->where('id_kategori_kriteria', $request->id_kategori_kriteria)
                        ->first();
                    if ($kategori) {
                        $syncPayload['kategori'] = $kategori->nama_kriteria;
                    }
                }

                // Lookup sub_kriteria ID → nama (jenis_dokumen)
                if ($request->id_sub_kriteria) {
                    $sub = DB::table('sub_kriteria')
                        ->where('id_sub_kriteria', $request->id_sub_kriteria)
                        ->first();
                    if ($sub) {
                        $syncPayload['jenis_dokumen'] = $sub->nama_sub_kriteria;
                    }
                }

                // Lookup item_sub_kriteria ID → nama (jenis_sub_pekerjaan)
                if ($request->id_item_sub_kriteria) {
                    $item = DB::table('item_sub_kriteria')
                        ->where('id_item_sub_kriteria', $request->id_item_sub_kriteria)
                        ->first();
                    if ($item) {
                        $syncPayload['jenis_sub_pekerjaan'] = $item->nama_item_sub_kriteria;
                    }
                }

                // Lookup jenis_pembayaran ID → nama
                if ($request->id_jenis_pembayaran) {
                    $jp = DB::table('jenis_pembayarans')
                        ->where('id_jenis_pembayaran', $request->id_jenis_pembayaran)
                        ->first();
                    if ($jp) {
                        $syncPayload['jenis_pembayaran'] = $jp->nama_jenis_pembayaran;
                    }
                }

                DB::connection('mysql_agenda_online')
                    ->table('dokumens')
                    ->where('id', $dokumen->id)
                    ->update($syncPayload);
            }
        }
        $pakaiSplit = $request->filled('split.kredit');
        $kreditUtama = $pakaiSplit ? 0 : ($validated['kredit'] ?? 0);
        BankKeluar::create([
            'dokumen_id' => $dokumen_id,
            'no_agenda' => $no_agenda,
            'agenda_tahun' => $agenda_tahun,
            'id_sumber_dana' => $request->id_sumber_dana,
            'id_bank_tujuan' => $request->id_bank_tujuan,
            'id_kategori_kriteria' => $request->id_kategori_kriteria,
            'id_sub_kriteria' => $request->id_sub_kriteria,
            'id_item_sub_kriteria' => $request->id_item_sub_kriteria,
            'uraian' => $request->uraian,
            'nilai_rupiah' => $request->nilai_rupiah ?? 0,
            'penerima' => $request->penerima,
            'tanggal' => $request->tanggal,
            'id_jenis_pembayaran' => $request->id_jenis_pembayaran,
            'debet' => $validated['debet'],
            'kredit' => $validated['kredit'],
            'keterangan' => $request->keterangan,
        ]);

        if ($request->filled('split.kredit')) {

            foreach ($request->split['kredit'] as $i => $nilai) {
                BankKeluar::create([
                    'agenda_tahun' => $agenda_tahun,
                    'dokumen_id' => $dokumen_id,
                    'no_agenda' => $no_agenda,
                    'id_sumber_dana' => $request->id_sumber_dana,
                    'id_bank_tujuan' => $request->id_bank_tujuan,
                    'id_kategori_kriteria' => $request->split['kategori'][$i] ?? null,
                    'id_sub_kriteria' => $request->split['sub_kriteria'][$i] ?? null,
                    'id_item_sub_kriteria' => $request->split['item_sub_kriteria'][$i] ?? null,
                    'uraian' => $request->uraian,
                    'penerima' => $request->penerima,
                    'tanggal' => $request->tanggal,
                    'id_jenis_pembayaran' => $request->id_jenis_pembayaran,
                    'nilai_rupiah' => $request->nilai_rupiah,
                    'kredit' => $nilai,
                    'debet' => 0,
                    'keterangan' => "Split pembayaran Agenda {$agenda_tahun}",
                ]);
            }


            // dd($request->split);
        }

        DB::commit();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data Bank Keluar berhasil disimpan']);
        }

        return redirect()->back()->with('success', 'Data Bank Keluar berhasil disimpan');
    }

    public function getSub($id)
    {
        try {
            \Log::info('getSub called with id: ' . $id);

            // Gunakan Query Builder langsung
            $subKriteria = DB::table('sub_kriteria')
                ->select('id_sub_kriteria', 'nama_sub_kriteria', 'id_kategori_kriteria')
                ->where('id_kategori_kriteria', $id)
                ->orderByRaw("CASE nama_sub_kriteria
                    WHEN 'Karyawan Pimpinan' THEN 1
                    WHEN 'Karyawan Pelaksana' THEN 2
                    WHEN 'Gaji Honor' THEN 3
                    WHEN 'Purchase Volume' THEN 4
                    WHEN 'Biaya Usaha dan Lainnya' THEN 5
                    WHEN 'Pajak' THEN 6
                    WHEN 'Operasional Produksi' THEN 7
                    ELSE 999
                END")
                ->orderBy('id_sub_kriteria')
                ->get();

            \Log::info('getSub result:', ['count' => $subKriteria->count(), 'data' => $subKriteria->toArray()]);

            return response()->json($subKriteria);

        } catch (\Exception $e) {
            \Log::error('Error getSub: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    public function getItem($id)
    {
        try {
            \Log::info('getItem called with id: ' . $id);

            // Gunakan Query Builder langsung, deduplicate by nama
            $itemSubKriteria = DB::table('item_sub_kriteria')
                ->select(
                    DB::raw('MIN(id_item_sub_kriteria) as id_item_sub_kriteria'),
                    'nama_item_sub_kriteria',
                    'id_sub_kriteria'
                )
                ->where('id_sub_kriteria', $id)
                ->groupBy('nama_item_sub_kriteria', 'id_sub_kriteria')
                ->orderBy(DB::raw('MIN(id_item_sub_kriteria)'))
                ->get();

            \Log::info('getItem result:', ['count' => $itemSubKriteria->count(), 'data' => $itemSubKriteria->toArray()]);

            return response()->json($itemSubKriteria);

        } catch (\Exception $e) {
            \Log::error('Error getItem: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }
    public function getDokumenDetail($id)
    {
        try {
            // ========================================
            // STEP 1: AMBIL DATA DARI DATABASE AGENDA
            // ========================================
            $dokumen = DB::connection('mysql_agenda_online')
                ->table('dokumens')
                ->select(
                    'id as dokumen_id',
                    'uraian_spp as uraian',
                    'nilai_rupiah',
                    'dibayar_kepada as penerima',
                    'jenis_pembayaran',
                    'kategori',
                    'jenis_dokumen',
                    'jenis_sub_pekerjaan'
                )
                ->where('id', $id)
                ->first();

            if (!$dokumen) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dokumen tidak ditemukan'
                ], 404);
            }

            // TAMBAHKAN LOG DATA MENTAH
            \Log::info('=== DATA MENTAH DARI AGENDA ===', [
                'dokumen_id' => $dokumen->dokumen_id,
                'jenis_pembayaran' => $dokumen->jenis_pembayaran,
                'uraian_spp' => $dokumen->uraian,
                'kategori' => $dokumen->kategori,
                'jenis_dokumen' => $dokumen->jenis_dokumen ?? '',
                'jenis_sub_pekerjaan' => $dokumen->jenis_sub_pekerjaan ?? '',
            ]);

            // Bersihkan spasi/newline
            $jenisPembayaranStr = trim($dokumen->jenis_pembayaran ?? '');
            $kategoriStr = trim($dokumen->kategori ?? '');
            $jenisDokumenStr = trim($dokumen->jenis_dokumen ?? '');
            $jenisSubPekerjaanStr = trim($dokumen->jenis_sub_pekerjaan ?? '');

            \Log::info('=== DATA SETELAH TRIM ===', [
                'jenis_pembayaran' => $jenisPembayaranStr,
                'kategori' => $kategoriStr,
                'jenis_dokumen' => $jenisDokumenStr,
                'jenis_sub_pekerjaan' => $jenisSubPekerjaanStr,
            ]);

            // ========================================
            // STEP 2: CARI JENIS PEMBAYARAN
            // ========================================
            $jenisPembayaranId = null;

            if (!empty($jenisPembayaranStr)) {
                $jenisPembayaran = DB::table('jenis_pembayarans')
                    ->whereRaw('LOWER(TRIM(nama_jenis_pembayaran)) = ?', [strtolower($jenisPembayaranStr)])
                    ->first();

                $jenisPembayaranId = $jenisPembayaran->id_jenis_pembayaran ?? null;

                \Log::info('Jenis Pembayaran:', [
                    'search' => $jenisPembayaranStr,
                    'found' => $jenisPembayaran ? 'YA' : 'TIDAK',
                    'id' => $jenisPembayaranId
                ]);
            }

            // ========================================
            // STEP 3: CARI KATEGORI DULU
            // ========================================
            $kategoriId = null;
            $kategoriNama = $kategoriStr;

            if (!empty($kategoriStr)) {
                // Cari dengan exact match dulu
                $kategori = DB::table('kategori_kriteria')
                    ->where('nama_kriteria', $kategoriStr)
                    ->where('tipe', 'Keluar')
                    ->first();

                // Kalau tidak ada, cari dengan LIKE
                if (!$kategori) {
                    $kategori = DB::table('kategori_kriteria')
                        ->whereRaw('LOWER(TRIM(nama_kriteria)) LIKE ?', ['%' . strtolower($kategoriStr) . '%'])
                        ->where('tipe', 'Keluar')
                        ->first();
                }

                if ($kategori) {
                    $kategoriId = $kategori->id_kategori_kriteria;
                    $kategoriNama = $kategori->nama_kriteria;
                }

                \Log::info('Kategori:', [
                    'search' => $kategoriStr,
                    'found' => $kategori ? 'YA' : 'TIDAK',
                    'id' => $kategoriId,
                    'nama' => $kategoriNama
                ]);
            }

            // ========================================
            // STEP 4: CARI SUB KRITERIA (JENIS DOKUMEN)
            // ========================================
            $subKriteriaId = null;
            $subKriteriaNama = $jenisDokumenStr;

            if (!empty($jenisDokumenStr)) {
                // Cari dengan exact match
                $subKriteria = DB::table('sub_kriteria')
                    ->where('nama_sub_kriteria', $jenisDokumenStr);

                // Filter by kategori jika ada
                if ($kategoriId) {
                    $subKriteria->where('id_kategori_kriteria', $kategoriId);
                }

                $subKriteria = $subKriteria->first();

                // Kalau tidak ada, cari dengan LIKE
                if (!$subKriteria) {
                    $subKriteria = DB::table('sub_kriteria')
                        ->whereRaw('LOWER(TRIM(nama_sub_kriteria)) LIKE ?', ['%' . strtolower($jenisDokumenStr) . '%']);

                    if ($kategoriId) {
                        $subKriteria->where('id_kategori_kriteria', $kategoriId);
                    }

                    $subKriteria = $subKriteria->first();
                }

                if ($subKriteria) {
                    $subKriteriaId = $subKriteria->id_sub_kriteria;
                    $subKriteriaNama = $subKriteria->nama_sub_kriteria;

                    // Update kategori dari relasi jika belum ada
                    if (!$kategoriId && $subKriteria->id_kategori_kriteria) {
                        $kategoriId = $subKriteria->id_kategori_kriteria;
                        $kat = DB::table('kategori_kriteria')
                            ->where('id_kategori_kriteria', $kategoriId)
                            ->first();
                        if ($kat) {
                            $kategoriNama = $kat->nama_kriteria;
                        }
                    }
                }

                \Log::info('Sub Kriteria (Jenis Dokumen):', [
                    'search' => $jenisDokumenStr,
                    'found' => $subKriteria ? 'YA' : 'TIDAK',
                    'id' => $subKriteriaId,
                    'nama' => $subKriteriaNama,
                    'kategori_id_from_relation' => $subKriteria->id_kategori_kriteria ?? null
                ]);
            }

            // ========================================
            // STEP 5: CARI ITEM SUB KRITERIA (JENIS SUB PEKERJAAN)
            // ========================================
            $itemSubKriteriaId = null;
            $itemSubKriteriaNama = $jenisSubPekerjaanStr;
            if (!$itemSubKriteriaId && $subKriteriaId) {
                // Jika jenis_sub_pekerjaan NULL, ambil item pertama dari sub_kriteria
                $defaultItem = DB::table('item_sub_kriteria')
                    ->where('id_sub_kriteria', $subKriteriaId)
                    ->first();

                if ($defaultItem) {
                    $itemSubKriteriaId = $defaultItem->id_item_sub_kriteria;
                    $itemSubKriteriaNama = $defaultItem->nama_item_sub_kriteria;

                    \Log::info('Menggunakan default item sub kriteria:', [
                        'id' => $itemSubKriteriaId,
                        'nama' => $itemSubKriteriaNama
                    ]);
                }
            }
            if (!empty($jenisSubPekerjaanStr)) {
                // Cari dengan exact match
                $itemSubKriteria = DB::table('item_sub_kriteria')
                    ->where('nama_item_sub_kriteria', $jenisSubPekerjaanStr);

                // Filter by sub_kriteria jika ada
                if ($subKriteriaId) {
                    $itemSubKriteria->where('id_sub_kriteria', $subKriteriaId);
                }

                $itemSubKriteria = $itemSubKriteria->first();

                // Kalau tidak ada, cari dengan LIKE
                if (!$itemSubKriteria) {
                    $itemSubKriteria = DB::table('item_sub_kriteria')
                        ->whereRaw('LOWER(TRIM(nama_item_sub_kriteria)) LIKE ?', ['%' . strtolower($jenisSubPekerjaanStr) . '%']);

                    if ($subKriteriaId) {
                        $itemSubKriteria->where('id_sub_kriteria', $subKriteriaId);
                    }

                    $itemSubKriteria = $itemSubKriteria->first();
                }

                if ($itemSubKriteria) {
                    $itemSubKriteriaId = $itemSubKriteria->id_item_sub_kriteria;
                    $itemSubKriteriaNama = $itemSubKriteria->nama_item_sub_kriteria;

                    // Update sub_kriteria dari relasi jika belum ada
                    if (!$subKriteriaId && $itemSubKriteria->id_sub_kriteria) {
                        $subKriteriaId = $itemSubKriteria->id_sub_kriteria;
                        $sub = DB::table('sub_kriteria')
                            ->where('id_sub_kriteria', $subKriteriaId)
                            ->first();
                        if ($sub) {
                            $subKriteriaNama = $sub->nama_sub_kriteria;

                            // Update kategori juga
                            if (!$kategoriId && $sub->id_kategori_kriteria) {
                                $kategoriId = $sub->id_kategori_kriteria;
                                $kat = DB::table('kategori_kriteria')
                                    ->where('id_kategori_kriteria', $kategoriId)
                                    ->first();
                                if ($kat) {
                                    $kategoriNama = $kat->nama_kriteria;
                                }
                            }
                        }
                    }
                }

                \Log::info('Item Sub Kriteria (Jenis Sub Pekerjaan):', [
                    'search' => $jenisSubPekerjaanStr,
                    'found' => $itemSubKriteria ? 'YA' : 'TIDAK',
                    'id' => $itemSubKriteriaId,
                    'nama' => $itemSubKriteriaNama,
                    'sub_kriteria_id_from_relation' => $itemSubKriteria->id_sub_kriteria ?? null
                ]);
            }

            // ========================================
            // STEP 6: SIAPKAN RESPONSE
            // ========================================
            $response = [
                'uraian' => $dokumen->uraian,
                'nilai_rupiah' => $dokumen->nilai_rupiah,
                'penerima' => $dokumen->penerima,
                'pembayaran' => $jenisPembayaranStr,

                // ID untuk dropdown
                'kategori_id' => $kategoriId,
                'sub_kriteria_id' => $subKriteriaId,
                'item_sub_kriteria_id' => $itemSubKriteriaId,
                'jenis_pembayaran_id' => $jenisPembayaranId,

                // Nama untuk display
                'kategori_nama' => $kategoriNama,
                'sub_kriteria_nama' => $subKriteriaNama,
                'item_sub_kriteria_nama' => $itemSubKriteriaNama,
                'jenis_pembayaran_nama' => $jenisPembayaranStr,
            ];

            \Log::info('=== RESPONSE FINAL ===', $response);

            return response()->json([
                'success' => true,
                'data' => $response
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getDokumenDetail: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function previewAgenda(Request $request)
    {
        $validated = $request->validate([
            'agenda_numbers' => 'required|string',
        ]);

        $agendaNumbers = $this->parseAgendaNumbers($validated['agenda_numbers']);

        if (empty($agendaNumbers)) {
            return response()->json([
                'message' => 'Masukkan minimal satu nomor agenda.',
            ], 422);
        }

        $dokumenList = DB::connection('mysql_agenda_online')
            ->table('dokumens')
            ->select(
                'id as dokumen_id',
                'nomor_agenda',
                'uraian_spp as uraian',
                'nilai_rupiah',
                'dibayar_kepada as penerima',
                'jenis_pembayaran',
                'kategori',
                'jenis_dokumen',
                'jenis_sub_pekerjaan',
                'tanggal_dibayar'
            )
            ->whereIn('nomor_agenda', $agendaNumbers)
            ->get()
            ->keyBy(fn($row) => trim((string) $row->nomor_agenda));

        $bankOptions = BankTujuan::orderBy('nama_tujuan')
            ->get(['id_bank_tujuan', 'nama_tujuan'])
            ->map(fn($bank) => [
                'id' => $bank->id_bank_tujuan,
                'text' => $bank->nama_tujuan,
            ])
            ->values();

        $rows = [];
        $warnings = 0;

        foreach ($agendaNumbers as $index => $agendaNumber) {
            $dokumen = $dokumenList->get($agendaNumber);

            if (!$dokumen) {
                $warnings++;
                $rows[] = [
                    'no' => $index + 1,
                    'agenda' => $agendaNumber,
                    'dokumen_id' => null,
                    'tanggal' => null,
                    'bank_tujuan_id' => null,
                    'bank_tujuan' => '-',
                    'kategori' => '-',
                    'sub_kriteria' => '-',
                    'item_sub_kriteria' => '-',
                    'jenis_pembayaran' => '-',
                    'penerima' => '-',
                    'uraian' => 'Dokumen tidak ditemukan di Agenda Online',
                    'kredit' => '0',
                    'warning' => true,
                    'warning_message' => 'Dokumen tidak ditemukan',
                    'can_save' => false,
                ];
                continue;
            }

            $mapped = $this->mapDokumenAgendaForBankKeluar($dokumen);
            $bankTujuan = $this->detectBankTujuanFromUraian($dokumen->uraian, $bankOptions);
            $tanggal = $this->normalizeAgendaDate($dokumen->tanggal_dibayar ?? null);
            $nilai = $this->parseCurrencyValue($dokumen->nilai_rupiah ?? 0);
            $hasWarning = !$bankTujuan;

            if ($hasWarning) {
                $warnings++;
            }

            $rows[] = [
                'no' => $index + 1,
                'agenda' => $dokumen->nomor_agenda ?: $agendaNumber,
                'dokumen_id' => $dokumen->dokumen_id,
                'tanggal' => $tanggal,
                'bank_tujuan_id' => $bankTujuan['id'] ?? null,
                'bank_tujuan' => $bankTujuan['text'] ?? '-',
                'kategori_id' => $mapped['kategori_id'],
                'kategori' => $mapped['kategori_nama'] ?: '-',
                'sub_kriteria_id' => $mapped['sub_kriteria_id'],
                'sub_kriteria' => $mapped['sub_kriteria_nama'] ?: '-',
                'item_sub_kriteria_id' => $mapped['item_sub_kriteria_id'],
                'item_sub_kriteria' => $mapped['item_sub_kriteria_nama'] ?: '-',
                'jenis_pembayaran_id' => $mapped['jenis_pembayaran_id'],
                'jenis_pembayaran' => $mapped['jenis_pembayaran_nama'] ?: '-',
                'penerima' => $dokumen->penerima ?: '-',
                'uraian' => $dokumen->uraian ?: '-',
                'kredit_raw' => $nilai,
                'kredit' => number_format($nilai, 0, ',', '.'),
                'warning' => $hasWarning,
                'warning_message' => $hasWarning ? 'Nomor VA di awal uraian tidak cocok dengan master Bank Tujuan' : '',
                'can_save' => true,
            ];
        }

        session(['bank_keluar_agenda_preview_rows' => $rows]);

        return response()->json([
            'rows' => $rows,
            'bank_options' => $bankOptions,
            'total' => count($rows),
            'savable' => collect($rows)->where('can_save', true)->count(),
            'warnings' => $warnings,
        ]);
    }

    public function confirmAgenda(Request $request)
    {
        $rows = session('bank_keluar_agenda_preview_rows', []);

        if (empty($rows)) {
            return response()->json([
                'error' => 'Data preview tidak ditemukan. Silakan tekan Preview Dokumen ulang.',
            ], 422);
        }

        $bankOverrides = (array) $request->input('bank_tujuan', []);
        $saved = 0;

        DB::beginTransaction();

        try {
            foreach ($rows as $index => $row) {
                if (empty($row['can_save'])) {
                    continue;
                }

                $bankTujuanId = $bankOverrides[$index] ?? $row['bank_tujuan_id'] ?? null;
                $bankTujuanId = $bankTujuanId !== '' ? $bankTujuanId : null;
                $kredit = $this->parseCurrencyValue($row['kredit_raw'] ?? $row['kredit'] ?? 0);

                BankKeluar::create([
                    'dokumen_id' => $row['dokumen_id'] ?? null,
                    'no_agenda' => null,
                    'agenda_tahun' => $row['agenda'] ?? null,
                    'id_sumber_dana' => null,
                    'id_bank_tujuan' => $bankTujuanId,
                    'id_kategori_kriteria' => $row['kategori_id'] ?? null,
                    'id_sub_kriteria' => $row['sub_kriteria_id'] ?? null,
                    'id_item_sub_kriteria' => $row['item_sub_kriteria_id'] ?? null,
                    'uraian' => $row['uraian'] ?? null,
                    'nilai_rupiah' => $kredit,
                    'penerima' => ($row['penerima'] ?? '-') !== '-' ? $row['penerima'] : null,
                    'tanggal' => $row['tanggal'] ?? now()->toDateString(),
                    'id_jenis_pembayaran' => $row['jenis_pembayaran_id'] ?? null,
                    'debet' => 0,
                    'kredit' => $kredit,
                    'keterangan' => 'Tarik otomatis dari nomor agenda',
                ]);

                $saved++;

                if (!empty($row['dokumen_id'])) {
                    try {
                        DB::connection('mysql_agenda_online')
                            ->table('dokumens')
                            ->where('id', $row['dokumen_id'])
                            ->update([
                                'status_pembayaran' => 'sudah_dibayar',
                                'dibayar' => $kredit,
                                'tanggal_dibayar' => $row['tanggal'] ?? now()->toDateString(),
                            ]);
                    } catch (\Throwable $e) {
                        \Log::warning('[CBSync] Gagal update status dokumen Agenda Online dari preview agenda.', [
                            'dokumen_id' => $row['dokumen_id'],
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            DB::commit();
            session()->forget('bank_keluar_agenda_preview_rows');

            return response()->json([
                'success' => "{$saved} data Bank Keluar berhasil disimpan.",
                'total' => $saved,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Confirm agenda bank keluar failed: ' . $e->getMessage());

            return response()->json([
                'error' => 'Gagal menyimpan data: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function parseAgendaNumbers(string $input): array
    {
        $parts = preg_split('/[\r\n,;]+/', $input);

        return collect($parts)
            ->map(fn($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function mapDokumenAgendaForBankKeluar($dokumen): array
    {
        $jenisPembayaranStr = trim($dokumen->jenis_pembayaran ?? '');
        $kategoriStr = trim($dokumen->kategori ?? '');
        $jenisDokumenStr = trim($dokumen->jenis_dokumen ?? '');
        $jenisSubPekerjaanStr = trim($dokumen->jenis_sub_pekerjaan ?? '');

        $jenisPembayaran = $jenisPembayaranStr !== ''
            ? DB::table('jenis_pembayarans')->whereRaw('LOWER(TRIM(nama_jenis_pembayaran)) = ?', [strtolower($jenisPembayaranStr)])->first()
            : null;

        $kategori = null;
        if ($kategoriStr !== '') {
            $kategori = DB::table('kategori_kriteria')
                ->where('tipe', 'Keluar')
                ->whereRaw('LOWER(TRIM(nama_kriteria)) = ?', [strtolower($kategoriStr)])
                ->first();

            if (!$kategori) {
                $kategori = DB::table('kategori_kriteria')
                    ->where('tipe', 'Keluar')
                    ->whereRaw('LOWER(TRIM(nama_kriteria)) LIKE ?', ['%' . strtolower($kategoriStr) . '%'])
                    ->first();
            }
        }

        $subKriteria = null;
        if ($jenisDokumenStr !== '') {
            $subQuery = DB::table('sub_kriteria')
                ->whereRaw('LOWER(TRIM(nama_sub_kriteria)) = ?', [strtolower($jenisDokumenStr)]);

            if ($kategori) {
                $subQuery->where('id_kategori_kriteria', $kategori->id_kategori_kriteria);
            }

            $subKriteria = $subQuery->first();

            if (!$subKriteria) {
                $subQuery = DB::table('sub_kriteria')
                    ->whereRaw('LOWER(TRIM(nama_sub_kriteria)) LIKE ?', ['%' . strtolower($jenisDokumenStr) . '%']);

                if ($kategori) {
                    $subQuery->where('id_kategori_kriteria', $kategori->id_kategori_kriteria);
                }

                $subKriteria = $subQuery->first();
            }
        }

        if (!$kategori && $subKriteria && $subKriteria->id_kategori_kriteria) {
            $kategori = DB::table('kategori_kriteria')->where('id_kategori_kriteria', $subKriteria->id_kategori_kriteria)->first();
        }

        $itemSubKriteria = null;
        if ($jenisSubPekerjaanStr !== '') {
            $itemQuery = DB::table('item_sub_kriteria')
                ->whereRaw('LOWER(TRIM(nama_item_sub_kriteria)) = ?', [strtolower($jenisSubPekerjaanStr)]);

            if ($subKriteria) {
                $itemQuery->where('id_sub_kriteria', $subKriteria->id_sub_kriteria);
            }

            $itemSubKriteria = $itemQuery->first();

            if (!$itemSubKriteria) {
                $itemQuery = DB::table('item_sub_kriteria')
                    ->whereRaw('LOWER(TRIM(nama_item_sub_kriteria)) LIKE ?', ['%' . strtolower($jenisSubPekerjaanStr) . '%']);

                if ($subKriteria) {
                    $itemQuery->where('id_sub_kriteria', $subKriteria->id_sub_kriteria);
                }

                $itemSubKriteria = $itemQuery->first();
            }
        }

        if (!$itemSubKriteria && $subKriteria) {
            $itemSubKriteria = DB::table('item_sub_kriteria')
                ->where('id_sub_kriteria', $subKriteria->id_sub_kriteria)
                ->orderBy('id_item_sub_kriteria')
                ->first();
        }

        return [
            'kategori_id' => $kategori->id_kategori_kriteria ?? null,
            'kategori_nama' => $kategori->nama_kriteria ?? $kategoriStr,
            'sub_kriteria_id' => $subKriteria->id_sub_kriteria ?? null,
            'sub_kriteria_nama' => $subKriteria->nama_sub_kriteria ?? $jenisDokumenStr,
            'item_sub_kriteria_id' => $itemSubKriteria->id_item_sub_kriteria ?? null,
            'item_sub_kriteria_nama' => $itemSubKriteria->nama_item_sub_kriteria ?? $jenisSubPekerjaanStr,
            'jenis_pembayaran_id' => $jenisPembayaran->id_jenis_pembayaran ?? null,
            'jenis_pembayaran_nama' => $jenisPembayaran->nama_jenis_pembayaran ?? $jenisPembayaranStr,
        ];
    }

    private function detectBankTujuanFromUraian(?string $uraian, $bankOptions): ?array
    {
        if (!$uraian || !preg_match('/^\s*(\d{6,20})(?=\D|$)/', $uraian, $matches)) {
            return null;
        }

        $vaNumber = $matches[1];

        foreach ($bankOptions as $bank) {
            if (preg_match('/^\s*' . preg_quote($vaNumber, '/') . '(?=\D|$)/', $bank['text'])) {
                return $bank;
            }
        }

        return null;
    }

    private function normalizeAgendaDate($date): string
    {
        if (!$date) {
            return now()->toDateString();
        }

        try {
            return Carbon::parse($date)->toDateString();
        } catch (\Throwable $e) {
            return now()->toDateString();
        }
    }

    private function parseCurrencyValue($value): float
    {
        if ($value === null || $value === '') {
            return 0;
        }

        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        $cleaned = preg_replace('/[^\d.,-]/', '', (string) $value);

        if ($cleaned === '') {
            return 0;
        }

        $hasComma = str_contains($cleaned, ',');
        $dotCount = substr_count($cleaned, '.');

        if ($hasComma) {
            $cleaned = str_replace('.', '', $cleaned);
            $cleaned = str_replace(',', '.', $cleaned);
        } elseif ($dotCount > 1) {
            $cleaned = str_replace('.', '', $cleaned);
        }

        return (float) $cleaned;
    }

    // public function dashboard()
    // {
    //     $total_pengeluaran =  BankKeluar::select(
    //         DB::raw("SUM(kredit) as total")
    //     )
    //     ->groupBy(DB::raw("MONTH(tanggal)"))
    //     ->pluck('total');

    //     $bulan = BankKeluar::select(
    //         DB::raw("MONTHNAME(tanggal) as bulan")
    //     )
    //     ->groupBy(DB::raw("MONTHNAME(tanggal)"))
    //     ->pluck('bulan');

    //     $tahun = BankKeluar::select(
    //         DB::raw("YEAR(tanggal) as tahun")
    //     )
    //     ->groupBy(DB::raw("YEAR(tanggal)"))
    //     ->pluck('tahun');

    //     return view('cash_bank.dashboard', compact('total_pengeluaran', 'bulan','tahun'));
    // }



    public function report(Request $request)
    {
        /* ================= AMBIL SEMUA REQUEST FILTER ================= */
        $tahun = $request->filled('tahun') ? $request->tahun : now()->year;
        $bulan = $request->filled('bulan') ? $request->bulan : null;
        $tglDari = $request->tgl_dari;
        $tglSampai = $request->tgl_sampai;
        $tanggalDipilih = array_values(array_filter((array) $request->input('tanggal', []), fn($value) => $value !== null && $value !== ''));
        $bankTujuanId = $request->filled('bank_tujuan') ? $request->bank_tujuan : null;
        $sumberDanaIds = array_values(array_filter((array) $request->input('sumber_dana', []), fn($value) => $value !== null && $value !== ''));
        $kategoriIds = array_values(array_filter((array) $request->input('kategori', []), fn($value) => $value !== null && $value !== ''));
        $rekapanVA = $request->rekapanVA;
        $idJenisPembayaran = $request->filled('id_jenis_pembayaran') ? $request->id_jenis_pembayaran : null;
        $perPageInput = $request->input('per_page', '10');
        $perPage = in_array((int) $perPageInput, [10, 25, 50, 100], true) ? (int) $perPageInput : 10;

        /* ================= HITUNG JUMLAH FILTER AKTIF ================= */
        $activeFilters = [];
        $timeFilters = [];

        if ($tahun)
            $timeFilters[] = 'tahun';
        if ($bulan)
            $timeFilters[] = 'bulan';
        if (count($tanggalDipilih) > 0)
            $timeFilters[] = 'tanggal';

        if ($bankTujuanId)
            $activeFilters[] = 'bank_tujuan';
        if ($sumberDanaIds && count($sumberDanaIds) > 0)
            $activeFilters[] = 'sumber_dana';
        if ($kategoriIds && count($kategoriIds) > 0)
            $activeFilters[] = 'kategori';
        if ($idJenisPembayaran)
            $activeFilters[] = 'jenis_pembayaran';
        if ($rekapanVA)
            $activeFilters[] = 'rekapan';

        $countActiveFilters = count($activeFilters);

        /* ================= FILTER TANGGAL (CLOSURE) ================= */
        $filterTanggal = function ($q) use ($tahun, $bulan, $tanggalDipilih, $tglDari, $tglSampai) {
            if (!empty($tanggalDipilih)) {
                $q->whereIn(DB::raw('DATE(tanggal)'), $tanggalDipilih);
                return;
            }

            if ($tahun) {
                $q->whereYear('tanggal', $tahun);
            }

            if ($bulan) {
                $q->whereMonth('tanggal', $bulan);
            }

            if ($tglDari) {
                $q->whereDate('tanggal', '>=', $tglDari);
            }

            if ($tglSampai) {
                $q->whereDate('tanggal', '<=', $tglSampai);
            }
        };

        /* ================= APPLY FILTER PROGRESIF ================= */
        $applyFilter = function ($q, $table = null) use ($filterTanggal, $bankTujuanId, $sumberDanaIds, $kategoriIds, $idJenisPembayaran, ) {
            $prefix = $table ? $table . '.' : '';

            $filterTanggal($q);

            if ($bankTujuanId) {
                $q->where($prefix . 'id_bank_tujuan', $bankTujuanId);
            }

            if ($sumberDanaIds && is_array($sumberDanaIds) && count($sumberDanaIds) > 0) {
                $q->whereIn($prefix . 'id_sumber_dana', $sumberDanaIds);
            }

            if ($kategoriIds && is_array($kategoriIds) && count($kategoriIds) > 0) {
                $q->whereIn($prefix . 'id_kategori_kriteria', $kategoriIds);
            }

            if ($idJenisPembayaran) {
                $q->where($prefix . 'id_jenis_pembayaran', $idJenisPembayaran);
            }
        };

        /* ================= FILTER KHUSUS UNTUK SALDO AWAL ================= */
        // Filter untuk hitung saldo awal (hanya filter waktu, bank, dan sumber dana)
        $applyFilterSaldoAwal = function ($q, $table = null) use ($filterTanggal, $bankTujuanId, $sumberDanaIds, ) {
            $prefix = $table ? $table . '.' : '';

            $filterTanggal($q);

            if ($bankTujuanId) {
                $q->where($prefix . 'id_bank_tujuan', $bankTujuanId);
            }

            if ($sumberDanaIds && is_array($sumberDanaIds) && count($sumberDanaIds) > 0) {
                $q->whereIn($prefix . 'id_sumber_dana', $sumberDanaIds);
            }
        };

        /* ================= DROPDOWN LISTS ================= */
        $tahunList = collect()
            ->merge(DB::table('bank_masuk')->selectRaw('YEAR(tanggal) as tahun')->pluck('tahun'))
            ->merge(DB::table('bank_keluars')->selectRaw('YEAR(tanggal) as tahun')->pluck('tahun'))
            ->unique()->sortDesc()->values();

        $bulanList = collect()
            ->merge(
                DB::table('bank_masuk')
                    ->when($tahun, fn($q) => $q->whereYear('tanggal', $tahun))
                    ->selectRaw('MONTH(tanggal) as bulan')
                    ->pluck('bulan')
            )
            ->merge(
                DB::table('bank_keluars')
                    ->when($tahun, fn($q) => $q->whereYear('tanggal', $tahun))
                    ->selectRaw('MONTH(tanggal) as bulan')
                    ->pluck('bulan')
            )
            ->unique()->sort()->values();

        $tanggalList = collect()
            ->merge(
                DB::table('bank_masuk')
                    ->when($tahun, fn($q) => $q->whereYear('tanggal', $tahun))
                    ->when($bulan, fn($q) => $q->whereMonth('tanggal', $bulan))
                    ->selectRaw('DATE(tanggal) as tanggal')
                    ->pluck('tanggal')
            )
            ->merge(
                DB::table('bank_keluars')
                    ->when($tahun, fn($q) => $q->whereYear('tanggal', $tahun))
                    ->when($bulan, fn($q) => $q->whereMonth('tanggal', $bulan))
                    ->selectRaw('DATE(tanggal) as tanggal')
                    ->pluck('tanggal')
            )
            ->unique()->sort()->values();

        $bankTujuanList = DB::table('bank_tujuan')
            ->where(function ($query) use ($filterTanggal, $sumberDanaIds, $kategoriIds, $idJenisPembayaran) {
                $query->whereExists(function ($sub) use ($filterTanggal, $sumberDanaIds, $kategoriIds, $idJenisPembayaran) {
                    $sub->select(DB::raw(1))
                        ->from('bank_keluars')
                        ->whereColumn('bank_keluars.id_bank_tujuan', 'bank_tujuan.id_bank_tujuan')
                        ->where(function ($q) use ($filterTanggal, $sumberDanaIds, $kategoriIds, $idJenisPembayaran) {
                            $filterTanggal($q);
                            if ($sumberDanaIds && count($sumberDanaIds) > 0) {
                                $q->whereIn('id_sumber_dana', $sumberDanaIds);
                            }
                            if ($kategoriIds && count($kategoriIds) > 0) {
                                $q->whereIn('id_kategori_kriteria', $kategoriIds);
                            }
                            if ($idJenisPembayaran) {
                                $q->where('id_jenis_pembayaran', $idJenisPembayaran);
                            }
                        });
                })
                    ->orWhereExists(function ($sub) use ($filterTanggal, $sumberDanaIds) {
                        $sub->select(DB::raw(1))
                            ->from('bank_masuk')
                            ->whereColumn('bank_masuk.id_bank_tujuan', 'bank_tujuan.id_bank_tujuan')
                            ->where(function ($q) use ($filterTanggal, $sumberDanaIds) {
                                $filterTanggal($q);
                                if ($sumberDanaIds && count($sumberDanaIds) > 0) {
                                    $q->whereIn('id_sumber_dana', $sumberDanaIds);
                                }
                            });
                    });
            })
            ->orderBy('nama_tujuan')
            ->get();

        $sumberDanaList = DB::table('sumber_dana')
            ->where(function ($query) use ($filterTanggal, $bankTujuanId, $kategoriIds, $idJenisPembayaran) {
                $query->whereExists(function ($sub) use ($filterTanggal, $bankTujuanId, $kategoriIds, $idJenisPembayaran) {
                    $sub->select(DB::raw(1))
                        ->from('bank_keluars')
                        ->whereColumn('bank_keluars.id_sumber_dana', 'sumber_dana.id_sumber_dana')
                        ->where(function ($q) use ($filterTanggal, $bankTujuanId, $kategoriIds, $idJenisPembayaran) {
                            $filterTanggal($q);
                            if ($bankTujuanId)
                                $q->where('id_bank_tujuan', $bankTujuanId);
                            if ($kategoriIds && count($kategoriIds) > 0) {
                                $q->whereIn('id_kategori_kriteria', $kategoriIds);
                            }
                            if ($idJenisPembayaran)
                                $q->where('id_jenis_pembayaran', $idJenisPembayaran);
                        });
                })
                    ->orWhereExists(function ($sub) use ($filterTanggal, $bankTujuanId) {
                        $sub->select(DB::raw(1))
                            ->from('bank_masuk')
                            ->whereColumn('bank_masuk.id_sumber_dana', 'sumber_dana.id_sumber_dana')
                            ->where(function ($q) use ($filterTanggal, $bankTujuanId) {
                                $filterTanggal($q);
                                if ($bankTujuanId)
                                    $q->where('id_bank_tujuan', $bankTujuanId);
                            });
                    });
            })
            ->orderBy('nama_sumber_dana')
            ->get();

        $kategoriList = DB::table('kategori_kriteria')
            ->whereExists(function ($sub) use ($filterTanggal, $bankTujuanId, $sumberDanaIds, $idJenisPembayaran) {
                $sub->select(DB::raw(1))
                    ->from('bank_keluars')
                    ->whereColumn('bank_keluars.id_kategori_kriteria', 'kategori_kriteria.id_kategori_kriteria')
                    ->where(function ($q) use ($filterTanggal, $bankTujuanId, $sumberDanaIds, $idJenisPembayaran) {
                        $filterTanggal($q);
                        if ($bankTujuanId)
                            $q->where('id_bank_tujuan', $bankTujuanId);
                        if ($sumberDanaIds && count($sumberDanaIds) > 0) {
                            $q->whereIn('id_sumber_dana', $sumberDanaIds);
                        }
                        if ($idJenisPembayaran)
                            $q->where('id_jenis_pembayaran', $idJenisPembayaran);
                    });
            })
            ->orderBy('nama_kriteria')
            ->get();

        // $jenisPembayaranList = DB::table('jenis_pembayarans')
        //     ->whereExists(function($sub) use ($filterTanggal, $bankTujuanId, $sumberDanaIds, $kategoriIds) {
        //         $sub->select(DB::raw(1))
        //             ->from('bank_keluars')
        //             ->whereColumn('bank_keluars.id_jenis_pembayaran', 'jenis_pembayarans.id_jenis_pembayaran')
        //             ->where(function($q) use ($filterTanggal, $bankTujuanId, $sumberDanaIds, $kategoriIds) {
        //                 $filterTanggal($q);
        //                 if ($bankTujuanId) $q->where('id_bank_tujuan', $bankTujuanId);
        //                 if ($sumberDanaIds && count($sumberDanaIds) > 0) {
        //                     $q->whereIn('id_sumber_dana', $sumberDanaIds);
        //                 }
        //                 if ($kategoriIds && count($kategoriIds) > 0) {
        //                     $q->whereIn('id_kategori_kriteria', $kategoriIds);
        //                 }
        //             });
        //     })
        //     ->orderBy('nama_jenis_pembayaran')
        //     ->get();
        $jenisPembayaranList = DB::table('jenis_pembayarans')
            ->whereExists(function ($sub) use ($filterTanggal, $bankTujuanId, $sumberDanaIds, $kategoriIds, $idJenisPembayaran, ) {
                $sub->select(DB::raw(1))
                    ->from('bank_keluars')
                    ->whereColumn(
                        'bank_keluars.id_jenis_pembayaran',
                        'jenis_pembayarans.id_jenis_pembayaran'
                    )
                    ->where(function ($q) use ($filterTanggal, $bankTujuanId, $sumberDanaIds, $kategoriIds) {

                        // filter waktu
                        $filterTanggal($q);

                        // filter lain
                        if ($bankTujuanId) {
                            $q->where('id_bank_tujuan', $bankTujuanId);
                        }

                        if (!empty($sumberDanaIds)) {
                            $q->whereIn('id_sumber_dana', $sumberDanaIds);
                        }

                        if (!empty($kategoriIds)) {
                            $q->whereIn('id_kategori_kriteria', $kategoriIds);
                        }
                    });
            })
            ->orderBy('nama_jenis_pembayaran')
            ->get();


        /* ================= LOGIKA TAMPILAN DATA ================= */
        $showDebet = false;
        $showSaldoAkhir = false;
        $showSAP = false;
        $showKreditJenisPembayaran = ($countActiveFilters == 1 && $idJenisPembayaran);

        // LOGIKA BARU: 
        // 1 filter atau tanpa filter = tampil DEBET + KREDIT + SALDO AKHIR
        // 2+ filter = tampil KREDIT saja + TOTAL KREDIT

        if ($countActiveFilters == 0) {
            // Tidak ada filter (tampil semua)
            $showDebet = true;
            $showSaldoAkhir = true;
            $showSAP = true;
        } elseif ($showKreditJenisPembayaran) {

            $showDebet = false;
            $showSaldoAkhir = false;
            $showSAP = false;
        } elseif ($countActiveFilters == 1) {
            // 1 filter saja (bank_tujuan, sumber_dana, atau rekapan)
            $showDebet = true;
            $showSaldoAkhir = true;
            $showSAP = true;
        } else {
            // 2 atau lebih filter = hanya kredit
            $showDebet = false;
            $showSaldoAkhir = false;
            $showSAP = false;
        }

        /* ================= QUERY DATA UTAMA ================= */
        if ($showDebet) {
            // Tampilkan Bank Masuk (Debet) + Bank Keluar (Kredit)
            $bankMasuk = DB::table('bank_masuk')
                ->leftJoin('sumber_dana', 'sumber_dana.id_sumber_dana', '=', 'bank_masuk.id_sumber_dana')
                ->leftJoin('bank_tujuan', 'bank_tujuan.id_bank_tujuan', '=', 'bank_masuk.id_bank_tujuan')
                ->select(
                    'bank_masuk.agenda_tahun',
                    'bank_masuk.id_sumber_dana',
                    'sumber_dana.nama_sumber_dana',
                    'bank_masuk.id_bank_tujuan',
                    'bank_tujuan.nama_tujuan',
                    'bank_masuk.uraian',
                    'bank_masuk.penerima',
                    'bank_masuk.tanggal',
                    'bank_masuk.debet',
                    DB::raw('0 as kredit'),
                    'bank_masuk.no_sap',
                    DB::raw('NULL as no_agenda'),
                    DB::raw('NULL as nama_kriteria'),
                    DB::raw('NULL as nama_sub_kriteria'),
                    DB::raw('NULL as nama_item_sub_kriteria'),
                    DB::raw('NULL as id_jenis_pembayaran'),
                    DB::raw('NULL as nama_jenis_pembayaran'),
                    DB::raw("'MASUK' as jenis"),
                    DB::raw('bank_masuk.id_bank_masuk as urut_id'),
                    DB::raw('0 as jenis_sort')
                )
                ->where(function ($q) use ($applyFilterSaldoAwal) {
                    // Gunakan filter saldo awal (tanpa kategori/jenis pembayaran)
                    $applyFilterSaldoAwal($q, 'bank_masuk');
                });

            // ->when($idJenisPembayaran, function($q) {
            //     $q->whereRaw('1 = 0');
            // });

            $bankKeluar = DB::table('bank_keluars')
                ->leftJoin('sumber_dana', 'sumber_dana.id_sumber_dana', '=', 'bank_keluars.id_sumber_dana')
                ->leftJoin('bank_tujuan', 'bank_tujuan.id_bank_tujuan', '=', 'bank_keluars.id_bank_tujuan')
                ->leftJoin('kategori_kriteria', 'kategori_kriteria.id_kategori_kriteria', '=', 'bank_keluars.id_kategori_kriteria')
                ->leftJoin('sub_kriteria', 'sub_kriteria.id_sub_kriteria', '=', 'bank_keluars.id_sub_kriteria')
                ->leftJoin('item_sub_kriteria', 'item_sub_kriteria.id_item_sub_kriteria', '=', 'bank_keluars.id_item_sub_kriteria')
                ->leftJoin('jenis_pembayarans', 'jenis_pembayarans.id_jenis_pembayaran', '=', 'bank_keluars.id_jenis_pembayaran')
                ->select(
                    'bank_keluars.agenda_tahun',
                    'bank_keluars.id_sumber_dana',
                    'sumber_dana.nama_sumber_dana',
                    'bank_keluars.id_bank_tujuan',
                    'bank_tujuan.nama_tujuan',
                    'bank_keluars.uraian',
                    'bank_keluars.penerima',
                    'bank_keluars.tanggal',
                    DB::raw('0 as debet'),
                    'bank_keluars.kredit',
                    'bank_keluars.no_sap',
                    DB::raw('bank_keluars.agenda_tahun as no_agenda'),
                    'kategori_kriteria.nama_kriteria',
                    'sub_kriteria.nama_sub_kriteria',
                    'item_sub_kriteria.nama_item_sub_kriteria',
                    'bank_keluars.id_jenis_pembayaran',
                    'jenis_pembayarans.nama_jenis_pembayaran',
                    DB::raw("'KELUAR' as jenis"),
                    DB::raw('bank_keluars.id_bank_keluar as urut_id'),
                    DB::raw('1 as jenis_sort')
                )
                ->where(function ($q) use ($applyFilterSaldoAwal) {
                    // Gunakan filter saldo awal (tanpa kategori/jenis pembayaran)
                    $applyFilterSaldoAwal($q, 'bank_keluars');
                });

            $baseQuery = $bankMasuk->unionAll($bankKeluar);
        } else {
            // Hanya tampilkan Bank Keluar (Kredit) dengan filter lengkap
            $baseQuery = DB::table('bank_keluars')
                ->leftJoin('sumber_dana', 'sumber_dana.id_sumber_dana', '=', 'bank_keluars.id_sumber_dana')
                ->leftJoin('bank_tujuan', 'bank_tujuan.id_bank_tujuan', '=', 'bank_keluars.id_bank_tujuan')
                ->leftJoin('kategori_kriteria', 'kategori_kriteria.id_kategori_kriteria', '=', 'bank_keluars.id_kategori_kriteria')
                ->leftJoin('sub_kriteria', 'sub_kriteria.id_sub_kriteria', '=', 'bank_keluars.id_sub_kriteria')
                ->leftJoin('item_sub_kriteria', 'item_sub_kriteria.id_item_sub_kriteria', '=', 'bank_keluars.id_item_sub_kriteria')
                ->leftJoin('jenis_pembayarans', 'jenis_pembayarans.id_jenis_pembayaran', '=', 'bank_keluars.id_jenis_pembayaran')
                ->select(
                    'bank_keluars.id_sumber_dana',
                    'sumber_dana.nama_sumber_dana',
                    'bank_keluars.id_bank_tujuan',
                    'bank_tujuan.nama_tujuan',
                    'bank_keluars.uraian',
                    'bank_keluars.penerima',
                    'bank_keluars.tanggal',
                    DB::raw('0 as debet'),
                    'bank_keluars.kredit',
                    'bank_keluars.no_sap',
                    DB::raw('bank_keluars.agenda_tahun as no_agenda'),
                    'kategori_kriteria.nama_kriteria',
                    'sub_kriteria.nama_sub_kriteria',
                    'item_sub_kriteria.nama_item_sub_kriteria',
                    'bank_keluars.id_jenis_pembayaran',
                    'jenis_pembayarans.nama_jenis_pembayaran',
                    DB::raw("'KELUAR' as jenis"),
                    DB::raw('bank_keluars.id_bank_keluar as urut_id'),
                    DB::raw('1 as jenis_sort')
                )
                ->where(function ($q) use ($applyFilter) {
                    // Gunakan filter lengkap (dengan kategori/jenis pembayaran)
                    $applyFilter($q, 'bank_keluars');
                });
        }

        /* ================= PAGINASI DATA UTAMA DI DATABASE ================= */
        $reportQuery = DB::query()->fromSub($baseQuery, 'trx');
        $totalEntries = (clone $reportQuery)->count();
        $totalDebet = (clone $reportQuery)->sum('debet');
        $totalKredit = (clone $reportQuery)->sum('kredit');
        $page = max(1, (int) $request->input('page', 1));

        $lastPage = max(1, (int) ceil($totalEntries / $perPage));
        $page = min($page, $lastPage);

        $pageRows = (clone $reportQuery)
            ->orderBy('tanggal')
            ->orderBy('urut_id')
            ->orderBy('jenis_sort')
            ->forPage($page, $perPage)
            ->get();

        $data = new LengthAwarePaginator(
            $pageRows,
            $totalEntries,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->except('page'),
            ]
        );

        /* ================= HITUNG SALDO BERJALAN / TOTAL KREDIT ================= */
        $finalSaldo = null;

        if ($showSaldoAkhir) {
            $finalSaldo = $totalDebet - $totalKredit;
            $saldo = 0;

            if ($data->count() > 0) {
                $firstRow = $data->first();
                $saldo = (clone $reportQuery)
                    ->where(function ($q) use ($firstRow) {
                        $q->where('tanggal', '<', $firstRow->tanggal)
                            ->orWhere(function ($sameDate) use ($firstRow) {
                                $sameDate->where('tanggal', $firstRow->tanggal)
                                    ->where(function ($sameDateOrder) use ($firstRow) {
                                        $sameDateOrder->where('urut_id', '<', $firstRow->urut_id)
                                            ->orWhere(function ($sameId) use ($firstRow) {
                                                $sameId->where('urut_id', $firstRow->urut_id)
                                                    ->where('jenis_sort', '<', $firstRow->jenis_sort);
                                            });
                                    });
                            });
                    })
                    ->selectRaw('COALESCE(SUM(debet), 0) - COALESCE(SUM(kredit), 0) as saldo')
                    ->value('saldo') ?? 0;
            }

            foreach ($data as $d) {
                $saldo += ($d->debet ?? 0) - ($d->kredit ?? 0);
                $d->saldo_akhir = $saldo;
            }
        } else {
            // Mode: Hanya Kredit + Total Kredit
            foreach ($data as $d) {
                $d->saldo_akhir = null;
            }
        }

        /* ================= REKAPAN ================= */
        $rekapVA = [];

        if ($request->rekapanVA === 'bank' && $tahun) {
            foreach (BankTujuan::all() as $bank) {
                $debetTotal = DB::table('bank_masuk')
                    ->whereYear('tanggal', $tahun)
                    ->where('id_bank_tujuan', $bank->id_bank_tujuan)
                    ->when($sumberDanaIds && count($sumberDanaIds) > 0, function ($q) use ($sumberDanaIds) {
                        $q->whereIn('id_sumber_dana', $sumberDanaIds);
                    })
                    ->sum('debet');

                $kreditTotal = DB::table('bank_keluars')
                    ->whereYear('tanggal', $tahun)
                    ->where('id_bank_tujuan', $bank->id_bank_tujuan)
                    ->when($sumberDanaIds && count($sumberDanaIds) > 0, function ($q) use ($sumberDanaIds) {
                        $q->whereIn('id_sumber_dana', $sumberDanaIds);
                    })
                    ->sum('kredit');

                $saldo = $debetTotal - $kreditTotal;

                if ($saldo != 0 || $debetTotal != 0 || $kreditTotal != 0) {
                    $rekapVA[] = [
                        'bank' => $bank->nama_tujuan,
                        'saldo_va' => $saldo,
                        'saldo_sap' => 0,
                        'selisih' => $saldo,
                        'keterangan' => "Saldo akhir tahun {$tahun}"
                    ];
                }
            }
        }

        if ($request->rekapanVA === 'va' && $tahun) {
            foreach (SumberDana::all() as $sd) {
                $debetTotal = DB::table('bank_masuk')
                    ->whereYear('tanggal', $tahun)
                    ->where('id_sumber_dana', $sd->id_sumber_dana)
                    ->when($bankTujuanId, function ($q) use ($bankTujuanId) {
                        $q->where('id_bank_tujuan', $bankTujuanId);
                    })
                    ->sum('debet');

                $kreditTotal = DB::table('bank_keluars')
                    ->whereYear('tanggal', $tahun)
                    ->where('id_sumber_dana', $sd->id_sumber_dana)
                    ->when($bankTujuanId, function ($q) use ($bankTujuanId) {
                        $q->where('id_bank_tujuan', $bankTujuanId);
                    })
                    ->sum('kredit');

                $saldo = $debetTotal - $kreditTotal;

                if ($saldo != 0 || $debetTotal != 0 || $kreditTotal != 0) {
                    $rekapVA[] = [
                        'bank' => $sd->nama_sumber_dana,
                        'saldo_va' => $saldo,
                        'saldo_sap' => 0,
                        'selisih' => $saldo,
                        'keterangan' => "Saldo akhir tahun {$tahun}"
                    ];
                }
            }
        }

        // Rekap Kategori Full (dengan filter progresif)
        if ($rekapanVA === 'kategori-full') {
            $dataKategori = DB::table('bank_keluars')
                ->join('kategori_kriteria', 'kategori_kriteria.id_kategori_kriteria', '=', 'bank_keluars.id_kategori_kriteria')
                ->join('sub_kriteria', 'sub_kriteria.id_sub_kriteria', '=', 'bank_keluars.id_sub_kriteria')
                ->join('item_sub_kriteria', 'item_sub_kriteria.id_item_sub_kriteria', '=', 'bank_keluars.id_item_sub_kriteria')
                ->where(function ($q) use ($applyFilter) {
                    $applyFilter($q, 'bank_keluars');
                })
                ->select(
                    'kategori_kriteria.nama_kriteria as kategori',
                    'sub_kriteria.nama_sub_kriteria as sub',
                    'item_sub_kriteria.nama_item_sub_kriteria as item',
                    DB::raw('SUM(bank_keluars.kredit) as kredit')
                )
                ->groupBy('kategori', 'sub', 'item')
                ->orderBy('kategori')
                ->orderBy('sub')
                ->orderBy('item')
                ->get();

            foreach ($dataKategori as $row) {
                $rekapVA[$row->kategori][$row->sub][] = [
                    'item' => $row->item,
                    'kredit' => (float) $row->kredit
                ];
            }
        }


        return view('cash_bank.reportKeluar', compact(
            'data',
            'tahunList',
            'bulanList',
            'tanggalList',
            'bankTujuanList',
            'sumberDanaList',
            'kategoriList',
            'jenisPembayaranList',
            'showDebet',
            'showSaldoAkhir',
            'showSAP',
            'rekapVA',
            'totalDebet',
            'totalKredit',
            'finalSaldo',
            'tahun',
            'bulan',
            'tanggalDipilih',
            'bankTujuanId',
            'sumberDanaIds',
            'kategoriIds',
            'idJenisPembayaran',
            'rekapanVA',
            'countActiveFilters'
        ));
    }

    private function buildReportDataQuery(Request $request): array
    {
        $tahun = $request->filled('tahun') ? $request->tahun : now()->year;
        $bulan = $request->filled('bulan') ? $request->bulan : null;
        $tglDari = $request->tgl_dari;
        $tglSampai = $request->tgl_sampai;
        $tanggalDipilih = array_values(array_filter((array) $request->input('tanggal', []), fn($value) => $value !== null && $value !== ''));
        $bankTujuanId = $request->filled('bank_tujuan') ? $request->bank_tujuan : null;
        $sumberDanaIds = array_values(array_filter((array) $request->input('sumber_dana', []), fn($value) => $value !== null && $value !== ''));
        $kategoriIds = array_values(array_filter((array) $request->input('kategori', []), fn($value) => $value !== null && $value !== ''));
        $rekapanVA = $request->rekapanVA;
        $idJenisPembayaran = $request->filled('id_jenis_pembayaran') ? $request->id_jenis_pembayaran : null;

        $activeFilters = [];

        if ($bankTujuanId) {
            $activeFilters[] = 'bank_tujuan';
        }
        if (count($sumberDanaIds) > 0) {
            $activeFilters[] = 'sumber_dana';
        }
        if (count($kategoriIds) > 0) {
            $activeFilters[] = 'kategori';
        }
        if ($idJenisPembayaran) {
            $activeFilters[] = 'jenis_pembayaran';
        }
        if ($rekapanVA) {
            $activeFilters[] = 'rekapan';
        }

        $countActiveFilters = count($activeFilters);

        $filterTanggal = function ($q) use ($tahun, $bulan, $tanggalDipilih, $tglDari, $tglSampai) {
            if (!empty($tanggalDipilih)) {
                $q->whereIn(DB::raw('DATE(tanggal)'), $tanggalDipilih);
                return;
            }

            if ($tahun) {
                $q->whereYear('tanggal', $tahun);
            }

            if ($bulan) {
                $q->whereMonth('tanggal', $bulan);
            }

            if ($tglDari) {
                $q->whereDate('tanggal', '>=', $tglDari);
            }

            if ($tglSampai) {
                $q->whereDate('tanggal', '<=', $tglSampai);
            }
        };

        $applyFilter = function ($q, $table = null) use ($filterTanggal, $bankTujuanId, $sumberDanaIds, $kategoriIds, $idJenisPembayaran) {
            $prefix = $table ? $table . '.' : '';

            $filterTanggal($q);

            if ($bankTujuanId) {
                $q->where($prefix . 'id_bank_tujuan', $bankTujuanId);
            }

            if (count($sumberDanaIds) > 0) {
                $q->whereIn($prefix . 'id_sumber_dana', $sumberDanaIds);
            }

            if (count($kategoriIds) > 0) {
                $q->whereIn($prefix . 'id_kategori_kriteria', $kategoriIds);
            }

            if ($idJenisPembayaran) {
                $q->where($prefix . 'id_jenis_pembayaran', $idJenisPembayaran);
            }
        };

        $applyFilterSaldoAwal = function ($q, $table = null) use ($filterTanggal, $bankTujuanId, $sumberDanaIds) {
            $prefix = $table ? $table . '.' : '';

            $filterTanggal($q);

            if ($bankTujuanId) {
                $q->where($prefix . 'id_bank_tujuan', $bankTujuanId);
            }

            if (count($sumberDanaIds) > 0) {
                $q->whereIn($prefix . 'id_sumber_dana', $sumberDanaIds);
            }
        };

        $showKreditJenisPembayaran = ($countActiveFilters == 1 && $idJenisPembayaran);

        if ($countActiveFilters == 0 || ($countActiveFilters == 1 && !$showKreditJenisPembayaran)) {
            $showDebet = true;
            $showSaldoAkhir = true;
            $showSAP = true;
        } else {
            $showDebet = false;
            $showSaldoAkhir = false;
            $showSAP = false;
        }

        if ($showDebet) {
            $bankMasuk = DB::table('bank_masuk')
                ->leftJoin('sumber_dana', 'sumber_dana.id_sumber_dana', '=', 'bank_masuk.id_sumber_dana')
                ->leftJoin('bank_tujuan', 'bank_tujuan.id_bank_tujuan', '=', 'bank_masuk.id_bank_tujuan')
                ->select(
                    'bank_masuk.agenda_tahun',
                    'bank_masuk.id_sumber_dana',
                    'sumber_dana.nama_sumber_dana',
                    'bank_masuk.id_bank_tujuan',
                    'bank_tujuan.nama_tujuan',
                    'bank_masuk.uraian',
                    'bank_masuk.penerima',
                    'bank_masuk.tanggal',
                    'bank_masuk.debet',
                    DB::raw('0 as kredit'),
                    'bank_masuk.no_sap',
                    DB::raw('NULL as no_agenda'),
                    DB::raw('NULL as nama_kriteria'),
                    DB::raw('NULL as nama_sub_kriteria'),
                    DB::raw('NULL as nama_item_sub_kriteria'),
                    DB::raw('NULL as id_jenis_pembayaran'),
                    DB::raw('NULL as nama_jenis_pembayaran'),
                    DB::raw("'MASUK' as jenis"),
                    DB::raw('bank_masuk.id_bank_masuk as urut_id'),
                    DB::raw('0 as jenis_sort')
                )
                ->where(function ($q) use ($applyFilterSaldoAwal) {
                    $applyFilterSaldoAwal($q, 'bank_masuk');
                });

            $bankKeluar = DB::table('bank_keluars')
                ->leftJoin('sumber_dana', 'sumber_dana.id_sumber_dana', '=', 'bank_keluars.id_sumber_dana')
                ->leftJoin('bank_tujuan', 'bank_tujuan.id_bank_tujuan', '=', 'bank_keluars.id_bank_tujuan')
                ->leftJoin('kategori_kriteria', 'kategori_kriteria.id_kategori_kriteria', '=', 'bank_keluars.id_kategori_kriteria')
                ->leftJoin('sub_kriteria', 'sub_kriteria.id_sub_kriteria', '=', 'bank_keluars.id_sub_kriteria')
                ->leftJoin('item_sub_kriteria', 'item_sub_kriteria.id_item_sub_kriteria', '=', 'bank_keluars.id_item_sub_kriteria')
                ->leftJoin('jenis_pembayarans', 'jenis_pembayarans.id_jenis_pembayaran', '=', 'bank_keluars.id_jenis_pembayaran')
                ->select(
                    'bank_keluars.agenda_tahun',
                    'bank_keluars.id_sumber_dana',
                    'sumber_dana.nama_sumber_dana',
                    'bank_keluars.id_bank_tujuan',
                    'bank_tujuan.nama_tujuan',
                    'bank_keluars.uraian',
                    'bank_keluars.penerima',
                    'bank_keluars.tanggal',
                    DB::raw('0 as debet'),
                    'bank_keluars.kredit',
                    'bank_keluars.no_sap',
                    DB::raw('bank_keluars.agenda_tahun as no_agenda'),
                    'kategori_kriteria.nama_kriteria',
                    'sub_kriteria.nama_sub_kriteria',
                    'item_sub_kriteria.nama_item_sub_kriteria',
                    'bank_keluars.id_jenis_pembayaran',
                    'jenis_pembayarans.nama_jenis_pembayaran',
                    DB::raw("'KELUAR' as jenis"),
                    DB::raw('bank_keluars.id_bank_keluar as urut_id'),
                    DB::raw('1 as jenis_sort')
                )
                ->where(function ($q) use ($applyFilterSaldoAwal) {
                    $applyFilterSaldoAwal($q, 'bank_keluars');
                });

            $baseQuery = $bankMasuk->unionAll($bankKeluar);
        } else {
            $baseQuery = DB::table('bank_keluars')
                ->leftJoin('sumber_dana', 'sumber_dana.id_sumber_dana', '=', 'bank_keluars.id_sumber_dana')
                ->leftJoin('bank_tujuan', 'bank_tujuan.id_bank_tujuan', '=', 'bank_keluars.id_bank_tujuan')
                ->leftJoin('kategori_kriteria', 'kategori_kriteria.id_kategori_kriteria', '=', 'bank_keluars.id_kategori_kriteria')
                ->leftJoin('sub_kriteria', 'sub_kriteria.id_sub_kriteria', '=', 'bank_keluars.id_sub_kriteria')
                ->leftJoin('item_sub_kriteria', 'item_sub_kriteria.id_item_sub_kriteria', '=', 'bank_keluars.id_item_sub_kriteria')
                ->leftJoin('jenis_pembayarans', 'jenis_pembayarans.id_jenis_pembayaran', '=', 'bank_keluars.id_jenis_pembayaran')
                ->select(
                    'bank_keluars.agenda_tahun',
                    'bank_keluars.id_sumber_dana',
                    'sumber_dana.nama_sumber_dana',
                    'bank_keluars.id_bank_tujuan',
                    'bank_tujuan.nama_tujuan',
                    'bank_keluars.uraian',
                    'bank_keluars.penerima',
                    'bank_keluars.tanggal',
                    DB::raw('0 as debet'),
                    'bank_keluars.kredit',
                    'bank_keluars.no_sap',
                    DB::raw('bank_keluars.agenda_tahun as no_agenda'),
                    'kategori_kriteria.nama_kriteria',
                    'sub_kriteria.nama_sub_kriteria',
                    'item_sub_kriteria.nama_item_sub_kriteria',
                    'bank_keluars.id_jenis_pembayaran',
                    'jenis_pembayarans.nama_jenis_pembayaran',
                    DB::raw("'KELUAR' as jenis"),
                    DB::raw('bank_keluars.id_bank_keluar as urut_id'),
                    DB::raw('1 as jenis_sort')
                )
                ->where(function ($q) use ($applyFilter) {
                    $applyFilter($q, 'bank_keluars');
                });
        }

        return [
            'reportQuery' => DB::query()->fromSub($baseQuery, 'trx'),
            'showSaldoAkhir' => $showSaldoAkhir,
            'showSAP' => $showSAP,
        ];
    }

    public function reportData(Request $request)
    {
        $context = $this->buildReportDataQuery($request);
        $reportQuery = $context['reportQuery'];
        $showSaldoAkhir = $context['showSaldoAkhir'];
        $showSAP = $context['showSAP'];

        $totalEntries = (clone $reportQuery)->count();
        $totalDebet = (clone $reportQuery)->sum('debet');
        $totalKredit = (clone $reportQuery)->sum('kredit');
        $finalSaldo = $showSaldoAkhir ? $totalDebet - $totalKredit : null;

        $start = max(0, (int) $request->input('start', 0));
        $length = (int) $request->input('length', 50);
        // Halaman Rekening Koran memuat seluruh baris sekali jalan (Tabulator
        // virtual DOM) — izinkan chunk besar; batas atas hanya pengaman memori.
        $length = $length > 0 ? min($length, 1000000) : 50;

        $rows = (clone $reportQuery)
            ->orderBy('tanggal')
            ->orderBy('urut_id')
            ->orderBy('jenis_sort')
            ->skip($start)
            ->take($length)
            ->get();

        $saldo = 0;

        if ($showSaldoAkhir && $rows->count() > 0) {
            $firstRow = $rows->first();
            $saldo = (clone $reportQuery)
                ->where(function ($q) use ($firstRow) {
                    $q->where('tanggal', '<', $firstRow->tanggal)
                        ->orWhere(function ($sameDate) use ($firstRow) {
                            $sameDate->where('tanggal', $firstRow->tanggal)
                                ->where(function ($sameDateOrder) use ($firstRow) {
                                    $sameDateOrder->where('urut_id', '<', $firstRow->urut_id)
                                        ->orWhere(function ($sameId) use ($firstRow) {
                                            $sameId->where('urut_id', $firstRow->urut_id)
                                                ->where('jenis_sort', '<', $firstRow->jenis_sort);
                                        });
                                });
                        });
                })
                ->selectRaw('COALESCE(SUM(debet), 0) - COALESCE(SUM(kredit), 0) as saldo')
                ->value('saldo') ?? 0;
        }

        $data = [];

        foreach ($rows as $index => $row) {
            if ($showSaldoAkhir) {
                $saldo += ($row->debet ?? 0) - ($row->kredit ?? 0);
            }

            $data[] = [
                'DT_RowClass' => ($row->jenis ?? '') === 'MASUK' ? 'rk-row-debet' : 'rk-row-kredit',
                'no' => $start + $index + 1,
                'no_agenda' => e($row->no_agenda ?? '-'),
                'tanggal' => $row->tanggal ? Carbon::parse($row->tanggal)->format('d/m/Y') : '-',
                'no_sap' => $showSAP ? e($row->no_sap ?? '-') : e($row->no_sap ?? '-'),
                'nama_sumber_dana' => e($row->nama_sumber_dana ?? '-'),
                'penerima' => e($row->penerima ?? '-'),
                'uraian' => e($row->uraian ?? '-'),
                'debet' => ($row->debet ?? 0) > 0 ? number_format($row->debet, 0, ',', '.') : '',
                'kredit' => ($row->kredit ?? 0) > 0 ? number_format($row->kredit, 0, ',', '.') : '',
                'saldo_akhir' => $showSaldoAkhir ? ($saldo < 0
                    ? '(' . number_format(abs($saldo), 0, ',', '.') . ')'
                    : number_format($saldo, 0, ',', '.')) : '',
            ];
        }

        return response()->json([
            'draw' => (int) $request->input('draw'),
            'recordsTotal' => $totalEntries,
            'recordsFiltered' => $totalEntries,
            'data' => $data,
            'totals' => [
                'debet' => number_format($totalDebet ?? 0, 0, ',', '.'),
                'kredit' => number_format($totalKredit ?? 0, 0, ',', '.'),
                'saldo' => $finalSaldo === null ? '-' : ($finalSaldo < 0
                    ? '(' . number_format(abs($finalSaldo), 0, ',', '.') . ')'
                    : number_format($finalSaldo, 0, ',', '.')),
            ],
        ]);
    }

    public function importExcel(Request $request)
    {
        $request->validate(['fileExcel' => 'required|file']);
        $ext = strtolower($request->file('fileExcel')->getClientOriginalExtension());
        if (!in_array($ext, ['xlsx', 'xls', 'csv'])) {
            return back()->withErrors(['fileExcel' => 'File harus berformat xlsx, xls, atau csv.']);
        }

        ini_set('memory_limit', '512M');
        set_time_limit(300);

        try {
            $file = $request->file('fileExcel');
            $filePath = $file->getRealPath();

            $importer = new \App\Imports\ImportKeluarCsv();
            $result = $importer->import($filePath);

            return redirect()
                ->route('bank-keluar.index')
                ->with('success', "Import berhasil! {$result['success']} data diimport dari {$result['total']} baris.");

        } catch (\Exception $e) {
            \Log::error('Import CSV failed: ' . $e->getMessage());
            return redirect()
                ->route('bank-keluar.index')
                ->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }

    /**
     * PREVIEW: Baca CSV tanpa simpan, return JSON untuk modal preview
     */
    public function previewImport(Request $request)
    {
        // ── Cek error upload PHP sebelum validasi Laravel ──
        if ($request->hasFile('fileExcel')) {
            $uploadError = $request->file('fileExcel')->getError();
            if ($uploadError !== UPLOAD_ERR_OK) {
                $phpMessages = [
                    UPLOAD_ERR_INI_SIZE => 'File terlalu besar (melebihi upload_max_filesize=' . ini_get('upload_max_filesize') . '). Hubungi admin server untuk menaikkan limit.',
                    UPLOAD_ERR_FORM_SIZE => 'File terlalu besar (melebihi MAX_FILE_SIZE form).',
                    UPLOAD_ERR_PARTIAL => 'File hanya ter-upload sebagian. Coba lagi.',
                    UPLOAD_ERR_NO_FILE => 'Tidak ada file yang di-upload.',
                    UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ditemukan di server.',
                    UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk server.',
                    UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh extension PHP.',
                ];
                $msg = $phpMessages[$uploadError] ?? "Upload gagal (kode error: {$uploadError}).";
                return response()->json(['message' => $msg], 422);
            }
        } elseif (!$request->hasFile('fileExcel')) {
            // File sama sekali tidak ada — kemungkinan post_max_size terlampaui
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

        // Simpan sementara
        $tempPath = $file->store('import_temp_keluar', 'local');
        session(['keluar_import_temp' => $tempPath]);

        $fullPath = \Illuminate\Support\Facades\Storage::disk('local')->path($tempPath);

        // ── Jika file xlsx/xls, konversi ke CSV sementara via PhpSpreadsheet ──
        $csvPath = $fullPath;
        if (in_array($ext, ['xlsx', 'xls'])) {
            try {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($fullPath);
                $csvPath = $fullPath . '.csv';
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
                $writer->setDelimiter(';');
                $writer->setEnclosure('"');
                $writer->setLineEnding("\r\n");
                $writer->setSheetIndex(0);
                $writer->save($csvPath);
                $spreadsheet->disconnectWorksheets();
                unset($spreadsheet);
            } catch (\Throwable $e) {
                \Log::error('previewImport xlsx->csv conversion failed: ' . $e->getMessage());
                return response()->json([
                    'message' => 'Gagal membaca file Excel: ' . $e->getMessage()
                ], 422);
            }
        }

        // Load cache referensi (1x query saja)
        $sumberDanaMap = \App\Models\SumberDana::pluck('id_sumber_dana', 'nama_sumber_dana')->toArray();
        $bankTujuanMap = \App\Models\BankTujuan::pluck('id_bank_tujuan', 'nama_tujuan')->toArray();
        $kategoriMap = \App\Models\KategoriKriteria::pluck('id_kategori_kriteria', 'nama_kriteria')->toArray();
        $subKriteriaMap = \App\Models\SubKriteria::pluck('id_sub_kriteria', 'nama_sub_kriteria')->toArray();
        $itemSubKritMap = \App\Models\ItemSubKriteria::pluck('id_item_sub_kriteria', 'nama_item_sub_kriteria')->toArray();
        $jenisPembMap = \App\Models\JenisPembayaran::pluck('id_jenis_pembayaran', 'nama_jenis_pembayaran')->toArray();

        $normalizeReference = function ($value) {
            $value = strtolower(trim((string) $value));
            $value = str_replace('&', ' dan ', $value);
            $value = preg_replace('/\bpt\b\.?/i', ' ', $value);
            $value = preg_replace('/\bcab\b\.?/i', ' cabang ', $value);
            $value = preg_replace('/[^a-z0-9]+/i', ' ', $value);
            $value = preg_replace('/\s+/', ' ', $value);

            return trim($value ?? '');
        };

        $extractNumbers = function ($value) {
            preg_match_all('/\b\d[\d\-\/\s]{5,}\d\b/', (string) $value, $matches);

            return collect($matches[0] ?? [])
                ->map(fn($number) => preg_replace('/\D+/', '', $number))
                ->filter(fn($number) => strlen($number) >= 6)
                ->unique()
                ->values()
                ->all();
        };

        // Helper partial match dengan guard empty, dukung nomor rekening dan variasi tanda baca.
        $findInMap = function ($map, $search) {
            $normalizeReference = function ($value) {
                $value = strtolower(trim((string) $value));
                $value = str_replace('&', ' dan ', $value);
                $value = preg_replace('/\bpt\b\.?/i', ' ', $value);
                $value = preg_replace('/\bcab\b\.?/i', ' cabang ', $value);
                $value = preg_replace('/[^a-z0-9]+/i', ' ', $value);
                $value = preg_replace('/\s+/', ' ', $value);

                return trim($value ?? '');
            };

            $extractNumbers = function ($value) {
                preg_match_all('/\b\d[\d\-\/\s]{5,}\d\b/', (string) $value, $matches);

                return collect($matches[0] ?? [])
                    ->map(fn($number) => preg_replace('/\D+/', '', $number))
                    ->filter(fn($number) => strlen($number) >= 6)
                    ->unique()
                    ->values()
                    ->all();
            };

            if (empty($search))
                return null;
            $sl = $normalizeReference($search);
            $searchNumbers = $extractNumbers($search);

            foreach ($map as $nama => $id) {
                $nl = $normalizeReference($nama);
                if ($nl === $sl) {
                    return $nama;
                }
            }

            if (!empty($searchNumbers)) {
                foreach ($map as $nama => $id) {
                    if (array_intersect($searchNumbers, $extractNumbers($nama))) {
                        return $nama;
                    }
                }
            }

            foreach ($map as $nama => $id) {
                $nl = $normalizeReference($nama);
                if ($nl === '' || $sl === '') {
                    continue;
                }

                if (str_contains($nl, $sl) || str_contains($sl, $nl)) {
                    return $nama;
                }

                $searchTokens = array_filter(explode(' ', $sl), fn($token) => strlen($token) >= 3);
                $nameTokens = array_filter(explode(' ', $nl), fn($token) => strlen($token) >= 3);
                if (!empty($searchTokens) && count(array_intersect($searchTokens, $nameTokens)) >= min(2, count($searchTokens))) {
                    return $nama;
                }
            }
            return null;
        };

        $inferKeluarReferences = function ($kategori, $subKriteria, $itemSubKriteria) use (
            $findInMap,
            $normalizeReference,
            $kategoriMap,
            $subKriteriaMap,
            $itemSubKritMap
        ) {
            $text = $normalizeReference($kategori . ' ' . $subKriteria . ' ' . $itemSubKriteria);

            if ($text !== '' && (str_contains($text, 'pembelian tbs') || preg_match('/\btbs\b/', $text))) {
                return [
                    'kategori' => $findInMap($kategoriMap, 'Payment Requirement for Exploitation Activity'),
                    'sub' => $findInMap($subKriteriaMap, 'Purchase Volume'),
                    'item' => $findInMap($itemSubKritMap, 'TBS FFB'),
                ];
            }

            return [
                'kategori' => null,
                'sub' => null,
                'item' => null,
            ];
        };

        $handle = fopen($csvPath, 'r');
        if (!$handle) {
            return response()->json(['message' => 'Gagal membaca file. Pastikan file tidak corrupt.'], 422);
        }

        // Auto-detect delimiter: baca baris pertama, hitung koma vs titik koma
        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);
            return response()->json(['message' => 'File kosong atau tidak dapat dibaca.'], 422);
        }
        $delimiter = (substr_count($firstLine, ';') >= substr_count($firstLine, ',')) ? ';' : ',';
        rewind($handle);

        // Baca header & normalisasi
        $header = fgetcsv($handle, 0, $delimiter);
        if (!$header || empty(array_filter($header))) {
            fclose($handle);
            return response()->json(['message' => 'Header file tidak valid atau kosong.'], 422);
        }
        $header = array_map(fn($h) => str_replace(' ', '_', strtolower(trim($h ?? ''))), $header);

        $preview = [];
        $warnings = 0;
        $i = 0;

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            // Skip baris benar-benar kosong
            if (empty(array_filter($row)))
                continue;

            $headerCount = count($header);
            $rowColCount = count($row);

            // Google Sheets kadang membungkus seluruh baris dalam 1 pasang double-quote
            // ketika ada field yang mengandung koma. fgetcsv() akan menganggap
            // seluruh isi baris sebagai 1 field tunggal.
            // Deteksi dan re-parse jika kolom jauh lebih sedikit dari header.
            if ($rowColCount < $headerCount) {
                $reparsed = str_getcsv(implode($delimiter, $row), $delimiter);
                if (count($reparsed) > $rowColCount) {
                    $row = $reparsed;
                    $rowColCount = count($row);
                }
            }

            // Jika kolom masih kurang dari header, padding dengan string kosong
            if ($rowColCount < $headerCount) {
                $row = array_pad($row, $headerCount, '');
            }
            // Jika kolom lebih dari header, potong kelebihan
            if ($rowColCount > $headerCount) {
                $row = array_slice($row, 0, $headerCount);
            }

            try {
                $data = array_combine($header, $row);
            } catch (\Throwable $e) {
                continue;
            }

            // Ambil nilai dari kolom CSV (support beragam nama header)
            // Catatan: di ekspor Google Sheets "Bank Keluar", kolom nomor agenda
            // (mis. 0004_2026) ada di kolom PERTAMA yang TANPA judul → key-nya ''.
            $agendaRaw = trim($data['agenda_tahun'] ?? $data['no_agenda'] ?? $data['nomor_agenda'] ?? $data['agenda'] ?? $data[''] ?? '');
            $tanggalRaw = trim($data['tanggal'] ?? '');
            // No Bukti manual dari pembukuan (header CSV "No. Bukti" → key no._bukti)
            $buktiRaw = trim($data['no._bukti'] ?? $data['no_bukti'] ?? $data['bukti'] ?? '');
            $sumberRaw = trim($data['sumber_dana'] ?? '');
            $bankRaw = trim($data['bank_tujuan'] ?? '');
            $kategoriRaw = trim($data['kategori'] ?? $data['kriteria_cf'] ?? $data['kriteria'] ?? '');
            // Strip prefix angka "50. " dari kategori
            $kategoriRaw = preg_replace('/^\d+\.\s*/', '', $kategoriRaw);
            $subKritRaw = trim($data['sub_kriteria'] ?? '');
            $itemSubKritRaw = trim($data['item_sub_kriteria'] ?? '');
            $penerimaRaw = trim($data['penerima'] ?? $data['penerima/dari'] ?? '');
            $uraianRaw = trim($data['uraian'] ?? $data['edit_uraian'] ?? '');
            // Kredit: CSV bank keluar punya kolom 'kredit', fallback ke 'debet'
            $debetRaw = trim($data['kredit'] ?? $data['debet'] ?? '');
            $jenisRaw = trim($data['jenis_pembayaran'] ?? '');

            // Hitung kredit
            $kreditNum = 0;
            if (!empty($debetRaw)) {
                $kreditNum = (float) str_replace(['.', ','], ['', '.'], $debetRaw);
            }

            // Skip baris tanpa tanggal — satu-satunya syarat wajib
            if (empty($tanggalRaw))
                continue;

            // Lookup referensi
            $inferred = $inferKeluarReferences($kategoriRaw, $subKritRaw, $itemSubKritRaw);
            $sumberFound = $findInMap($sumberDanaMap, $sumberRaw);
            $bankFound = $findInMap($bankTujuanMap, $bankRaw);
            $kategoriFound = $findInMap($kategoriMap, $kategoriRaw) ?? $inferred['kategori'];
            $subKritFound = $findInMap($subKriteriaMap, $subKritRaw) ?? $inferred['sub'];
            $itemSubKritFound = $findInMap($itemSubKritMap, $itemSubKritRaw) ?? $inferred['item'];
            $jenisFound = $findInMap($jenisPembMap, $jenisRaw);

            $hasWarning = ($sumberRaw && !$sumberFound)
                || ($bankRaw && !$bankFound)
                || ($kategoriRaw && !$kategoriFound)
                || ($subKritRaw && !$subKritFound)
                || ($itemSubKritRaw && !$itemSubKritFound)
                || ($jenisRaw && !$jenisFound);

            if ($hasWarning)
                $warnings++;

            $i++;
            $preview[] = [
                'no' => $i,
                'agenda' => $agendaRaw ?: '-',
                'bukti' => $buktiRaw ?: '-',
                'tanggal' => $tanggalRaw,
                'sumber' => $sumberFound ?? ($sumberRaw ?: '-'),
                'bank' => $bankFound ?? ($bankRaw ?: '-'),
                'kategori' => $kategoriFound ?? ($kategoriRaw ?: '-'),
                'sub_kriteria' => $subKritFound ?? ($subKritRaw ?: '-'),
                'item_sub_krit' => $itemSubKritFound ?? ($itemSubKritRaw ?: '-'),
                'jenis' => $jenisFound ?? ($jenisRaw ?: '-'),
                'penerima' => $penerimaRaw ?: '-',
                'uraian' => $uraianRaw ?: '-',
                'kredit' => number_format($kreditNum, 0, ',', '.'),
                'warning' => $hasWarning,
                'warn_sumber' => $sumberRaw && !$sumberFound,
                'warn_bank' => $bankRaw && !$bankFound,
                'warn_kategori' => $kategoriRaw && !$kategoriFound,
                'warn_sub_krit' => $subKritRaw && !$subKritFound,
                'warn_item_sub' => $itemSubKritRaw && !$itemSubKritFound,
                'warn_jenis' => $jenisRaw && !$jenisFound,
            ];
        }
        fclose($handle);

        return response()->json([
            'rows' => $preview,
            'total' => count($preview),
            'warnings' => $warnings,
        ]);
    }

    /**
     * CONFIRM: Jalankan ImportKeluarCsv dari file temp di session
     */
    public function confirmImport(Request $request)
    {
        $tempPath = session('keluar_import_temp');

        if (!$tempPath || !\Illuminate\Support\Facades\Storage::disk('local')->exists($tempPath)) {
            return response()->json(['error' => 'File sementara tidak ditemukan. Silakan upload ulang.'], 422);
        }

        ini_set('memory_limit', '-1');
        set_time_limit(0);

        try {
            $fullPath = \Illuminate\Support\Facades\Storage::disk('local')->path($tempPath);

            $importer = new \App\Imports\ImportKeluarCsv();
            $result = $importer->import($fullPath);

            \Illuminate\Support\Facades\Storage::disk('local')->delete($tempPath);
            session()->forget('keluar_import_temp');

            return response()->json([
                'success' => "Data berhasil diimport! {$result['success']} baris tersimpan.",
                'total' => $result['success'],
            ]);

        } catch (\Exception $e) {
            \Log::error('Confirm import keluar failed: ' . $e->getMessage());
            return response()->json(['error' => 'Import gagal: ' . $e->getMessage()], 500);
        }
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

        // ── Sheet Referensi: daftar nama master yang sah ──
        // Kriteria dibatasi tipe Keluar (sama dengan dropdown aplikasi);
        // sub & item mengikuti rantai kriteria Keluar tersebut.
        $kategoriKeluarIds = KategoriKriteria::where('tipe', 'Keluar')->pluck('id_kategori_kriteria');
        $subKeluarIds = SubKriteria::whereIn('id_kategori_kriteria', $kategoriKeluarIds)->pluck('id_sub_kriteria');
        $refLists = [
            ['Sumber Dana', SumberDana::orderBy('nama_sumber_dana')->pluck('nama_sumber_dana')->all()],
            ['Bank Tujuan', BankTujuan::orderBy('nama_tujuan')->pluck('nama_tujuan')->all()],
            ['Kriteria', KategoriKriteria::where('tipe', 'Keluar')->orderBy('nama_kriteria')->pluck('nama_kriteria')->all()],
            ['Sub Kriteria', SubKriteria::whereIn('id_kategori_kriteria', $kategoriKeluarIds)->orderBy('nama_sub_kriteria')->pluck('nama_sub_kriteria')->all()],
            ['Item Sub Kriteria', ItemSubKriteria::whereIn('id_sub_kriteria', $subKeluarIds)->orderBy('nama_item_sub_kriteria')->pluck('nama_item_sub_kriteria')->all()],
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
        $ref->getStyle('A1:F1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
        ]);
        $ref->freezePane('A2');

        // ── Sheet Data: tempat user mengisi transaksi ──
        $sheet = $spreadsheet->getSheet(0);
        $sheet->setTitle('Data');
        $headers = ['No Agenda', 'Tanggal', 'No Bukti', 'Sumber Dana', 'Bank Tujuan', 'Kriteria',
            'Sub Kriteria', 'Item Sub Kriteria', 'Jenis Pembayaran', 'Penerima',
            'Uraian', 'Kredit', 'Keterangan'];
        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:M1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);
        foreach (['A' => 14, 'B' => 13, 'C' => 12, 'D' => 28, 'E' => 28, 'F' => 24, 'G' => 24,
            'H' => 24, 'I' => 20, 'J' => 28, 'K' => 45, 'L' => 16, 'M' => 25] as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }
        $sheet->getStyle('B2:B1000')->getNumberFormat()->setFormatCode('dd/mm/yyyy');
        $sheet->getStyle('L2:L1000')->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('A2:A1000')->getNumberFormat()
            ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
        $sheet->getStyle('C2:C1000')->getNumberFormat()
            ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
        $sheet->freezePane('A2');

        $buatDropdown = function ($dataCol, $formula) use ($sheet) {
            $dv = new \PhpOffice\PhpSpreadsheet\Cell\DataValidation();
            $dv->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)
                ->setAllowBlank(true)
                ->setShowDropDown(true)
                ->setShowErrorMessage(false)
                ->setFormula1($formula)
                ->setSqref($dataCol . '2:' . $dataCol . '1000');
            $sheet->setDataValidation($dataCol . '2', $dv);
        };

        // Dropdown statis: Sumber Dana, Bank Tujuan, Kriteria, Jenis Pembayaran
        $dropdowns = ['D' => 0, 'E' => 1, 'F' => 2, 'I' => 5];
        foreach ($dropdowns as $dataCol => $i) {
            $count = count($refLists[$i][1]);
            if ($count === 0) {
                continue;
            }
            $refCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
            $rangeName = 'RefBK' . $dataCol;
            $spreadsheet->addNamedRange(new \PhpOffice\PhpSpreadsheet\NamedRange(
                $rangeName, $ref, '$' . $refCol . '$2:$' . $refCol . '$' . ($count + 1)
            ));
            $buatDropdown($dataCol, $rangeName);
        }

        // ── Rumus otomatis Bank Tujuan (E) dari Uraian (K) ──
        // Uraian umumnya diawali 11 digit nomor VA (mis. "81029155507|...");
        // 11 karakter pertama dicocokkan ke awalan nama di daftar Bank Tujuan.
        // Memilih manual dari dropdown tetap bisa (menimpa rumus di baris itu).
        if (count($refLists[1][1]) > 0) {
            for ($r = 2; $r <= 1000; $r++) {
                $sheet->setCellValue(
                    "E{$r}",
                    "=IF(K{$r}=\"\",\"\",IFERROR(INDEX(RefBKE,MATCH(LEFT(TRIM(K{$r}),11)&\"*\",RefBKE,0)),\"\"))"
                );
            }
        }

        // ── Sheet RefMap (disembunyikan): sumber dropdown BERTINGKAT ──
        // Meniru aturan kaskade web: Sub Kriteria mengikuti Kriteria terpilih,
        // Item mengikuti Sub — via INDIRECT(VLOOKUP(...)) ke named range per induk.
        $katRows = KategoriKriteria::where('tipe', 'Keluar')
            ->orderBy('nama_kriteria')->get(['id_kategori_kriteria', 'nama_kriteria']);
        $subRows = SubKriteria::whereIn('id_kategori_kriteria', $kategoriKeluarIds)
            ->orderBy('nama_sub_kriteria')->get(['id_sub_kriteria', 'id_kategori_kriteria', 'nama_sub_kriteria']);
        $itemRows = ItemSubKriteria::whereIn('id_sub_kriteria', $subKeluarIds)
            ->orderBy('nama_item_sub_kriteria')->get(['id_item_sub_kriteria', 'id_sub_kriteria', 'nama_item_sub_kriteria']);

        $nSubAll = count($refLists[3][1]);
        $nItemAll = count($refLists[4][1]);
        if ($nSubAll > 0) {
            $spreadsheet->addNamedRange(new \PhpOffice\PhpSpreadsheet\NamedRange('RefSubAll', $ref, '$D$2:$D$' . ($nSubAll + 1)));
        }
        if ($nItemAll > 0) {
            $spreadsheet->addNamedRange(new \PhpOffice\PhpSpreadsheet\NamedRange('RefItemAll', $ref, '$E$2:$E$' . ($nItemAll + 1)));
        }

        $rmap = $spreadsheet->createSheet();
        $rmap->setTitle('RefMap');
        $blokCol = 7; // blok daftar per-induk mulai kolom G

        // Peta Kriteria → named range daftar sub-nya (kolom A:B)
        foreach ($katRows as $i => $k) {
            $subs = $subRows->where('id_kategori_kriteria', $k->id_kategori_kriteria)
                ->pluck('nama_sub_kriteria')->values();
            $rmap->setCellValue('A' . ($i + 1), $k->nama_kriteria);
            if (count($subs) === 0) {
                $rmap->setCellValue('B' . ($i + 1), 'RefSubAll');
                continue;
            }
            $nmRange = 'KatSub_' . ($i + 1);
            $rmap->setCellValue('B' . ($i + 1), $nmRange);
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($blokCol++);
            foreach ($subs as $j => $nm) {
                $rmap->setCellValue($col . ($j + 1), $nm);
            }
            $spreadsheet->addNamedRange(new \PhpOffice\PhpSpreadsheet\NamedRange(
                $nmRange, $rmap, '$' . $col . '$1:$' . $col . '$' . count($subs)
            ));
        }

        // Peta Sub → named range daftar item-nya (kolom D:E);
        // nama sub duplikat lintas kriteria: entri pertama yang dipakai VLOOKUP
        $rSub = 0;
        $subSeen = [];
        foreach ($subRows as $s) {
            if (isset($subSeen[$s->nama_sub_kriteria])) {
                continue;
            }
            $subSeen[$s->nama_sub_kriteria] = true;
            $items = $itemRows->where('id_sub_kriteria', $s->id_sub_kriteria)
                ->pluck('nama_item_sub_kriteria')->values();
            $rSub++;
            $rmap->setCellValue('D' . $rSub, $s->nama_sub_kriteria);
            if (count($items) === 0) {
                $rmap->setCellValue('E' . $rSub, 'RefItemAll');
                continue;
            }
            $nmRange = 'SubItem_' . $rSub;
            $rmap->setCellValue('E' . $rSub, $nmRange);
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($blokCol++);
            foreach ($items as $j => $nm) {
                $rmap->setCellValue($col . ($j + 1), $nm);
            }
            $spreadsheet->addNamedRange(new \PhpOffice\PhpSpreadsheet\NamedRange(
                $nmRange, $rmap, '$' . $col . '$1:$' . $col . '$' . count($items)
            ));
        }
        $rmap->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);

        // Dropdown bertingkat: Sub (G) mengikuti Kriteria (F), Item (H) mengikuti Sub (G).
        // Bila induk belum dipilih/tak dikenal → tampilkan daftar lengkap.
        if ($katRows->count() > 0 && $nSubAll > 0) {
            $spreadsheet->addNamedRange(new \PhpOffice\PhpSpreadsheet\NamedRange(
                'RefMapKriteria', $rmap, '$A$1:$B$' . $katRows->count()
            ));
            $buatDropdown('G', 'INDIRECT(IFERROR(VLOOKUP(F2,RefMapKriteria,2,0),"RefSubAll"))');
        }
        if ($rSub > 0 && $nItemAll > 0) {
            $spreadsheet->addNamedRange(new \PhpOffice\PhpSpreadsheet\NamedRange(
                'RefMapSub', $rmap, '$D$1:$E$' . $rSub
            ));
            $buatDropdown('H', 'INDIRECT(IFERROR(VLOOKUP(G2,RefMapSub,2,0),"RefItemAll"))');
        }

        // ── Sheet Petunjuk ──
        $ptr = $spreadsheet->createSheet();
        $ptr->setTitle('Petunjuk');
        $petunjuk = [
            'PETUNJUK PENGISIAN TEMPLATE IMPORT BANK KELUAR',
            '',
            '1. Isi transaksi pada sheet "Data". Baris 1 (judul kolom) jangan diubah atau dihapus.',
            '2. Tidak semua kolom wajib diisi. Kolom yang kosong tetap ikut terimport,',
            '   tampil "-" di tabel Bank Keluar, dan bisa dilengkapi kemudian lewat tombol edit.',
            '3. Tanggal: gunakan format tanggal Excel biasa atau ketik dd/mm/yyyy (contoh 05/07/2026).',
            '4. Kredit: angka nilai transaksi, contoh 1500000 (boleh memakai pemisah ribuan 1.500.000).',
            '5. No Bukti: nomor bukti pembukuan Anda sendiri (manual, TIDAK dibuat otomatis oleh sistem) —',
            '   diambil apa adanya dari file ini saat import.',
            '6. Sumber Dana, Bank Tujuan, Kriteria, Sub Kriteria, Item Sub Kriteria, dan Jenis Pembayaran',
            '   harus sama persis dengan daftar di sheet "Referensi" — gunakan dropdown yang tersedia.',
            '   Nama yang tidak dikenali akan dikosongkan otomatis saat import (data lain tetap masuk).',
            '7. Isi berurutan dari kiri: dropdown Sub Kriteria menyesuaikan Kriteria yang dipilih',
            '   di baris yang sama, dan Item Sub Kriteria menyesuaikan Sub Kriteria (seperti di aplikasi).',
            '8. Bank Tujuan terisi OTOMATIS bila Uraian diawali 11 digit nomor VA',
            '   (contoh "81029155507| MCM InhouseTrf ..."). Tidak perlu memilih satu per satu;',
            '   pilihan manual lewat dropdown tetap bisa dan akan menimpa isian otomatisnya.',
            '9. Simpan file, upload lewat tombol Import Excel, periksa hasil baca di pratinjau,',
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
        }, 'template_import_bank_keluar.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * IMPORT MANDIRI — parser inti file template. Dipakai previewTemplate
     * (tanpa simpan) dan confirmTemplate (simpan). Kolom boleh kosong; nama
     * referensi dicocokkan exact (case-insensitive) dengan aturan kaskade.
     * Mengembalikan ['error' => pesan] atau ['inserts', 'warnings', 'preview'].
     */
    private function parseTemplateKeluar(string $path): array
    {
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        try {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($path);
            $spreadsheet = $reader->load($path);
            $sheet = $spreadsheet->getSheetByName('Data') ?? $spreadsheet->getSheet(0);
            $rows = $sheet->toArray(null, true, false, false);
        } catch (\Throwable $e) {
            \Log::error('parseTemplateKeluar gagal membaca file: ' . $e->getMessage());
            return ['error' => 'Gagal membaca file: ' . $e->getMessage()];
        }

        if (count($rows) < 2) {
            return ['error' => 'File tidak berisi baris data.'];
        }

        // ── Peta header → field (urutan kolom bebas, dicocokkan by nama) ──
        $normHeader = fn($v) => preg_replace('/[^a-z]/', '', strtolower((string) $v));
        $aliases = [
            'noagenda' => 'no_agenda',
            'agenda' => 'no_agenda',
            'tanggal' => 'tanggal',
            'nobukti' => 'bukti',
            'bukti' => 'bukti',
            'sumberdana' => 'sumber',
            'banktujuan' => 'bank',
            'kriteria' => 'kategori',
            'kategori' => 'kategori',
            'kategorikriteria' => 'kategori',
            'subkriteria' => 'sub',
            'itemsubkriteria' => 'item',
            'jenispembayaran' => 'jenis',
            'penerima' => 'penerima',
            'uraian' => 'uraian',
            'kredit' => 'kredit',
            'nilai' => 'kredit',
            'nilairupiah' => 'kredit',
            'keterangan' => 'keterangan',
        ];
        $colMap = [];
        foreach ($rows[0] as $idx => $judul) {
            $key = $normHeader($judul);
            if (isset($aliases[$key]) && !isset($colMap[$aliases[$key]])) {
                $colMap[$aliases[$key]] = $idx;
            }
        }
        if (!isset($colMap['uraian']) && !isset($colMap['kredit'])) {
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
        // Kriteria dibatasi tipe Keluar (rantai sub & item mengikuti) agar
        // nama kriteria Bank Masuk tidak ikut cocok di Bank Keluar
        $kategoriKeluarIds = KategoriKriteria::where('tipe', 'Keluar')->pluck('id_kategori_kriteria');
        $subKeluarIds = SubKriteria::whereIn('id_kategori_kriteria', $kategoriKeluarIds)->pluck('id_sub_kriteria');
        $refMaps = [
            'sumber' => ['id_sumber_dana', 'Sumber Dana', $buildMap(SumberDana::pluck('id_sumber_dana', 'nama_sumber_dana'))],
            'bank' => ['id_bank_tujuan', 'Bank Tujuan', $buildMap(BankTujuan::pluck('id_bank_tujuan', 'nama_tujuan'))],
            'kategori' => ['id_kategori_kriteria', 'Kriteria', $buildMap(KategoriKriteria::where('tipe', 'Keluar')->pluck('id_kategori_kriteria', 'nama_kriteria'))],
            'jenis' => ['id_jenis_pembayaran', 'Jenis Pembayaran', $buildMap(JenisPembayaran::pluck('id_jenis_pembayaran', 'nama_jenis_pembayaran'))],
        ];

        // Sub & Item dicocokkan dengan aturan kaskade web: sub harus milik
        // kriteria terpilih, item harus milik sub. Peta menyertakan induknya.
        $subLookup = [];
        $subParent = [];
        foreach (SubKriteria::whereIn('id_kategori_kriteria', $kategoriKeluarIds)
            ->get(['id_sub_kriteria', 'id_kategori_kriteria', 'nama_sub_kriteria']) as $s) {
            $subLookup[$normNama($s->nama_sub_kriteria)][] = $s;
            $subParent[$s->id_sub_kriteria] = $s->id_kategori_kriteria;
        }
        $itemLookup = [];
        foreach (ItemSubKriteria::whereIn('id_sub_kriteria', $subKeluarIds)
            ->get(['id_item_sub_kriteria', 'id_sub_kriteria', 'nama_item_sub_kriteria']) as $it) {
            $itemLookup[$normNama($it->nama_item_sub_kriteria)][] = $it;
        }

        // Nama per id — untuk menampilkan induk yang terisi otomatis di preview
        $namaKategoriById = KategoriKriteria::pluck('nama_kriteria', 'id_kategori_kriteria');
        $namaSubById = SubKriteria::pluck('nama_sub_kriteria', 'id_sub_kriteria');

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
                return Carbon::parse($v)->format('Y-m-d');
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
                // 1,500,000 = ribuan; 1500,50 = desimal koma
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
                // agenda_tahun = field yang ditampilkan tabel & dipakai fitur lain;
                // no_agenda diisi sama agar konsisten dengan data lama
                'no_agenda' => $get($row, 'no_agenda'),
                'agenda_tahun' => $get($row, 'no_agenda'),
                'no_bukti' => $get($row, 'bukti'),
                'tanggal' => $parseTanggal($get($row, 'tanggal')),
                'penerima' => $get($row, 'penerima'),
                'uraian' => $get($row, 'uraian'),
                'keterangan' => $get($row, 'keterangan'),
                'debet' => 0,
                'kredit' => $parseAngka($get($row, 'kredit')) ?? 0,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $rawTanggal = $get($row, 'tanggal');
            if ($rawTanggal !== null && $data['tanggal'] === null) {
                $warnings[] = "Baris {$rowNum}: Tanggal \"{$rawTanggal}\" tidak dikenali — dikosongkan";
                $warnFields['tanggal'] = true;
            }
            $rawKredit = $get($row, 'kredit');
            if ($rawKredit !== null && $parseAngka($rawKredit) === null) {
                $warnings[] = "Baris {$rowNum}: Kredit \"{$rawKredit}\" bukan angka — diisi 0";
                $warnFields['kredit'] = true;
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

            // Sub Kriteria: harus konsisten dengan Kriteria (aturan kaskade web);
            // bila Kriteria kosong tapi Sub dikenal, Kriteria diisi dari induknya.
            $data['id_sub_kriteria'] = null;
            $namaSub = $get($row, 'sub');
            if ($namaSub !== null) {
                $cands = $subLookup[$normNama($namaSub)] ?? [];
                if (empty($cands)) {
                    $warnings[] = "Baris {$rowNum}: Sub Kriteria \"{$namaSub}\" tidak ada di master — dikosongkan";
                    $warnFields['sub'] = true;
                } elseif ($data['id_kategori_kriteria'] !== null) {
                    $cocok = array_values(array_filter(
                        $cands,
                        fn($s) => $s->id_kategori_kriteria == $data['id_kategori_kriteria']
                    ));
                    if (empty($cocok)) {
                        $warnings[] = "Baris {$rowNum}: Sub Kriteria \"{$namaSub}\" bukan bagian dari Kriteria yang dipilih — dikosongkan";
                        $warnFields['sub'] = true;
                    } else {
                        $data['id_sub_kriteria'] = $cocok[0]->id_sub_kriteria;
                    }
                } else {
                    $data['id_sub_kriteria'] = $cands[0]->id_sub_kriteria;
                    $data['id_kategori_kriteria'] = $cands[0]->id_kategori_kriteria;
                }
            }

            // Item Sub Kriteria: pola yang sama terhadap Sub Kriteria;
            // bila Sub kosong tapi Item dikenal, Sub (dan Kriteria) diisi dari induknya.
            $data['id_item_sub_kriteria'] = null;
            $namaItem = $get($row, 'item');
            if ($namaItem !== null) {
                $cands = $itemLookup[$normNama($namaItem)] ?? [];
                if (empty($cands)) {
                    $warnings[] = "Baris {$rowNum}: Item Sub Kriteria \"{$namaItem}\" tidak ada di master — dikosongkan";
                    $warnFields['item'] = true;
                } elseif ($data['id_sub_kriteria'] !== null) {
                    $cocok = array_values(array_filter(
                        $cands,
                        fn($i2) => $i2->id_sub_kriteria == $data['id_sub_kriteria']
                    ));
                    if (empty($cocok)) {
                        $warnings[] = "Baris {$rowNum}: Item Sub Kriteria \"{$namaItem}\" bukan bagian dari Sub Kriteria yang dipilih — dikosongkan";
                        $warnFields['item'] = true;
                    } else {
                        $data['id_item_sub_kriteria'] = $cocok[0]->id_item_sub_kriteria;
                    }
                } else {
                    $cands2 = $cands;
                    if ($data['id_kategori_kriteria'] !== null) {
                        $cands2 = array_values(array_filter(
                            $cands,
                            fn($i2) => ($subParent[$i2->id_sub_kriteria] ?? null) == $data['id_kategori_kriteria']
                        ));
                        if (empty($cands2)) {
                            $warnings[] = "Baris {$rowNum}: Item Sub Kriteria \"{$namaItem}\" bukan bagian dari Kriteria yang dipilih — dikosongkan";
                            $warnFields['item'] = true;
                        }
                    }
                    if (!empty($cands2)) {
                        $data['id_item_sub_kriteria'] = $cands2[0]->id_item_sub_kriteria;
                        $data['id_sub_kriteria'] = $cands2[0]->id_sub_kriteria;
                        $data['id_kategori_kriteria'] = $data['id_kategori_kriteria']
                            ?? ($subParent[$cands2[0]->id_sub_kriteria] ?? null);
                    }
                }
            }

            $inserts[] = $data;

            // Baris pratinjau: tampilkan tulisan asli user; induk yang terisi
            // otomatis (dari sub/item) ditampilkan dengan penanda "(otomatis)"
            $preview[] = [
                'baris' => $rowNum,
                'agenda' => $data['no_agenda'],
                'bukti' => $data['no_bukti'],
                'tanggal' => $data['tanggal']
                    ? Carbon::parse($data['tanggal'])->format('d/m/Y')
                    : ($rawTanggal !== null ? (string) $rawTanggal : null),
                'sumber' => $get($row, 'sumber'),
                'bank' => $bankOtomatis
                    ? ($namaBankById[$data['id_bank_tujuan']] ?? '') . ' (otomatis)'
                    : $get($row, 'bank'),
                'kategori' => $get($row, 'kategori')
                    ?? ($data['id_kategori_kriteria'] !== null
                        ? ($namaKategoriById[$data['id_kategori_kriteria']] ?? '') . ' (otomatis)'
                        : null),
                'sub' => $namaSub
                    ?? ($data['id_sub_kriteria'] !== null
                        ? ($namaSubById[$data['id_sub_kriteria']] ?? '') . ' (otomatis)'
                        : null),
                'item' => $namaItem,
                'jenis' => $get($row, 'jenis'),
                'penerima' => $data['penerima'],
                'uraian' => $data['uraian'],
                'kredit' => number_format($data['kredit'], 0, ',', '.'),
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
     * File ditahan sementara; baru disimpan saat confirmTemplate.
     */
    public function previewTemplate(Request $request)
    {
        $request->validate(['fileTemplate' => 'required|file|max:20480']);
        $ext = strtolower($request->file('fileTemplate')->getClientOriginalExtension());
        if (!in_array($ext, ['xlsx', 'xls', 'csv'])) {
            return response()->json(['message' => 'File harus berformat xlsx, xls, atau csv.'], 422);
        }

        $tempPath = $request->file('fileTemplate')->store('import_temp_keluar', 'local');
        session(['keluar_template_temp' => $tempPath]);

        $hasil = $this->parseTemplateKeluar(
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
        $tempPath = session('keluar_template_temp');
        if (!$tempPath || !\Illuminate\Support\Facades\Storage::disk('local')->exists($tempPath)) {
            return response()->json(['message' => 'File sementara tidak ditemukan. Silakan upload ulang.'], 422);
        }

        $hasil = $this->parseTemplateKeluar(
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
                    BankKeluar::insert($chunk);
                }
            });
        } catch (\Throwable $e) {
            \Log::error('confirmTemplate gagal menyimpan: ' . $e->getMessage());
            return response()->json(['message' => 'Import gagal saat menyimpan: ' . $e->getMessage()], 500);
        }

        \Illuminate\Support\Facades\Storage::disk('local')->delete($tempPath);
        session()->forget('keluar_template_temp');

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
        $keluar = bankKeluar::findOrFail($id);
        return view('cash_bank.modal.editKeluar', compact('keluar'));
    }
    public function update(Request $request, string $id)
    {
        $bankKeluar = bankKeluar::findOrFail($id);

        // Simpan flag apakah user memilih "-" untuk reset kategori
        $kategoriReset = $request->input('id_kategori_kriteria') === '-';

        // Jika user pilih "-", set kategori, sub, dan item ke null di database
        $data = $request->except(['_method', '_token']);

        // '-' / '' dari dropdown inline pada referensi lain = kosongkan (null)
        foreach (['id_sumber_dana', 'id_bank_tujuan', 'id_jenis_pembayaran'] as $f) {
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
        if ($kategoriReset) {
            $data['id_kategori_kriteria'] = null;
            $data['id_sub_kriteria'] = null;
            $data['id_item_sub_kriteria'] = null;
        } else {
            // Jika sub atau item bernilai "-", set ke null juga
            if (isset($data['id_sub_kriteria']) && $data['id_sub_kriteria'] === '-') {
                $data['id_sub_kriteria'] = null;
            }
            if (isset($data['id_item_sub_kriteria']) && $data['id_item_sub_kriteria'] === '-') {
                $data['id_item_sub_kriteria'] = null;
            }
        }

        $bankKeluar->update($data);

        // Refresh model to get the latest values after update
        $bankKeluar->refresh();

        // === Direct Sync ke Agenda Online ===
        try {
            $agendaKey = $bankKeluar->agenda_tahun;
            if ($agendaKey || $bankKeluar->dokumen_id) {
                $syncPayload = [];

                // Map basic fields
                $syncPayload['uraian_spp'] = $bankKeluar->uraian;
                $syncPayload['nilai_rupiah'] = $bankKeluar->kredit;
                $syncPayload['dibayar'] = $bankKeluar->kredit;
                $syncPayload['dibayar_kepada'] = $bankKeluar->penerima;
                $syncPayload['tanggal_dibayar'] = $bankKeluar->tanggal;

                // Jika user reset kategori dengan "-", set semua ke "-"
                if ($kategoriReset) {
                    $syncPayload['kategori'] = '-';
                    $syncPayload['jenis_dokumen'] = '-';
                    $syncPayload['jenis_sub_pekerjaan'] = '-';
                } else {
                    // Lookup kategori ID → nama
                    if ($bankKeluar->id_kategori_kriteria) {
                        $kategori = DB::table('kategori_kriteria')
                            ->where('id_kategori_kriteria', $bankKeluar->id_kategori_kriteria)
                            ->first();
                        if ($kategori) {
                            $syncPayload['kategori'] = $kategori->nama_kriteria;
                        }
                    }

                    // Lookup sub_kriteria ID → nama (jenis_dokumen)
                    if ($bankKeluar->id_sub_kriteria) {
                        $sub = DB::table('sub_kriteria')
                            ->where('id_sub_kriteria', $bankKeluar->id_sub_kriteria)
                            ->first();
                        if ($sub) {
                            $syncPayload['jenis_dokumen'] = $sub->nama_sub_kriteria;
                        }
                    }

                    // Lookup item_sub_kriteria ID → nama (jenis_sub_pekerjaan)
                    if ($bankKeluar->id_item_sub_kriteria) {
                        $item = DB::table('item_sub_kriteria')
                            ->where('id_item_sub_kriteria', $bankKeluar->id_item_sub_kriteria)
                            ->first();
                        if ($item) {
                            $syncPayload['jenis_sub_pekerjaan'] = $item->nama_item_sub_kriteria;
                        }
                    }
                }

                // Lookup jenis_pembayaran ID → nama
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
                    'agenda_key' => $agendaKey,
                    'rows_affected' => $affected,
                ]);
            }
        } catch (\Throwable $e) {
            \Log::error('[CBSync] Direct sync CB → AO GAGAL.', [
                'bank_keluar_id' => $bankKeluar->id_bank_keluar,
                'error' => $e->getMessage(),
            ]);
        }

        if ($request->ajax()) {
            return response()->json(['success' => 'Data berhasil diperbarui']);
        }
        return redirect()->route('bank-keluar.index')->with('success', 'Data berhasil diperbarui');
    }

    public function destroy(string $id)
    {
        $data = bankKeluar::findOrFail($id);
        $data->delete();

        return redirect()->route('bank-keluar.index')->with('success', 'Data berhasil dihapus');
    }

    public function deleteAll(Request $request)
    {
        // Support JSON body (untuk menghindari batas max_input_vars PHP saat data ribuan)
        $ids = $request->json('ids') ?? $request->input('ids');

        if (empty($ids) || !is_array($ids)) {
            return response()->json(['error' => 'Tidak ada data yang dipilih.'], 422);
        }

        $deleted = BankKeluar::whereIn('id_bank_keluar', $ids)->delete();

        return response()->json([
            'success' => "Berhasil menghapus {$deleted} data Bank Keluar."
        ]);
    }

    public function export_excel(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        return Excel::download(
            new excelBankKeluar($request->only(['tahun', 'bulan', 'kategori', 'sumber_dana'])),
            'bankKeluar-' . date('Y-m-d') . '.xlsx'
        );
    }

    public function report_export_excel(Request $request)
    {
        return Excel::download(
            new reportKeluarExcel($request),
            'report-bank-keluar-' . date('Y-m-d') . '.xlsx'
        );
    }


    public function view_pdf(Request $request)
    {
        // Batas aman halaman cetak: ribuan baris membuat browser/print macet
        $maxRows = 3000;
        $jumlah = $this->applyExportFilter(BankKeluar::query(), $request)->count();
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

        $query = BankKeluar::select(
            'id_bank_keluar',
            'agenda_tahun',
            'tanggal',
            'id_sumber_dana',
            'id_bank_tujuan',
            'id_kategori_kriteria',
            'id_sub_kriteria',
            'id_item_sub_kriteria',
            'penerima',
            'uraian',
            'id_jenis_pembayaran',
            'nilai_rupiah',
            'kredit',
            'keterangan'
        )
            ->with([
                'sumberDana:id_sumber_dana,nama_sumber_dana',
                'bankTujuan:id_bank_tujuan,nama_tujuan',
                'kategori:id_kategori_kriteria,nama_kriteria',
                'subKriteria:id_sub_kriteria,nama_sub_kriteria',
                'itemSubKriteria:id_item_sub_kriteria,nama_item_sub_kriteria',
                'jenisPembayaran:id_jenis_pembayaran,nama_jenis_pembayaran',
            ])
            ->orderBy('tanggal', 'asc')
            ->orderBy('id_bank_keluar');
        $data = $this->applyExportFilter($query, $request)->get();


        // Info filter untuk kop laporan
        $bulanNama = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
        $filterInfo = 'Periode: ' . ($request->filled('bulan') ? ($bulanNama[(int) $request->bulan] ?? '') . ' ' : '')
            . ($request->filled('tahun') ? $request->tahun : 'Semua Tahun');
        if ($request->filled('kategori')) {
            $filterInfo .= ' • Kriteria: ' . (optional(KategoriKriteria::find($request->kategori))->nama_kriteria ?? '-');
        }
        if ($request->filled('sumber_dana')) {
            $filterInfo .= ' • Sumber Dana: ' . (optional(SumberDana::find($request->sumber_dana))->nama_sumber_dana ?? '-');
        }

        return view('cash_bank.exportPDF.keluarPdf', [
            'data' => $data,
            'filterInfo' => $filterInfo,
        ]);
    }
    public function reportKeluarPdf(Request $request)
    {
        /* ================= AMBIL SEMUA REQUEST FILTER ================= */
        $tahun = $request->tahun;
        $bulan = $request->bulan;
        $tanggalDipilih = $request->tanggal;
        $bankTujuanId = $request->bank_tujuan;
        $sumberDanaIds = $request->sumber_dana;
        $kategoriIds = $request->kategori;
        $rekapanVA = $request->rekapanVA;
        $idJenisPembayaran = $request->id_jenis_pembayaran;

        /* ================= HITUNG JUMLAH FILTER AKTIF ================= */
        $activeFilters = [];
        $timeFilters = [];

        if ($tahun)
            $timeFilters[] = 'tahun';
        if ($bulan)
            $timeFilters[] = 'bulan';
        if ($tanggalDipilih && count($tanggalDipilih) > 0)
            $timeFilters[] = 'tanggal';

        if ($bankTujuanId)
            $activeFilters[] = 'bank_tujuan';
        if ($sumberDanaIds && count($sumberDanaIds) > 0)
            $activeFilters[] = 'sumber_dana';
        if ($kategoriIds && count($kategoriIds) > 0)
            $activeFilters[] = 'kategori';
        if ($idJenisPembayaran)
            $activeFilters[] = 'jenis_pembayaran';
        if ($rekapanVA)
            $activeFilters[] = 'rekapan';

        $countActiveFilters = count($activeFilters);

        /* ================= FILTER TANGGAL (CLOSURE) ================= */
        $filterTanggal = function ($q) use ($tahun, $bulan, $tanggalDipilih) {
            if (!empty($tanggalDipilih) && is_array($tanggalDipilih)) {
                $q->whereIn(DB::raw('DATE(tanggal)'), $tanggalDipilih);
            } elseif ($tahun && $bulan) {
                $q->whereYear('tanggal', $tahun)->whereMonth('tanggal', $bulan);
            } elseif ($tahun) {
                $q->whereYear('tanggal', $tahun);
            }
        };

        /* ================= APPLY FILTER PROGRESIF ================= */
        $applyFilter = function ($q, $table = null) use ($filterTanggal, $bankTujuanId, $sumberDanaIds, $kategoriIds, $idJenisPembayaran, ) {
            $prefix = $table ? $table . '.' : '';

            $filterTanggal($q);

            if ($bankTujuanId) {
                $q->where($prefix . 'id_bank_tujuan', $bankTujuanId);
            }

            if ($sumberDanaIds && is_array($sumberDanaIds) && count($sumberDanaIds) > 0) {
                $q->whereIn($prefix . 'id_sumber_dana', $sumberDanaIds);
            }

            if ($kategoriIds && is_array($kategoriIds) && count($kategoriIds) > 0) {
                $q->whereIn($prefix . 'id_kategori_kriteria', $kategoriIds);
            }

            if ($idJenisPembayaran) {
                $q->where($prefix . 'id_jenis_pembayaran', $idJenisPembayaran);
            }
        };

        /* ================= FILTER KHUSUS UNTUK SALDO AWAL ================= */
        // Filter untuk hitung saldo awal (hanya filter waktu, bank, dan sumber dana)
        $applyFilterSaldoAwal = function ($q, $table = null) use ($filterTanggal, $bankTujuanId, $sumberDanaIds, $idJenisPembayaran, ) {
            $prefix = $table ? $table . '.' : '';

            $filterTanggal($q);

            if ($bankTujuanId) {
                $q->where($prefix . 'id_bank_tujuan', $bankTujuanId);
            }
            if ($idJenisPembayaran) {
                $q->where($prefix . 'id_jenis_pembayaran', $bankTujuanId);
            }

            if ($sumberDanaIds && is_array($sumberDanaIds) && count($sumberDanaIds) > 0) {
                $q->whereIn($prefix . 'id_sumber_dana', $sumberDanaIds);
            }
        };

        /* ================= DROPDOWN LISTS ================= */
        $tahunList = collect()
            ->merge(DB::table('bank_masuk')->selectRaw('YEAR(tanggal) as tahun')->pluck('tahun'))
            ->merge(DB::table('bank_keluars')->selectRaw('YEAR(tanggal) as tahun')->pluck('tahun'))
            ->unique()->sortDesc()->values();

        $bulanList = collect()
            ->merge(
                DB::table('bank_masuk')
                    ->when($tahun, fn($q) => $q->whereYear('tanggal', $tahun))
                    ->selectRaw('MONTH(tanggal) as bulan')
                    ->pluck('bulan')
            )
            ->merge(
                DB::table('bank_keluars')
                    ->when($tahun, fn($q) => $q->whereYear('tanggal', $tahun))
                    ->selectRaw('MONTH(tanggal) as bulan')
                    ->pluck('bulan')
            )
            ->unique()->sort()->values();

        $tanggalList = collect()
            ->merge(
                DB::table('bank_masuk')
                    ->when($tahun, fn($q) => $q->whereYear('tanggal', $tahun))
                    ->when($bulan, fn($q) => $q->whereMonth('tanggal', $bulan))
                    ->selectRaw('DATE(tanggal) as tanggal')
                    ->pluck('tanggal')
            )
            ->merge(
                DB::table('bank_keluars')
                    ->when($tahun, fn($q) => $q->whereYear('tanggal', $tahun))
                    ->when($bulan, fn($q) => $q->whereMonth('tanggal', $bulan))
                    ->selectRaw('DATE(tanggal) as tanggal')
                    ->pluck('tanggal')
            )
            ->unique()->sort()->values();

        $bankTujuanList = DB::table('bank_tujuan')
            ->where(function ($query) use ($filterTanggal, $sumberDanaIds, $kategoriIds, $idJenisPembayaran) {
                $query->whereExists(function ($sub) use ($filterTanggal, $sumberDanaIds, $kategoriIds, $idJenisPembayaran) {
                    $sub->select(DB::raw(1))
                        ->from('bank_keluars')
                        ->whereColumn('bank_keluars.id_bank_tujuan', 'bank_tujuan.id_bank_tujuan')
                        ->where(function ($q) use ($filterTanggal, $sumberDanaIds, $kategoriIds, $idJenisPembayaran) {
                            $filterTanggal($q);
                            if ($sumberDanaIds && count($sumberDanaIds) > 0) {
                                $q->whereIn('id_sumber_dana', $sumberDanaIds);
                            }
                            if ($kategoriIds && count($kategoriIds) > 0) {
                                $q->whereIn('id_kategori_kriteria', $kategoriIds);
                            }
                            if ($idJenisPembayaran) {
                                $q->where('id_jenis_pembayaran', $idJenisPembayaran);
                            }
                        });
                })
                    ->orWhereExists(function ($sub) use ($filterTanggal, $sumberDanaIds) {
                        $sub->select(DB::raw(1))
                            ->from('bank_masuk')
                            ->whereColumn('bank_masuk.id_bank_tujuan', 'bank_tujuan.id_bank_tujuan')
                            ->where(function ($q) use ($filterTanggal, $sumberDanaIds) {
                                $filterTanggal($q);
                                if ($sumberDanaIds && count($sumberDanaIds) > 0) {
                                    $q->whereIn('id_sumber_dana', $sumberDanaIds);
                                }
                            });
                    });
            })
            ->orderBy('nama_tujuan')
            ->get();

        $sumberDanaList = DB::table('sumber_dana')
            ->where(function ($query) use ($filterTanggal, $bankTujuanId, $kategoriIds, $idJenisPembayaran) {
                $query->whereExists(function ($sub) use ($filterTanggal, $bankTujuanId, $kategoriIds, $idJenisPembayaran) {
                    $sub->select(DB::raw(1))
                        ->from('bank_keluars')
                        ->whereColumn('bank_keluars.id_sumber_dana', 'sumber_dana.id_sumber_dana')
                        ->where(function ($q) use ($filterTanggal, $bankTujuanId, $kategoriIds, $idJenisPembayaran) {
                            $filterTanggal($q);
                            if ($bankTujuanId)
                                $q->where('id_bank_tujuan', $bankTujuanId);
                            if ($kategoriIds && count($kategoriIds) > 0) {
                                $q->whereIn('id_kategori_kriteria', $kategoriIds);
                            }
                            if ($idJenisPembayaran)
                                $q->where('id_jenis_pembayaran', $idJenisPembayaran);
                        });
                })
                    ->orWhereExists(function ($sub) use ($filterTanggal, $bankTujuanId) {
                        $sub->select(DB::raw(1))
                            ->from('bank_masuk')
                            ->whereColumn('bank_masuk.id_sumber_dana', 'sumber_dana.id_sumber_dana')
                            ->where(function ($q) use ($filterTanggal, $bankTujuanId) {
                                $filterTanggal($q);
                                if ($bankTujuanId)
                                    $q->where('id_bank_tujuan', $bankTujuanId);
                            });
                    });
            })
            ->orderBy('nama_sumber_dana')
            ->get();

        $kategoriList = DB::table('kategori_kriteria')
            ->whereExists(function ($sub) use ($filterTanggal, $bankTujuanId, $sumberDanaIds, $idJenisPembayaran) {
                $sub->select(DB::raw(1))
                    ->from('bank_keluars')
                    ->whereColumn('bank_keluars.id_kategori_kriteria', 'kategori_kriteria.id_kategori_kriteria')
                    ->where(function ($q) use ($filterTanggal, $bankTujuanId, $sumberDanaIds, $idJenisPembayaran) {
                        $filterTanggal($q);
                        if ($bankTujuanId)
                            $q->where('id_bank_tujuan', $bankTujuanId);
                        if ($sumberDanaIds && count($sumberDanaIds) > 0) {
                            $q->whereIn('id_sumber_dana', $sumberDanaIds);
                        }
                        if ($idJenisPembayaran)
                            $q->where('id_jenis_pembayaran', $idJenisPembayaran);
                    });
            })
            ->orderBy('nama_kriteria')
            ->get();

        $jenisPembayaranList = DB::table('jenis_pembayarans')
            ->whereExists(function ($sub) use ($filterTanggal, $bankTujuanId, $sumberDanaIds, $kategoriIds) {
                $sub->select(DB::raw(1))
                    ->from('bank_keluars')
                    ->whereColumn('bank_keluars.id_jenis_pembayaran', 'jenis_pembayarans.id_jenis_pembayaran')
                    ->where(function ($q) use ($filterTanggal, $bankTujuanId, $sumberDanaIds, $kategoriIds) {
                        $filterTanggal($q);
                        if ($bankTujuanId)
                            $q->where('id_bank_tujuan', $bankTujuanId);
                        if ($sumberDanaIds && count($sumberDanaIds) > 0) {
                            $q->whereIn('id_sumber_dana', $sumberDanaIds);
                        }
                        if ($kategoriIds && count($kategoriIds) > 0) {
                            $q->whereIn('id_kategori_kriteria', $kategoriIds);
                        }
                    });
            })
            ->orderBy('nama_jenis_pembayaran')
            ->get();

        /* ================= LOGIKA TAMPILAN DATA ================= */
        $showDebet = false;
        $showSaldoAkhir = false;
        $showSAP = false;

        // LOGIKA BARU: 
        // 1 filter atau tanpa filter = tampil DEBET + KREDIT + SALDO AKHIR
        // 2+ filter = tampil KREDIT saja + TOTAL KREDIT

        if ($countActiveFilters == 0) {
            // Tidak ada filter (tampil semua)
            $showDebet = true;
            $showSaldoAkhir = true;
            $showSAP = true;
        } elseif ($countActiveFilters == 1) {
            // 1 filter saja (bank_tujuan, sumber_dana, atau rekapan)
            $showDebet = true;
            $showSaldoAkhir = true;
            $showSAP = true;
        } else {
            // 2 atau lebih filter = hanya kredit
            $showDebet = false;
            $showSaldoAkhir = false;
            $showSAP = false;
        }
        if ($countActiveFilters == 1 && $idJenisPembayaran) {
            $showDebet = false;
            $showSaldoAkhir = false;
            $showSAP = false;
        }

        /* ================= QUERY DATA UTAMA ================= */
        if ($showDebet) {
            // Tampilkan Bank Masuk (Debet) + Bank Keluar (Kredit)
            $bankMasuk = DB::table('bank_masuk')
                ->leftJoin('sumber_dana', 'sumber_dana.id_sumber_dana', '=', 'bank_masuk.id_sumber_dana')
                ->leftJoin('bank_tujuan', 'bank_tujuan.id_bank_tujuan', '=', 'bank_masuk.id_bank_tujuan')
                ->select(
                    'bank_masuk.agenda_tahun',
                    'bank_masuk.id_sumber_dana',
                    'sumber_dana.nama_sumber_dana',
                    'bank_masuk.id_bank_tujuan',
                    'bank_tujuan.nama_tujuan',
                    'bank_masuk.uraian',
                    'bank_masuk.penerima',
                    'bank_masuk.tanggal',
                    'bank_masuk.debet',
                    DB::raw('0 as kredit'),
                    'bank_masuk.no_sap',
                    DB::raw('NULL as nama_kriteria'),
                    DB::raw('NULL as nama_sub_kriteria'),
                    DB::raw('NULL as nama_item_sub_kriteria'),
                    DB::raw('NULL as id_jenis_pembayaran'),
                    DB::raw('NULL as nama_jenis_pembayaran'),
                    DB::raw("'MASUK' as jenis"),
                    DB::raw('bank_masuk.id_bank_masuk as urut_id')
                )
                ->where(function ($q) use ($applyFilterSaldoAwal) {
                    // Gunakan filter saldo awal (tanpa kategori/jenis pembayaran)
                    $applyFilterSaldoAwal($q, 'bank_masuk');
                });

            $bankKeluar = DB::table('bank_keluars')
                ->leftJoin('sumber_dana', 'sumber_dana.id_sumber_dana', '=', 'bank_keluars.id_sumber_dana')
                ->leftJoin('bank_tujuan', 'bank_tujuan.id_bank_tujuan', '=', 'bank_keluars.id_bank_tujuan')
                ->leftJoin('kategori_kriteria', 'kategori_kriteria.id_kategori_kriteria', '=', 'bank_keluars.id_kategori_kriteria')
                ->leftJoin('sub_kriteria', 'sub_kriteria.id_sub_kriteria', '=', 'bank_keluars.id_sub_kriteria')
                ->leftJoin('item_sub_kriteria', 'item_sub_kriteria.id_item_sub_kriteria', '=', 'bank_keluars.id_item_sub_kriteria')
                ->leftJoin('jenis_pembayarans', 'jenis_pembayarans.id_jenis_pembayaran', '=', 'bank_keluars.id_jenis_pembayaran')
                ->select(
                    'bank_keluars.agenda_tahun',
                    'bank_keluars.id_sumber_dana',
                    'sumber_dana.nama_sumber_dana',
                    'bank_keluars.id_bank_tujuan',
                    'bank_tujuan.nama_tujuan',
                    'bank_keluars.uraian',
                    'bank_keluars.penerima',
                    'bank_keluars.tanggal',
                    DB::raw('0 as debet'),
                    'bank_keluars.kredit',
                    'bank_keluars.no_sap',
                    'kategori_kriteria.nama_kriteria',
                    'sub_kriteria.nama_sub_kriteria',
                    'item_sub_kriteria.nama_item_sub_kriteria',
                    'bank_keluars.id_jenis_pembayaran',
                    'jenis_pembayarans.nama_jenis_pembayaran',
                    DB::raw("'KELUAR' as jenis"),
                    DB::raw('bank_keluars.id_bank_keluar as urut_id')
                )
                ->where(function ($q) use ($applyFilterSaldoAwal) {
                    // Gunakan filter saldo awal (tanpa kategori/jenis pembayaran)
                    $applyFilterSaldoAwal($q, 'bank_keluars');
                });

            $data = $bankMasuk
                ->unionAll($bankKeluar)
                ->orderBy('tanggal')
                ->orderBy('urut_id')
                ->get();
        } else {
            // Hanya tampilkan Bank Keluar (Kredit) dengan filter lengkap
            $data = DB::table('bank_keluars')
                ->leftJoin('sumber_dana', 'sumber_dana.id_sumber_dana', '=', 'bank_keluars.id_sumber_dana')
                ->leftJoin('bank_tujuan', 'bank_tujuan.id_bank_tujuan', '=', 'bank_keluars.id_bank_tujuan')
                ->leftJoin('kategori_kriteria', 'kategori_kriteria.id_kategori_kriteria', '=', 'bank_keluars.id_kategori_kriteria')
                ->leftJoin('sub_kriteria', 'sub_kriteria.id_sub_kriteria', '=', 'bank_keluars.id_sub_kriteria')
                ->leftJoin('item_sub_kriteria', 'item_sub_kriteria.id_item_sub_kriteria', '=', 'bank_keluars.id_item_sub_kriteria')
                ->leftJoin('jenis_pembayarans', 'jenis_pembayarans.id_jenis_pembayaran', '=', 'bank_keluars.id_jenis_pembayaran')
                ->select(
                    'bank_keluars.agenda_tahun',
                    'bank_keluars.id_sumber_dana',
                    'sumber_dana.nama_sumber_dana',
                    'bank_keluars.id_bank_tujuan',
                    'bank_tujuan.nama_tujuan',
                    'bank_keluars.uraian',
                    'bank_keluars.penerima',
                    'bank_keluars.tanggal',
                    DB::raw('0 as debet'),
                    'bank_keluars.kredit',
                    'bank_keluars.no_sap',
                    'kategori_kriteria.nama_kriteria',
                    'sub_kriteria.nama_sub_kriteria',
                    'item_sub_kriteria.nama_item_sub_kriteria',
                    'bank_keluars.id_jenis_pembayaran',
                    'jenis_pembayarans.nama_jenis_pembayaran',
                    DB::raw("'KELUAR' as jenis"),
                    DB::raw('bank_keluars.id_bank_keluar as urut_id')
                )
                ->where(function ($q) use ($applyFilter) {
                    // Gunakan filter lengkap (dengan kategori/jenis pembayaran)
                    $applyFilter($q, 'bank_keluars');
                })
                ->orderBy('tanggal')
                ->orderBy('urut_id')
                ->get();
        }

        /* ================= HITUNG SALDO BERJALAN / TOTAL KREDIT ================= */
        if ($showSaldoAkhir) {
            // Mode: Tampil Debet + Kredit + Saldo Akhir
            // Karena $data sudah berisi semua bank_masuk dan bank_keluar yang difilter
            // Kita bisa langsung hitung saldo berjalan
            $saldo = 0;
            foreach ($data as $d) {
                $saldo += ($d->debet ?? 0) - ($d->kredit ?? 0);
                $d->saldo_akhir = $saldo;
            }
        } else {
            // Mode: Hanya Kredit + Total Kredit
            foreach ($data as $d) {
                $d->saldo_akhir = null;
            }
        }

        // Hitung Total Kredit (untuk mode 2+ filter)
        $totalKredit = $data->sum('kredit');

        /* ================= REKAPAN ================= */
        $rekapVA = [];

        if ($request->rekapanVA === 'bank' && $tahun) {
            foreach (BankTujuan::all() as $bank) {
                $debetTotal = DB::table('bank_masuk')
                    ->whereYear('tanggal', $tahun)
                    ->where('id_bank_tujuan', $bank->id_bank_tujuan)
                    ->when($sumberDanaIds && count($sumberDanaIds) > 0, function ($q) use ($sumberDanaIds) {
                        $q->whereIn('id_sumber_dana', $sumberDanaIds);
                    })
                    ->sum('debet');

                $kreditTotal = DB::table('bank_keluars')
                    ->whereYear('tanggal', $tahun)
                    ->where('id_bank_tujuan', $bank->id_bank_tujuan)
                    ->when($sumberDanaIds && count($sumberDanaIds) > 0, function ($q) use ($sumberDanaIds) {
                        $q->whereIn('id_sumber_dana', $sumberDanaIds);
                    })
                    ->sum('kredit');

                $saldo = $debetTotal - $kreditTotal;

                if ($saldo != 0 || $debetTotal != 0 || $kreditTotal != 0) {
                    $rekapVA[] = [
                        'bank' => $bank->nama_tujuan,
                        'saldo_va' => $saldo,
                        'saldo_sap' => 0,
                        'selisih' => $saldo,
                        'keterangan' => "Saldo akhir tahun {$tahun}"
                    ];
                }
            }
        }

        if ($request->rekapanVA === 'va' && $tahun) {
            foreach (SumberDana::all() as $sd) {
                $debetTotal = DB::table('bank_masuk')
                    ->whereYear('tanggal', $tahun)
                    ->where('id_sumber_dana', $sd->id_sumber_dana)
                    ->when($bankTujuanId, function ($q) use ($bankTujuanId) {
                        $q->where('id_bank_tujuan', $bankTujuanId);
                    })
                    ->sum('debet');

                $kreditTotal = DB::table('bank_keluars')
                    ->whereYear('tanggal', $tahun)
                    ->where('id_sumber_dana', $sd->id_sumber_dana)
                    ->when($bankTujuanId, function ($q) use ($bankTujuanId) {
                        $q->where('id_bank_tujuan', $bankTujuanId);
                    })
                    ->sum('kredit');

                $saldo = $debetTotal - $kreditTotal;

                if ($saldo != 0 || $debetTotal != 0 || $kreditTotal != 0) {
                    $rekapVA[] = [
                        'bank' => $sd->nama_sumber_dana,
                        'saldo_va' => $saldo,
                        'saldo_sap' => 0,
                        'selisih' => $saldo,
                        'keterangan' => "Saldo akhir tahun {$tahun}"
                    ];
                }
            }
        }

        // Rekap Kategori Full (dengan filter progresif)
        if ($rekapanVA === 'kategori-full') {
            $dataKategori = DB::table('bank_keluars')
                ->join('kategori_kriteria', 'kategori_kriteria.id_kategori_kriteria', '=', 'bank_keluars.id_kategori_kriteria')
                ->join('sub_kriteria', 'sub_kriteria.id_sub_kriteria', '=', 'bank_keluars.id_sub_kriteria')
                ->join('item_sub_kriteria', 'item_sub_kriteria.id_item_sub_kriteria', '=', 'bank_keluars.id_item_sub_kriteria')
                ->where(function ($q) use ($applyFilter) {
                    $applyFilter($q, 'bank_keluars');
                })
                ->select(
                    'kategori_kriteria.nama_kriteria as kategori',
                    'sub_kriteria.nama_sub_kriteria as sub',
                    'item_sub_kriteria.nama_item_sub_kriteria as item',
                    DB::raw('SUM(bank_keluars.kredit) as kredit')
                )
                ->groupBy('kategori', 'sub', 'item')
                ->orderBy('kategori')
                ->orderBy('sub')
                ->orderBy('item')
                ->get();

            foreach ($dataKategori as $row) {
                $rekapVA[$row->kategori][$row->sub][] = [
                    'item' => $row->item,
                    'kredit' => (float) $row->kredit
                ];
            }
        }

        return view('cash_bank.exportPDF.reportKeluar', compact(
            'data',
            'tahunList',
            'bulanList',
            'tanggalList',
            'bankTujuanList',
            'sumberDanaList',
            'kategoriList',
            'jenisPembayaranList',
            'showDebet',
            'showSaldoAkhir',
            'showSAP',
            'rekapVA',
            'totalKredit',
            'tahun',
            'bulan',
            'tanggalDipilih',
            'bankTujuanId',
            'sumberDanaIds',
            'kategoriIds',
            'idJenisPembayaran',
            'rekapanVA',
            'countActiveFilters'
        ));
    }

}



//    public function getSub($id)
//     {
//         return SubKriteria::where('id_kategori_kriteria', $id)->get();
//     }

//     public function getItem($id)
//     {
//         return ItemSubKriteria::where('id_sub_kriteria', $id)->get();
//     }


//     public function getDokumenDetail($id)
//     {
//     try {
//         $dokumen = DB::connection('mysql_agenda_online')
//             ->table('dokumens')
//             ->select(
//                 'id as dokumen_id',
//                 'uraian_spp as uraian',
//                 'nilai_rupiah',
//                 'dibayar_kepada as penerima',
//                 'jenis_pembayaran as pembayaran',
//                 'kategori',
//                 'jenis_dokumen',
//                 'jenis_sub_pekerjaan'
//             )
//             ->where('id', $id)
//             ->first();

//         if ($dokumen) {
//             $kategori = null;
//             $subKriteria = null;
//             $itemSubKriteria = null;


//             $itemSubKriteria = ItemSubKriteria::where('nama_item_sub_kriteria', $dokumen->jenis_sub_pekerjaan)->first();


//             if (!$itemSubKriteria) {
//                 $itemSubKriteria = ItemSubKriteria::where('nama_item_sub_kriteria', $dokumen->jenis_dokumen)->first();
//             }


//             if ($itemSubKriteria) {
//                 $subKriteria = SubKriteria::find($itemSubKriteria->id_sub_kriteria);


//                 if ($subKriteria) {
//                     $kategori = KategoriKriteria::find($subKriteria->id_kategori_kriteria);
//                 }
//             }


//             if (!$subKriteria) {
//                 $subKriteria = SubKriteria::where('nama_sub_kriteria', $dokumen->jenis_dokumen)->first();
//                 if ($subKriteria) {
//                     $kategori = KategoriKriteria::find($subKriteria->id_kategori_kriteria);
//                 }
//             }

//             if (!$kategori) {
//                 $kategori = KategoriKriteria::where('nama_kriteria', $dokumen->kategori)->first();
//             }


//             $dokumen->kategori_id = $kategori->id_kategori_kriteria ?? null;
//             $dokumen->kategori_nama = $kategori->nama_kriteria ?? $dokumen->kategori;

//             $dokumen->sub_kriteria_id = $subKriteria->id_sub_kriteria ?? null;
//             $dokumen->sub_kriteria_nama = $subKriteria->nama_sub_kriteria ?? $dokumen->jenis_dokumen;

//             $dokumen->item_sub_kriteria_id = $itemSubKriteria->id_item_sub_kriteria ?? null;
//             $dokumen->item_sub_kriteria_nama = $itemSubKriteria->nama_item_sub_kriteria ?? $dokumen->jenis_sub_pekerjaan;

//             // Debug info
//             $dokumen->debug_info = [
//                 'original_kategori' => $dokumen->kategori,
//                 'original_jenis_dokumen' => $dokumen->jenis_dokumen,
//                 'original_jenis_sub_pekerjaan' => $dokumen->jenis_sub_pekerjaan,
//                 'item_found' => $itemSubKriteria ? true : false,
//                 'sub_found' => $subKriteria ? true : false,
//                 'kategori_found' => $kategori ? true : false,
//             ];
//         }

//         return response()->json([
//             'success' => true,
//             'data' => $dokumen
//         ]);
//     } catch (\Exception $e) {
//         return response()->json([
//             'success' => false,
//             'message' => 'Data tidak ditemukan: ' . $e->getMessage()
//         ], 404);
//     }

// }
