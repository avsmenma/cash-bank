<?php

namespace App\Support;

class DashboardKriteriaHierarchy
{
    public static function structure(): array
    {
        return [
            'Kebutuhan Gaji, Upah dan Tunjangan' => [
                'Karyawan Pimpinan' => [
                    'Gaji dan Tunjangan',
                    'Cuti Tahunan',
                    'Cuti Panjang',
                    'T H R',
                    'Bonus',
                    'PPh pasal 21',
                    'Iuran Dapenbun (Normal)',
                    'Penghargaan Masa Kerja',
                    'Iuran BPJS B. Perusahaan',
                    'SHT (Cicilan)',
                    'Lainnya',
                ],
                'Karyawan Pelaksana' => [
                    'Gaji dan Tunjangan',
                    'Lembur',
                    'Premi',
                    'Cuti Tahunan',
                    'Cuti Panjang',
                    'T H R',
                    'Bonus',
                    'PPh pasal 21',
                    'Iuran Dapenbun (Normal)',
                    'Iuran Dapenbun (Tambahan)',
                    'Penghargaan Masa Kerja',
                    'Iuran BPJS B. Perusahaan',
                    'SHT (Cicilan)',
                    'Lainnya',
                ],
            ],
            'Payment Requirement for Exploitation Activity' => [
                'TBS (FFB)' => [''],
                'Operasional Produksi' => [
                    'Pemeliharaan Tanaman Menghasilkan',
                    'Pemupukan',
                    'Bahan',
                    'Aplikasi Pemupukan',
                    'Panen & Pengumpulan',
                    'Pengangkutan',
                    'Pengolahan',
                    'Pembelian Bahan Bakar Minyak (BBM)',
                    'Lainnya',
                ],
                'Biaya Usaha dan lainnya' => [
                    'Biaya Pengiriman ke Pelabuhan',
                    'Biaya Pelabuhan',
                    'Biaya Jasa KPBN',
                    'Biaya Pemasaran Lainnya',
                    'Biaya Pengangkutan, Perjalanan & Penginapan',
                    'Biaya Pemeliharaan Bangunan, Mesin, Jalan dan Instalasi',
                    'Biaya Keamanan',
                    'Biaya Pemeliharaan Perlengkapan Kantor',
                    'Biaya Pajak dan Retribusi',
                    'Biaya Premi Asuransi',
                    'Biaya Pengendalian Lingkungan (ISO 14000)',
                    'Biaya Sistem Manajemen Kesehatan & Keselamatan Kerja',
                    'Biaya Sumbangan dan Iuran',
                    'Biaya CSR',
                    'Biaya Pendidikan dan Pengembangan SDM',
                    'Biaya Konsultan',
                    'Biaya Audit',
                    'Utilities (Air, Listrik, ATK, Brg Umum, Sewa Kantor)',
                    'Biaya Media',
                    'Lainnya',
                ],
                'Pajak' => [
                    'PPh Badan',
                    'PBB',
                    'PPH Masa',
                    'PPN',
                    'BPHTB',
                    'Denda Pajak',
                ],
            ],
            'Kebutuhan Pembayaran Pekerjaan Aktivitas Investasi' => [
                'Investasi On Farm' => [
                    'Pekerjaan TU, TK, TB',
                    'Pekerjaan Pemeliharaan TBM',
                    'Pupuk',
                    'Pekerjaan Pemeliharaan TBM diluar Pupuk',
                    'Pembangunan bibitan',
                ],
                'Investasi Off Farm' => [
                    'Pekerjaan Pembangunan Rumah',
                    'Pekerjaan Pembangunan Perusahaan',
                    'Pekerjaan Pembangunan Mesin dan Instalasi',
                    'Pekerjaan Pembangunan Jalan, Jembatan dan Saluran Air',
                    'Pekerjaan Alat Angkutan',
                    'Pekerjaan Inventaris kecil',
                    'Pekerjaan Investasi Off Farm Lainnya',
                    'KSO',
                    'Penyertaan Modal',
                ],
                'Pembayaran investasi lainnya' => [
                    'Instalasi Pembibitan',
                    'HGU',
                    'Pengeluaran Plasma',
                    'Pengeluaran biaya PT NB',
                    'Pengeluaran biaya PT KMN',
                ],
            ],
        ];
    }

