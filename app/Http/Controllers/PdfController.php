<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class pdfController extends Controller
{
    public function index(){
        $bankkeluar = bankKeluar::all();
        return view('book',['books' => $books]);
    }

    public function FunctionName(Type $var = null){
        $pdf = Pdf::loadView('pdf.export-pdf',$data);
        return $pdf ->download('invoice.pdf');
    }
}
