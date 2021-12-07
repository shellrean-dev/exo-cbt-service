<?php
namespace App\Http\Controllers\Api\Gateway;

use App\Actions\SendResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

/**
 * @desc handle jurusan gateway
 * @author shellrean <wandinak17@gmail.com>
 * @since 3.1.0
 * @year 2021
 */
final class JurusanGatewayController extends Controller
{
    /**
     * @route(path="api/gateway/jurusans/all", methods={"GET"})
     *
     * @return  Response
     * @author shellrean <wandinak17@gmail.com>
     * @since 3.1.0
     */
    public function allData()
    {
        $jurusans = DB::table('jurusans as t_0')
            ->orderBy('nama')
            ->get();
        return SendResponse::acceptData($jurusans);
    }
}