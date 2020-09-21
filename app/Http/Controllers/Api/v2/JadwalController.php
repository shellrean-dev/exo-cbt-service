<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use App\Actions\SendResponse;
use Illuminate\Http\Request;
use App\SiswaUjian;
use App\Banksoal;
use App\Jadwal;

class JadwalController extends Controller
{
    /**
     * [getJadwalPeserta description]
     * @return [type] [description]
     */
    public function getJadwalPeserta()
    {
        $peserta = request()->get('peserta-auth');
        $hascomplete = SiswaUjian::where([
            'peserta_id'        => $peserta->id,
            'status_ujian'      => 1
        ])->get()->pluck('jadwal_id');

        $jadwals = Jadwal::where([
            'status_ujian' => 1,
            'sesi'      => $peserta->sesi
        ])->get();

        $dets = $jadwals->map(function($value, $key) use($peserta) {
            $ids = array_column($value->banksoal_id, 'jurusan','id');
            $ids = collect($ids)->keys();
            
            $bks = Banksoal::with('matpel','matpel')->whereIn('id', $ids)->get();
            $det = [
                'banksoal'  => '',
                'jadwal'    => ''
            ];

            foreach($bks as $bk) {
                if($bk->matpel->agama_id != 0) {
                    if($bk->matpel->agama_id == $peserta['agama_id']) {
                        $det['banksoal'] = $bk->id;
                        $det['jadwal'] = $value->id;
                    }
                } else {
                    if(is_array($bk->matpel->jurusan_id)) {
                        foreach ($bk->matpel->jurusan_id as $d) {
                            if($d == $peserta['jurusan_id']) {
                                $det['banksoal'] = $bk->id;
                                $det['jadwal'] = $value->id;
                            }
                        }
                    } else {
                        if($bk->matpel->jurusan_id == 0) {
                            $det['banksoal'] = $bk->id;
                            $det['jadwal'] = $value->id;
                        }
                    }
                }   
            }
            return $det;
        });

        $ids = collect($dets)->map(function ($value) {
            return $value['jadwal'];
        })->reject(function($value) use($hascomplete) {
            return $value == '' || in_array($value, $hascomplete->toArray());
        });

        $ter = $jadwals->whereIn('id', $ids)->values()->toArray();

        if(!$ter) {
            return SendResponse::acceptData([]);
        }

        return SendResponse::acceptData($ter);
    }
}
