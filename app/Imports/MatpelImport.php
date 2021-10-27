<?php

namespace App\Imports;

use App\Actions\SendResponse;
use App\Matpel;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\ToCollection;

class MatpelImport implements ToCollection, WithStartRow
{
    public function collection(Collection $rows)
    {
        $agama_kodes = [];
        $jurusan_kodes = [];

        $matpels = [];
        foreach($rows as $row) {
            if($row->filter()->isNotEmpty()) {
                $agama_kodes[] = $row[2];
                if ($row[3] !== 0 && $row[3] !== "[]") {
                    $tmp_jurusan = explode(",", $row[3]);

                    if (!is_null($tmp_jurusan) && is_array($tmp_jurusan) && count($tmp_jurusan)) {
                        $jurusan_kodes = array_merge($jurusan_kodes, $tmp_jurusan);
                    }
                }
                $matpels[] = [
                    'id' => Str::uuid()->toString(),
                    'kode_mapel' => $row[0],
                    'nama' => $row[1],
                    'agama_id' => $row[2],
                    'jurusan_id' => $row[3],
                    'correctors' => '[]'
                ];
            }
        }
        $agama_kodes = array_unique($agama_kodes);
        $jurusan_kodes = array_unique($jurusan_kodes);
        $agamas = DB::table('agamas')->whereIn('kode', $agama_kodes)->get();
        $jurusans = DB::table('jurusans')->whereIn('kode', $jurusan_kodes)->get();

        $real = array_map(function($item) use ($agamas, $jurusans) {
            if ($item['agama_id'] !== 0) {
                $item['agama_id'] = $agamas->firstWhere('kode',$item['agama_id'])->id;
            }
            if ($item['jurusan_id'] !== 0) {
                $real_jurusan = [];
                foreach(explode(",",$item['jurusan_id']) as $jurusan) {
                    $jurusan_concrit = $jurusans->where('kode', $jurusan)->first();
                    if ($jurusan_concrit) {
                        $real_jurusan[] = $jurusan_concrit->id;
                    }
                }
                $item['jurusan_id'] = json_encode($real_jurusan);
            }
            return $item;
        }, $matpels);

        try {
            DB::table('matpels')->insert($real);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function startRow(): int
    {
        return 2;
    }
}