    public static function normalizePath(?string $category, ?string $sub, ?string $item): array
    {
        $category = trim((string) $category);
        $sub = trim((string) $sub);
        $item = trim((string) $item);

        $category = self::aliasFrom(self::categoryAliases(), $category, $category ?: '-');

        foreach (self::pathAliases() as $alias => $path) {
            if (self::samePath($alias, $category, $sub, $item)) {
                return $path;
            }
        }

        $sub = self::aliasFrom(self::subAliases(), $sub, $sub ?: '-');
        $item = self::aliasFrom(self::itemAliases(), $item, $item ?: '-');

        if ($category === 'Payment Requirement for Exploitation Activity' && $sub === 'TBS (FFB)') {
            $item = self::norm($item) === self::norm('TBS (FFB)') ? '' : $item;
        }

        return [$category, $sub, $item];
    }

    public static function normalizeFlatRows(array $rows): array
    {
        $normalized = [];

        foreach ($rows as $row) {
            [$category, $sub, $item] = self::normalizePath(
                $row['kategori'] ?? '',
                $row['sub_kriteria'] ?? '',
                $row['item_kriteria'] ?? ''
            );

            $key = implode('|', [$category, $sub, $item]);

            if (!isset($normalized[$key])) {
                $row['kategori'] = $category;
                $row['sub_kriteria'] = $sub;
                $row['item_kriteria'] = $item;
                $normalized[$key] = $row;
                continue;
            }

            $normalized[$key]['data'] = self::mergeNumeric(
                $normalized[$key]['data'] ?? [],
                $row['data'] ?? []
            );
        }

        uasort($normalized, fn ($a, $b) => self::comparePath(
            [$a['kategori'] ?? '', $a['sub_kriteria'] ?? '', $a['item_kriteria'] ?? ''],
            [$b['kategori'] ?? '', $b['sub_kriteria'] ?? '', $b['item_kriteria'] ?? '']
        ));

        return $normalized;
    }

    public static function ensureFlatRows(array $rows, array $periodKeys): array
    {
        $rows = self::normalizeFlatRows($rows);
        $template = [];

        foreach ($periodKeys as $key => $value) {
            $template[is_int($key) ? $value : $key] = 0;
        }

        foreach (self::structure() as $category => $subs) {
            foreach ($subs as $sub => $items) {
                foreach ($items as $item) {
                    $rowKey = implode('|', [$category, $sub, $item]);

                    if (!isset($rows[$rowKey])) {
                        $rows[$rowKey] = [
                            'kategori' => $category,
                            'sub_kriteria' => $sub,
                            'item_kriteria' => $item,
                            'data' => $template,
                        ];
                    }
                }
            }
        }

        uasort($rows, fn ($a, $b) => self::comparePath(
            [$a['kategori'] ?? '', $a['sub_kriteria'] ?? '', $a['item_kriteria'] ?? ''],
            [$b['kategori'] ?? '', $b['sub_kriteria'] ?? '', $b['item_kriteria'] ?? '']
        ));

        return $rows;
    }

    public static function normalizeNested(array $rows): array
    {
        $normalized = [];

        foreach ($rows as $category => $subs) {
            foreach ($subs as $sub => $items) {
                foreach ($items as $item => $value) {
                    [$nextCategory, $nextSub, $nextItem] = self::normalizePath($category, $sub, $item);

                    if (!isset($normalized[$nextCategory][$nextSub][$nextItem])) {
                        $normalized[$nextCategory][$nextSub][$nextItem] = $value;
                        continue;
                    }

                    $normalized[$nextCategory][$nextSub][$nextItem] = self::mergeNumeric(
                        $normalized[$nextCategory][$nextSub][$nextItem],
                        $value
                    );
                }
            }
        }

        return self::sortNested($normalized);
    }

