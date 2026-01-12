<?php

namespace App\Models;

use App\Models\SubKriteria;
use Illuminate\Database\Eloquent\Model;

class ItemSubKriteria extends Model
{
    protected $table = 'item_sub_kriteria';
    protected $primaryKey = 'id_item_sub_kriteria';
    protected $fillable = ['id_sub_kriteria', 'nama_item_sub_kriteria'];

    public function subKriteria()
    {
        return $this->belongsTo(
            SubKriteria::class,
            'id_sub_kriteria',
            'id_sub_kriteria'
        );
    }
}