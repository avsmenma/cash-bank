<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class SyncBankSheetsCommand extends Command
{
    protected $signature = 'bank:sync-sheets
                            {--masuk-url= : Google Sheets Bank Masuk CSV URL}
                            {--keluar-url= : Google Sheets Bank Keluar CSV URL}
                            {--with-agenda : Synchronize Agenda Online documents first}
                            {--skip-agenda : Skip Agenda Online synchronization}
                            {--only-masuk : Only synchronize Bank Masuk}
                            {--only-keluar : Only synchronize Bank Keluar}
                            {--fix-selisih : Enable nominal discrepancy correction}
                            {--dry-run : Only simulate changes without writing to database}';

    protected $description = 'Automated synchronization of Bank Masuk and Bank Keluar directly from Google Sheets CSV';

    private const DEFAULT_MASUK_URL = 'https://docs.google.com/spreadsheets/d/e/2PACX-1vQF2ljfBpL54qpUSpQMToZ2vGxv2pvxKCAfbGTAC8cvI1cPVjT2zMUn-h2RuBeyZLMV7kM7_Q7gb5_5/pub?gid=980790878&single=true&output=csv';
    private const DEFAULT_KELUAR_URL = 'https://docs.google.com/spreadsheets/d/e/2PACX-1vQF2ljfBpL54qpUSpQMToZ2vGxv2pvxKCAfbGTAC8cvI1cPVjT2zMUn-h2RuBeyZLMV7kM7_Q7gb5_5/pub?gid=817239154&single=true&output=csv';

    public function handle()
    {
        @set_time_limit(1800);
        ini_set('memory_limit', '2048M');

        $isDryRun = (bool) $this->option('dry-run');
        $isFixSelisih = (bool) $this->option('fix-selisih');
        $withAgenda = $this->option('with-agenda') || (!$this->option('skip-agenda') && !$this->option('only-masuk') && !$this->option('only-keluar'));
        $syncMasuk = !$this->option('only-keluar');
        $syncKeluar = !$this->option('only-masuk');

        $masukUrl = $this->option('masuk-url') ?: env('GOOGLE_SHEETS_MASUK_URL', self::DEFAULT_MASUK_URL);
        $keluarUrl = $this->option('keluar-url') ?: env('GOOGLE_SHEETS_KELUAR_URL', self::DEFAULT_KELUAR_URL);

        // 1. Sinkronisasi Agenda Online terlebih dahulu jika diminta
        if ($withAgenda) {
            $this->syncAgendaOnline($isDryRun);
        }

        $this->info("==================================================================");
        $this->info("MEMULAI AUTO-SYNC CASH & BANK DARI GOOGLE SHEETS");
        if ($syncMasuk) $this->info("Masuk URL : {$masukUrl}");
        if ($syncKeluar) $this->info("Keluar URL: {$keluarUrl}");
        if ($isFixSelisih) $this->info("Koreksi Selisih: AKTIF");
        if ($isDryRun) {
            $this->warn("MODE: DRY RUN (SIMULASI). Data TIDAK akan diubah di database.");
        }
        $this->info("==================================================================");

        $tempMasuk = storage_path('app/temp_masuk_sync_' . uniqid() . '.csv');
        $tempKeluar = storage_path('app/temp_keluar_sync_' . uniqid() . '.csv');

        try {
            $params = [];

            // 2. Download Bank Masuk jika diperlukan
            if ($syncMasuk) {
                $this->line("Mengunduh data Bank Masuk dari Google Sheets...");
                $codeMasuk = $this->downloadFile($masukUrl, $tempMasuk);
                if ($codeMasuk !== 200 || !file_exists($tempMasuk) || filesize($tempMasuk) < 50) {
                    $this->error("Gagal mengunduh CSV Bank Masuk (HTTP Status: {$codeMasuk})");
                    return Command::FAILURE;
                }
                $this->info("Unduhan Bank Masuk selesai: " . number_format(filesize($tempMasuk) / 1024, 2) . " KB.");
                $params['--masuk'] = $tempMasuk;
            }

            // 3. Download Bank Keluar jika diperlukan
            if ($syncKeluar) {
                $this->line("Mengunduh data Bank Keluar dari Google Sheets...");
                $codeKeluar = $this->downloadFile($keluarUrl, $tempKeluar);
                if ($codeKeluar !== 200 || !file_exists($tempKeluar) || filesize($tempKeluar) < 50) {
                    $this->error("Gagal mengunduh CSV Bank Keluar (HTTP Status: {$codeKeluar})");
                    return Command::FAILURE;
                }
                $this->info("Unduhan Bank Keluar selesai: " . number_format(filesize($tempKeluar) / 1024, 2) . " KB.");
                $params['--keluar'] = $tempKeluar;
            }

            // 4. Eksekusi Rekonsiliasi
            if ($isDryRun) {
                $params['--dry-run'] = true;
            } else {
                $params['--force'] = true;
            }

            if ($isFixSelisih) {
                $params['--fix-selisih'] = true;
            }

            $this->line("Menjalankan rekonsiliasi dan sinkronisasi database...");
            Artisan::call('bank:reconcile', $params, $this->output);

            if (!$isDryRun) {
                $nowIso = now()->toISOString();
                if ($syncMasuk) Cache::put('sync_last_masuk_at', $nowIso, now()->addDays(30));
                if ($syncKeluar) Cache::put('sync_last_keluar_at', $nowIso, now()->addDays(30));
            }

            $this->info("Sinkronisasi Google Sheets Cash & Bank selesai dengan sukses.");
            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $this->error("Terjadi kesalahan saat sinkronisasi: " . $e->getMessage());
            Log::error("[BankSheetsSync] Gagal: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return Command::FAILURE;
        } finally {
            // 5. SELALU HAPUS FILE TEMPORARY AGAR VPS TIDAK BERAT / PENUH
            if (file_exists($tempMasuk)) {
                @unlink($tempMasuk);
                $this->line("File temporary Bank Masuk telah dibersihkan.");
            }
            if (file_exists($tempKeluar)) {
                @unlink($tempKeluar);
                $this->line("File temporary Bank Keluar telah dibersihkan.");
            }
        }
    }

    private function syncAgendaOnline(bool $isDryRun): bool
    {
        $agendaPath = $this->getAgendaOnlinePath();
        if (!$agendaPath) {
            $this->warn("Info: Direktori Agenda Online tidak ditemukan. Melewati sinkronisasi Agenda Online.");
            return false;
        }

        $this->info("==================================================================");
        $this->info("MEMULAI AUTO-SYNC AGENDA ONLINE (PRASYARAT CASH & BANK)");
        $this->info("Direktori: {$agendaPath}");
        $this->info("==================================================================");

        $phpBinary = $this->getPhpBinary();
        $artisanFile = $agendaPath . DIRECTORY_SEPARATOR . 'artisan';

        if (!file_exists($artisanFile)) {
            $this->warn("File artisan tidak ditemukan di {$agendaPath}");
            return false;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $cmd = [$phpBinary, $artisanFile, 'agenda:sync-sheets'];
        } else {
            $pathEnv = getenv('PATH') ?: '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin';
            $cmd = ['env', '-i', 'PATH=' . $pathEnv, 'HOME=' . (getenv('HOME') ?: '/var/www'), $phpBinary, $artisanFile, 'agenda:sync-sheets'];
        }

        if ($isDryRun) {
            $cmd[] = '--dry-run';
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
            $process->run(function ($type, $buffer) {
                $this->output->write($buffer);
            });

            if (!$process->isSuccessful()) {
                $this->warn("Peringatan: Sinkronisasi Agenda Online keluar dengan kode " . $process->getExitCode());
                Log::warning("[BankSheetsSync] Agenda Online sync warning: " . $process->getErrorOutput());
                return false;
            }

            if (!$isDryRun) {
                Cache::put('sync_last_agenda_at', now()->toISOString(), now()->addDays(30));
            }

            $this->info("Sinkronisasi Agenda Online selesai dengan sukses.");
            return true;
        } catch (\Throwable $e) {
            $this->warn("Peringatan saat sinkronisasi Agenda Online: " . $e->getMessage());
            Log::warning("[BankSheetsSync] Exception running Agenda Online: " . $e->getMessage());
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
            if (file_exists('/usr/bin/php')) {
                return '/usr/bin/php';
            }
            if (file_exists('/usr/local/bin/php')) {
                return '/usr/local/bin/php';
            }
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
