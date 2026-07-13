<?php

namespace App\Http\Controllers;

use App\Models\BankTujuan;
use App\Models\BankMasuk;
use App\Models\BankKeluar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VADashboardController extends Controller
{
    /**
     * Show the Detail Transaksi VA page for the logged-in VA user.
     */
    public function index()
    {
        $user = Auth::user();
        $id = $user->id_bank_tujuan;

        $va = BankTujuan::findOrFail($id);

        // Get Bank Masuk transactions for this VA
        $masuk = BankMasuk::where('id_bank_tujuan', $id)
            ->select('tanggal', 'penerima', 'uraian', 'debet', 'kredit', 'created_at')
            ->get()
            ->map(function ($item) {
                return [
                    'tanggal' => $item->tanggal,
                    'created_at' => $item->created_at,
                    'penerima' => $item->penerima,
                    'uraian' => $item->uraian,
                    'debet' => (float) $item->debet,
                    'kredit' => 0,
                    'tipe' => 'masuk',
                ];
            });

        // Get Bank Keluar transactions for this VA
        $keluar = BankKeluar::where('id_bank_tujuan', $id)
            ->select('tanggal', 'penerima', 'uraian', 'debet', 'kredit', 'created_at')
            ->get()
            ->map(function ($item) {
                return [
                    'tanggal' => $item->tanggal,
                    'created_at' => $item->created_at,
                    'penerima' => $item->penerima,
                    'uraian' => $item->uraian,
                    'debet' => 0,
                    'kredit' => (float) $item->kredit,
                    'tipe' => 'keluar',
                ];
            });

        // Merge and sort by date ascending
        $transactions = $masuk->toBase()->merge($keluar->toBase())
            ->sortBy(['tanggal', 'created_at'])
            ->values();

        // Compute running total (saldo akhir)
        $saldo = 0;
        $transactions = $transactions->map(function ($item) use (&$saldo) {
            $saldo = $saldo + $item['debet'] - $item['kredit'];
            $item['saldo'] = $saldo;
            return $item;
        });

        // Daftar tahun untuk dropdown filter
        $years = $transactions->pluck('tanggal')
            ->filter()
            ->map(fn ($t) => substr((string) $t, 0, 4))
            ->unique()
            ->sortDesc()
            ->values();

        return view('cash_bank.va.dashboard', compact('va', 'transactions', 'years'));
    }

    /**
     * Show the Rekening Koran page for the VA user.
     */
    public function rekeningKoran()
    {
        return view('cash_bank.va.rekeningKoran');
    }
}
