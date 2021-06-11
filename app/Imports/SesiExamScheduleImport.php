<?php

namespace App\Imports;

use App\Peserta;
use App\SesiSchedule;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use App\Actions\SendResponse;

class SesiExamScheduleImport implements ToCollection, WithStartRow
{
    private $jadwal_id;

    public function __construct($jadwal_id)
    {
        $this->jadwal_id = $jadwal_id;
    }

    public function collection(Collection $rows)
    {
        $sesi = [
            '1' => [],
            '2' => [],
            '3' => [],
            '4' => [],
        ];

        foreach($rows as $row) {
            if($row->filter()->isNotEmpty()) {
                switch($row[0]) {
                    case 1:
                        array_push($sesi['1'], $row[1]);
                    break;
                    case 2:
                        array_push($sesi['2'], $row[1]);
                    break;
                    case 3:
                        array_push($sesi['3'], $row[1]);
                    break;
                    case 4:
                        array_push($sesi['4'], $row[1]);
                    break;
                }
            }
        }

        foreach($sesi as $key => $ses) {
            $pesertas = Peserta::whereIn('no_ujian', $ses)->select('id','no_ujian','nama')->get();
            $sesiSchedule = SesiSchedule::where([
                'sesi'      => $key,
                'jadwal_id' => $this->jadwal_id
            ])->first();

            if($sesiSchedule) {
                try {
                    $new_array = array_unique(array_merge($sesiSchedule->peserta_ids, $pesertas->pluck('id')->toArray()));
                    $sesiSchedule->peserta_ids = $new_array;
                    $sesiSchedule->save();
                } catch (\Exception $e) {
                    return SendResponse::internalServerError($e->getMessage());
                }
            } else {
                try {
                    $sesiSchedule = SesiSchedule::create([
                        'sesi'  => $key,
                        'jadwal_id' => $this->jadwal_id,
                        'peserta_ids'   => $pesertas->pluck('id')->toArray()
                    ]);
                } catch (\Exception $e) {
                    return SendResponse::internalServerError($e->getMessage());
                }
            }
        }
    }

    public function startRow(): int
    {
        return 2;
    }
}
