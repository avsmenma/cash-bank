<?php
namespace App\Models;

use App\Models\Dokumen;
use App\Models\BankTujuan;
use App\Models\SumberDana;
use App\Models\SubKriteria;
use App\Models\ItemSubKriteria;
use App\Models\JenisPembayaran;
use App\Models\KategoriKriteria;
use Illuminate\Database\Eloquent\Model;

class BankMasuk extends Model
{
    protected $table = 'bank_masuk';
    protected $primaryKey = 'id_bank_masuk';

    protected $fillable = [
        'agenda_tahun',
        'no_bukti',
        'id_sumber_dana',
        'id_bank_tujuan',
        'id_kategori_kriteria',
        'id_jenis_pembayaran',         
        'uraian',
        'nilai_rupiah',
        'penerima',
        'tanggal',  
        'debet',          
        'kredit',
        'keterangan',
    ];

    public $timestamps = true;

    // Relasi
    public function sumberDana()
    {
        return $this->belongsTo(SumberDana::class, 'id_sumber_dana', 'id_sumber_dana');
    }

    public function bankTujuan()
    {
        return $this->belongsTo(BankTujuan::class, 'id_bank_tujuan', 'id_bank_tujuan');
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriKriteria::class, 'id_kategori_kriteria', 'id_kategori_kriteria');
    }

    public function jenisPembayaran()
    {
        return $this->belongsTo(JenisPembayaran::class,'id_jenis_pembayaran', 'id_jenis_pembayaran');
    }
}
