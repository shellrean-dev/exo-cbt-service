<?php
namespace App\Http\Controllers\Api\Gateway;

use App\Actions\SendResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

/**
 * @desc handle agama gateway
 * @author shellrean <wandinak17@gmail.com>
 * @since 3.1.0
 * @year 2021
 */
final class AgamaGatewayController extends Controller
{
    /**
     * @route(path="api/gateway/agamas/all", methods={"GET"})
     *
     * @return  Response
     * @author shellrean <wandinak17@gmail.com>
     * @since 3.1.0
     */
    public function allData()
    {
        $agamas = DB::table('agamas as t_0')
            ->orderBy('nama')
            ->get();
        return SendResponse::acceptData($agamas);
    }
}