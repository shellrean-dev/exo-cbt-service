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
use Illuminate\Support\Str;
use App\JawabanPeserta;
use App\JawabanEsay;
use App\HasilUjian;
use App\Banksoal;
use App\Jadwal;
use App\Soal;

class UjianController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
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
        return SendResponse::acceptData($ujian);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'tanggal'           => 'required',
            'mulai'             => 'required',
            'lama'              => 'required|int',
            'alias'             => 'required',
            'banksoal_id'       => 'required|array',
            'setting'           => 'required|array'
        ]);

        $data = [
            'mulai'             => date('H:i:s', strtotime($request->mulai)),
            'lama'              => $request->lama*60,
            'tanggal'           => date('Y-m-d',strtotime($request->tanggal)),
            'status_ujian'      => 0,
            'alias'             => $request->alias,
            'event_id'          => $request->event_id == '' ? 0 : $request->event_id,
            'setting'           => $request->setting
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

        if($request->server_id != '') {
            $fill = array();
            foreach($request->server_id as $server) {
                array_push($fill, $server['server_name']);
            }

            $data['server_id'] = $fill;
        }

        Jadwal::create($data);

        return SendResponse::accept();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Jadwal $ujian)
    {
        return SendResponse::acceptData($ujian);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Jadwal $ujian)
    {
        $request->validate([
            'tanggal'       => 'required',
            'mulai'         => 'required',
            'lama'          => 'required',
            'alias'         => 'required',
            'banksoal_id'       => 'required|array',
            'setting'           => 'required|array'
        ]);

        $data = [
            'mulai'         => date('H:i:s', strtotime($request->mulai)),
            'lama'          => $request->lama*60,
            'tanggal'       => date('Y-m-d', strtotime($request->tanggal)),
            'alias'         => $request->alias,
            'event_id'      => $request->event_id == '' ? 0 : $request->event_id,
            'setting'           => $request->setting
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

        $ujian->update($data);

        return SendResponse::accept();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Jadwal $ujian)
    {
        $ujian->delete();
        return SendResponse::accept();
    }

    /**
     * Set status ujian.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function setStatus(Request $request)
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
     * Get all ujian without pagination
     *
     * @return \Illuminate\http\Response
     */
    public function allData()
    {
        $ujians = Jadwal::orderBy('id','desc')->get();
        return SendResponse::acceptData($ujians);
    }

    /**
     * [getActive description]
     * @return [type] [description]
     */
    public function getActive()
    {
        $ujian = Jadwal::with('event')->where('status_ujian',1)->get();
        return SendResponse::acceptData($ujian);
    }

    /**
     * [getBanksoalByJadwal description]
     * @param  Jadwal $jadwal [description]
     * @return [type]         [description]
     */
    public function getBanksoalByJadwal(Jadwal $jadwal)
    {
        $res = HasilUjian::where('jadwal_id', $jadwal->id)
        ->get()->pluck('banksoal_id');

        $bankSoal = Banksoal::find($res);
        return SendResponse::acceptData($bankSoal);
    }
}
