@extends('layouts.index')

@section('content')
<div class="content-wrapper" style="background: #f8fafc; padding: 24px 28px; min-height: 100vh; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    <div class="container-fluid p-0">

        {{-- ===================== 1. HEADER HALAMAN ===================== --}}
        <div class="d-flex align-items-center mb-4" style="gap: 14px;">
            <div style="font-size: 26px; color: #0f172a; line-height: 1;">
                <i class="fas fa-university"></i>
            </div>
            <div>
                <h4 class="mb-0 font-weight-bold" style="color: #0f172a; font-size: 22px; letter-spacing: -0.02em;">
                    Saldo Kas &amp; Bank
                </h4>
                <div style="color: #64748b; font-size: 13px; margin-top: 2px;">
                    Ringkasan saldo dan rekonsiliasi rekening regional.
                </div>
            </div>
        </div>

        {{-- ===================== 2. FILTER BAR ===================== --}}
        <div class="card mb-4 shadow-sm" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 1px 3px 0 rgba(0,0,0,0.04);">
            <div class="card-body py-3 px-4">
                <form id="formFilterBankV2" method="GET" action="{{ route('dashboard.bank-v2.index') }}" class="d-flex flex-wrap align-items-end" style="gap: 12px;">

                    {{-- Dropdown Tahun --}}
                    <div style="min-width: 140px; flex: 1 1 140px;">
                        <label class="d-block mb-1 font-weight-bold" style="font-size: 11.5px; color: #475569; letter-spacing: 0.02em;">
                            Tahun
                        </label>
                        <select name="tahun" id="filterTahun" class="form-control form-control-sm" style="border-radius: 8px; border: 1px solid #cbd5e1; height: 38px; font-size: 13px; color: #1e293b;">
                            <option value="">Semua Tahun</option>
                            @foreach($tahunList as $y)
                                <option value="{{ $y }}" {{ (string)$tahun === (string)$y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Dropdown Bulan --}}
                    <div style="min-width: 140px; flex: 1 1 140px;">
                        <label class="d-block mb-1 font-weight-bold" style="font-size: 11.5px; color: #475569; letter-spacing: 0.02em;">
                            Bulan
                        </label>
                        <select name="bulan" id="filterBulan" class="form-control form-control-sm" style="border-radius: 8px; border: 1px solid #cbd5e1; height: 38px; font-size: 13px; color: #1e293b;">
                            <option value="">Semua Bulan</option>
                            @foreach($bulanList as $k => $b)
                                <option value="{{ $k }}" {{ (string)$bulan === (string)$k ? 'selected' : '' }}>{{ $b }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Input Dari Tanggal --}}
                    <div style="min-width: 150px; flex: 1 1 150px;">
                        <label class="d-block mb-1 font-weight-bold" style="font-size: 11.5px; color: #475569; letter-spacing: 0.02em;">
                            Dari tanggal
                        </label>
                        <div class="input-group input-group-sm">
                            <input type="text" name="tgl_dari" id="filterTglDari" value="{{ $tglDari }}" placeholder="dd-mm-yyyy"
                                   class="form-control" style="border-radius: 8px; border: 1px solid #cbd5e1; height: 38px; font-size: 13px; color: #1e293b; background: #fff;">
                        </div>
                    </div>

                    {{-- Input Sampai Tanggal --}}
                    <div style="min-width: 150px; flex: 1 1 150px;">
                        <label class="d-block mb-1 font-weight-bold" style="font-size: 11.5px; color: #475569; letter-spacing: 0.02em;">
                            Sampai tanggal
                        </label>
                        <div class="input-group input-group-sm">
                            <input type="text" name="tgl_sampai" id="filterTglSampai" value="{{ $tglSampai }}" placeholder="dd-mm-yyyy"
                                   class="form-control" style="border-radius: 8px; border: 1px solid #cbd5e1; height: 38px; font-size: 13px; color: #1e293b; background: #fff;">
                        </div>
                    </div>

                    {{-- Tombol Terapkan & Reset --}}
                    <div class="d-flex align-items-center" style="gap: 8px;">
                        <button type="submit" class="btn btn-primary" style="height: 38px; padding: 0 18px; border-radius: 8px; background: #0d6efd; border-color: #0d6efd; font-weight: 600; font-size: 13px; display: inline-flex; align-items: center; gap: 7px; box-shadow: 0 1px 2px rgba(13,110,253,0.15);">
                            <i class="fas fa-filter" style="font-size: 12px;"></i>
                            <span>Terapkan</span>
                        </button>
                        <a href="{{ route('dashboard.bank-v2.index') }}" class="btn btn-light" style="height: 38px; padding: 0 16px; border-radius: 8px; border: 1px solid #cbd5e1; background: #ffffff; color: #334155; font-weight: 600; font-size: 13px; display: inline-flex; align-items: center; gap: 7px;">
                            <i class="fas fa-sync-alt" style="font-size: 12px; color: #64748b;"></i>
                            <span>Reset</span>
                        </a>
                    </div>

                    {{-- Tombol Export (Sebelah Kanan) --}}
                    <div class="ml-auto">
                        <div class="dropdown">
                            <button type="button" class="btn btn-light dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                    style="height: 38px; padding: 0 16px; border-radius: 8px; border: 1px solid #cbd5e1; background: #ffffff; color: #0d6efd; font-weight: 600; font-size: 13px; display: inline-flex; align-items: center; gap: 8px;">
                                <i class="fas fa-file-export" style="font-size: 13px;"></i>
                                <span>Export</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right shadow-sm" style="border-radius: 8px; border: 1px solid #e2e8f0; font-size: 13px; padding: 6px 0;">
                                <a class="dropdown-item py-2 px-3 text-success font-weight-bold" href="javascript:void(0)" onclick="openExportModalV2('excel')">
                                    <i class="fas fa-file-excel mr-2"></i> Export Excel
                                </a>
                                <a class="dropdown-item py-2 px-3 text-danger font-weight-bold" href="javascript:void(0)" onclick="openExportModalV2('pdf')">
                                    <i class="fas fa-file-pdf mr-2"></i> Export PDF
                                </a>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>

        {{-- ===================== 3. KARTU METRIK RINGKASAN (KPI CARDS) ===================== --}}
        <div class="row mb-4" style="margin-left: -8px; margin-right: -8px;">

            {{-- Kartu 1: Total Saldo Bank --}}
            <div class="col-md-4 mb-3 mb-md-0 px-2">
                <div class="card h-100 shadow-sm" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px 22px; box-shadow: 0 1px 3px 0 rgba(0,0,0,0.03);">
                    <div class="d-flex align-items-center" style="gap: 16px;">
                        <div style="width: 52px; height: 52px; border-radius: 50%; background: #e0edff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                            <i class="fas fa-university"></i>
                        </div>
                        <div style="min-width: 0;">
                            <div style="font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 2px;">
                                Total Saldo Bank
                            </div>
                            <div style="font-size: 22px; font-weight: 800; color: #0f172a; line-height: 1.2; letter-spacing: -0.01em;">
                                Rp {{ number_format($totalSaldoBank, 0, ',', '.') }}
                            </div>
                            <div style="font-size: 11.5px; color: #94a3b8; margin-top: 4px;">
                                Seluruh rekening bank
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kartu 2: Saldo VA Unit --}}
            <div class="col-md-4 mb-3 mb-md-0 px-2">
                <div class="card h-100 shadow-sm" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px 22px; box-shadow: 0 1px 3px 0 rgba(0,0,0,0.03);">
                    <div class="d-flex align-items-center" style="gap: 16px;">
                        <div style="width: 52px; height: 52px; border-radius: 50%; background: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <div style="min-width: 0;">
                            <div style="font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 2px;">
                                Saldo VA Unit
                            </div>
                            <div style="font-size: 22px; font-weight: 800; color: #0f172a; line-height: 1.2; letter-spacing: -0.01em;">
                                Rp {{ number_format($totalSaldoVA, 0, ',', '.') }}
                            </div>
                            <div style="font-size: 11.5px; color: #94a3b8; margin-top: 4px;">
                                Bagian dari rekening 408
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kartu 3: Digunakan Region --}}
            <div class="col-md-4 px-2">
                <div class="card h-100 shadow-sm" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px 22px; box-shadow: 0 1px 3px 0 rgba(0,0,0,0.03);">
                    <div class="d-flex align-items-center" style="gap: 16px;">
                        <div style="width: 52px; height: 52px; border-radius: 50%; background: #ede9fe; color: #7c3aed; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                            <i class="fas fa-users"></i>
                        </div>
                        <div style="min-width: 0;">
                            <div style="font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 2px;">
                                Digunakan Region
                            </div>
                            <div style="font-size: 22px; font-weight: 800; color: #0f172a; line-height: 1.2; letter-spacing: -0.01em;">
                                Rp {{ number_format($saldoRegion, 0, ',', '.') }}
                            </div>
                            <div style="font-size: 11.5px; color: #94a3b8; margin-top: 4px;">
                                Saldo rekening 408 dikurangi VA
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- ===================== 4. TABEL REKENING BANK ===================== --}}
        <div class="card mb-4 shadow-sm" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 1px 3px 0 rgba(0,0,0,0.04); overflow: hidden;">
            <div class="card-body p-0">
                <div class="px-4 pt-4 pb-2">
                    <h6 class="font-weight-bold mb-0" style="color: #0f172a; font-size: 15px;">
                        Rekening Bank
                    </h6>
                    <div style="color: #64748b; font-size: 12px; margin-top: 2px;">
                        Rincian saldo per rekening.
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table mb-0" style="font-size: 13px; border-top: 1px solid #e2e8f0;">
                        <thead>
                            <tr style="background: #f8fafc; color: #475569; font-weight: 600; font-size: 12px; border-bottom: 1px solid #e2e8f0;">
                                <th class="text-center" style="width: 50px; padding: 12px 14px;">No.</th>
                                <th style="padding: 12px 14px;">Nama Bank</th>
                                <th style="padding: 12px 14px; width: 260px;">Nomor Rekening</th>
                                <th class="text-right" style="padding: 12px 18px; width: 220px;">Saldo (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sumberDanaList as $idx => $sd)
                            @php
                                $noRek = '';
                                $namaBersih = $sd->nama_sumber_dana;
                                if (preg_match('/\*\s*([\d\-\/]+)\s*$/', $sd->nama_sumber_dana, $m)) {
                                    $noRek = trim($m[1]);
                                    $namaBersih = trim(preg_replace('/\s*\*\s*[\d\-\/]+\s*$/', '', $sd->nama_sumber_dana));
                                }
                            @endphp
                            <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s ease;">
                                <td class="text-center align-middle" style="color: #64748b; padding: 12px 14px;">{{ $idx + 1 }}</td>
                                <td class="align-middle font-weight-500" style="color: #1e293b; padding: 12px 14px;">{{ $namaBersih }}</td>
                                <td class="align-middle" style="color: #64748b; padding: 12px 14px; font-family: -apple-system, BlinkMacSystemFont, monospace; font-size: 12.5px;">{{ $noRek ?: '-' }}</td>
                                <td class="text-right align-middle font-weight-bold" style="color: #0f172a; padding: 12px 18px;">
                                    @if($sd->saldo_va < 0)
                                        <span style="color: #ef4444;">({{ number_format(abs($sd->saldo_va), 0, ',', '.') }})</span>
                                    @elseif($sd->saldo_va > 0)
                                        {{ number_format($sd->saldo_va, 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Tidak ada data rekening bank</td>
                            </tr>
                            @endforelse

                            {{-- BARIS TOTAL SALDO BANK --}}
                            <tr style="background: #e0edff; border-top: 2px solid #bfdbfe;">
                                <td colspan="3" class="font-weight-bold" style="color: #1e40af; padding: 12px 18px; font-size: 13.5px;">
                                    Total Saldo Bank
                                </td>
                                <td class="text-right font-weight-bold" style="color: #1e40af; padding: 12px 18px; font-size: 14px;">
                                    @if($totalSaldoBank < 0)
                                        ({{ number_format(abs($totalSaldoBank), 0, ',', '.') }})
                                    @else
                                        {{ number_format($totalSaldoBank, 0, ',', '.') }}
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ===================== 5. TABEL REKONSILIASI VA & SAP ===================== --}}
        <div class="card mb-4 shadow-sm" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 1px 3px 0 rgba(0,0,0,0.04); overflow: hidden;">
            <div class="card-body p-0">
                <div class="d-flex flex-wrap align-items-center justify-content-between px-4 pt-4 pb-3" style="gap: 12px;">
                    <div>
                        <h6 class="font-weight-bold mb-0" style="color: #0f172a; font-size: 15px;">
                            Rekonsiliasi VA &amp; SAP
                        </h6>
                    </div>
                    {{-- Input Pencarian Realtime --}}
                    <div style="position: relative; width: 260px; max-width: 100%;">
                        <i class="fas fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 12px; pointer-events: none;"></i>
                        <input type="text" id="vaSearchInput" placeholder="Cari nama unit..." class="form-control form-control-sm"
                               style="padding-left: 34px; border-radius: 8px; border: 1px solid #cbd5e1; height: 36px; font-size: 12.5px; color: #1e293b; background: #fff;">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table mb-0" id="tblRekonsiliasiVA" style="font-size: 13px; border-top: 1px solid #e2e8f0;">
                        <thead>
                            <tr style="background: #f8fafc; color: #475569; font-weight: 600; font-size: 12px; border-bottom: 1px solid #e2e8f0;">
                                <th class="text-center" style="width: 50px; padding: 12px 14px;">No.</th>
                                <th style="padding: 12px 14px; min-width: 220px;">Unit / Virtual Account</th>
                                <th class="text-right" style="padding: 12px 16px; width: 200px;">Saldo Akhir (Rp)</th>
                                <th class="text-right" style="padding: 12px 16px; width: 220px;">Saldo SAP (Rp)</th>
                                <th class="text-right" style="padding: 12px 18px; width: 200px;">Selisih (Rp)</th>
                            </tr>
                        </thead>
                        <tbody id="vaTableBody">
                            @forelse($bankVAList as $index => $va)
                            @php
                                $vaNomor = '';
                                $unitNama = $va->nama_tujuan;
                                if (preg_match('/^(\d+)\s*-\s*(.+)$/', $va->nama_tujuan, $matches)) {
                                    $vaNomor = trim($matches[1]);
                                    $unitNama = trim($matches[2]);
                                }
                            @endphp
                            <tr class="va-row" data-id="{{ $va->id_bank_tujuan }}" data-unit="{{ strtolower($unitNama) }}" data-va="{{ $vaNomor }}"
                                style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s ease;">
                                <td class="text-center align-middle va-row-no" style="color: #64748b; padding: 12px 14px;">
                                    {{ $index + 1 }}
                                </td>
                                <td class="align-middle" style="padding: 12px 14px;">
                                    <div class="font-weight-bold" style="color: #0f172a; font-size: 13px; letter-spacing: -0.01em;">
                                        {{ $unitNama }}
                                    </div>
                                    <div style="color: #64748b; font-size: 11.5px; font-family: monospace; margin-top: 1px;">
                                        {{ $vaNomor ?: $va->nama_tujuan }}
                                    </div>
                                </td>
                                <td class="text-right align-middle va-saldo-akhir" style="padding: 12px 16px; font-weight: 600; color: #1e293b;" data-raw="{{ $va->saldo }}">
                                    @if($va->saldo < 0)
                                        <span style="color: #ef4444;">({{ number_format(abs($va->saldo), 0, ',', '.') }})</span>
                                    @elseif($va->saldo > 0)
                                        {{ number_format($va->saldo, 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </td>

                                {{-- OPSI A: Interactive Inline Editable Saldo SAP --}}
                                <td class="text-right align-middle" style="padding: 8px 16px;">
                                    <div class="d-inline-flex align-items-center justify-content-end" style="position: relative; width: 100%; max-width: 170px;">
                                        <input type="text"
                                               class="form-control form-control-sm text-right sap-input-v2 font-weight-500"
                                               data-id="{{ $va->id_bank_tujuan }}"
                                               data-original="{{ $va->sap_nilai != 0 ? number_format($va->sap_nilai, 0, ',', '.') : '' }}"
                                               value="{{ $va->sap_nilai != 0 ? number_format($va->sap_nilai, 0, ',', '.') : '-' }}"
                                               style="border-radius: 6px; border: 1px solid transparent; background: transparent; height: 32px; font-size: 13px; color: #0f172a; padding: 4px 8px;"
                                               title="Dobel-klik atau klik untuk edit nilai SAP">
                                        <span class="sap-status-indicator" style="position: absolute; right: -18px; display: none; font-size: 12px;"></span>
                                    </div>
                                </td>

                                <td class="text-right align-middle font-weight-bold va-selisih-col" style="padding: 12px 18px;" data-raw="{{ $va->selisih }}">
                                    @if($va->selisih < 0)
                                        <span style="color: #ef4444;">({{ number_format(abs($va->selisih), 0, ',', '.') }})</span>
                                    @elseif($va->selisih > 0)
                                        <span style="color: #0f172a;">{{ number_format($va->selisih, 0, ',', '.') }}</span>
                                    @else
                                        <span style="color: #64748b;">-</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr id="emptyVaRow">
                                <td colspan="5" class="text-center text-muted py-4">Tidak ada data Bank Virtual Account</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Footer Info & Status Pagination (Menampilkan semua 35 unit) --}}
                <div class="d-flex align-items-center justify-content-between px-4 py-3" style="background: #ffffff; border-top: 1px solid #f1f5f9; font-size: 12.5px; color: #64748b;">
                    <div>
                        Menampilkan <span id="vaCountDisplay">{{ count($bankVAList) }}</span> dari {{ count($bankVAList) }} data
                    </div>
                    <div class="d-flex align-items-center" style="gap: 4px;">
                        <button class="btn btn-sm btn-light disabled" style="border: 1px solid #e2e8f0; border-radius: 6px; padding: 2px 10px; font-size: 12px; color: #94a3b8; background: #fff;" disabled>
                            &lt;
                        </button>
                        <button class="btn btn-sm" style="border-radius: 6px; padding: 2px 10px; font-size: 12px; background: #eff6ff; border: 1px solid #bfdbfe; color: #2563eb; font-weight: 600;">
                            1
                        </button>
                        <button class="btn btn-sm btn-light disabled" style="border: 1px solid #e2e8f0; border-radius: 6px; padding: 2px 10px; font-size: 12px; color: #94a3b8; background: #fff;" disabled>
                            &gt;
                        </button>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

{{-- ===================== MODAL EXPORT ===================== --}}
<div class="modal fade" id="modalExportV2" tabindex="-1" role="dialog" aria-labelledby="modalExportV2Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:460px;">
        <div class="modal-content" style="border-top:4px solid #0d6efd; border-radius:10px; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
            <div class="modal-header py-3" style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                <h6 class="modal-title font-weight-bold text-dark mb-0" id="modalExportV2Label">
                    <i class="fas fa-file-export mr-2 text-primary"></i>
                    <span id="modalExportV2Title">Export Dokumen</span>
                </h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body px-4 py-3">
                <p class="text-muted small mb-3">
                    <i class="fas fa-info-circle mr-1"></i>
                    Lengkapi data berikut sebelum mengekspor dokumen.
                </p>

                {{-- Tanggal Dokumen --}}
                <div class="form-group mb-3">
                    <label class="small font-weight-bold text-dark mb-1">
                        <i class="fas fa-calendar-alt mr-1 text-primary"></i>Tanggal Dokumen
                    </label>
                    <div class="d-flex align-items-center" style="gap:6px;">
                        <span class="text-muted" style="white-space:nowrap; font-size:13px;">Pontianak,</span>
                        <select id="selHariV2" class="form-control form-control-sm" style="width:65px; font-size:12px; border-radius: 6px;">
                            @php
                                $hariSekarang = (int) date('j');
                                $bulanSekarang = (int) date('n');
                                $tahunSekarang = (int) date('Y');
                            @endphp
                            @for($h = 1; $h <= 31; $h++)
                                <option value="{{ $h }}" {{ $h == $hariSekarang ? 'selected' : '' }}>{{ $h }}</option>
                            @endfor
                        </select>
                        <select id="selBulanV2" class="form-control form-control-sm" style="width:110px; font-size:12px; border-radius: 6px;">
                            @foreach($bulanList as $no => $namaB)
                                <option value="{{ $namaB }}" {{ $no == $bulanSekarang ? 'selected' : '' }}>{{ $namaB }}</option>
                            @endforeach
                        </select>
                        <select id="selTahunV2" class="form-control form-control-sm" style="width:80px; font-size:12px; border-radius: 6px;">
                            @for($y = $tahunSekarang - 3; $y <= $tahunSekarang + 2; $y++)
                                <option value="{{ $y }}" {{ $y == $tahunSekarang ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                {{-- Nama Penandatangan --}}
                <div class="form-group mb-3">
                    <label class="small font-weight-bold text-dark mb-1">
                        <i class="fas fa-user mr-1 text-primary"></i>Nama Penandatangan
                    </label>
                    <input type="text" id="inpNamaV2" class="form-control form-control-sm" value="Herry Wahyudi" style="font-size:12px; border-radius: 6px;">
                </div>

                {{-- Jabatan --}}
                <div class="form-group mb-0">
                    <label class="small font-weight-bold text-dark mb-1">
                        <i class="fas fa-id-badge mr-1 text-primary"></i>Jabatan
                    </label>
                    <input type="text" id="inpJabatanV2" class="form-control form-control-sm" value="Kepala Bagian Akuntansi &amp; Keuangan" style="font-size:12px; border-radius: 6px;">
                </div>
            </div>
            <div class="modal-footer py-2" style="background:#f8fafc; border-top:1px solid #e2e8f0;">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" style="border-radius: 6px;">
                    <i class="fas fa-times mr-1"></i>Batal
                </button>
                <button type="button" class="btn btn-sm" id="btnDoExportV2" style="min-width:120px; border-radius: 6px;">
                    <i class="fas fa-download mr-1"></i>
                    <span id="btnExportV2Label">Export</span>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Styling interaktif baris tabel */
    .table tbody tr:hover {
        background-color: #f8fafc !important;
    }
    .sap-input-v2:hover {
        border-color: #cbd5e1 !important;
        background: #ffffff !important;
        cursor: text;
    }
    .sap-input-v2:focus {
        border-color: #0d6efd !important;
        background: #ffffff !important;
        box-shadow: 0 0 0 2px rgba(13,110,253,0.15) !important;
        outline: none;
    }
    .sap-saving {
        background: #fffbeb !important;
        border-color: #fcd34d !important;
    }
    .sap-saved {
        background: #f0fdf4 !important;
        border-color: #86efac !important;
    }
    .sap-error {
        background: #fef2f2 !important;
        border-color: #fca5a5 !important;
    }
</style>

@push('scripts')
<script>
$(document).ready(function () {
    'use strict';

    // ── 1. FLATPICKR UNTUK FILTER TANGGAL ──
    var fpDari, fpSampai;
    if (typeof flatpickr !== 'undefined') {
        fpDari = flatpickr("#filterTglDari", {
            dateFormat: "d-m-Y",
            allowInput: true,
            locale: "id"
        });
        fpSampai = flatpickr("#filterTglSampai", {
            dateFormat: "d-m-Y",
            allowInput: true,
            locale: "id"
        });
    }

    var selTahun = document.getElementById('filterTahun');
    var selBulan = document.getElementById('filterBulan');
    var inpDari = document.getElementById('filterTglDari');
    var inpSampai = document.getElementById('filterTglSampai');

    function syncDates() {
        var y = selTahun.value || new Date().getFullYear();
        var m = selBulan.value;
        if (m) {
            var mm = String(m).padStart(2, '0');
            var lastDay = new Date(parseInt(y), parseInt(m), 0).getDate();
            var d1 = '01-' + mm + '-' + y;
            var d2 = String(lastDay).padStart(2, '0') + '-' + mm + '-' + y;
            if (fpDari) { fpDari.setDate(d1); } else { inpDari.value = d1; }
            if (fpSampai) { fpSampai.setDate(d2); } else { inpSampai.value = d2; }
        } else if (selTahun.value) {
            var d1 = '01-01-' + y;
            var d2 = '31-12-' + y;
            if (fpDari) { fpDari.setDate(d1); } else { inpDari.value = d1; }
            if (fpSampai) { fpSampai.setDate(d2); } else { inpSampai.value = d2; }
        }
    }

    if (selTahun) selTahun.addEventListener('change', syncDates);
    if (selBulan) selBulan.addEventListener('change', syncDates);

    // ── 2. LIVE SEARCH UNTUK TABEL REKONSILIASI VA & SAP ──
    var searchInput = document.getElementById('vaSearchInput');
    var vaRows = document.querySelectorAll('.va-row');
    var countDisplay = document.getElementById('vaCountDisplay');

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            var query = this.value.trim().toLowerCase();
            var visibleCount = 0;

            vaRows.forEach(function (row) {
                var unit = row.getAttribute('data-unit') || '';
                var va = row.getAttribute('data-va') || '';
                if (unit.indexOf(query) !== -1 || va.indexOf(query) !== -1) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            if (countDisplay) {
                countDisplay.textContent = visibleCount;
            }
        });
    }

    // ── 3. INLINE EDITING SALDO SAP (OPSI A) ──
    function idNum(v) {
        return (Math.round(Number(v) || 0)).toLocaleString('id-ID');
    }

    $('.sap-input-v2').on('focus', function () {
        var raw = $(this).val().replace(/[^\d]/g, '');
        $(this).val(raw === '0' || raw === '' ? '' : raw);
    });

    $('.sap-input-v2').on('blur', function () {
        var $inp = $(this);
        var id = $inp.data('id');
        var valStr = $inp.val().replace(/[^\d]/g, '');
        var numVal = Math.round(Number(valStr) || 0);

        var original = $inp.attr('data-original') || '';
        var origNum = Math.round(Number(original.replace(/[^\d]/g, '')) || 0);

        // Jika tidak berubah, kembalikan tampilan terformat
        if (numVal === origNum && $inp.attr('data-blurred-once')) {
            $inp.val(numVal > 0 ? idNum(numVal) : '-');
            return;
        }
        $inp.attr('data-blurred-once', 'true');

        $inp.val(numVal > 0 ? idNum(numVal) : '-');
        $inp.attr('data-original', numVal > 0 ? idNum(numVal) : '');

        // Hitung ulang Selisih di baris terkait
        var $row = $inp.closest('tr');
        var saldoAkhir = Number($row.find('.va-saldo-akhir').data('raw')) || 0;
        var selisih = saldoAkhir - numVal;
        var $selisihCol = $row.find('.va-selisih-col');

        if (selisih < 0) {
            $selisihCol.html('<span style="color: #ef4444;">(' + idNum(Math.abs(selisih)) + ')</span>');
        } else if (selisih > 0) {
            $selisihCol.html('<span style="color: #0f172a;">' + idNum(selisih) + '</span>');
        } else {
            $selisihCol.html('<span style="color: #64748b;">-</span>');
        }

        // Simpan ke server via AJAX
        $inp.removeClass('sap-saved sap-error').addClass('sap-saving');
        $.ajax({
            url: "{{ url('/daftarBank') }}/" + id + "/sap",
            method: 'PATCH',
            data: { sap: numVal },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function () {
                $inp.removeClass('sap-saving').addClass('sap-saved');
                setTimeout(function () {
                    $inp.removeClass('sap-saved');
                }, 900);
            },
            error: function () {
                $inp.removeClass('sap-saving').addClass('sap-error');
            }
        });
    });

    $('.sap-input-v2').on('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            $(this).blur();
        }
    });

    // ── 4. EKSPOR DOKUMEN ──
    var _exportTypeV2 = 'excel';
    window.openExportModalV2 = function (type) {
        _exportTypeV2 = type;
        if (type === 'excel') {
            document.getElementById('modalExportV2Title').textContent = 'Export Excel';
            document.getElementById('btnExportV2Label').textContent   = 'Export Excel';
            document.getElementById('btnDoExportV2').className        = 'btn btn-success btn-sm';
            document.getElementById('btnDoExportV2').querySelector('i').className = 'fas fa-file-excel mr-1';
        } else {
            document.getElementById('modalExportV2Title').textContent = 'Export PDF';
            document.getElementById('btnExportV2Label').textContent   = 'Export PDF';
            document.getElementById('btnDoExportV2').className        = 'btn btn-danger btn-sm';
            document.getElementById('btnDoExportV2').querySelector('i').className = 'fas fa-file-pdf mr-1';
        }
        $('#modalExportV2').modal('show');
    };

    var btnDoExportV2 = document.getElementById('btnDoExportV2');
    if (btnDoExportV2) {
        btnDoExportV2.addEventListener('click', function () {
            var hari    = document.getElementById('selHariV2').value;
            var bulan   = document.getElementById('selBulanV2').value;
            var tahun   = document.getElementById('selTahunV2').value;
            var nama    = document.getElementById('inpNamaV2').value;
            var jabatan = document.getElementById('inpJabatanV2').value;

            var tanggal = 'Pontianak, ' + hari + ' ' + bulan + ' ' + tahun;

            var params = new URLSearchParams({
                tanggal: tanggal,
                nama: nama,
                jabatan: jabatan
            });

            var urlParams = new URLSearchParams(window.location.search);
            ['tahun', 'bulan', 'tgl_dari', 'tgl_sampai'].forEach(function(key) {
                if (urlParams.has(key) && urlParams.get(key)) {
                    params.set(key, urlParams.get(key));
                }
            });

            $('#modalExportV2').modal('hide');

            if (_exportTypeV2 === 'excel') {
                window.location.href = '{{ route("dashboard.bank.excel") }}?' + params.toString();
            } else {
                window.open('{{ route("dashboard.bank.pdf") }}?' + params.toString(), '_blank');
            }
        });
    }

});
</script>
@endpush
@endsection
