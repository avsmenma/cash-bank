<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GoogleSheetsSyncController extends Controller
{
    /**
     * Trigger on-demand sync from Google Sheets for Bank Masuk & Bank Keluar
     */
    public function sync(Request $request)
    {
        $lock = Cache::lock('google_sheets_sync_cash_bank', 600);

        if (!$lock->get()) {
            return response()->json([
                'success' => false,
                'message' => 'Proses sinkronisasi sedang berjalan oleh pengguna lain atau sistem. Mohon tunggu beberapa saat.',
            ], 429);
        }

        try {
            @set_time_limit(1800);
            ini_set('memory_limit', '2048M');

            Artisan::call('bank:sync-sheets', ['--with-agenda' => true]);
            $output = Artisan::output();

            return response()->json([
                'success' => true,
                'message' => 'Sinkronisasi data Agenda Online dan Cash & Bank berhasil dijalankan.',
                'output'  => $output,
            ]);
        } catch (\Throwable $e) {
            Log::error('[GoogleSheetsSync] Gagal: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal sinkronisasi Google Sheets: ' . $e->getMessage(),
            ], 500);
        } finally {
            $lock->release();
        }
    }
}
