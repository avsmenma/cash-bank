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
 *
 * Laporan dapat memuat BEBERAPA SERI angka sekaligus (mis. seri "global" untuk
 * gabungan seluruh unit, ditambah satu seri per unit/kebun pada halaman admin).
 * Seluruh seri dijumlah dalam satu jalur kode yang sama, sehingga total global
 * dan total tiap unit dijamin memakai aturan penjumlahan yang identik.
 */
class CashflowReportBuilder
{
    /** Nama seri utama; nilainya yang dipakai kolom Realisasi & kartu ringkasan. */
    public const SERI_GLOBAL = 'global';

    /** Bagian utama laporan beserta penomoran romawi & judulnya. */
    private const SECTIONS = [
        ['prefix' => 'A', 'roman' => 'I',   'title' => 'ARUS KAS DARI AKTIVITAS PENDANAAN OPERASI'],
        ['prefix' => 'B', 'roman' => 'II',  'title' => 'ARUS KAS DARI AKTIVITAS PENDANAAN INVESTASI'],
        ['prefix' => 'C', 'roman' => 'III', 'title' => 'ARUS KAS DARI AKTIVITAS PENDANAAN'],
    ];

    /** Sub-bagian: akhiran kode 3-huruf -> penomoran, label & judul baris totalnya. */
    private const SUBSECTIONS = [
        '01' => ['numeral' => 'I',  'label' => 'Penerimaan',  'total' => 'Total Penerimaan'],
        '02' => ['numeral' => 'II', 'label' => 'Pengeluaran', 'total' => 'Total Pengeluaran'],
    ];

    /**
     * Laporan satu seri (dipakai halaman unit/kebun).
     *
     * @param  array<string,array{current:float,previous:float}>  $amounts  realisasi per reference_key
     * @param  Collection  $references  kamus CashflowReference (ber-key reference_key)
     * @param  bool  $showEmpty  ikut tampilkan baris detail yang nol di kedua tahun
     * @return array<int,array<string,mixed>>
     */
    public static function build(array $amounts, Collection $references, bool $showEmpty = false): array
    {
        $rows = self::buildMulti([self::SERI_GLOBAL => $amounts], $references, $showEmpty);

        // Halaman satu seri tidak butuh rincian per seri.
        foreach ($rows as &$row) {
            unset($row['values']);
        }

        return $rows;
    }

    /**
     * Laporan banyak seri (dipakai halaman admin: global + tiap unit/kebun).
     *
     * Baris tetap membawa `current`/`previous` berisi angka seri global supaya
     * bisa dipakai kode yang sama dengan laporan satu seri, ditambah `values`
     * berisi angka seluruh seri.
     *
     * @param  array<string,array<string,array{current:float,previous:float}>>  $series
     *         [nama seri => [reference_key => ['current' => x, 'previous' => y]]]
     * @param  Collection  $references
     * @param  bool  $showEmpty  ikut tampilkan baris yang nol di SEMUA seri
     * @return array<int,array<string,mixed>>
     */
    public static function buildMulti(array $series, Collection $references, bool $showEmpty = false): array
    {
        $seriesKeys = array_keys($series);
        if (!in_array(self::SERI_GLOBAL, $seriesKeys, true)) {
            array_unshift($seriesKeys, self::SERI_GLOBAL);
            $series = [self::SERI_GLOBAL => []] + $series;
        }

        $tree = self::buildTree($series, $seriesKeys, $references);

        $rows = [];
        $sectionTotals = [];

        foreach (self::SECTIONS as $section) {
            $prefix = $section['prefix'];
            if (!isset($tree[$prefix])) {
                continue;
            }

            $rows[] = self::row('section', '', '', $section['roman'] . '. ' . $section['title'], self::zero($seriesKeys));
            $sectionSum = self::zero($seriesKeys);

            foreach (self::SUBSECTIONS as $suffix => $meta) {
                $groups = $tree[$prefix][$prefix . $suffix] ?? null;
                if ($groups === null) {
                    continue;
                }

                $rows[] = self::row('subsection', '', '', $section['roman'] . '.' . $meta['numeral'] . ' ' . $meta['label'], self::zero($seriesKeys));

                $subSum = self::zero($seriesKeys);

                foreach ($groups as $parentKey => $group) {
                    // Total sub-bagian selalu ikut dijumlah, termasuk grup yang
                    // barisnya disembunyikan, supaya angka total tetap utuh.
                    $subSum = self::add($subSum, $group['values']);

                    $items = $showEmpty
                        ? $group['items']
                        : array_values(array_filter(
                            $group['items'],
                            fn ($item) => !self::isEmpty($item['values'])
                        ));

                    // Grup tanpa realisasi di seri mana pun disembunyikan agar
                    // laporan tetap ringkas; centang "Tampilkan semua akun" untuk
                    // melihat kerangka lengkap seperti pada berkas Excel baku.
                    if (!$showEmpty && empty($items) && self::isEmpty($group['values'])) {
                        continue;
                    }

                    $rows[] = self::row('group', $parentKey, '-', $group['name'], $group['values']);

                    foreach ($items as $item) {
                        $rows[] = self::row('detail', $parentKey, $item['reference_key'], $item['uraian'], $item['values']);
                    }
                }

                $rows[] = self::row('subtotal', '', '', $meta['total'], $subSum);
                $sectionSum = self::add($sectionSum, $subSum);
            }

            $rows[] = self::row('total', '', '', 'JUMLAH ' . $section['title'], $sectionSum);
            $rows[] = self::row('spacer', '', '', '', self::zero($seriesKeys));

            $sectionTotals[] = $sectionSum;
        }

        // Dampak Perubahan Kurs (grup D) ikut menambah arus kas bersih.
        $net = self::zero($seriesKeys);
        foreach ($sectionTotals as $total) {
            $net = self::add($net, $total);
        }

        foreach (self::flatten($tree['D'] ?? []) as $item) {
            $rows[] = self::row('plain', $item['parent_key'], $item['reference_key'], $item['uraian'], $item['values']);
            $net = self::add($net, $item['values']);
        }

        $rows[] = self::row('net', '', '', 'Kenaikan (Penurunan) Arus Kas Bersih', $net);
        $rows[] = self::row('spacer', '', '', '', self::zero($seriesKeys));

        // Bank Clearing & reklasifikasi (grup E) tidak masuk arus kas bersih,
        // tapi ikut menentukan pergerakan saldo kas periode berjalan.
        $closing = $net;

        foreach (self::flatten($tree['E'] ?? []) as $item) {
            $rows[] = self::row('plain', $item['parent_key'], $item['reference_key'], $item['uraian'], $item['values']);
            $closing = self::add($closing, $item['values']);
        }

        $rows[] = self::row('closing', '', '', 'Pergerakan Kas Bersih Periode Ini', $closing);

        return $rows;
    }

