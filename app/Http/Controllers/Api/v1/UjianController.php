<?php

namespace App\Http\Controllers\Api\v1;

use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\URL;
use App\Exports\CapaianSiswaExport;
use App\Exports\HasilUjianExport;
use App\Exports\CapaianExport;
use App\Actions\SendResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\JawabanPeserta;
use App\JawabanEsay;
use App\HasilUjian;
use App\Banksoal;
use App\Jadwal;
use App\Soal;
use ShellreanDev\Cache\CacheHandler;

class UjianController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return App\Actions\SendResponse
     * @author shellrean <wandinak17@gmail.com>
     */
    public function index()
    {
        $ujian = Jadwal::with('event')->orderBy('created_at', 'DESC');
        if (request()->q != '') {
            $ujian = $ujian->where('alias', 'LIKE', '%'. request()->q.'%');
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
            'view_result'       => 'required|int'
        ]);

        $data = [
            'mulai'             => date('H:i:s', strtotime($request->mulai)),
            'lama'              => $request->lama*60,
            'tanggal'           => date('Y-m-d',strtotime($request->tanggal)),
            'status_ujian'      => 0,
            'alias'             => $request->alias,
            'event_id'          => $request->event_id == '' ? 0 : $request->event_id,
            'setting'           => $request->setting,
            'mulai_sesi'        => array_map(function($item) {
                return date('H:i:s', strtotime($item));
            }, $request->mulai_sesi),
            'view_result'       => $request->view_result
        ];

        if($request->banksoal_id != '') {
            $fill = array();
            foreach($request->banksoal_id as $banksol) {
                $fush = [
                    'id' => $banksol['id'],
                    'jurusan' => $banksol['matpel']['jurusan_id']
                ];
                array_push($fill, $fush);
            }

            $data['banksoal_id'] = $fill;
        }

        if($request->group_ids != '') {
            $fill = array();
            foreach($request->group_ids as $group) {
                $fush = [
                    'id' => $group['id'],
                    'parent' => $group['parent_id']
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
            'banksoal_id'       => 'required|array',
            'setting'           => 'required|array',
            'mulai_sesi'    => 'required|array',
            'view_result'   => 'required|int'
        ]);

        $data = [
            'mulai'         => date('H:i:s', strtotime($request->mulai)),
            'lama'          => $request->lama*60,
            'tanggal'       => date('Y-m-d', strtotime($request->tanggal)),
            'alias'         => $request->alias,
            'event_id'      => $request->event_id == '' ? 0 : $request->event_id,
            'setting'       => $request->setting,
            'mulai_sesi'    => array_map(function($item) {
                return date('H:i:s', strtotime($item));
            }, $request->mulai_sesi),
            'view_result'   => $request->view_result
        ];

        if($request->banksoal_id != '') {
            $fill = array();
            foreach ($request->banksoal_id as $banksoal) {
                $fush = [
                    'id'        => $banksoal['id'],
                    'jurusan'   => $banksoal['matpel']['jurusan_id']
                ];
                array_push($fill, $fush);
            }

            $data['banksoal_id'] = $fill;
        }

        if($request->group_ids != '') {
            $fill = array();
            foreach($request->group_ids as $group) {
                $fush = [
                    'id' => $group['id'],
                    'parent' => $group['parent_id']
                ];
                array_push($fill, $fush);
            }

            $data['group_ids'] = $fill;
        }

        $ujian->update($data);

        $key = md5(sprintf('jadwal:data:active:today'));
        $cache->cache($key, [], 0);

        return SendResponse::accept();
    }

    /**
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

            // set cache ujian 'aktif' hari ini
            $key = md5(sprintf('jadwal:data:active:today'));
            $jadwals = DB::table('jadwals')->where([
                'status_ujian'  => 1,
                'tanggal'       => now()->format('Y-m-d')
            ])
            ->select('id','alias','banksoal_id','lama','mulai','tanggal','setting','group_ids')
            ->get();

            $cache->cache($key, $jadwals);

            return SendResponse::accept();
        }
        return SendResponse::notFound();
    }

    /**
     * Get all ujian without pagination
     *
     * @return App\Actions\SendResponse
     * @author shellrean <wandinak17@gmail.com>
     */
    public function allData()
    {
        $ujians = Jadwal::orderBy('id','desc')->get();
        return SendResponse::acceptData($ujians);
    }

    /**
     * Get data with status active
     * 
     * @return App\Actions\SendResponse
     * @author shellrean <wandinak17@gmail.com>
     */
    public function getActive()
    {
        $ujian = Jadwal::with('event')->where('status_ujian',1)->get();
        return SendResponse::acceptData($ujian);
    }

    /**
     * Get banksoal by jadwal
     * 
     * @param  App\Jadwal $jadwal
     * @return App\Actions\SendResponse
     */
    public function getBanksoalByJadwal(Jadwal $jadwal)
    {
        $res = HasilUjian::where('jadwal_id', $jadwal->id)
        ->get()->pluck('banksoal_id');

        $bankSoal = Banksoal::find($res);
        return SendResponse::acceptData($bankSoal);
    }
}
