<?php

namespace App\Console\Commands;

use App\Services\BankReconcileService;
use Illuminate\Console\Command;

class ReconcileBankDataCommand extends Command
{
    protected $signature = 'bank:reconcile
                            {--masuk= : Path to Bank Masuk CSV}
                            {--keluar= : Path to Bank Keluar CSV}
                            {--dry-run : Only simulate and show changes without modifying database}
                            {--force : Actually execute and save changes}
                            {--fix-selisih : Enable discrepancy fix: update nominal (debet/kredit) if record matches by no_bukti or agenda}';

    protected $description = 'Reconcile Bank Masuk & Bank Keluar database records against master CSV files';

    public function handle(BankReconcileService $service)
    {
        ini_set('max_execution_time', '1800');
        ini_set('memory_limit', '2048M');

        $isDryRun = $this->option('dry-run') || !$this->option('force');
        $isFixSelisih = (bool) $this->option('fix-selisih');

        if ($isDryRun) {
            $this->warn("==================================================================");
            $this->warn("MODE: DRY RUN (SIMULASI). Data TIDAK akan diubah di database.");
            $this->warn("Gunakan flag --force untuk benar-benar menerapkan perubahan.");
            if ($isFixSelisih) {
                $this->warn("KOREKSI SELISIH: AKTIF (Simulasi perbaikan nominal diubah).");
            }
            $this->warn("==================================================================");
        } else {
            $this->info("==================================================================");
            $this->info("MODE: LIVE / FORCE. Perubahan AKAN diterapkan ke database.");
            if ($isFixSelisih) {
                $this->info("KOREKSI SELISIH: AKTIF (Nominal yang berubah di CSV akan di-update ke DB).");
            }
            $this->info("==================================================================");
        }

        $masukFile = $this->option('masuk') ?: storage_path('app/bank_masuk_reconcile.csv');
        $keluarFile = $this->option('keluar') ?: storage_path('app/bank_keluar_reconcile.csv');

        // 1. Reconcile Bank Masuk
        if (file_exists($masukFile)) {
            $this->info("\n>>> MEMULAI REKONSILIASI BANK MASUK <<<");
            $this->line("File: {$masukFile}");
            $resBM = $service->reconcileBankMasuk($masukFile, $isDryRun, $isFixSelisih, function ($pct, $msg) {
                $this->line(" [{$pct}%] {$msg}");
            });

            $this->info("Hasil Bank Masuk: Matched {$resBM['matched']}, Updated {$resBM['updated']}, Inserted {$resBM['inserted']}");
            if ($isFixSelisih) {
                $this->info("Selisih Nominal Diperbaiki: {$resBM['fixed_selisih']} dokumen.");
            }
        } else {
            $this->error("File Bank Masuk CSV tidak ditemukan: {$masukFile}");
        }

        // 2. Reconcile Bank Keluar
        if (file_exists($keluarFile)) {
            $this->info("\n>>> MEMULAI REKONSILIASI BANK KELUAR <<<");
            $this->line("File: {$keluarFile}");
            $resBK = $service->reconcileBankKeluar($keluarFile, $isDryRun, $isFixSelisih, function ($pct, $msg) {
                $this->line(" [{$pct}%] {$msg}");
            });

            $this->info("Hasil Bank Keluar: Matched {$resBK['matched']}, Updated {$resBK['updated']}, Inserted {$resBK['inserted']}");
            if ($isFixSelisih) {
                $this->info("Selisih Nominal Diperbaiki: {$resBK['fixed_selisih']} dokumen.");
            }
        } else {
            $this->error("File Bank Keluar CSV tidak ditemukan: {$keluarFile}");
        }

        $this->info("\nProses rekonsiliasi selesai.");
        return Command::SUCCESS;
    }
}
