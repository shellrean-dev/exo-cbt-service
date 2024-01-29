<?php
namespace App\Http\Controllers\Api\Gateway;

use App\Actions\SendResponse;
use App\Http\Controllers\Controller;
use App\Models\JadwalConstant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @desc handle jadwal gateway
 * @author shellrean <wandinak17@gmail.com>
 * @since 3.1.0
 * @year 2021
 */
final class JadwalGatewayController extends Controller
{
    /**
     * @route(path="api/gateway/jurusans/all", methods={"GET"})
     *
     * @return  Response
     * @author shellrean <wandinak17@gmail.com>
     * @since 3.1.0
     */
    public function allData(Request $request)
    {
        $user = $request->user();

        $jadwals = DB::table('jadwals as t_0')
            ->orderBy('tanggal','DESC')
            ->orderBy('mulai','DESC')
            ->select([
                't_0.id',
                't_0.alias'
            ]);
        if ($user->role == 'guru') {
            $jadwals = $jadwals->where('t_0.created_by', $user->id);
        }
        $jadwals = $jadwals ->get();
        return SendResponse::acceptData($jadwals);
    }

    /**
     * @route(path="api/gateway/ujians/active-status", methods={"GET"})
     *
     * @return  Response
     * @author shellrean <wandinak17@gmail.com>
     * @since 3.1.0
     */
    public function activeStatus(Request $request)
    {
        $user = $request->user();
        $jadwals = DB::table('jadwals as t_0')
            ->where('status_ujian', JadwalConstant::STATUS_ACTIVE)
            ->orderBy('tanggal','DESC')
            ->orderBy('mulai','DESC')
            ->select([
                't_0.id',
                't_0.alias',
                't_0.tanggal',
                't_0.mulai',
                't_0.sesi'
            ]);
        if ($user->role == 'guru') {
            $jadwals = $jadwals->where('t_0.created_by', $user->id);
        }
        $jadwals = $jadwals ->get();
        return SendResponse::acceptData($jadwals);
    }
}
