<?php

namespace App\Imports;

use App\Soal;
use App\JawabanSoal;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\OnEachRow;

class BanksoalImport implements OnEachRow, WithStartRow
{
    /**
     * [$banksoal_id description]
     * @var [type]
     */
    protected $banksoal_id;

    /**
     * [__construct description]
     * @param [type] $banksoal_id [description]
     */
    public function __construct($banksoal_id)
    {
        $this->banksoal_id = $banksoal_id;
    }
    /**
    * @param array $row
    *
    * @return Model|null
    */
    public function onRow(Row $row)
    {
        $rowIndex = $row->getIndex();
        $row      = $row->toArray();
        if($row[0] == '') {
            return null;
        }

        $soal = [
            'banksoal_id'   => $this->banksoal_id,
            'pertanyaan'    => $row[0],
            'tipe_soal'     => $row[1],
            'rujukan'       => ''
        ];

        $arr = "ABCDEF";

        $soal = Soal::create($soal);

        if($row[1] == 1) {
            $jawab = strrpos($arr, $row[2]);

            foreach (range(3, 7) as $key => $value) {
                if(isset($row[$value]) && $row[$value] != '') {
                    JawabanSoal::create([
                        'soal_id'   => $soal->id,
                        'text_jawaban'  => $row[$value],
                        'correct' => ($jawab == $key ? '1' : '0')
                    ]);
                }
            }
        }
    }

    public function startRow(): int
    {
        return 2;
    }
}
