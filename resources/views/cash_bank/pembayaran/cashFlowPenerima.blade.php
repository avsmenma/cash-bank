<!-- Tabel DataTable -->
<style>
    /* Header & baris total navy, konsisten dengan tabel lain */
    #example1 thead th,
    #example1 tfoot th {
        background-color: #1e3a5f !important;
        color: #ffffff !important;
        border-color: #17324b !important;
    }
</style>
<div class=" table-responsive">


<div class="card-body">
    <table id="example1" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>PENERIMAAN</th>
                <th>Januari</th>
                <th>Februari</th>
                <th>Maret</th>
                <th>April</th>
                <th>Mei</th>
                <th>Juni</th>
                <th>Juli</th>
                <th>Agustus</th>
                <th>September</th>
                <th>Oktober</th>
                <th>November</th>
                <th>Desember</th>
                <th>Total</th>
            </tr>
        </thead>
        @php
            $totalBulan = array_fill(1, 12, 0);
        @endphp

        <tbody>
        @foreach($result as $kategori => $bulan)
        <tr>
            <td><strong>{{ $kategori }}</strong></td>

            @for($i = 1; $i <= 12; $i++)
                @php
                    $nilai = $bulan[$i] ?? 0;
                    $totalBulan[$i] += $nilai;
                @endphp
                <td class="text-right">
                    {{ number_format($nilai, 0, ',', '.') }}
                </td>
            @endfor

            <td class="text-right font-weight-bold">
                {{ number_format(array_sum($bulan), 0, ',', '.') }}
            </td>
        </tr>
        @endforeach
        </tbody>

        <tfoot class="font-weight-bold">
        <tr>
            <th>Total</th>
            @for($i = 1; $i <= 12; $i++)
                <th class="text-right">
                    {{ number_format($totalBulan[$i], 0, ',', '.') }}
                </th>
            @endfor
            <th class="text-right">
                {{ number_format(array_sum($totalBulan), 0, ',', '.') }}
            </th>
        </tr>
        </tfoot>

    </table>
</div>
</div>