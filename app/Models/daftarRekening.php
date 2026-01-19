<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DaftarRekening extends Model
{
  use HasFactory;
  protected $table = ('daftarrekenings');

  protected $fillable = [
    'id_daftar_bank',
    'nomor_rekening'
  ];

  public $timestamps = true;

  // Relasi
  public function bank()
  {
    return $this->belongsTo(DaftarBank::class, 'id_daftar_bank', 'id_daftar_bank');
  }
}