    public static function ensureNestedRows(array $rows, $template): array
    {
        foreach (self::structure() as $category => $subs) {
            foreach ($subs as $sub => $items) {
                foreach ($items as $item) {
                    if (!isset($rows[$category][$sub][$item])) {
                        $rows[$category][$sub][$item] = $template;
                    }
                }
            }
        }

        return self::sortNested($rows);
    }

    public static function normalizeByMonth(array $rows): array
    {
        foreach ($rows as $month => $monthRows) {
            $rows[$month] = self::normalizeNested($monthRows);
        }

        ksort($rows);

        return $rows;
    }

    public static function sortNested(array $rows): array
    {
        uksort($rows, fn ($a, $b) => self::compareCategory($a, $b));

        foreach ($rows as $category => &$subs) {
            uksort($subs, fn ($a, $b) => self::compareSub($category, $a, $b));

            foreach ($subs as $sub => &$items) {
                uksort($items, fn ($a, $b) => self::compareItem($category, $sub, $a, $b));
            }
            unset($items);
        }
        unset($subs);

        return $rows;
    }

    public static function sortCategorySub(array $rows): array
    {
        uksort($rows, fn ($a, $b) => self::compareCategory($a, $b));

        foreach ($rows as $category => &$subs) {
            uksort($subs, fn ($a, $b) => self::compareSub($category, $a, $b));
        }
        unset($subs);

        return $rows;
    }

    private static function categoryAliases(): array
    {
        return [
            'Kebutuhan Pembayaran Aktivitas Ekploitasi' => 'Payment Requirement for Exploitation Activity',
            'Payment Requirement For Exploitation Activity' => 'Payment Requirement for Exploitation Activity',
            'Kebutuhan Pekerjaan Aktivitas Investasi' => 'Kebutuhan Pembayaran Pekerjaan Aktivitas Investasi',
            'Kebutuhan Pembayaran Pekerja Aktivits Investasi' => 'Kebutuhan Pembayaran Pekerjaan Aktivitas Investasi',
            'Kebutuhan Pembayaran Pekerja Aktivitas Investasi' => 'Kebutuhan Pembayaran Pekerjaan Aktivitas Investasi',
        ];
    }

    private static function subAliases(): array
    {
        return [
            'Biaya Usaha dan Lainnya' => 'Biaya Usaha dan lainnya',
            'Pembelian TBS' => 'TBS (FFB)',
            'Purchase Volume' => 'TBS (FFB)',
        ];
    }

