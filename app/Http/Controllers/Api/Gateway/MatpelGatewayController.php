<?php
namespace App\Http\Controllers\Api\Gateway;

use App\Actions\SendResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

/**
 * @desc handle matpel gateway
 * @author shellrean <wandinak17@gmail.com>
 * @since 3.1.0
 * @year 2021
 */
final class MatpelGatewayController extends Controller
{
    /**
     * @route(path="api/gateway/matpels/all", methods={"GET"})
     *
     * @return  Response
     * @author shellrean <wandinak17@gmail.com>
     * @since 3.1.0
     */
    public function allData()
    {
        $matpels = DB::table('matpels as t_0')
            ->orderBy('nama')
            ->select(
                'id', 
                DB::Raw("CONCAT(kode_mapel, ' | ',nama) AS nama")
            )
            ->get();
        return SendResponse::acceptData($matpels);
    }
}