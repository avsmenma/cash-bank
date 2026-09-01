<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CashflowLock extends Model
{
    use HasFactory;

    protected $table = 'cashflow_locks';

    protected $fillable = [
        'tahun',
        'bulan',
        'is_locked',
        'keterangan',
        'locked_by',
        'locked_at',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'bulan' => 'integer',
        'is_locked' => 'boolean',
        'locked_at' => 'datetime',
    ];

    public static function getMonthNames(): array
    {
        return [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
    }

    /**
     * Cek apakah bulan dan tahun tertentu berstatus terkunci.
     */
    public static function isLocked(int $tahun, int $bulan): bool
    {
        return (bool) static::where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->where('is_locked', true)
            ->exists();
    }

    /**
     * Dapatkan peta semua bulan yang terkunci dalam format ["{tahun}_{bulan}" => true].
     */
    public static function getLockedMap(): array
    {
        return static::where('is_locked', true)
            ->get(['tahun', 'bulan'])
            ->mapWithKeys(fn ($item) => ["{$item->tahun}_{$item->bulan}" => true])
            ->toArray();
    }

    /**
     * Dapatkan daftar status lock dan jumlah data transaksi untuk 12 bulan pada tahun tertentu.
     */
    public static function getStatusForYear(int $tahun): array
    {
        $monthNames = static::getMonthNames();

        // Ambil data lock yang tersimpan di DB
        $locks = static::where('tahun', $tahun)
            ->get()
            ->keyBy('bulan');

        // Ambil agregat jumlah record & total nilai dari tabel cashflows
        $cfStats = DB::table('cashflows')
            ->where('tahun', $tahun)
            ->groupBy('bulan')
            ->selectRaw('bulan, COUNT(*) as total_rows, SUM(amount) as total_amount')
            ->get()
            ->keyBy('bulan');

        $result = [];
        for ($m = 1; $m <= 12; $m++) {
            $lockRecord = $locks->get($m);
            $cfStat = $cfStats->get($m);

            $result[$m] = [
                'bulan' => $m,
                'nama_bulan' => $monthNames[$m],
                'is_locked' => $lockRecord ? (bool) $lockRecord->is_locked : false,
                'keterangan' => $lockRecord?->keterangan,
                'locked_by' => $lockRecord?->locked_by,
                'locked_at' => $lockRecord?->locked_at?->format('Y-m-d H:i:s'),
                'total_rows' => $cfStat ? (int) $cfStat->total_rows : 0,
                'total_amount' => $cfStat ? (float) $cfStat->total_amount : 0.0,
            ];
        }

        return $result;
    }

    /**
     * Set status lock untuk bulan dan tahun tertentu.
     */
    public static function setLock(int $tahun, int $bulan, bool $isLocked, ?string $user = null): static
    {
        return static::updateOrCreate(
            ['tahun' => $tahun, 'bulan' => $bulan],
            [
                'is_locked' => $isLocked,
                'locked_by' => $user ?: auth()->user()?->username ?? 'system',
                'locked_at' => $isLocked ? now() : null,
            ]
        );
    }

    /**
     * Set lock secara batch untuk daftar bulan tertentu pada satu tahun.
     */
    public static function batchSetLocks(int $tahun, array $lockedMonths, ?string $user = null): void
    {
        $user = $user ?: auth()->user()?->username ?? 'system';
        $now = now();

        for ($m = 1; $m <= 12; $m++) {
            $isLocked = in_array($m, $lockedMonths, true);
            static::updateOrCreate(
                ['tahun' => $tahun, 'bulan' => $m],
                [
                    'is_locked' => $isLocked,
                    'locked_by' => $user,
                    'locked_at' => $isLocked ? $now : null,
                ]
            );
        }
    }
}