    private static function itemAliases(): array
    {
        return [
            'THR' => 'T H R',
            'PPh Pasal 21' => 'PPh pasal 21',
            'TBS' => '',
            'TBS (FFB)' => '',
            'Pemeliharaan Tanaman Menghasilkan' => 'Pemeliharaan Tanaman Menghasilkan',
            'Bahan Pemupukan' => 'Bahan',
            'Pengangkutan (TBS)' => 'Pengangkutan',
            'Biaya Pengiriman ke Pelabuhan (CPO)' => 'Biaya Pengiriman ke Pelabuhan',
            'Biaya Pemeliharaan Bangunan, Mesin, Jalan dan lainnya' => 'Biaya Pemeliharaan Bangunan, Mesin, Jalan dan Instalasi',
            'Biaya Pemeliharaan Bangunan, Mesin, Jalan dan Instalasi (DTPL)' => 'Biaya Pemeliharaan Bangunan, Mesin, Jalan dan Instalasi',
            'Biaya Pemeliharaan Bangunan, Mesin, Jalan dan Instalasi (DINF)' => 'Biaya Pemeliharaan Bangunan, Mesin, Jalan dan Instalasi',
            'Biaya Pemeliharaan Bangunan, Mesin, Jalan dan Instalasi (DINP)' => 'Biaya Pemeliharaan Bangunan, Mesin, Jalan dan Instalasi',
            'Biaya Pengendalian Lingkungan (ISO/RSPO)' => 'Biaya Pengendalian Lingkungan (ISO 14000)',
            'Biaya Konsultan Hukum' => 'Biaya Konsultan',
            'Biaya Konsultan (DSKP)' => 'Biaya Konsultan',
            'Biaya Konsultan (DRPH)' => 'Biaya Konsultan',
            'Biaya Konsultan (DAPN)' => 'Biaya Konsultan',
            'Biaya Konsultan (DSPN)' => 'Biaya Konsultan',
            'Biaya Konsultan (DSSM)' => 'Biaya Konsultan',
            'Biaya Konsultan (DTPL)' => 'Biaya Konsultan',
            'Biaya Konsultan (DAPR)' => 'Biaya Konsultan',
            'PPh Masa' => 'PPH Masa',
            'PPh 23' => 'PPH Masa',
            'PPh Pasal 4' => 'PPH Masa',
            'PPh Pasal 25' => 'PPH Masa',
            'Pekerjaan TU,TK,TB.' => 'Pekerjaan TU, TK, TB',
            'Pekerjaan Tanaman Ulang (TU)' => 'Pekerjaan TU, TK, TB',
            'Pekerjaan Tanaman Konversi (TK)' => 'Pekerjaan TU, TK, TB',
            'Pekerjaan Tanaman Baru (TB)' => 'Pekerjaan TU, TK, TB',
            'Pekerjaan Pemeliharaan TBM (Pupuk)' => 'Pekerjaan Pemeliharaan TBM',
            'Pekerjaan Pembangunan Rumah Perusahaan' => 'Pekerjaan Pembangunan Rumah',
            'Pekerjaan Bangunan Perusahaan' => 'Pekerjaan Pembangunan Perusahaan',
            'Pekerjaaan Pembangunan Mesin dan Instalasi' => 'Pekerjaan Pembangunan Mesin dan Instalasi',
            'Pekerjaan Pembangunan Jalan, Jembatan dan Saluran Air' => 'Pekerjaan Pembangunan Jalan, Jembatan dan Saluran Air',
            'Pekerjaan Pembangunan Jalan/Jembatan/Saluran Air' => 'Pekerjaan Pembangunan Jalan, Jembatan dan Saluran Air',
            'Pekerjaan Pembangunan Jalan' => 'Pekerjaan Pembangunan Jalan, Jembatan dan Saluran Air',
            'Pekerjaan Pembangunan Jembatan' => 'Pekerjaan Pembangunan Jalan, Jembatan dan Saluran Air',
            'Pekerjaan Pembangunan Saluran Air' => 'Pekerjaan Pembangunan Jalan, Jembatan dan Saluran Air',
            'Pekerjaan Alat Transportasi/Alat Berat/Bengkel' => 'Pekerjaan Alat Angkutan',
            'Pekerjaan Alat Transportasi (Infrastruktur)' => 'Pekerjaan Alat Angkutan',
            'Pekerjaan Alat Transportasi (Investasi Tanaman)' => 'Pekerjaan Alat Angkutan',
            'Pekerjaan Alat Berat' => 'Pekerjaan Alat Angkutan',
            'Pekerjaan Investasi Off Farm Lainnya (Non Tanaman)' => 'Pekerjaan Investasi Off Farm Lainnya',
            'Pekerjaan Investasi Off Farm Lainnya (DTPL)' => 'Pekerjaan Investasi Off Farm Lainnya',
            'Pekerjaan Investasi Off Farm Lainnya (DITN)' => 'Pekerjaan Investasi Off Farm Lainnya',
            'Pekerjaan Investasi Off Farm Lainnya (DINF)' => 'Pekerjaan Investasi Off Farm Lainnya',
            'Pekerjaan Inventaris Kecil (Non Tanaman)' => 'Pekerjaan Inventaris kecil',
            'Pekerjaan Inventaris Kecil' => 'Pekerjaan Inventaris kecil',
            'Penyertaan Modal (Non Tanaman)' => 'Penyertaan Modal',
        ];
    }

