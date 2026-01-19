<?php

namespace App\Models;

use App\Models\JenisPembayaran;
use Illuminate\Database\Eloquent\Model;

class BankKeluar extends Model
{
    protected $table = 'bank_keluars';
    protected $primaryKey = 'id_bank_keluar'; 

    protected $fillable = [
        'no_agenda',
        'id_sumber_dana',
        'id_bank_tujuan',
        'id_kategori_kriteria',
        'id_sub_kriteria',
        'id_item_sub_kriteria',
        'dokumen_id',
        'agenda_tahun',
        'uraian',
        'nilai_rupiah',
        'penerima',
        'tanggal',
        'id_jenis_pembayaran',
        'debet',
        'kredit',
        'keterangan',
        'no_sap'
    ];

    public $timestamps = true;

    // Relasi ke dokumen (wajib)
    public function dokumen()
    {
        return $this->belongsTo(Dokumen::class, 'dokumen_id');
    }

    // Relasi lainnya
    public function sumberDana()
    {
        return $this->belongsTo(SumberDana::class, 'id_sumber_dana');
    }

    public function bankTujuan()
    {
        return $this->belongsTo(BankTujuan::class, 'id_bank_tujuan');
    }

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

    public function jenisPembayaran()
    {
        return $this->belongsTo(JenisPembayaran::class, 'id_jenis_pembayaran');
    }
}
