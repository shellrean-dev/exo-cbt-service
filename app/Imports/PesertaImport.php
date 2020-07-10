<?php

namespace App\Imports;

use App\Peserta;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class PesertaImport implements ToModel, WithStartRow, WithValidation
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Peserta([
            'sesi'            => $row[0],
            'no_ujian'        => $row[1],
            'nama'            => $row[2],
            'password'        => $row[3],
            'jurusan_id'      => $row[4],
            'agama_id'        => $row[5]
        ]);
    }

    public function startRow(): int
    {
        return 2;
    }

    public function rules(): array
    {
        return [
            '1' => 'unique:pesertas,no_ujian',
            '4' => 'exists:jurusans,id',
            '5' => 'exists:agamas,id'
        ];
    }
}
