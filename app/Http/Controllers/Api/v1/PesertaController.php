<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use App\Imports\PesertaImport;
use App\Actions\SendResponse;
use Illuminate\Http\Request;
use App\Peserta;

/**
 * PesertaController
 * @author shellrean <wandinak17@gmail.com>
 */
class PesertaController extends Controller
{
    /**
     * @Route(path="api/v1/pesertas", methods={"GET"})
     * 
     * Display a listing of the resource.
     *
     * @return App\Actions\SendResponse
     * @author shellrean <wandinak17@gmail.com>
     */
    public function index()
    {
        $peserta = DB::table('pesertas')
            ->join('agamas','pesertas.agama_id','=','agamas.id')
            ->join('jurusans','pesertas.jurusan_id','=','jurusans.id')
            ->select('pesertas.id','pesertas.sesi','pesertas.no_ujian','pesertas.nama as nama_peserta','pesertas.password','agamas.nama as agama','jurusans.nama as jurusan');
        if (request()->q != '') {
            $peserta = $peserta->where('pesertas.nama', 'LIKE', '%'.request()->q.'%');
        }
        
        $peserta = $peserta->paginate(40);
        return SendResponse::acceptData($peserta);
    }

    /**
     * @Route(path="api/v1/pesertas", methods={"POST"})
     * 
     * Store a newly created resource in storage.
     *
     * @param Illuminate\Http\Request  $request
     * @return App\Actions\SendResponse
     * @author shellrean <wandinak17@gmail.com>
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
     * @Route(path="api/v1/pesertas/{peserta}", methods={"GET"})
     * 
     * Display the specified resource.
     *
     * @param App\Peserta $peserta
     * @return App\Actions\SendResponse
     * @author shellrean <wandinak17@gmail.com>
     */
    public function show(Peserta $peserta)
    {
        return SendResponse::acceptData($peserta);
    }

    /**
     * @Route(path="api/v1/pesertas/{peserta}, methods={"PUT", "PATCH"})
     * 
     * Update the specified resource in storage.
     *
     * @param Illuminate\Http\Request  $request
     * @param App\Peserta $pesertas
     * @return App\Actions\SendResponse
     * @author shellrean <wandinak17@gmail.com>
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
     * @Route(path="api/v1/pesertas/{peserta}", methods={"DELETE"})
     * 
     * Remove the specified resource from storage.
     *
     * @param App\Peserta $peserta
     * @return Illuminate\Http\Response
     */
    public function destroy(Peserta $peserta)
    {
        $peserta->delete();
        return SendResponse::accept();
    }

    /**
     * @Route(path="api/v1/pesertas/upload", methods={"POST"})
     * 
     * Upload peserta by excel
     *
     * @param Illuminate\Http\Request  $request
     * @return App\Actions\SendResponse
     * @author shellrean <wandinak17@gmail.com>
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
     * @Route(path="api/v1/pesertas/login", methods={"GET"})
     * 
     * @return App\Actions\SendResponse
     */
    public function getPesertaLogin()
    {
        // $peserta = Peserta::orderBy('no_ujian');
        $peserta = DB::table('pesertas')
            ->select('id','no_ujian','nama');
        $peserta->where('api_token','!=','');
        if (request()->q != '') {
            $peserta = $peserta->where('nama', 'LIKE', '%'.request()->q.'%');
            $peserta = $peserta->orWhere('no_ujian', 'LIKE', '%'.request()->q.'%');
        }

        $peserta = $peserta->paginate(10);
        return SendResponse::acceptData($peserta);
    }

    /**
     * @Route(path="api/v1/pesertas/{peserta}/login", methods={"DELETE"})
     * 
     * @param  App\Peserta $peserta
     * @return App\Actions\SendResponse
     */
    public function resetPesertaLogin(Peserta $peserta)
    {
        $peserta->api_token = '';
        $peserta->save();

        return SendResponse::accept();
    }

    /**
     * @Route(path="api/v1/pesertas/multi-reset-login", methods={"GET"})
     * 
     * Multiple reset api-token peserta
     * 
     * @return App\Actions\SendResponse
     * @author shellrean <wandinak17@gmail.com>
     */
    public function multiResetPeserta()
    {
        try {
            $ids = request()->q;
            $ids = explode(',', $ids);

            DB::table('pesertas')
                ->whereIn('id', $ids)
                ->update([
                    'api_token' => ''
                ]);
        } catch (\Exception $e) {
            return SendResponse::internalServerError('kesalahan 500 ('.$e->getMessage().')');
        }
    }

    /**
     * @Route(path="api/v1/pesertas/delete-multiple", methods={"POST"})
     * 
     * @param Illuminate\Http\Request $request
     * @return App\Actions\SendResponse
     */
    public function destroyMultiple(Request $request)
    {
        $request->validate([    
            'peserta_id'    => 'required|array'
        ]);

        DB::beginTransaction();
        try {
            Peserta::whereIn('id', $request->peserta_id)->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return SendResponse::badRequest('Error: '.$e->getMessage());
        }
        return SendResponse::accept();
    }
}
