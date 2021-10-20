<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Actions\SendResponse;
use Exception;
use Illuminate\Http\Request;
use App\Jurusan;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

/**
 * JurusanController
 * @author shellrean <wandinak17@gmail.com>
 */
class JurusanController extends Controller
{
    /**
     * @Route(path="api/v1/jurusans", methods={"GET"})
     *
     *  Display list of the source
     *
     *  @return  Response
     *  @author shellrean <wandinak17@gmail.com>
     */
    public function index()
    {
        $jurusans = Jurusan::orderBy('nama');
        if (request()->q != '') {
            $jurusans = $jurusans->where('nama', 'LIKE', '%'. request()->q.'%');
        }

        if(request()->perPage != '') {
            $jurusans = $jurusans->paginate(request()->perPage);
        } else {
            $jurusans = $jurusans->get();
        }
        return SendResponse::acceptData($jurusans);
    }

    /**
     * @Route(path="api/v1/jurusans", methods={"POST"})
     *
     * Store jurusan new
     *
     *  @return Response
     *  @author shellrean <wandinak17@gmail.com>
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required',
        ]);

        if (isset($request->kode)) {
            $kode = $request->kode;

            $find = DB::table('jurusans')->where('kode', $kode)->count();
            if ($find) {
                return SendResponse::badRequest('Kode jurusan sudah dipakai');
            }
        } else {
            $kode = uniqid();
        }

        Jurusan::create([
            'kode' => $kode,
            'nama' => $request->nama
        ]);
        return SendResponse::accept();
    }

    /**
     * @Route(path="api/v1/jurusans/{jurusan}", methods={"GET"})
     *
     * Get jurusan by id
     *
     * @param Jurusan $jurusan
     * @return  Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function show(Jurusan $jurusan)
    {
        return SendResponse::acceptData($jurusan);
    }

    /**
     * @Route(path="api/v1/jurusans/{jurusan}", methods={"PUT", "PATCH"})
     *
     * Update jurusan by id
     *
     * @param Request $request
     * @param Jurusan $jurusan
     * @return Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function update(Request $request, Jurusan $jurusan)
    {
        $request->validate([
            'nama' => 'required',
            'kode' => 'required'
        ]);
        if ($request->kode != $jurusan->kode) {
            $find = DB::table('pesertas')->where('jurusan_id', $jurusan->id)->count();
            if ($find > 0) {
                return SendResponse::badRequest('Jurusan sudah digunakan oleh peserta, tidak boleh edit kode jurusan');
            }
        }

        $find = DB::table('jurusans')->where('kode', $request->kode)->first();
        if ($find && $find->id != $jurusan->id) {
            return SendResponse::badRequest('Kode jurusan sudah dipakai');
        }

        $jurusan->nama = $request->nama;
        $jurusan->kode = $request->kode;
        $jurusan->save();

        return SendResponse::acceptData($jurusan);
    }

    /**
     * @Route(path="api/v1/jurusans/{jurusan}", methods={"DELETE"})
     *
     * Delete Jurusan by id
     *
     * @param Jurusan $jurusan
     * @return Response
     * @throws Exception
     * @author shellrean <wandinak17@gmail.com>
     */
    public function destroy(Jurusan $jurusan)
    {
        $used = DB::table('pesertas')->where('jurusan_id', $jurusan->id)->count();
        if ($used) {
            return SendResponse::badRequest('Tidak bisa menghapus jurusan. ['.$used.'] peserta menggunakan jurusan ini');
        }
        $jurusan->delete();
        return SendResponse::accept();
    }

    /**
     * @Route(path="api/v1/jurusans/all", methods={"GET"})
     *
     * Get all jurusan
     *
     * @return  Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function allData()
    {
        $jurusans = Jurusan::orderBy('nama')->get();
        return SendResponse::acceptData($jurusans);
    }

    /**
     * @Route(path="api/v1/jurusans/delete-multiple", methods={"GET"})
     *
     * Multiple destroy jurusan
     *
     * @param Request $request
     * @return Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function destroyMultiple(Request $request)
    {
        $request->validate([
            'jurusan_id'    => 'required|array'
        ]);

        $jurusans = $request->jurusan_id;

        $used = DB::table('pesertas')->whereIn('jurusan_id', $jurusans)->count();
        if ($used) {
            return SendResponse::badRequest('Jurusan telah digunakan oleh peserta');
        }

        DB::beginTransaction();

        try {
            Jurusan::whereIn('id', $jurusans)->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return SendResponse::badRequest('Error: '.$e->getMessage());
        }
        return SendResponse::accept();
    }
}
