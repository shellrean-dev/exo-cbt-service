<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Actions\SendResponse;
use App\Group;

use Ramsey\Uuid\Uuid;
use ShellreanDev\Services\Jadwal\JadwalService;

/**
 * JadwalController
 * @author shellrean <wandinak17@gmail.com>
 */
class JadwalController extends Controller
{
    /**
     * @Route(path="api/v2/jadwals/peserta", methods={"GET"})
     *
     * Ambil data jadwal yang dapat diikuti oleh peserta
     * @return Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function getJadwalPeserta(JadwalService $jadwalService)
    {
        # data peserta
        $peserta = request()->get('peserta-auth');

        # ambil data jadwal
        # yang telah diselesaikan peserta
        $hascomplete = $jadwalService->hasCompletedBy($peserta->id);

        # ujian yang sedang dilaksanakan 'aktif' dan hari ini
        $jadwals = $jadwalService->activeToday();

        $avail_jadwal = [];
        foreach($jadwals as $key => $jadwal) {
            if (in_array($jadwal->id, $hascomplete->toArray())) {
                continue;
            }
            # ambil value dari field id pada banksoal_id
            $ids = array_column(json_decode($jadwal->banksoal_id, true), 'id');

            # cari banksoal yang digunakan oleh jadwal
            $bks = DB::table('banksoals')
                ->join('matpels','banksoals.matpel_id','=','matpels.id')
                ->select([
                    'banksoals.id',
                    'matpels.agama_id',
                    'matpels.jurusan_id'])
                ->whereIn('banksoals.id', $ids)
                ->get();

            $jadwal_id = '';

            # loop banksoal
            # untuk filter matpek khusus jurusan
            # dan matpel khusus agama
            foreach($bks as $bk) {
                # cek apakah matpel tersebut adalah matpel agama
                # agama_id != 0
                if(Uuid::isValid(strval($bk->agama_id))) {
                    # cek apakah agama di matpel sama dengan agama di peserta
                    # jika iya maka ambil banksoal
                    if($bk->agama_id == $peserta['agama_id']) {
                        $jadwal_id = $jadwal->id;
                        break;
                    }
                } else {
                    # jika jurusan_id adalah array
                    # artinya ini adalah matpel khusus
                    $jurusans = ($bk->jurusan_id == '0' || $bk->jurusan_id == '') ? 0 : json_decode($bk->jurusan_id, true);
                    if(is_array($jurusans) && $jurusans != null) {
                        # loop jurusan tersebut
                        foreach($jurusans as $d) {
                            if(!Uuid::isValid($d)) {
                                continue;
                            }
                            # cek apakah jurusan dari matpel
                            # sama dengan jurusan pada peserta
                            if ($d == $peserta['jurusan_id']) {
                                $jadwal_id = $jadwal->id;
                                break;
                            }
                        }
                    } else {
                        $jadwal_id = $jadwal->id;
                        break;
                    }
                }
            }
            if ($jadwal_id == '') {
                continue;
            }

            # cek group
            # cocokan antara group jadwal dan group siswa
            if ($jadwal->group_ids != '' && is_string($jadwal->group_ids)) {
                $groups = json_decode($jadwal->group_ids, true);
                if (is_array($groups) && count($groups) > 0) {

                    # peserta tidak memiliki group
                    if ($peserta->group == null) {
                        continue;
                    }

                    # mengecek apakah group didapaat
                    $isGet = false;
                    $ids = array_column($groups, 'id');

                    # ambil data grup pada setting
                    # dengan childrennya
                    $groups = Group::with('childs')->whereIn('id', $ids)->get();

                    foreach ($groups as $group) {

                        # jika group memiliki children
                        # cek childrennya
                        if (count($group->childs) > 0) {
                            if (in_array($peserta->group->group_id, $group->childs->pluck('id')->toArray())) {
                                $isGet = true;
                                break;
                            }
                        }
                        # jika grup sama dengan grup peserta
                        if ($group->id == $peserta->group->group_id) {
                            $isGet = true;
                            break;
                        }
                    }

                    # jika tidak sama antara grup jadwal
                    # dengan grup peserta
                    if (!$isGet) {
                        continue;
                    }
                }
            }

            array_push($avail_jadwal, $jadwal);
        }

        if (!$avail_jadwal) {
            return SendResponse::acceptData([]);
        }

        $jadwal_mapped = [];
        foreach ($avail_jadwal as $item) {
            $setting = json_decode($item->setting);
            $jadwal_mapped[] = [
                'id'        => $item->id,
                'alias'     => $item->alias,
                'lama'      => $item->lama,
                'mulai'     => $item->mulai,
                'tanggal'   => $item->tanggal,
                'setting'   => [
                    'token' => intval($setting->token == '1' ? '1' : '0'),
                ]
            ];
        }

        return SendResponse::acceptData($jadwal_mapped);
    }
}
