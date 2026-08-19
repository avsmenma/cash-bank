<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashflowReference extends Model
{
    protected $table = 'cashflow_references';

    protected $fillable = [
        'reference_key',
        'parent_key',
        'parent_name',
        'uraian',
        'nature',
    ];
}
