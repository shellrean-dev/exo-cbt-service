<?php

namespace App\Http\Controllers\Api\v1;

use App\Services\AbsensiUjianService;
use App\Http\Controllers\Controller;
use App\Services\BeritaAcaraService;
use App\Actions\SendResponse;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\DB;

/**
 * ReportingController
 * @author shellrean <wandinak17@gmail.com>
 */
class ReportingController extends Controller
{
    /**
     * @Route(path="api/v1/berita-acara/{id}", methods={"GET"})
     *
     * Download berita acara pdf
     *
     * @param $event_id
     * @return void
     * @author shellrean <wandinak17@gmail.com>
     */
    public function berita_acara($event_id)
    {
        if (! request()->hasValidSignature()) {
            return SendResponse::badRequest('Kesalahan, url tidak valid');
        }

        try {
            $berita_acara = new BeritaAcaraService($event_id);
            $berita_acara->download();
        } catch (\Exception $e) {
            return SendResponse::internalServerError('kesalahan 500. '.$e->getMessage());
        }
    }

    /**
     * @Route(path="api/v1/berita-acara/{id}/link", methods={"GET"})
     *
     * Buat berita acara pdf link
     *
     * @param $event_id
     * @return \Illuminate\Http\Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function berita_acara_link($event_id)
    {
        $event = DB::table('event_ujians')
            ->where('id', $event_id)
            ->count();
        if ($event < 1) {
            return SendResponse::badRequest('kesalahan, event tidak ditemukan');
        }

        $url = URL::temporarySignedRoute(
            'beritaacara.download.excel',
            now()->addMinutes(5),
            ['id' => $event_id]
        );
        return SendResponse::acceptData($url);
    }

    /**
     * @Route(path="api/v1/absensi-ujian/{id}", methods={"GET"})
     *
     * Download absesnsi pdf
     *
     * @param $jadwal_id
     * @return \Illuminate\Http\Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function absensi_ujian($jadwal_id)
    {
        try {
            $sesi = request()->q;
            if (!in_array(intval($sesi), [1,2,3,4])) {
                return SendResponse::badRequest('sesi invalid');
            }
            $absensi = new AbsensiUjianService($jadwal_id, $sesi);
            $absensi->generate();
            $absensi->download();
        } catch (\Exception $e) {
            return SendResponse::internalServerError('kesalahan 500.'.$e->getMessage());
        }
    }

    /**
     * @Route(path="api/v1/absensi-ujian/{id}/link", methods={"GET"})
     *
     * Buat berita acara pdf link
     *
     * @param $event_id
     * @return \Illuminate\Http\Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function absensi_ujian_link($jadwal_id)
    {
        $sesi = request()->q;
        if (!in_array(intval($sesi), [1,2,3,4])) {
            return SendResponse::badRequest('sesi invalid');
        }

        $check = DB::table('sesi_schedules')
            ->where('jadwal_id', $jadwal_id)
            ->where('sesi', $sesi)
            ->count();
        if ($check < 1) {
            return SendResponse::badRequest('tidak ada peserta pada sesi tersebut');
        }

        $url = URL::temporarySignedRoute(
            'absensi.download.excel',
            now()->addMinutes(5),
            ['id' => $jadwal_id, 'q' => $sesi]
        );
        return SendResponse::acceptData($url);
    }
}