    private static function pathAliases(): array
    {
        return [
            'Payment Requirement for Exploitation Activity|Purchase Volume|TBS (FFB)' => [
                'Payment Requirement for Exploitation Activity',
                'TBS (FFB)',
                '',
            ],
        ];
    }

    private static function aliasFrom(array $aliases, string $value, string $default): string
    {
        $normalized = self::norm($value);

        foreach ($aliases as $alias => $canonical) {
            if (self::norm($alias) === $normalized) {
                return $canonical;
            }
        }

        return $default;
    }

    private static function samePath(string $alias, string $category, string $sub, string $item): bool
    {
        [$aliasCategory, $aliasSub, $aliasItem] = array_pad(explode('|', $alias), 3, '');

        return self::norm($aliasCategory) === self::norm($category)
            && self::norm($aliasSub) === self::norm($sub)
            && self::norm($aliasItem) === self::norm($item);
    }

    private static function comparePath(array $left, array $right): int
    {
        return self::compareValues(self::pathSortKey($left), self::pathSortKey($right));
    }

    private static function compareCategory(string $left, string $right): int
    {
        return self::compareValues(self::categorySortKey($left), self::categorySortKey($right));
    }

    private static function compareSub(string $category, string $left, string $right): int
    {
        return self::compareValues(self::subSortKey($category, $left), self::subSortKey($category, $right));
    }

    private static function compareItem(string $category, string $sub, string $left, string $right): int
    {
        return self::compareValues(self::itemSortKey($category, $sub, $left), self::itemSortKey($category, $sub, $right));
    }

    private static function pathSortKey(array $path): array
    {
        [$category, $sub, $item] = array_pad($path, 3, '');

        return array_merge(
            self::categorySortKey($category),
            self::subSortKey($category, $sub),
            self::itemSortKey($category, $sub, $item)
        );
    }

    private static function categorySortKey(string $category): array
    {
        $categories = array_keys(self::structure());
        $index = array_search($category, $categories, true);

        return [$index === false ? 999 : $index, self::norm($category)];
    }

    private static function subSortKey(string $category, string $sub): array
    {
        $subs = array_keys(self::structure()[$category] ?? []);
        $index = array_search($sub, $subs, true);

        return [$index === false ? 999 : $index, self::norm($sub)];
    }

    private static function itemSortKey(string $category, string $sub, string $item): array
    {
        $items = self::structure()[$category][$sub] ?? [];
        $index = array_search($item, $items, true);

        return [$index === false ? 999 : $index, self::norm($item)];
    }

    private static function compareValues(array $left, array $right): int
    {
        $limit = max(count($left), count($right));

        for ($i = 0; $i < $limit; $i++) {
            $a = $left[$i] ?? null;
            $b = $right[$i] ?? null;

            if ($a === $b) {
                continue;
            }

            return $a <=> $b;
        }

        return 0;
    }

    private static function mergeNumeric($base, $incoming)
    {
        if (is_array($base) && is_array($incoming)) {
            foreach ($incoming as $key => $value) {
                $base[$key] = array_key_exists($key, $base)
                    ? self::mergeNumeric($base[$key], $value)
                    : $value;
            }

            return $base;
        }

        if (is_numeric($base) && is_numeric($incoming)) {
            return $base + $incoming;
        }

        return $incoming;
    }

    private static function norm(?string $value): string
    {
        $value = strtolower((string) $value);
        $value = str_replace('&', ' dan ', $value);
        $value = preg_replace('/[^a-z0-9]+/', ' ', $value);

        return trim(preg_replace('/\s+/', ' ', $value));
    }
}
