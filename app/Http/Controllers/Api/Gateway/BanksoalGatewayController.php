<?php
namespace App\Http\Controllers\Api\Gateway;

use App\Actions\SendResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
    public function allData(Request $request)
    {
        $user = $request->user();
        $banksoals = DB::table('banksoals as t_0')
            ->join('matpels as t_1', 't_0.matpel_id', 't_1.id')
            ->orderByDesc('t_0.created_at')
            ->select(
                't_0.id',
                DB::Raw("CONCAT(t_0.kode_banksoal, ' | ', t_1.nama) AS kode_banksoal")
            );
        if ($user->role == 'guru') {
            $banksoals = $banksoals->where('t_0.author', $user->id);
        }
        $banksoals  = $banksoals->get();
        return SendResponse::acceptData($banksoals);
    }
}
