<?php
namespace App\Http\Controllers\Api\Gateway;

use App\Actions\SendResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

/**
 * @desc handle group gateway
 * @author shellrean <wandinak17@gmail.com>
 * @since 3.1.0
 * @year 2021
 */
final class GroupGatewayController extends Controller
{
    /**
     * @route(path="api/gateway/groups/all", methods={"GET"})
     *
     * @return  Response
     * @author shellrean <wandinak17@gmail.com>
     * @since 3.1.0
     */
    public function allData()
    {
        $groups = DB::table('groups as t_0')
            ->orderBy('created_at')
            ->select([
                't_0.id',
                't_0.parent_id',
                't_0.name'
            ])
            ->get();
        return SendResponse::acceptData($groups);
    }
}