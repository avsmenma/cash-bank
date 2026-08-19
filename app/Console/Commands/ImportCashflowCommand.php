<?php

namespace App\Console\Commands;

use App\Models\Cashflow;
use App\Services\CashflowImportService;
use Illuminate\Console\Command;

class ImportCashflowCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cashflow:import {--reff= : Path ke file Standarisasi Reffkey} {--cf= : Path ke file Cashflow.xlsx} {--truncate : Kosongkan tabel sebelum import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import file Excel Cashflow SAP dan Standarisasi Reffkey';

    /**
     * Execute the console command.
     */
    public function handle(CashflowImportService $service)
    {
        ini_set('max_execution_time', '600');
        ini_set('memory_limit', '1024M');

        $reffPath = $this->option('reff') ?: (file_exists(base_path('docs/Standarisasi Reffkey (1).xlsx')) ? base_path('docs/Standarisasi Reffkey (1).xlsx') : base_path('../docs/Standarisasi Reffkey (1).xlsx'));
        $cfPath = $this->option('cf') ?: (file_exists(base_path('docs/Cashflow.xlsx')) ? base_path('docs/Cashflow.xlsx') : base_path('../docs/Cashflow.xlsx'));

        $this->info("Memulai import Standarisasi Reffkey dari: {$reffPath}");
        if (file_exists($reffPath)) {
            $countRef = $service->importStandarisasiReffkey($reffPath);
            $this->info("Berhasil mengimpor {$countRef} kode referensi standarisasi.");
        } else {
            $this->warn("File standarisasi tidak ditemukan: {$reffPath}");
        }

        $this->info("Memulai import Transaksi Cashflow dari: {$cfPath}");
        if (file_exists($cfPath)) {
            if ($this->option('truncate')) {
                $this->warn("Mengosongkan tabel cashflows...");
                Cashflow::truncate();
            }
            $countCf = $service->importCashflowTransactions($cfPath);
            $this->info("Berhasil mengimpor {$countCf} baris transaksi cashflow.");
        } else {
            $this->warn("File transaksi cashflow tidak ditemukan: {$cfPath}");
        }

        $this->info("Import Cashflow selesai dengan sukses!");
        return Command::SUCCESS;
    }
}
