<?php

namespace App\Http\Controllers\Api\v3;

use App\Actions\SendResponse;
use App\Http\Controllers\Controller;
use Gregwar\Tex2pngBundle\Tex2png;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * AppInfoController Controller
 * @author shellrean <wandinak17@gmail.com>
 */
class AppInfoController extends Controller
{
    /**
     * @Route(path="api/v3/info-feature", methods={"GET"})
     */
    public function info(Request $request)
    {
        Tex2png::create('\sum_{i = 0}^{i = n} \frac{i}{2}')
            ->saveTo('sample.png')
            ->generate();
        return SendResponse::accept('oke');

        $name = $request->query('name');
        $info = DB::table('feature_infos')
            ->where('name', $name)
            ->orderByDesc('id')
            ->first();
        if (!$info) {
            return SendResponse::acceptData(['content' => 'informasi tidak ditemukan']);
        }
        return SendResponse::acceptData($info);
    }
}
