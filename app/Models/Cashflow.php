<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cashflow extends Model
{
    protected $table = 'cashflows';

    protected $fillable = [
        'id_bank_tujuan',
        'profit_center',
        'nama_profit_center',
        'document_number',
        'posting_date',
        'posting_period',
        'bulan',
        'tahun',
        'account',
        'offsetting_account',
        'name_of_offsetting_account',
        'posting_key',
        'amount',
        'text',
        'gl_account_desc',
        'reference_key',
        'reference_key_1',
        'reference_key_3',
        'cost_center',
        'uraian',
    ];

    public function bankTujuan()
    {
        return $this->belongsTo(BankTujuan::class, 'id_bank_tujuan', 'id_bank_tujuan');
    }

    public function reference()
    {
        return $this->belongsTo(CashflowReference::class, 'reference_key_1', 'reference_key');
    }
}
