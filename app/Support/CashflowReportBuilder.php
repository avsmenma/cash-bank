<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * Menyusun baris Laporan Arus Kas berjenjang mengikuti format baku PTPN
 * (berkas ekspor "EXPORT_*.xlsx"):
 *
 *   Kode | Reference | Uraian | Realisasi <tahun> | Realisasi <tahun-1>
 *
 * Hierarki laporan dibaca dari kamus `cashflow_references`:
 *   huruf ke-1 reference_key  -> bagian (A=Operasi, B=Investasi, C=Pendanaan)
 *   3 huruf pertama           -> sub-bagian (xx01=Penerimaan, xx02=Pengeluaran)
 *   parent_key (5 huruf)      -> grup Kode
 *   reference_key (8 huruf)   -> baris detail
 *
 * Nilai transaksi bertanda: positif = arus kas masuk, negatif = arus kas keluar,
 * sehingga seluruh total cukup dijumlah apa adanya (tanpa pembalikan tanda).
 */
class CashflowReportBuilder
{
    /** Bagian utama laporan beserta penomoran romawi & judulnya. */
    private const SECTIONS = [
        ['prefix' => 'A', 'roman' => 'I',   'title' => 'ARUS KAS DARI AKTIVITAS PENDANAAN OPERASI'],
        ['prefix' => 'B', 'roman' => 'II',  'title' => 'ARUS KAS DARI AKTIVITAS PENDANAAN INVESTASI'],
        ['prefix' => 'C', 'roman' => 'III', 'title' => 'ARUS KAS DARI AKTIVITAS PENDANAAN'],
    ];

    /** Sub-bagian: akhiran kode 3-huruf -> penomoran, label & judul totalnya. */
    private const SUBSECTIONS = [
        '01' => ['numeral' => 'I',  'label' => 'Penerimaan',  'total' => 'Total Penerimaan'],
        '02' => ['numeral' => 'II', 'label' => 'Pengeluaran', 'total' => 'Total Pengeluaran'],
    ];

    /**
     * @param  array<string,array{current:float,previous:float}>  $amounts     realisasi per reference_key
     * @param  Collection  $references  kamus CashflowReference (ber-key reference_key)
     * @param  bool  $showEmpty  ikut tampilkan baris detail yang nol di kedua tahun
     * @return array<int,array<string,mixed>>
     */
    public static function build(array $amounts, Collection $references, bool $showEmpty = false): array
    {
        $tree = self::buildTree($amounts, $references);

        $rows = [];
        $sectionTotals = [];

        foreach (self::SECTIONS as $section) {
            $prefix = $section['prefix'];
            if (!isset($tree[$prefix])) {
                continue;
            }

            $rows[] = self::row('section', '', '', $section['roman'] . '. ' . $section['title']);
            $sectionCur = 0.0;
            $sectionPrev = 0.0;

            foreach (self::SUBSECTIONS as $suffix => $meta) {
                $groups = $tree[$prefix][$prefix . $suffix] ?? null;
                if ($groups === null) {
                    continue;
                }

                $rows[] = self::row('subsection', '', '', $section['roman'] . '.' . $meta['numeral'] . ' ' . $meta['label']);

                $subCur = 0.0;
                $subPrev = 0.0;

                foreach ($groups as $parentKey => $group) {
                    // Total sub-bagian selalu ikut dijumlah, termasuk grup yang
                    // barisnya disembunyikan, supaya angka total tetap utuh.
                    $subCur += $group['current'];
                    $subPrev += $group['previous'];

                    $items = $showEmpty
                        ? $group['items']
                        : array_values(array_filter(
                            $group['items'],
                            fn ($item) => !self::isZero($item['current']) || !self::isZero($item['previous'])
                        ));

                    // Grup tanpa realisasi sama sekali disembunyikan agar laporan
                    // tetap ringkas; centang "Tampilkan semua akun" untuk melihat
                    // kerangka lengkap seperti pada berkas Excel baku.
                    if (!$showEmpty && empty($items) && self::isZero($group['current']) && self::isZero($group['previous'])) {
                        continue;
                    }

                    $rows[] = self::row('group', $parentKey, '-', $group['name'], $group['current'], $group['previous']);

                    foreach ($items as $item) {
                        $rows[] = self::row('detail', $parentKey, $item['reference_key'], $item['uraian'], $item['current'], $item['previous']);
                    }
                }

                $rows[] = self::row('subtotal', '', '', $meta['total'], $subCur, $subPrev);
                $sectionCur += $subCur;
                $sectionPrev += $subPrev;
            }

            $rows[] = self::row('total', '', '', 'JUMLAH ' . $section['title'], $sectionCur, $sectionPrev);
            $rows[] = self::row('spacer', '', '', '');

            $sectionTotals[] = [$sectionCur, $sectionPrev];
        }

        // Dampak Perubahan Kurs (grup D) ikut menambah arus kas bersih.
        $netCur = array_sum(array_column($sectionTotals, 0));
        $netPrev = array_sum(array_column($sectionTotals, 1));

        foreach (self::flatten($tree['D'] ?? []) as $item) {
            $rows[] = self::row('plain', $item['parent_key'], $item['reference_key'], $item['uraian'], $item['current'], $item['previous']);
            $netCur += $item['current'];
            $netPrev += $item['previous'];
        }

        $rows[] = self::row('net', '', '', 'Kenaikan (Penurunan) Arus Kas Bersih', $netCur, $netPrev);
        $rows[] = self::row('spacer', '', '', '');

        // Bank Clearing & reklasifikasi (grup E) tidak masuk arus kas bersih,
        // tapi ikut menentukan pergerakan saldo kas periode berjalan.
        $closingCur = $netCur;
        $closingPrev = $netPrev;

        foreach (self::flatten($tree['E'] ?? []) as $item) {
            $rows[] = self::row('plain', $item['parent_key'], $item['reference_key'], $item['uraian'], $item['current'], $item['previous']);
            $closingCur += $item['current'];
            $closingPrev += $item['previous'];
        }

        $rows[] = self::row('closing', '', '', 'Pergerakan Kas Bersih Periode Ini', $closingCur, $closingPrev);

        return $rows;
    }

