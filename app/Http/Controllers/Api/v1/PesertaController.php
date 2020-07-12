<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use App\Imports\PesertaImport;
use App\Actions\SendResponse;
use Illuminate\Http\Request;
use App\Peserta;

class PesertaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @author shellrean <wandinak17@gmail.com>
     * @return  \App\Actions\SendResponse
     */
    public function index()
    {
        $peserta = Peserta::with('agama','jurusan')->orderBy('id');
        if (request()->q != '') {
            $peserta = $peserta->where('nama', 'LIKE', '%'.request()->q.'%');
        }
        $peserta = $peserta->paginate(40);
        return SendResponse::acceptData($peserta);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @author shellrean <wandinak17@gmail.com>
     * @return  \App\Actions\SendResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'no_ujian'      => 'required|unique:pesertas,no_ujian',
            'nama'          => 'required',
            'password'      => 'required',
            'sesi'          => 'required',
            'jurusan_id'    => 'required',
            'agama_id'      => 'required'
        ]);

        $data = [
            'no_ujian'      => $request->no_ujian,
            'nama'          => $request->nama,
            'password'      => $request->password,
            'sesi'          => $request->sesi,
            'jurusan_id'    => $request->jurusan_id,
            'agama_id'      => $request->agama_id
        ];

        $data = Peserta::create($data);
        return SendResponse::acceptData($data);
    }

    /**
     * Display the specified resource.
     *
     * @author shellrean <wandinak17@gmail.com>
     * @return  \App\Actions\SendResponse
     */
    public function show(Peserta $peserta)
    {
        return SendResponse::acceptData($peserta);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @author shellrean <wandinak17@gmail.com>
     * @return  \App\Actions\SendResponse
     */
    public function update(Request $request, Peserta $peserta)
    {
        $request->validate([
            'no_ujian'      => 'required|unique:pesertas,no_ujian,'.$peserta->id,
            'nama'          => 'required',
            'password'      => 'required',
            'sesi'          => 'required',
            'jurusan_id'    => 'required',
            'agama_id'      => 'required'
        ]);

        $data = [
            'no_ujian'      => $request->no_ujian,
            'nama'          => $request->nama,
            'password'      => $request->password,
            'sesi'          => $request->sesi,
            'jurusan_id'    => $request->jurusan_id,
            'agama_id'      => $request->agama_id
        ];

        $peserta->update($data);
        return SendResponse::acceptData($data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Peserta $peserta)
    {
        $peserta->delete();
        return SendResponse::accept();
    }

    /**
     * Upload peserta by excel
     *
     * @param \Illuminate\Http\Request  $request
     * @author shellrean <wandinak17@gmail.com>
     * @return  \App\Actions\SendResponse
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        DB::beginTransaction();

        try {
            Excel::import(new PesertaImport,$request->file('file'));
            
            DB::commit();    
        } catch (\Exception $e) {
            DB::rollback();
            return SendResponse::badRequest('Pastikan tidak ada no ujian duplikat dan format sesuai');
        }
        return SendResponse::accept();
    }

    /**
     * [getPesertaLogin description]
     * @return [type] [description]
     */
    public function getPesertaLogin()
    {
        $peserta = Peserta::orderBy('no_ujian');
        $peserta->where('api_token','!=','');
        if (request()->q != '') {
            $peserta = $peserta->where('nama', 'LIKE', '%'.request()->q.'%');
        }

        $peserta = $peserta->paginate(30);
        return SendResponse::acceptData($peserta);
    }

    /**
     * [resetPesertaLogin description]
     * @param  Peserta $peserta [description]
     * @return [type]           [description]
     */
    public function resetPesertaLogin(Peserta $peserta)
    {
        $peserta->api_token = '';
        $peserta->save();

        return SendResponse::accept();
    }
}
