<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;

class CapaianExport implements FromView
{
    protected $capaian;

    public function __construct($capaian)
    {
        $this->capaian = $capaian;
    }

    public function view():View
    {
        return view('excel.capaian', $this->capaian);
    }
}
