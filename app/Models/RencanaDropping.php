<?php

namespace App\Models;

use App\Models\SubKriteria;
use App\Models\ItemSubKriteria;
use App\Models\KategoriKriteria;
use Illuminate\Database\Eloquent\Model;

class RencanaDropping extends Model
{
    protected $primaryKey = 'id_rencana_dropping';
    protected $fillable = [
        'id_kategori_kriteria',
        'id_sub_kriteria',
        'id_item_sub_kriteria',
        'tahun',
        'januari','februari','maret','april','mei','juni',
        'juli','agustus','september','oktober','november','desember'
    ];
    public $timestamps = true;

    public function kategori()
    {
        return $this->belongsTo(KategoriKriteria::class, 'id_kategori_kriteria');
    }

    public function subKriteria()
    {
        return $this->belongsTo(SubKriteria::class, 'id_sub_kriteria');
    }

    public function itemSubKriteria()
    {
        return $this->belongsTo(ItemSubKriteria::class, 'id_item_sub_kriteria');
    }
}
