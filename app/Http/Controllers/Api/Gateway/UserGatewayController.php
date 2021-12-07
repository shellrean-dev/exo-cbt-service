<?php
namespace App\Http\Controllers\Api\Gateway;

use App\Actions\SendResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

/**
 * @desc handle user gateway
 * @author shellrean <wandinak17@gmail.com>
 * @since 3.1.0
 * @year 2021
 */
final class UserGatewayController extends Controller
{
    /**
     * @route(path="api/gateway/users/correctors", methods={"GET"})
     *
     * @return  Response
     * @author shellrean <wandinak17@gmail.com>
     * @since 3.1.0
     */
    public function correctors()
    {
        $users = DB::table('users as t_0')
            ->orderBy('name')
            ->select([
              't_0.id',
              't_0.name',
              't_0.email'
            ])
            ->get();
        return SendResponse::acceptData($users);
    }
}