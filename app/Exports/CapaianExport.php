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
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return new Collection([
            range(0,$this->capaian['soal'])
        ]);
        
    }

    public function view():View
    {
        return view('excel.capaian', [
            'capaian' => $this->capaian
        ]);
    }
}