    /**
     * Ringkasan angka untuk kartu statistik di atas tabel.
     *
     * @param  array<int,array<string,mixed>>  $rows  keluaran build()
     * @return array<string,float>
     */
    public static function summarize(array $rows): array
    {
        $penerimaan = 0.0;
        $penerimaanLalu = 0.0;
        $pengeluaran = 0.0;
        $pengeluaranLalu = 0.0;
        $bersih = 0.0;
        $bersihLalu = 0.0;

        foreach ($rows as $r) {
            if ($r['type'] === 'subtotal' && $r['uraian'] === 'Total Penerimaan') {
                $penerimaan += $r['current'];
                $penerimaanLalu += $r['previous'];
            } elseif ($r['type'] === 'subtotal' && $r['uraian'] === 'Total Pengeluaran') {
                $pengeluaran += $r['current'];
                $pengeluaranLalu += $r['previous'];
            } elseif ($r['type'] === 'net') {
                $bersih = $r['current'];
                $bersihLalu = $r['previous'];
            }
        }

        return [
            'penerimaan' => $penerimaan,
            'penerimaan_lalu' => $penerimaanLalu,
            'pengeluaran' => $pengeluaran,
            'pengeluaran_lalu' => $pengeluaranLalu,
            'bersih' => $bersih,
            'bersih_lalu' => $bersihLalu,
        ];
    }

    /**
     * Susun pohon: [huruf bagian][kode 3-huruf][parent_key] => grup + detail.
     * Reference key yang ada di transaksi tapi belum terdaftar di kamus tetap
     * ikut tampil (memakai 5 huruf pertama sebagai parent) supaya tidak ada
     * nilai yang hilang diam-diam dari laporan.
     */
    private static function buildTree(array $amounts, Collection $references): array
    {
        $catalog = [];

        foreach ($references as $ref) {
            $catalog[$ref->reference_key] = [
                'reference_key' => $ref->reference_key,
                'parent_key' => $ref->parent_key ?: substr($ref->reference_key, 0, 5),
                'parent_name' => $ref->parent_name ?: $ref->uraian,
                'uraian' => $ref->uraian,
            ];
        }

        foreach (array_keys($amounts) as $key) {
            if (isset($catalog[$key])) {
                continue;
            }

            $catalog[$key] = [
                'reference_key' => $key,
                'parent_key' => substr($key, 0, 5),
                'parent_name' => $key,
                'uraian' => $key . ' (belum terdaftar di kamus referensi)',
            ];
        }

        ksort($catalog);

        $tree = [];
        foreach ($catalog as $key => $meta) {
            $sectionKey = substr($key, 0, 1);
            $subKey = substr($key, 0, 3);
            $parentKey = $meta['parent_key'];

            $current = (float) ($amounts[$key]['current'] ?? 0);
            $previous = (float) ($amounts[$key]['previous'] ?? 0);

            if (!isset($tree[$sectionKey][$subKey][$parentKey])) {
                $tree[$sectionKey][$subKey][$parentKey] = [
                    'name' => $meta['parent_name'],
                    'current' => 0.0,
                    'previous' => 0.0,
                    'items' => [],
                ];
            }

            $tree[$sectionKey][$subKey][$parentKey]['current'] += $current;
            $tree[$sectionKey][$subKey][$parentKey]['previous'] += $previous;
            $tree[$sectionKey][$subKey][$parentKey]['items'][] = [
                'reference_key' => $key,
                'parent_key' => $parentKey,
                'uraian' => $meta['uraian'],
                'current' => $current,
                'previous' => $previous,
            ];
        }

        return $tree;
    }

    /** Ratakan sub-pohon satu bagian menjadi daftar baris detail. */
    private static function flatten(array $sectionTree): array
    {
        $items = [];
        foreach ($sectionTree as $groups) {
            foreach ($groups as $group) {
                foreach ($group['items'] as $item) {
                    $items[] = $item;
                }
            }
        }

        return $items;
    }

    private static function row(string $type, string $kode, string $reference, string $uraian, float $current = 0.0, float $previous = 0.0): array
    {
        return [
            'type' => $type,
            'kode' => $kode,
            'reference' => $reference,
            'uraian' => $uraian,
            'current' => round($current, 2),
            'previous' => round($previous, 2),
        ];
    }

    private static function isZero(float $value): bool
    {
        return abs($value) < 0.005;
    }
}
