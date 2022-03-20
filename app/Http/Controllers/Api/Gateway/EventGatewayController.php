<?php
namespace App\Http\Controllers\Api\Gateway;

use App\Actions\SendResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

/**
 * @desc handle event gateway
 * @author shellrean <wandinak17@gmail.com>
 * @since 3.1.0
 * @year 2021
 */
final class EventGatewayController extends Controller
{
    /**
     * @route(path="api/gateway/events/all", methods={"GET"})
     *
     * @return  \Illuminate\Http\Response
     * @author shellrean <wandinak17@gmail.com>
     * @since 3.1.0
     */
    public function allData()
    {
        $events = DB::table('event_ujians as t_0')
            ->orderBy('t_0.created_at')
            ->orderBy('t_0.name')
            ->select([
              't_0.id',
              't_0.name'
            ])
            ->get();
        return SendResponse::acceptData($events);
    }
}
