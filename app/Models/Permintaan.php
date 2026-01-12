<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permintaan extends Model
{
    protected $primaryKey = 'id_permintaan';
     protected $fillable = [
        'id_kategori_kriteria',
        'id_sub_kriteria',
        'id_item_sub_kriteria',
        'M1',
        'M2',
        'M3',
        'M4',
        'bulan',
        'tahun',
    ];
    public $timestamps = true;

    public function kategori()
    {
        return $this->belongsTo(KategoriKriteria::class, 'id_kategori_kriteria', 'id_kategori_kriteria');
    }

    public function subKriteria()
    {
        return $this->belongsTo(SubKriteria::class, 'id_sub_kriteria','id_sub_kriteria');
    }

    public function itemSubKriteria()
    {
        return $this->belongsTo(ItemSubKriteria::class, 'id_item_sub_kriteria','id_item_sub_kriteria');
    }
}
