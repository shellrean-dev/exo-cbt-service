<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Actions\SendResponse;
use Illuminate\Http\Request;
use App\SesiSchedule;
use App\Peserta;

class SesiScheduleController extends Controller
{
    /**
     * Get student on sesi
     *
     * @param \Illuminate\Http\Request
     * @return \App\Actions\SendResponse
     * @since 2.0.0
     */
    public function studentBySesi()
    {
        $sesi = isset(request()->s) ? request()->s : '';
        $jadwal = isset(request()->j) ? request()->j : '';

        if($sesi == '' || $jadwal == '') {
            return SendResponse::badRequest("Invalid parameters");
        }

        $sesiSchedule = SesiSchedule::where([
            'sesi'      => $sesi,
            'jadwal_id' => $jadwal
        ])->first();

        if(!$sesiSchedule) {
            return SendResponse::acceptData([]);
        }

        $students = Peserta::whereIn('id', $sesiSchedule->peserta_ids)
                            ->select('id','no_ujian','nama')
                            ->get();

        return SendResponse::acceptData($students);
    }

    /**
     * Push student to sesi
     *
     * @param \Illuminate\Http\Request
     * @return \App\Actions\SendResponse
     * @since 2.0.0
     */
    public function pushToSesi(Request $request)
    {
        $request->validate([
            'sesi'      => 'required',
            'jadwal_id' => 'required',
            'peserta_ids'   => 'required'
        ]);

        $filter = rtrim(ltrim($request->peserta_ids,','), ',');
        $peserta_ids = explode(',',$filter);

        $pesertas = Peserta::whereIn('no_ujian', $peserta_ids)->select('id','no_ujian','nama')->get();

        $sesiSchedule = SesiSchedule::where([
            'sesi'      => $request->sesi,
            'jadwal_id' => $request->jadwal_id
        ])->first();

        if($sesiSchedule) {
            try {
                $new_array = array_unique(array_merge($sesiSchedule->peserta_ids, $pesertas->pluck('id')->toArray()));
                $sesiSchedule->peserta_ids = $new_array;
                $sesiSchedule->save();
            } catch (\Exception $e) {
                return SendResponse::internalServerError($e->getMessage());
            }
        } else {
            try {
                $sesiSchedule = SesiSchedule::create([
                    'sesi'  => $request->sesi,
                    'jadwal_id' => $request->jadwal_id,
                    'peserta_ids'   => $pesertas->pluck('id')->toArray()
                ]);
            } catch (\Exception $e) {
                return SendResponse::internalServerError($e->getMessage());
            }
        }

        return SendResponse::acceptData('insert sesi sukses');
    }

    /**
     * Remove siswa from sesi
     *
     * @param \Illuminate\Http\Request
     * @return \App\Actions\SendResponse
     * @since 2.0.0
     */
    public function removeFromSesi(Request $request)
    {
        $request->validate([
            's'  => 'required',
            'j' => 'required',
            'p' => 'required'
        ]);

        $sesiSchedule = SesiSchedule::where([
            'sesi'      => $request->s,
            'jadwal_id' => $request->j
        ])->first();
        if(!$sesiSchedule) {
            return SendResponse::notFound('Sesi schedule not found');
        }

        $peserta_ids = explode(',',rtrim(ltrim($request->p,','),''));

        try {
            $new_arr = array_diff($sesiSchedule->peserta_ids, $peserta_ids);
            $sesiSchedule->peserta_ids = $new_arr;
            $sesiSchedule->save();
        } catch (\Exception $e) {
            return SendResponse::internalServerError($e->getMessage());
        }

        return SendResponse::acceptData($sesiSchedule->peserta_ids);
    }
}
