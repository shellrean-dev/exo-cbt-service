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
     * @return \Illuminate\Http\Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function index()
    {
        $perPage = intval(request()->perPage);
        if (!$perPage) {
            $perPage = 30;
        }
        $peserta = DB::table('pesertas as t_0')
            ->join('agamas as t_1','t_0.agama_id','=','t_1.id')
            ->join('jurusans as t_2','t_0.jurusan_id','=','t_2.id')
            ->select([
                't_0.id',
                't_0.sesi',
                't_0.no_ujian',
                't_0.nama as nama_peserta',
                't_0.password',
                't_0.status',
                't_0.block_reason',
                't_0.antiblock',
                't_1.nama as agama',
                't_2.nama as jurusan'
            ]);
        if (request()->q != '') {
            $peserta = $peserta->where('t_0.nama', 'LIKE', '%'.request()->q.'%');
        }

        $peserta = $peserta
            ->orderBy('t_0.created_at')
            ->paginate($perPage);

        return SendResponse::acceptData($peserta);
    }

    /**
     * @Route(path="api/v1/pesertas", methods={"POST"})
     *
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
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
            'status'        => 'required|int',
            'agama_id'      => 'required'
        ]);

        $data = [
            'no_ujian'      => $request->no_ujian,
            'nama'          => $request->nama,
            'password'      => $request->password,
            'sesi'          => $request->sesi,
            'status'        => $request->status,
            'jurusan_id'    => $request->jurusan_id,
            'agama_id'      => $request->agama_id,
            'antiblock'     => isset($request->antiblock) ? $request->antiblock :  false
        ];

        $data = Peserta::create($data);
        return SendResponse::acceptData($data);
    }

    /**
     * @Route(path="api/v1/pesertas/{peserta}", methods={"GET"})
     *
     * Display the specified resource.
     *
     * @param \App\Peserta $peserta
     * @return \Illuminate\Http\Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function show($peserta)
    {
        $data = DB::table('pesertas as t_0')
            ->select([
                't_0.id',
                't_0.agama_id',
                't_0.jurusan_id',
                't_0.nama',
                't_0.no_ujian',
                't_0.sesi',
                't_0.block_reason',
                't_0.status',
                't_0.password',
                't_0.antiblock'
            ])
            ->where('id', $peserta)
            ->first();
        return SendResponse::acceptData($data);
    }

    /**
     * @Route(path="api/v1/pesertas/{peserta}, methods={"PUT", "PATCH"})
     *
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request  $request
     * @param \App\Peserta $pesertas
     * @return \Illuminate\Http\Response
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
            'agama_id'      => 'required',
            'status'        => 'required|int'
        ]);

        $data = [
            'no_ujian'      => $request->no_ujian,
            'nama'          => $request->nama,
            'password'      => $request->password,
            'sesi'          => $request->sesi,
            'status'        => $request->status,
            'jurusan_id'    => $request->jurusan_id,
            'agama_id'      => $request->agama_id,
            'block_reason'  => $request->status == 1 ? "" : $request->block_reason,
            'antiblock'     => isset($request->antiblock) ? $request->antiblock :  false
        ];

        if($request->status == 1) {
            DB::table("siswa_ujians")->where("peserta_id", $peserta->id)->update(['out_ujian_counter' => 0]);
        }

        $peserta->update($data);
        return SendResponse::acceptData($data);
    }

    /**
     * @Route(path="api/v1/pesertas/{peserta}", methods={"DELETE"})
     *
     * Remove the specified resource from storage.
     *
     * @param \App\Peserta $peserta
     * @return \Illuminate\Http\Response
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
     * @param \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
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
            return SendResponse::badRequest('Pastikan tidak ada no ujian duplikat dan format sesuai: '.$e->getMessage());
        }
        return SendResponse::accept();
    }

    /**
     * @Route(path="api/v1/pesertas/login", methods={"GET"})
     *
     * @return \Illuminate\Http\Response
     */
    public function getPesertaLogin()
    {
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
     * @param  \App\Peserta $peserta
     * @return \Illuminate\Http\Response
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
     * @return \Illuminate\Http\Response
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
            return SendResponse::accept('success');
        } catch (\Exception $e) {
            return SendResponse::internalServerError('kesalahan 500 ('.$e->getMessage().')');
        }
    }

    /**
     * @Route(path="api/v1/pesertas/delete-multiple", methods={"POST"})
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
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

    /**
     * @route(path="api/v1/pesertas/status-blocked", methods={"GET"})
     *
     * @return \Illuminate\Http\Response
     */
    public function blocked()
    {
        $peserta = DB::table('pesertas as t_0')
            ->select([
                't_0.id',
                't_0.sesi',
                't_0.no_ujian',
                't_0.nama as nama_peserta'
            ])
            ->orderByDesc('t_0.created_at')
            ->where('t_0.status', 0)
            ->get();

        return SendResponse::acceptData($peserta);
    }

    /**
     * @route(path="api/v1/pesertas/unblock/{peserta_id}", methods={"DELETE"})
     *
     * @return \Illuminate\Http\Response
     */
    public function unblock(Request $request)
    {
        $pesertaSrt = $request->get('peserta_id');
        $pesertas = explode(',', $pesertaSrt);

        DB::table('pesertas')->whereIn('id', $pesertas)->update([
            'status' => 1,
            'block_reason' => ''
        ]);
        DB::table("siswa_ujians")->whereIn("peserta_id", $pesertas)->update(['out_ujian_counter' => 0]);
        return SendResponse::accept();
    }
}
