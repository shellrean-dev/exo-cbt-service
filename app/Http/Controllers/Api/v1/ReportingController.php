<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;;
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
}
