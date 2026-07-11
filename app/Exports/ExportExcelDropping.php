<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ExportExcelDropping implements FromView
{
    protected $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function view(): View
    {
        return view('cash_bank.exportExcel.excelDropping', $this->payload);
    }
}
