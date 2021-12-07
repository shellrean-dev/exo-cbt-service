<?php
namespace App\Http\Controllers\Api\Gateway;

use App\Actions\SendResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

final class BanksoalGatewayController extends Controller
{
    /**
     * @route(path="api/gateway/banksoals/all", methods={"GET"})
     *
     * @return  Response
     * @author shellrean <wandinak17@gmail.com>
     * @since 3.1.0
     */
    public function allData()
    {
        $banksoals = DB::table('banksoals as t_0')
            ->orderByDesc('created_at')
            ->select([
              't_0.id',
              't_0.kode_banksoal'
            ])
            ->get();
        return SendResponse::acceptData($banksoals);
    }
}