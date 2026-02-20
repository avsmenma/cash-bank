<?php

namespace App\Models;

use App\Models\KategoriKriteria;
use Illuminate\Database\Eloquent\Model;

class Penerima extends Model
{
    protected $primaryKey = 'id_penerima';

    protected $fillable = [
        'kontrak',
        'id_kategori_kriteria',
        'pembeli',
        'no_reg',
        'tanggal',
        'volume',
        'harga',
        'nilai',
        'ppn',
        'potppn',
        'nilai_inc_ppn',
    ];

    public function kategori()
    {
        return $this->belongsTo(KategoriKriteria::class, 'id_kategori_kriteria', 'id_kategori_kriteria');
    }
}
