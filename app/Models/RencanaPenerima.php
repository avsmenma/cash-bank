<?php

namespace App\Models;

use App\Models\KategoriKriteria;
use Illuminate\Database\Eloquent\Model;

class RencanaPenerima extends Model
{
    protected $primaryKey = 'id_rencana_penerima';
    protected $fillable = [
        'id_kategori_kriteria',
        'januari',
        'februari',
        'maret',
        'april',
        'mei',
        'juni',
        'juli',
        'agustus',
        'september',
        'oktober',
        'november',
        'desember',
        'tahun',
    ];
    public $timestamps = true;

    public function kategori()
    {
        return $this->belongsTo(KategoriKriteria::class, 'id_kategori_kriteria');
    }
}
