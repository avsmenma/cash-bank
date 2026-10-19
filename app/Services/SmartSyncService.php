<?php

namespace App\Services;

use App\Models\BankMasuk;
use App\Models\BankKeluar;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class SmartSyncService
{
    private const DEFAULT_MASUK_URL = 'https://docs.google.com/spreadsheets/d/e/2PACX-1vQF2ljfBpL54qpUSpQMToZ2vGxv2pvxKCAfbGTAC8cvI1cPVjT2zMUn-h2RuBeyZLMV7kM7_Q7gb5_5/pub?gid=980790878&single=true&output=csv';
    private const DEFAULT_KELUAR_URL = 'https://docs.google.com/spreadsheets/d/e/2PACX-1vQF2ljfBpL54qpUSpQMToZ2vGxv2pvxKCAfbGTAC8cvI1cPVjT2zMUn-h2RuBeyZLMV7kM7_Q7gb5_5/pub?gid=817239154&single=true&output=csv';

    public function __construct(
        protected BankReconcileService $reconcileService
    ) {}

    /**
     * Get the latest sync timestamps and status for Agenda, Bank Masuk, and Bank Keluar.
     */
    public function getSyncStatus(): array
    {
        Carbon::setLocale('id');

        $agendaAt = Cache::get('sync_last_agenda_at');
        $masukAt  = Cache::get('sync_last_masuk_at');
        $keluarAt = Cache::get('sync_last_keluar_at');

        // Fallbacks if not yet in cache
        if (!$masukAt) {
            $latestBM = BankMasuk::max('updated_at');
            if ($latestBM) $masukAt = Carbon::parse($latestBM)->toISOString();
        }

        if (!$keluarAt) {
            $latestBK = BankKeluar::max('updated_at');
            if ($latestBK) $keluarAt = Carbon::parse($latestBK)->toISOString();
        }

        $now = Carbon::now();

        $formatStatus = function (?string $isoString) use ($now) {
            if (!$isoString) {
                return [
                    'last_synced_at' => null,
                    'human_time' => 'Belum pernah disinkronkan',
                    'is_fresh' => false,
                ];
            }

            try {
                $dt = Carbon::parse($isoString);
                $diffMinutes = $now->diffInMinutes($dt);
                $isFresh = $diffMinutes < 30;

                return [
                    'last_synced_at' => $dt->toISOString(),
                    'human_time' => $dt->diffForHumans(),
                    'is_fresh' => $isFresh,
                ];
            } catch (\Throwable $e) {
                return [
                    'last_synced_at' => null,
                    'human_time' => 'Format tidak valid',
                    'is_fresh' => false,
                ];
            }
        };

        return [
            'agenda' => $formatStatus($agendaAt),
            'bank_masuk' => $formatStatus($masukAt),
            'bank_keluar' => $formatStatus($keluarAt),
        ];
    }

    /**
     * Stream synchronization with live progress callbacks.
     *
     * @param array $options [sync_agenda => bool, sync_masuk => bool, sync_keluar => bool, fix_selisih => bool]
     * @param callable $emit function(array $eventData)
     */
    public function streamSync(array $options, callable $emit): array
    {
        @set_time_limit(1800);
        ini_set('memory_limit', '2048M');

        $syncAgenda  = !empty($options['sync_agenda']);
        $syncMasuk   = !empty($options['sync_masuk']);
        $syncKeluar  = !empty($options['sync_keluar']);
        $isFixSelisih = !empty($options['fix_selisih']);

        $masukUrl  = env('GOOGLE_SHEETS_MASUK_URL', self::DEFAULT_MASUK_URL);
        $keluarUrl = env('GOOGLE_SHEETS_KELUAR_URL', self::DEFAULT_KELUAR_URL);

        // Build active step list
        $activeSteps = [];
        if ($syncAgenda) $activeSteps[] = 'agenda';
        if ($syncMasuk)  $activeSteps[] = 'bank_masuk';
        if ($syncKeluar) $activeSteps[] = 'bank_keluar';

        $totalSteps = count($activeSteps);
        if ($totalSteps === 0) {
            $emit([
                'status' => 'completed',
                'step' => 0,
                'total_steps' => 0,
                'percent' => 100,
                'step_name' => 'Selesai',
                'message' => 'Tidak ada target yang dipilih untuk sinkronisasi.',
                'summary' => [],
            ]);
            return [];
        }

        // Calculate progress allocation per active step
        // Reserve 5% at end for finalize
        $availablePercent = 95.0;
        $stepWeight = $availablePercent / $totalSteps;

        $currentStepIndex = 0;
        $summary = [
            'agenda' => null,
            'bank_masuk' => null,
            'bank_keluar' => null,
            'fix_selisih' => $isFixSelisih,
        ];

        $tempMasuk = storage_path('app/temp_masuk_sync_' . uniqid() . '.csv');
        $tempKeluar = storage_path('app/temp_keluar_sync_' . uniqid() . '.csv');

        try {
            // STEP 1: AGENDA ONLINE
            if ($syncAgenda) {
                $currentStepIndex++;
                $basePct = ($currentStepIndex - 1) * $stepWeight;

                $emit([
                    'status' => 'running',
                    'step' => $currentStepIndex,
                    'total_steps' => $totalSteps,
                    'percent' => (int) round($basePct + ($stepWeight * 0.1)),
                    'step_name' => 'Agenda Online',
                    'message' => 'Menghubungi service Agenda Online untuk auto-sync dokumen...',
                ]);

                $agendaOk = $this->runAgendaSyncProcess(function ($line) use ($emit, $currentStepIndex, $totalSteps, $basePct, $stepWeight) {
                    $emit([
                        'status' => 'running',
                        'step' => $currentStepIndex,
                        'total_steps' => $totalSteps,
                        'percent' => (int) round($basePct + ($stepWeight * 0.6)),
                        'step_name' => 'Agenda Online',
                        'message' => 'Sinkronisasi Agenda: ' . substr(trim($line), 0, 70),
                    ]);
                });

                if ($agendaOk) {
                    Cache::put('sync_last_agenda_at', now()->toISOString(), now()->addDays(30));
                    $summary['agenda'] = ['status' => 'success', 'message' => 'Agenda Online berhasil disinkronkan'];
                } else {
                    $summary['agenda'] = ['status' => 'warning', 'message' => 'Agenda Online dilewati atau tidak ditemukan'];
                }

                $emit([
                    'status' => 'running',
                    'step' => $currentStepIndex,
                    'total_steps' => $totalSteps,
                    'percent' => (int) round($basePct + $stepWeight),
                    'step_name' => 'Agenda Online',
                    'message' => 'Selesai sinkronisasi Agenda Online.',
                ]);
            }

            // STEP 2: BANK MASUK
            if ($syncMasuk) {
                $currentStepIndex++;
                $basePct = ($currentStepIndex - 1) * $stepWeight;

                $emit([
                    'status' => 'running',
                    'step' => $currentStepIndex,
                    'total_steps' => $totalSteps,
                    'percent' => (int) round($basePct + ($stepWeight * 0.05)),
                    'step_name' => 'Bank Masuk',
                    'message' => 'Mengunduh data Bank Masuk dari Google Sheets CSV...',
                ]);

                $code = $this->downloadFile($masukUrl, $tempMasuk);
                if ($code !== 200 || !file_exists($tempMasuk) || filesize($tempMasuk) < 50) {
                    throw new \RuntimeException("Gagal mengunduh CSV Bank Masuk dari Google Sheets (HTTP Status: {$code})");
                }

                $sizeKb = round(filesize($tempMasuk) / 1024, 1);
                $emit([
                    'status' => 'running',
                    'step' => $currentStepIndex,
                    'total_steps' => $totalSteps,
                    'percent' => (int) round($basePct + ($stepWeight * 0.2)),
                    'step_name' => 'Bank Masuk',
                    'message' => "Unduhan Bank Masuk selesai ({$sizeKb} KB). Memulai rekonsiliasi...",
                ]);

                $resBM = $this->reconcileService->reconcileBankMasuk(
                    $tempMasuk,
                    false, // live
                    $isFixSelisih,
                    function ($subPct, $msg) use ($emit, $currentStepIndex, $totalSteps, $basePct, $stepWeight) {
                        $scaled = $basePct + ($stepWeight * 0.2) + (($stepWeight * 0.75) * ($subPct / 100));
                        $emit([
                            'status' => 'running',
                            'step' => $currentStepIndex,
                            'total_steps' => $totalSteps,
                            'percent' => (int) round($scaled),
                            'step_name' => 'Bank Masuk',
                            'message' => $msg,
                        ]);
                    }
                );

                Cache::put('sync_last_masuk_at', now()->toISOString(), now()->addDays(30));
                $summary['bank_masuk'] = $resBM;

                $emit([
                    'status' => 'running',
                    'step' => $currentStepIndex,
                    'total_steps' => $totalSteps,
                    'percent' => (int) round($basePct + $stepWeight),
                    'step_name' => 'Bank Masuk',
                    'message' => "Bank Masuk selesai: {$resBM['updated']} diperbarui, {$resBM['inserted']} baru" . ($isFixSelisih ? ", {$resBM['fixed_selisih']} selisih diperbaiki" : ""),
                ]);
            }

            // STEP 3: BANK KELUAR
            if ($syncKeluar) {
                $currentStepIndex++;
                $basePct = ($currentStepIndex - 1) * $stepWeight;

                $emit([
                    'status' => 'running',
                    'step' => $currentStepIndex,
                    'total_steps' => $totalSteps,
                    'percent' => (int) round($basePct + ($stepWeight * 0.05)),
                    'step_name' => 'Bank Keluar',
                    'message' => 'Mengunduh data Bank Keluar dari Google Sheets CSV...',
                ]);

                $code = $this->downloadFile($keluarUrl, $tempKeluar);
                if ($code !== 200 || !file_exists($tempKeluar) || filesize($tempKeluar) < 50) {
                    throw new \RuntimeException("Gagal mengunduh CSV Bank Keluar dari Google Sheets (HTTP Status: {$code})");
                }

                $sizeKb = round(filesize($tempKeluar) / 1024, 1);
                $emit([
                    'status' => 'running',
                    'step' => $currentStepIndex,
                    'total_steps' => $totalSteps,
                    'percent' => (int) round($basePct + ($stepWeight * 0.2)),
                    'step_name' => 'Bank Keluar',
                    'message' => "Unduhan Bank Keluar selesai ({$sizeKb} KB). Memulai rekonsiliasi...",
                ]);

                $resBK = $this->reconcileService->reconcileBankKeluar(
                    $tempKeluar,
                    false, // live
                    $isFixSelisih,
                    function ($subPct, $msg) use ($emit, $currentStepIndex, $totalSteps, $basePct, $stepWeight) {
                        $scaled = $basePct + ($stepWeight * 0.2) + (($stepWeight * 0.75) * ($subPct / 100));
                        $emit([
                            'status' => 'running',
                            'step' => $currentStepIndex,
                            'total_steps' => $totalSteps,
                            'percent' => (int) round($scaled),
                            'step_name' => 'Bank Keluar',
                            'message' => $msg,
                        ]);
                    }
                );

                Cache::put('sync_last_keluar_at', now()->toISOString(), now()->addDays(30));
                $summary['bank_keluar'] = $resBK;

                $emit([
                    'status' => 'running',
                    'step' => $currentStepIndex,
                    'total_steps' => $totalSteps,
                    'percent' => (int) round($basePct + $stepWeight),
                    'step_name' => 'Bank Keluar',
                    'message' => "Bank Keluar selesai: {$resBK['updated']} diperbarui, {$resBK['inserted']} baru" . ($isFixSelisih ? ", {$resBK['fixed_selisih']} selisih diperbaiki" : ""),
                ]);
            }

            // FINAL STEP: COMPLETION
            $emit([
                'status' => 'completed',
                'step' => $totalSteps,
                'total_steps' => $totalSteps,
                'percent' => 100,
                'step_name' => 'Selesai',
                'message' => 'Semua proses sinkronisasi berhasil diselesaikan dengan sukses!',
                'summary' => $summary,
            ]);

            return $summary;

        } catch (\Throwable $e) {
            Log::error("[SmartSyncService] Exception: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $emit([
                'status' => 'error',
                'step' => $currentStepIndex,
                'total_steps' => $totalSteps,
                'percent' => 100,
                'step_name' => 'Error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'summary' => $summary,
            ]);
            throw $e;
        } finally {
            // Clean temp files
            if (file_exists($tempMasuk)) @unlink($tempMasuk);
            if (file_exists($tempKeluar)) @unlink($tempKeluar);
        }
    }

    private function runAgendaSyncProcess(?callable $onOutput = null): bool
    {
        $agendaPath = $this->getAgendaOnlinePath();
        if (!$agendaPath) {
            return false;
        }

        $phpBinary = $this->getPhpBinary();
        $artisanFile = $agendaPath . DIRECTORY_SEPARATOR . 'artisan';

        if (!file_exists($artisanFile)) {
            return false;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $cmd = [$phpBinary, $artisanFile, 'agenda:sync-sheets'];
        } else {
            $pathEnv = getenv('PATH') ?: '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin';
            $cmd = ['env', '-i', 'PATH=' . $pathEnv, 'HOME=' . (getenv('HOME') ?: '/var/www'), $phpBinary, $artisanFile, 'agenda:sync-sheets'];
        }

        try {
            $cleanEnv = [
                'DB_CONNECTION' => false,
                'DB_DATABASE' => false,
                'DB_USERNAME' => false,
                'DB_PASSWORD' => false,
                'DB_HOST' => false,
                'DB_PORT' => false,
                'APP_KEY' => false,
                'APP_NAME' => false,
                'APP_URL' => false,
            ];

            $process = new Process($cmd, $agendaPath, $cleanEnv);
            $process->setTimeout(600);
            $process->run(function ($type, $buffer) use ($onOutput) {
                if ($onOutput && !empty(trim($buffer))) {
                    $onOutput($buffer);
                }
            });

            return $process->isSuccessful();
        } catch (\Throwable $e) {
            Log::warning("[SmartSyncService] Agenda Online Process failed: " . $e->getMessage());
            return false;
        }
    }

    private function getAgendaOnlinePath(): ?string
    {
        $candidates = [
            env('AGENDA_ONLINE_PATH'),
            '/var/www/agenda-online-PTPN',
            base_path('../agenda_2026'),
            base_path('../../agenda_2026'),
            'D:/Downloads/agenda_2026',
            'D:\\Downloads\\agenda_2026',
        ];

        foreach ($candidates as $path) {
            if (!empty($path) && file_exists($path . DIRECTORY_SEPARATOR . 'artisan')) {
                return realpath($path) ?: $path;
            }
        }

        return null;
    }

    private function getPhpBinary(): string
    {
        if (PHP_SAPI !== 'cli') {
            if (file_exists('/usr/bin/php')) return '/usr/bin/php';
            if (file_exists('/usr/local/bin/php')) return '/usr/local/bin/php';
        }
        return PHP_BINARY ?: 'php';
    }

    private function downloadFile(string $url, string $destination): int
    {
        $ch = curl_init($url);
        $fp = fopen($destination, 'wb');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 180);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($fp);

        return $httpCode;
    }
}
