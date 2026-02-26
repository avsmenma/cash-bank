<?php

namespace App\Imports;

use App\Models\Dropping;
use App\Models\Permintaan;
use App\Models\SubKriteria;
use App\Models\ItemSubKriteria;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class ImportDropping implements ToArray, WithHeadingRow, WithCalculatedFormulas
{
    protected $target; // 'dropping' or 'permintaan'
    protected $subKriteriaId;
    protected $bulan;
    protected $tahun;
    protected $imported = 0;
    protected $skipped = 0;

    public function __construct(string $target, $subKriteriaId, $bulan, $tahun)
    {
        $this->target = $target;
        $this->subKriteriaId = $subKriteriaId;
        $this->bulan = $bulan;
        $this->tahun = $tahun;
    }

    public function array(array $rows)
    {
        $subKriteria = SubKriteria::find($this->subKriteriaId);
        if (!$subKriteria) {
            throw new \Exception('Sub Kriteria tidak ditemukan');
        }

        // Get all items for this sub kriteria for matching
        $items = ItemSubKriteria::where('id_sub_kriteria', $this->subKriteriaId)->get();

        // Build lookup map (lowercase name => item)
        $itemMap = [];
        foreach ($items as $item) {
            $itemMap[strtolower(trim($item->nama_item_sub_kriteria))] = $item;
        }

        DB::beginTransaction();

        try {
            foreach ($rows as $row) {
                $itemName = trim($row['item_sub_kriteria'] ?? '');

                if (empty($itemName)) {
                    $this->skipped++;
                    continue;
                }

                // Match by name (case-insensitive)
                $matchedItem = $itemMap[strtolower($itemName)] ?? null;

                if (!$matchedItem) {
                    $this->skipped++;
                    continue;
                }

                $m1 = $this->parseNilai($row['m1'] ?? 0);
                $m2 = $this->parseNilai($row['m2'] ?? 0);
                $m3 = $this->parseNilai($row['m3'] ?? 0);
                $m4 = $this->parseNilai($row['m4'] ?? 0);

                // Choose model based on target
                $model = $this->target === 'permintaan' ? Permintaan::class : Dropping::class;

                $record = $model::firstOrNew([
                    'id_item_sub_kriteria' => $matchedItem->id_item_sub_kriteria,
                    'id_sub_kriteria' => $this->subKriteriaId,
                    'tahun' => $this->tahun,
                    'bulan' => $this->bulan,
                ]);

                if (!$record->exists) {
                    $record->id_kategori_kriteria = $subKriteria->id_kategori_kriteria;
                }

                $record->M1 = $m1;
                $record->M2 = $m2;
                $record->M3 = $m3;
                $record->M4 = $m4;
                $record->save();

                $this->imported++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function parseNilai($val)
    {
        if ($val === null || $val === '')
            return 0;
        if (is_numeric($val))
            return (int) $val;

        // Remove dots (thousand separator) and replace comma with dot (decimal)
        $cleaned = str_replace(['.', ','], ['', '.'], (string) $val);
        return (int) floatval($cleaned);
    }

    public function getImported(): int
    {
        return $this->imported;
    }

    public function getSkipped(): int
    {
        return $this->skipped;
    }
}
