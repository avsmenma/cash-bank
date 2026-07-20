<?php

namespace Tests\Unit;

use App\Services\AgendaTanggalBayarSync;
use PHPUnit\Framework\TestCase;

/**
 * Uji logika pemetaan nomor agenda → tanggal bayar.
 *
 * Sengaja murni (tanpa database): perilaku SQL-nya diuji terpisah terhadap
 * MySQL sungguhan, karena SQLite di test pernah menyembunyikan dua bug produksi.
 */
class AgendaTanggalBayarSyncTest extends TestCase
{
    private AgendaTanggalBayarSync $sync;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sync = new AgendaTanggalBayarSync();
    }

    public function test_memetakan_nomor_agenda_ke_tanggalnya(): void
    {
        $peta = $this->sync->petakanTanggalTerawal([
            ['agenda_tahun' => '5006_2026', 'tanggal' => '2026-07-17'],
        ]);

        $this->assertSame(['5006_2026' => '2026-07-17'], $peta);
    }

    public function test_memakai_tanggal_terawal_bila_satu_agenda_punya_banyak_transaksi(): void
    {
        $peta = $this->sync->petakanTanggalTerawal([
            ['agenda_tahun' => '5006_2026', 'tanggal' => '2026-07-17'],
            ['agenda_tahun' => '5006_2026', 'tanggal' => '2026-07-02'],
            ['agenda_tahun' => '5006_2026', 'tanggal' => '2026-07-20'],
        ]);

        $this->assertSame(['5006_2026' => '2026-07-02'], $peta);
    }

    public function test_melewati_baris_tanpa_nomor_agenda_atau_tanpa_tanggal(): void
    {
        $peta = $this->sync->petakanTanggalTerawal([
            ['agenda_tahun' => null, 'tanggal' => '2026-07-17'],
            ['agenda_tahun' => '', 'tanggal' => '2026-07-17'],
            ['agenda_tahun' => '   ', 'tanggal' => '2026-07-17'],
            ['agenda_tahun' => '5007_2026', 'tanggal' => null],
            ['agenda_tahun' => '5008_2026', 'tanggal' => ''],
            ['agenda_tahun' => '5009_2026'],
        ]);

        $this->assertSame([], $peta);
    }

    public function test_memangkas_komponen_jam_menjadi_tanggal_saja(): void
    {
        $peta = $this->sync->petakanTanggalTerawal([
            ['agenda_tahun' => '5006_2026', 'tanggal' => '2026-07-17 19:47:53'],
        ]);

        $this->assertSame(['5006_2026' => '2026-07-17'], $peta);
    }

    public function test_mempertahankan_nomor_agenda_polos_sebagai_teks(): void
    {
        // Kunci array PHP yang berupa angka otomatis jadi integer — pastikan
        // tidak merusak pencocokan ke kolom nomor_agenda yang bertipe teks.
        $peta = $this->sync->petakanTanggalTerawal([
            ['agenda_tahun' => '5006', 'tanggal' => '2026-07-17'],
        ]);

        $this->assertCount(1, $peta);
        $this->assertSame('2026-07-17', $peta['5006']);
    }

    public function test_membuang_spasi_di_sekitar_nomor_agenda(): void
    {
        $peta = $this->sync->petakanTanggalTerawal([
            ['agenda_tahun' => ' 5006_2026 ', 'tanggal' => '2026-07-17'],
        ]);

        $this->assertSame(['5006_2026' => '2026-07-17'], $peta);
    }

    public function test_menerima_objek_model_bukan_hanya_array(): void
    {
        $peta = $this->sync->petakanTanggalTerawal([
            (object) ['agenda_tahun' => '5006_2026', 'tanggal' => '2026-07-17'],
        ]);

        $this->assertSame(['5006_2026' => '2026-07-17'], $peta);
    }
}
