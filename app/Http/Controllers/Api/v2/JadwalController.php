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
     * Ambil data jadwal yang dapat diikuti oleh peserta
     * @return \App\Actions\SendResponse
     * @author <wandinak17@gmail.com>
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
        ])
        ->select('jadwal_id')
        ->get()
        ->pluck('jadwal_id');

        // ujian yang sedang dilaksanakan 'aktif' dan hari ini
        $jadwals = DB::table('jadwals')->where([
            'status_ujian'  => 1,
            'tanggal'       => now()->format('Y-m-d')
        ])
        ->select('id','alias','banksoal_id','lama','mulai','tanggal','setting')
        ->get();

        $jadwal_ids = [];

        foreach($jadwals as $key => $jadwal) {
            if (in_array($jadwal->id, $hascomplete->toArray())) {
                continue;
            }
            // ambil value dari field id pada banksoal_id
            $ids = array_column(json_decode($jadwal->banksoal_id, true), 'id');

            // cari banksoal yang digunakan oleh jadwal
            $bks = DB::table('banksoals')
                ->join('matpels','banksoals.matpel_id','=','matpels.id')
                ->select('banksoals.id','matpels.agama_id','matpels.jurusan_id')
                ->whereIn('banksoals.id', $ids)
                ->get();

            $det = [
                'banksoal'  => '',
                'jadwal'    => ''
            ];

            $jadwal_id = '';

            // loop banksoal
            foreach($bks as $bk) {
                // cek apakah matpel tersebut adalah matpel agama
                // agama_id != 0
                if($bk->agama_id != 0) {
                    // cek apakah agama di matpel sama dengan agama di peserta
                    // jika iya maka ambil banksoal
                    if($bk->agama_id == $peserta['agama_id']) {
                        $jadwal_id = $jadwal->id;
                        break;
                    }
                } else {
                    // jika jurusan_id adalah array
                    // artinya ini adalah matpel khusus
                    $jurusans = $bk->jurusan_id == '0' || $bk->jurusan_id == '' ? 0 : json_decode($bk->jurusan_id, true);
                    if(is_array($jurusans)) {
                        // loop jurusan tersebut
                        foreach($jurusans as $d) {
                            // cek apakah jurusan dari matpel
                            // sama dengan jurusan pada peserta
                            if ($d == $peserta['jurusan_id']) {
                                $jadwal_id = $jadwal->id;
                                break;
                            }
                        }
                    } else {
                        // jika jurusan id == 0
                        if ($bk->jurusan_id == 0) {
                            $jadwal_id = $jadwal->id;
                            break;
                        }
                    }
                }
            }
            if ($jadwal_id == '') {
                continue;
            }
            array_push($jadwal_ids, $jadwal_id);
        }

        $avail_jadwal = $jadwals->whereIn('id', $jadwal_ids)->values();

        if (!$avail_jadwal) {
            return SendResponse::acceptData([]);
        }

        $avail_jadwal = $avail_jadwal->map(function($item) {
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

        return SendResponse::acceptData($avail_jadwal);
    }
}
