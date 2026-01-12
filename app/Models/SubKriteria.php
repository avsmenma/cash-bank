<?php

namespace App\Models;

use App\Models\KategoriKriteria;
use Illuminate\Database\Eloquent\Model;

class SubKriteria extends Model
{
    protected $table = 'sub_kriteria';
    protected $primaryKey = 'id_sub_kriteria';
    protected $fillable = ['id_kategori_kriteria', 'nama_sub_kriteria'];

    public function kategori()
    {
        return $this->belongsTo(
            KategoriKriteria::class,
            'id_kategori_kriteria',
            'id_kategori_kriteria'
        );
    }
}
