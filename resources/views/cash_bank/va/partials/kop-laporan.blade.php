{{--
    Kop laporan bersama halaman VA (Cash Flow & Detail Transaksi Virtual Account).
    Dipakai bersama supaya kedua halaman dijamin tampil sama persis.

    Parameter:
      $judul   — nama laporan, mis. "Laporan Arus Kas"
      $unit    — nama VA / unit kebun
      $periode — baris keterangan periode (opsional). Baris ini diberi
                 id="kopPeriode" agar bisa diperbarui dari JS saat filter
                 berubah tanpa memuat ulang halaman.
--}}
@push('styles')
    <style>
        .cf-kop {
            text-align: center;
            border-bottom: 3px double var(--cf-navy, #1E3A5F);
            padding-bottom: 10px;
            margin-bottom: 18px;
        }

        .cf-kop .cf-perusahaan {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .12em;
            color: var(--cf-navy, #1E3A5F);
            margin-bottom: 2px;
        }

        .cf-kop .cf-judul {
            font-size: 17px;
            font-weight: 700;
            color: var(--cf-navy, #1E3A5F);
            text-transform: uppercase;
            letter-spacing: .05em;
            margin-bottom: 2px;
        }

        .cf-kop .cf-unit {
            font-size: 12px;
            font-weight: 600;
            color: #37506e;
        }

        .cf-kop .cf-periode {
            font-size: 11px;
            color: #6B7280;
            font-style: italic;
        }
    </style>
@endpush

<div class="cf-kop">
    <div class="cf-perusahaan">PT PERKEBUNAN NUSANTARA IV &mdash; REGIONAL V</div>
    <div class="cf-judul">{{ $judul }}</div>
    <div class="cf-unit">{{ $unit }}</div>
    @isset($periode)
        <div class="cf-periode" id="kopPeriode">{{ $periode }}</div>
    @endisset
</div>
