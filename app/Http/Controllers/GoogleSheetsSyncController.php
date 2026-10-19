<?php

namespace App\Http\Controllers;

use App\Services\SmartSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GoogleSheetsSyncController extends Controller
{
    public function __construct(
        protected SmartSyncService $smartSyncService
    ) {}

    /**
     * Get real-time status & timestamps of all sync targets.
     */
    public function status(Request $request)
    {
        try {
            $status = $this->smartSyncService->getSyncStatus();
            return response()->json([
                'success' => true,
                'status'  => $status,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil status sinkronisasi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Stream on-demand sync from Google Sheets via Server-Sent Events (SSE).
     */
    public function stream(Request $request): StreamedResponse
    {
        $options = [
            'sync_agenda'  => filter_var($request->query('sync_agenda', 1), FILTER_VALIDATE_BOOLEAN),
            'sync_masuk'   => filter_var($request->query('sync_masuk', 1), FILTER_VALIDATE_BOOLEAN),
            'sync_keluar'  => filter_var($request->query('sync_keluar', 1), FILTER_VALIDATE_BOOLEAN),
            'fix_selisih'  => filter_var($request->query('fix_selisih', 0), FILTER_VALIDATE_BOOLEAN),
        ];

        return response()->stream(function () use ($options) {
            // Clean any existing output buffers to ensure live streaming
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            $lock = Cache::lock('google_sheets_sync_cash_bank', 900);

            if (!$lock->get()) {
                echo "data: " . json_encode([
                    'status' => 'error',
                    'percent' => 100,
                    'message' => 'Proses sinkronisasi sedang berjalan oleh pengguna lain atau sistem. Mohon tunggu beberapa saat.',
                ]) . "\n\n";
                flush();
                return;
            }

            try {
                $this->smartSyncService->streamSync($options, function (array $data) {
                    echo "data: " . json_encode($data) . "\n\n";
                    flush();
                });
            } catch (\Throwable $e) {
                Log::error('[GoogleSheetsSyncController@stream] Error: ' . $e->getMessage());
                echo "data: " . json_encode([
                    'status' => 'error',
                    'percent' => 100,
                    'message' => 'Gagal sinkronisasi: ' . $e->getMessage(),
                ]) . "\n\n";
                flush();
            } finally {
                $lock->release();
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-transform',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Trigger on-demand sync from Google Sheets for Bank Masuk & Bank Keluar (Standard POST fallback)
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

            $params = ['--with-agenda' => true];
            if ($request->boolean('fix_selisih')) {
                $params['--fix-selisih'] = true;
            }

            Artisan::call('bank:sync-sheets', $params);
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
