<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class VALedgerSortingTest extends TestCase
{
    public function test_masuk_is_sorted_before_keluar_on_same_date(): void
    {
        $masuk = collect([
            [
                'id' => 10,
                'tanggal' => '2026-10-19',
                'created_at' => '2026-10-24 10:00:00', // created later
                'penerima' => '-',
                'uraian' => 'Dropping Biaya PLN Kebun Unit Sep 2026',
                'debet' => 5325690.0,
                'kredit' => 0.0,
                'tipe' => 'masuk',
                'tipe_order' => 0,
            ],
        ]);

        $keluar = collect([
            [
                'id' => 5,
                'tanggal' => '2026-10-19',
                'created_at' => '2026-10-24 08:00:00', // created earlier
                'penerima' => '-',
                'uraian' => '81029155508[Tagihan PLN Okt 26',
                'debet' => 0.0,
                'kredit' => 5325690.0,
                'tipe' => 'keluar',
                'tipe_order' => 1,
            ],
        ]);

        $transactions = $masuk->toBase()->merge($keluar->toBase())
            ->sortBy([
                ['tanggal', 'asc'],
                ['tipe_order', 'asc'],
                ['created_at', 'asc'],
                ['id', 'asc'],
            ])
            ->values();

        $saldo = 0.0;
        $transactions = $transactions->map(function ($item) use (&$saldo) {
            $saldo = $saldo + $item['debet'] - $item['kredit'];
            $item['saldo'] = $saldo;
            return $item;
        });

        // First item should be Masuk (Debet)
        $this->assertSame('masuk', $transactions[0]['tipe']);
        $this->assertEquals(5325690.0, $transactions[0]['debet']);
        $this->assertEquals(5325690.0, $transactions[0]['saldo']);

        // Second item should be Keluar (Kredit)
        $this->assertSame('keluar', $transactions[1]['tipe']);
        $this->assertEquals(5325690.0, $transactions[1]['kredit']);
        $this->assertEquals(0.0, $transactions[1]['saldo']);

        // Assert saldo never dipped below 0
        foreach ($transactions as $t) {
            $this->assertGreaterThanOrEqual(0.0, $t['saldo']);
        }
    }

    public function test_multi_entry_same_date_preserves_created_at_and_id_within_same_type(): void
    {
        $entries = collect([
            ['id' => 3, 'tanggal' => '2026-10-19', 'tipe_order' => 1, 'created_at' => '2026-10-19 11:00:00', 'debet' => 0, 'kredit' => 200, 'label' => 'K2'],
            ['id' => 2, 'tanggal' => '2026-10-19', 'tipe_order' => 1, 'created_at' => '2026-10-19 10:00:00', 'debet' => 0, 'kredit' => 100, 'label' => 'K1'],
            ['id' => 1, 'tanggal' => '2026-10-19', 'tipe_order' => 0, 'created_at' => '2026-10-19 09:00:00', 'debet' => 500, 'kredit' => 0, 'label' => 'M1'],
        ]);

        $sorted = $entries->sortBy([
            ['tanggal', 'asc'],
            ['tipe_order', 'asc'],
            ['created_at', 'asc'],
            ['id', 'asc'],
        ])->values();

        $this->assertSame(['M1', 'K1', 'K2'], $sorted->pluck('label')->all());
    }
}
