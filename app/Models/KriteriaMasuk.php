<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KriteriaMasuk extends Model
{
    protected $table = 'kriteria_masuks';
    protected $primaryKey = 'id_kriteria_masuk';
    protected $fillable = ['id_kriteria_masuk', 'nama_kriteria_masuk'];
}
