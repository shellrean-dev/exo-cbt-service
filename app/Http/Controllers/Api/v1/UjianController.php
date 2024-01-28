<?php

namespace App\Http\Controllers\Api\v1;

use App\Jadwal;
use App\Banksoal;
use App\HasilUjian;
use App\Actions\SendResponse;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


use ShellreanDev\Cache\CacheHandler;

/**
 * UjianController
 * @author shellrean <wandinak17@gmail.com>
 */
class UjianController extends Controller
{
    /**
     * @Route(path="api/v1/ujians", methods={"GET"})
     *
     * Display a listing of the resource.
     *
     * @return App\Actions\SendResponse
     * @author shellrean <wandinak17@gmail.com>
     */
    public function index(Request  $request)
    {
        $user = $request->user();

        $ujian = Jadwal::with('event')
            ->orderBy('tanggal','DESC')
            ->orderBy('mulai','DESC');

        if (request()->q != '') {
            $ujian = $ujian->where('alias', 'LIKE', '%'. request()->q.'%');
        }
        if ($user->role == 'guru') {
            $ujian = $ujian->where('created_by', $user->id);
        }

        if (request()->perPage != '') {
            $ujian = $ujian->paginate(request()->perPage);
        } else {
            $ujian = $ujian->paginate(20);
        }

        $ujian->makeHidden('banksoal_id');
        $ujian->makeVisible('groups');
        return SendResponse::acceptData($ujian);
    }

    /**
     * @Route(path="api/v1/ujians", methods={"POST"})
     *
     * Store a newly created resource in storage.
     *
     * @param  Illuminate\Http\Request  $request
     * @return App\Actions\SendResponse
     * @author shellrean <wandinak17@gmail.com>
     */
    public function store(Request $request)
    {
        $request->validate([
            'tanggal'           => 'required',
            'mulai'             => 'required',
            'lama'              => 'required|int',
            'alias'             => 'required',
            'banksoal_id'       => 'required|array',
            'setting'           => 'required|array',
            'mulai_sesi'        => 'required|array',
            'view_result'       => 'required|int',
            'min_test'          => 'required|int'
        ]);

        $user = $request->user();

        $data = [
            'mulai'             => date('H:i:s', strtotime($request->mulai)),
            'lama'              => $request->lama*60,
            'tanggal'           => date('Y-m-d',strtotime($request->tanggal)),
            'status_ujian'      => 0,
            'alias'             => $request->alias,
            'event_id'          => $request->event_id == '' ? null : $request->event_id,
            'setting'           => $request->setting,
            'mulai_sesi'        => array_map(function($item) {
                return date('H:i:s', strtotime($item));
            }, $request->mulai_sesi),
            'view_result'       => $request->view_result,
            'min_test'          => $request->min_test,
            'created_by'        => $user->id,
        ];

        if($request->banksoal_id != '') {
            $fill = array();

            foreach($request->banksoal_id as $banksol) {
                $fush = [
                    'id' => $banksol['id']
                ];
                array_push($fill, $fush);
            }

            $data['banksoal_id'] = $fill;
        }

        if($request->group_ids != '') {
            $fill = array();
            foreach($request->group_ids as $group) {
                $fush = [
                    'id' => $group['id']
                ];
                array_push($fill, $fush);
            }

            $data['group_ids'] = $fill;
        }

        if($request->server_id != '') {
            $fill = array();
            foreach($request->server_id as $server) {
                array_push($fill, $server['server_name']);
            }

            $data['server_id'] = $fill;
        }

        Jadwal::create($data);

        return SendResponse::accept('banksoal created');
    }

    /**
     * @Route(path="api/v1/ujians/{ujian}", methods={"GET"})
     *
     * Display the specified resource.
     *
     * @param  App\Jadwal $ujian
     * @return App\Actions\SendResponse
     * @author shellrean <wandinak17@gmail.com>
     */
    public function show(Jadwal $ujian)
    {
        return SendResponse::acceptData($ujian);
    }

