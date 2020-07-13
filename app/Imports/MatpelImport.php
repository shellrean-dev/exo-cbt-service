<?php

namespace App\Imports;

use App\Matpel;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class MatpelImport implements ToModel, WithStartRow, WithValidation
{
    /**
    * @param Collection $collection
    */
    public function model(array $row)
    {
        return new Matpel([
            'kode_mapel'    => $row[0],
            'nama'          => $row[1],
            'agama_id'      => $row[2],
            'jurusan_id'    => $row[3],
            'correctors'       => $row[4]
        ]);
    }

    public function startRow(): int
    {
        return 2;
    }

    public function rules(): array
    {
        return [
            '0' => 'unique:matpels,kode_mapel',
        ];
    }
}
