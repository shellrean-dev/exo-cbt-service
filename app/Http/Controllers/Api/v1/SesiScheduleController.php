<?php

namespace App\Http\Controllers\Api\v1;

use App\Imports\SesiExamScheduleImport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use App\Actions\SendResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\SesiSchedule;
use App\Peserta;

/**
 * SesiScheduleController
 * @author shellrean <wandinak17@gmail.com>
 */
class SesiScheduleController extends Controller
{
    /**
     * @Route(path="api/v1/sesi", methods={"GET"})
     *
     * Get student on sesi
     *
     * @param Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     * @since 2.0.0
     * @author shellrean <wandinak17@gmail.com>
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
     * @Route(path="api/v1/sesi", methods={"POST"})
     *
     * Push student to sesi
     *
     * @param Illuminate\Http\Request
     * @return App\Actions\SendResponse
     * @since 2.0.0
     * @author shellrean <wandinak17@gmail.com>
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
     * @Route(path="api/v1/sesi", methods={"DELETE"})
     *
     * Remove siswa from sesi
     *
     * @param Illuminate\Http\Request
     * @return App\Actions\SendResponse
     * @since 2.0.0
     * @author shellrean <wandinak17@gmail.com>
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

    /**
     * @Route(path="api/v1/import", methods={"POST"})
     *
     * Import sesi schedule from sesi
     *
     * @param Illuminate\Http\Request
     * @since 2.0.0
     * @author shellrean <wandinak17@gmail.com>
     */
    public function importToSesi(Request $request)
    {
        $request->validate([
            'file'      => 'required|mimes:xlsx,xls',
            'j'         => 'required'
        ]);

        DB::beginTransaction();

        try {
            Excel::import(new SesiExamScheduleImport($request->j), $request->file('file'));

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return SendResponse::badRequest($e->getMessage());
        }
        return SendResponse::accept();
    }

    /**
     * @Route(path="api/v1/sesi/copy", methods={"POST"})
     *
     * Copy sesi dari default siswa
     *
     * @param Illuminate\Http\Request
     * @since 2.0.0
     * @author shellrean <wandinak17@gmail.com>
     */
    public function copyFromDefault(Request $request)
    {
        $request->validate([
            'jadwal_id' => 'required|exists:jadwals,id'
        ]);

        $students = DB::table('pesertas')->select('id','sesi')->get();
        $groupped = $students->groupBy('sesi');

        $data = [];
        foreach($groupped->all() as $key => $item) {
            $sesi = [];
            foreach($item as $student) {
                array_push($sesi, $student->id);
            }
            array_push($data, [
                'id'        => Str::uuid()->toString(),
                'jadwal_id' => $request->jadwal_id,
                'sesi'      => $key,
                'peserta_ids' => json_encode($sesi),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        try {
            DB::beginTransaction();

            DB::table('sesi_schedules')->where('jadwal_id', $request->jadwal_id)->delete();

            DB::table('sesi_schedules')->insert($data);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return SendResponse::internalServerError('Kesalahan 500. '.$e->getMessage());
        }
    }
}