    /**
     * @Route(path="api/v1/ujians/{ujian}", methods={"PUT","PATCH"})
     *
     * Update the specified resource in storage.
     *
     * @param  Illuminate\Http\Request  $request
     * @param  App\Jadwal  $ujian
     * @return App\Actions\SendResponse
     * @author shellrean <wandinak17@gmail.com>
     */
    public function update(Request $request, Jadwal $ujian, CacheHandler $cache)
    {
        $request->validate([
            'tanggal'       => 'required',
            'mulai'         => 'required',
            'lama'          => 'required',
            'alias'         => 'required',
            'banksoal_id'   => 'required|array',
            'setting'       => 'required|array',
            'mulai_sesi'    => 'required|array',
            'view_result'   => 'required|int',
            'min_test'      => 'required|int'
        ]);

        $data = [
            'mulai'         => date('H:i:s', strtotime($request->mulai)),
            'lama'          => $request->lama*60,
            'tanggal'       => date('Y-m-d', strtotime($request->tanggal)),
            'alias'         => $request->alias,
            'event_id'      => $request->event_id == '' ? null : $request->event_id,
            'setting'       => $request->setting,
            'mulai_sesi'    => array_map(function($item) {
                return date('H:i:s', strtotime($item));
            }, $request->mulai_sesi),
            'view_result'   => $request->view_result,
            'min_test'      => $request->min_test
        ];

        if($request->banksoal_id != '') {
            $fill = array();
            foreach ($request->banksoal_id as $banksoal) {
                $fush = [
                    'id'        => $banksoal['id']
                ];
                array_push($fill, $fush);
            }

            $data['banksoal_id'] = $fill;
        }

        if($request->group_ids != '') {
            $fill = array();
            foreach($request->group_ids as $group) {
                $fush = [
                    'id' => $group['id']
                ];
                array_push($fill, $fush);
            }

            $data['group_ids'] = $fill;
        }

        $ujian->update($data);


        return SendResponse::accept();
    }

    /**
     * @Route(path="api/v1/ujians/{ujian}", methods={"DELETE"})
     *
     * Remove the specified resource from storage.
     *
     * @param  App\Jadwal  $ujian
     * @return App\Actions\SendResponse
     * @author shellrean <wandinak17@gmail.com>
     */
    public function destroy(Jadwal $ujian)
    {
        $ujian->delete();
        return SendResponse::accept();
    }

    /**
     * @Route(path="api/v1/ujians/set-status", methods={"POST"})
     *
     * Set status ujian.
     *
     * @param  Illuminate\Http\Request  $request
     * @param  ShellreanDev\Cache\CacheHandler $cache
     * @author shellrean <wandinak17@gmail.com>
     */
    public function setStatus(Request $request, CacheHandler $cache)
    {
        $jadwal = Jadwal::find($request->id);
        if($jadwal) {
            $jadwal->status_ujian = $request->status;
            $jadwal->save();
            return SendResponse::accept();
        }
        return SendResponse::notFound();
    }

    /**
     * @Route(path="api/v1/ujian/all", methods={"GET"})
     *
     * Get all ujian without pagination
     *
     * @return \Illuminate\Http\Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function allData(Request  $request)
    {
        $user = $request->user();
        $ujians = Jadwal::orderBy('tanggal','DESC')
            ->orderBy('mulai','DESC');

        if ($user->role == 'guru') {
            $ujians = $ujians->where('created_by', $user->id);
        }
        $ujians = $ujians->get();

        return SendResponse::acceptData($ujians);
    }

    /**
     * @Route(path="api/v1/ujians/active-status", methods={"GET"})
     *
     * Get data with status active
     *
     * @return \Illuminate\Http\Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function getActive()
    {
        $ujian = Jadwal::with('event')
            ->where('status_ujian',1)
            ->orderBy('tanggal','DESC')
            ->orderBy('mulai','DESC')
            ->get();
        return SendResponse::acceptData($ujian);
    }

    /**
     * @Route(path="api/v1/ujians/{jadwal}/result/banksoal", methods={"GET"})
     *
     * Get banksoal by jadwal
     *
     * @param  App\Jadwal $jadwal
     * @return \Illuminate\Http\Response
     */
    public function getBanksoalByJadwal(Jadwal $jadwal)
    {
        $res = HasilUjian::where('jadwal_id', $jadwal->id)
        ->get()->pluck('banksoal_id');

        $bankSoal = Banksoal::find($res);
        return SendResponse::acceptData($bankSoal);
    }
}
