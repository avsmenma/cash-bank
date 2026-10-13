<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class GoogleSheetsSyncController extends Controller
{
    /**
     * Trigger on-demand sync from Google Sheets for Bank Masuk & Bank Keluar
     */
    public function sync(Request $request)
    {
        try {
            @set_time_limit(1800);
            ini_set('memory_limit', '2048M');

            Artisan::call('bank:sync-sheets');
            $output = Artisan::output();

            return response()->json([
                'success' => true,
                'message' => 'Sinkronisasi dengan Google Sheets berhasil dijalankan.',
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
        }
    }
}
