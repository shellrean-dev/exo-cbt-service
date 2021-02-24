<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
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
        // data peserta
        $peserta = request()->get('peserta-auth');

        // ambil data jadwal
        // yang telah diselesaikan peserta
        $hascomplete = DB::table('siswa_ujians')->where([
            'peserta_id'        => $peserta->id,
            'status_ujian'      => 1
        ])->get()->pluck('jadwal_id');

        // ujian yang sedang dilaksanakan 'aktif' dan hari ini
        $jadwals = DB::table('jadwals')->where([
            'status_ujian'  => 1,
            'tanggal'       => now()->format('Y-m-d')
        ])
        ->select('id','alias','banksoal_id','lama','mulai','tanggal','setting')
        ->get();

        $dets = $jadwals->map(function($value, $key) use($peserta) {
            // ambil kolom jurusan sebagai id
            $ids = array_column(json_decode($value->banksoal_id, true), 'id');

            // cari banksoal yang digunakan oleh jadwal
            $bks = Banksoal::with('matpel')->whereIn('id', $ids)->get();
            $det = [
                'banksoal'  => '',
                'jadwal'    => ''
            ];

            // loop banksoal
            foreach($bks as $bk) {
                // cek apakah matpel tersebut adalah matpel agam
                // agama_id != 0
                if($bk->matpel->agama_id != 0) {
                    // cek apakah agama di matpel sama dengan agama di peserta
                    // jika iya maka ambil banksoal
                    if($bk->matpel->agama_id == $peserta['agama_id']) {
                        $det['banksoal'] = $bk->id;
                        $det['jadwal'] = $value->id;
                    }
                } else {
                    // jika jurusan id adalah array
                    // artinya ini adalah matpel khusus
                    if(is_array($bk->matpel->jurusan_id)) {
                        // loop jurusan tersebut
                        foreach ($bk->matpel->jurusan_id as $d) {
                            // cek apakah jurusan dari matpel sama dengan jurusan pada peserta
                            if($d == $peserta['jurusan_id']) {
                                $det['banksoal'] = $bk->id;
                                $det['jadwal'] = $value->id;
                            }
                        }
                    } else {
                        // jika jurusan id == 0
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

        $ter = $jadwals->whereIn('id', $ids)->values();

        if(!$ter) {
            return SendResponse::acceptData([]);
        }

        $ter = $ter->map(function($item) {
            $setting = json_decode($item->setting);
            return [
                'id' => $item->id,
                'alias' => $item->alias,
                'lama' => $item->lama,
                'mulai' => $item->mulai,
                'tanggal' => $item->tanggal,
                'setting' => [
                    'token' => intval($setting->token == '1' ? '1' : '0'),
                ]
            ];
        });

        return SendResponse::acceptData($ter);
    }
}
