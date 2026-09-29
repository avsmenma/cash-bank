<?php

namespace Tests\Feature;

use App\Exports\reportKeluarExcel;
use App\Http\Controllers\BankKeluarController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RekeningKoranSumberDanaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Always use a separate in-memory connection, never the application's database.
        config(['database.default' => 'f01_test', 'database.connections.f01_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        DB::purge('f01_test');
        $pdo = DB::connection()->getPdo();
        // Dropdown queries use MySQL YEAR/MONTH; live MySQL is checked separately.
        $pdo->sqliteCreateFunction('YEAR', fn($date) => (int) substr($date, 0, 4));
        $pdo->sqliteCreateFunction('MONTH', fn($date) => (int) substr($date, 5, 2));

        foreach ([
            'sumber_dana' => ['id_sumber_dana', 'nama_sumber_dana'],
            'bank_tujuan' => ['id_bank_tujuan', 'nama_tujuan'],
            'kategori_kriteria' => ['id_kategori_kriteria', 'nama_kriteria'],
            'sub_kriteria' => ['id_sub_kriteria', 'nama_sub_kriteria'],
            'item_sub_kriteria' => ['id_item_sub_kriteria', 'nama_item_sub_kriteria'],
            'jenis_pembayarans' => ['id_jenis_pembayaran', 'nama_jenis_pembayaran'],
        ] as $table => [$id, $name]) {
            Schema::create($table, function (Blueprint $schema) use ($id, $name) {
                $schema->integer($id)->primary();
                $schema->string($name);
            });
            DB::table($table)->insert([$id => 1, $name => 'Fixture 1']);
        }
        DB::table('sumber_dana')->insert(['id_sumber_dana' => 2, 'nama_sumber_dana' => 'Fixture 2']);

        foreach (['bank_masuk' => 'id_bank_masuk', 'bank_keluars' => 'id_bank_keluar'] as $table => $id) {
            Schema::create($table, function (Blueprint $schema) use ($id) {
                $schema->increments($id);
                foreach (['id_sumber_dana', 'id_bank_tujuan', 'id_kategori_kriteria',
                    'id_sub_kriteria', 'id_item_sub_kriteria', 'id_jenis_pembayaran'] as $column) {
                    $schema->integer($column)->nullable();
                }
                foreach (['agenda_tahun', 'no_sap', 'uraian', 'penerima'] as $column) {
                    $schema->string($column)->nullable();
                }
                $schema->date('tanggal');
                $schema->decimal('debet', 18, 2)->default(0);
                $schema->decimal('kredit', 18, 2)->default(0);
            });
            foreach ([
                [1, '2026-01-05', 1000, 100],
                [2, '2026-01-06', 2000, 200],
                [null, '2026-01-07', 500, 50],
                [1, '2026-02-05', 9000, 900],
            ] as [$source, $date, $debet, $kredit]) {
                DB::table($table)->insert([
                    'id_sumber_dana' => $source, 'id_bank_tujuan' => 1,
                    'id_kategori_kriteria' => 1, 'id_sub_kriteria' => 1,
                    'id_item_sub_kriteria' => 1, 'id_jenis_pembayaran' => 1,
                    'agenda_tahun' => 'TEST', 'uraian' => 'Fixture', 'tanggal' => $date,
                    'debet' => $table === 'bank_masuk' ? $debet : 0,
                    'kredit' => $table === 'bank_keluars' ? $kredit : 0,
                ]);
            }
        }
    }

    protected function tearDown(): void
    {
        DB::purge('f01_test');
        parent::tearDown();
    }

    public static function sourceFilters(): array
    {
        return [
            'omitted' => [[], 6, 3500, 350, 0],
            'empty array' => [['sumber_dana' => []], 6, 3500, 350, 0],
            'empty scalar' => [['sumber_dana' => ''], 6, 3500, 350, 0],
            'null scalar' => [['sumber_dana' => null], 6, 3500, 350, 0],
            'empty element' => [['sumber_dana' => ['']], 6, 3500, 350, 0],
            'null element after middleware' => [['sumber_dana' => [null]], 6, 3500, 350, 0],
            'one source' => [['sumber_dana' => ['1']], 2, 1000, 100, 1],
            'scalar source' => [['sumber_dana' => '1'], 2, 1000, 100, 1],
            'multiple sources' => [['sumber_dana' => ['1', '2']], 4, 3000, 300, 1],
            'mixed blanks and source' => [['sumber_dana' => [null, '', '2']], 2, 2000, 200, 1],
            'unknown source stays filtered' => [['sumber_dana' => ['999']], 0, 0, 0, 1],
        ];
    }

    #[DataProvider('sourceFilters')]
    public function test_table_excel_and_pdf_agree_on_source_filter(
        array $filter, int $count, int $debet, int $kredit, int $activeFilters
    ): void {
        $request = Request::create('/bank-keluar/report', 'GET', array_merge([
            'tahun' => '2026', 'bulan' => '1', 'length' => 100,
        ], $filter));
        $controller = app(BankKeluarController::class);

        $json = $controller->reportData($request)->getData(true);
        $this->assertSame($count, $json['recordsFiltered']);
        $this->assertCount($count, $json['data']);
        $this->assertSame([
            'debet' => number_format($debet, 0, ',', '.'),
            'kredit' => number_format($kredit, 0, ',', '.'),
            'saldo' => number_format($debet - $kredit, 0, ',', '.'),
        ], $json['totals']);

        $excel = new reportKeluarExcel($request);
        $rows = $excel->collection();
        $this->assertCount($count + 1, $rows); // Includes the TOTAL footer.
        $this->assertSame([(float) $debet, (float) $kredit, (float) ($debet - $kredit)], array_slice($rows->last(), -3));
        $this->assertContains('Debet', $excel->headings());
        $this->assertContains('Saldo Akhir', $excel->headings());

        // PDF endpoint returns a printable Blade view; inspect its actual query results.
        $pdf = $controller->reportKeluarPdf($request)->getData();
        $this->assertCount($count, $pdf['data']);
        $this->assertEquals($debet, $pdf['data']->sum('debet'));
        $this->assertEquals($kredit, $pdf['data']->sum('kredit'));
        $this->assertEquals($debet - $kredit, $pdf['data']->last()->saldo_akhir ?? 0);
        $this->assertSame($activeFilters, $pdf['countActiveFilters']);
        $this->assertTrue($pdf['showDebet']);
        $this->assertTrue($pdf['showSaldoAkhir']);
    }
}
