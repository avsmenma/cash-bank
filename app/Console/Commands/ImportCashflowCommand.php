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
    protected $signature = 'cashflow:import
                            {--reff= : Path ke file Standarisasi Reffkey}
                            {--cf= : Path ke file Cashflow.xlsx}
                            {--truncate : Kosongkan tabel cashflows sebelum import}
                            {--append : Tambahkan ke data yang sudah ada (sadar risiko duplikat)}';

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
            // Impor transaksi bersifat INSERT murni (tanpa kunci unik), jadi
            // menjalankannya dua kali akan menggandakan seluruh nilai realisasi.
            // Karena itu bila tabel sudah berisi data, pilihan harus eksplisit.
            $sudahAda = Cashflow::count();
            if ($sudahAda > 0 && !$this->option('truncate') && !$this->option('append')) {
                $this->error("Tabel cashflows sudah berisi {$sudahAda} baris.");
                $this->line('Jalankan ulang dengan --truncate (ganti total) atau --append (sengaja menambah).');

                return Command::FAILURE;
            }

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
