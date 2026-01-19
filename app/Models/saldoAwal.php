<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaldoAwal extends Model
{
    protected $table = 'saldo_awals'; // sesuaikan dengan nama tabel

    protected $fillable = [
        'id_daftar_bank',
        'id_rekening',
        'saldo_awal'
    ];

    public function bank()
    {
        return $this->belongsTo(DaftarBank::class, 'id_daftar_bank', 'id_daftar_bank');
    }

    public function rekening()
    {
        return $this->belongsTo(DaftarRekening::class, 'id_rekening', 'id_rekening');
    }
}
