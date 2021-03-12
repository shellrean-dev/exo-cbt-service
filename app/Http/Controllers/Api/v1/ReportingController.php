<?php

namespace App\Http\Controllers\Api\v1;

use App\Services\AbsensiUjianService;
use App\Http\Controllers\Controller;
use App\Services\BeritaAcaraService;
use App\Actions\SendResponse;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ReportingController extends Controller
{
    /**
     * Download berita acara pdf
     * 
     * @param $event_id
     * @return void
     * @author <wandinak17@gmail.com>
     */
    public function berita_acara($event_id)
    {
        if (! request()->hasValidSignature()) {
            return SendResponse::badRequest('Kesalahan, url tidak valid');
        }
        
        $berita_acara = new BeritaAcaraService($event_id);
        $berita_acara->download();
    }

    /**
     * Buat berita acara pdf link
     * 
     * @param $event_id
     * @return void
     * @author <wandinak17@gmail.com>
     */
    public function berita_acara_link($event_id)
    {
        $url = URL::temporarySignedRoute(
            'beritaacara.download.excel', 
            now()->addMinutes(5),
            ['id' => $event_id]
        );
        return SendResponse::acceptData($url);
    }

    /**
     * Download absesnsi pdf
     * 
     * @param $jadwal_id
     * @return void
     * @author <wandinak17@gmail.com>
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
     * Buat berita acara pdf link
     * 
     * @param $event_id
     * @return void
     * @author <wandinak17@gmail.com>
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