    /**
     * Ringkasan angka seri global untuk kartu statistik di atas tabel.
     *
     * @param  array<int,array<string,mixed>>  $rows  keluaran build()/buildMulti()
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
    private static function buildTree(array $series, array $seriesKeys, Collection $references): array
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

        foreach ($series as $amounts) {
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
        }

        ksort($catalog);

        $tree = [];
        foreach ($catalog as $key => $meta) {
            $sectionKey = substr($key, 0, 1);
            $subKey = substr($key, 0, 3);
            $parentKey = $meta['parent_key'];

            $values = [];
            foreach ($seriesKeys as $s) {
                $values[$s] = [
                    'current' => (float) ($series[$s][$key]['current'] ?? 0),
                    'previous' => (float) ($series[$s][$key]['previous'] ?? 0),
                ];
            }

            if (!isset($tree[$sectionKey][$subKey][$parentKey])) {
                $tree[$sectionKey][$subKey][$parentKey] = [
                    'name' => $meta['parent_name'],
                    'values' => self::zero($seriesKeys),
                    'items' => [],
                ];
            }

            $tree[$sectionKey][$subKey][$parentKey]['values'] =
                self::add($tree[$sectionKey][$subKey][$parentKey]['values'], $values);

            $tree[$sectionKey][$subKey][$parentKey]['items'][] = [
                'reference_key' => $key,
                'parent_key' => $parentKey,
                'uraian' => $meta['uraian'],
                'values' => $values,
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

    /**
     * @param  array<string,array{current:float,previous:float}>  $values
     */
    private static function row(string $type, string $kode, string $reference, string $uraian, array $values): array
    {
        $bulat = [];
        foreach ($values as $seri => $v) {
            $bulat[$seri] = [
                'current' => round($v['current'], 2),
                'previous' => round($v['previous'], 2),
            ];
        }

        $global = $bulat[self::SERI_GLOBAL] ?? ['current' => 0.0, 'previous' => 0.0];

        return [
            'type' => $type,
            'kode' => $kode,
            'reference' => $reference,
            'uraian' => $uraian,
            'current' => $global['current'],
            'previous' => $global['previous'],
            'values' => $bulat,
        ];
    }

    /** Vektor nol untuk seluruh seri. */
    private static function zero(array $seriesKeys): array
    {
        $out = [];
        foreach ($seriesKeys as $s) {
            $out[$s] = ['current' => 0.0, 'previous' => 0.0];
        }

        return $out;
    }

    /** Jumlahkan dua vektor seri. */
    private static function add(array $a, array $b): array
    {
        foreach ($b as $seri => $v) {
            $a[$seri]['current'] = ($a[$seri]['current'] ?? 0) + $v['current'];
            $a[$seri]['previous'] = ($a[$seri]['previous'] ?? 0) + $v['previous'];
        }

        return $a;
    }

    /** Benar bila seluruh seri bernilai nol di kedua tahun. */
    private static function isEmpty(array $values): bool
    {
        foreach ($values as $v) {
            if (abs($v['current']) >= 0.005 || abs($v['previous']) >= 0.005) {
                return false;
            }
        }

        return true;
    }
}
