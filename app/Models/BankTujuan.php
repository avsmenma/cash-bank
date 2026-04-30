<?php

namespace App\Models;

use App\Models\BankMasuk;
use App\Models\BankKeluar;
use Illuminate\Database\Eloquent\Model;

class BankTujuan extends Model
{
    protected $table = 'bank_tujuan';
    protected $primaryKey = 'id_bank_tujuan';
    protected $fillable = ['nama_tujuan', 'sap'];

    public function bankMasuk()
    {
        return $this->hasMany(BankMasuk::class, 'id_bank_tujuan', 'id_bank_tujuan');
    }

    public function bankKeluar()
    {
        return $this->hasMany(BankKeluar::class, 'id_bank_tujuan', 'id_bank_tujuan');
    }
}
