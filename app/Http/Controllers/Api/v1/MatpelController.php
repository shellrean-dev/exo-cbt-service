<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use App\Imports\MatpelImport;
use App\Actions\SendResponse;
use Illuminate\Http\Request;
use App\Matpel;

/**
 * MatpelController
 * @author shellrean <wandinak17@gmail.com>
 */
class MatpelController extends Controller
{
    /**
     * @Route(path="api/v1/matpels", methods={"GET"})
     *
     * Display a listing of the resource.
     *
     * @return App\Actions\SendResponse
     * @author shellrean <wandinak17@gmail.com>
     */
    public function index()
    {
        $perPage = request()->perPage ?: 10;

        $matpels = Matpel::orderBy('nama');
        if (request()->q != '') {
            $matpels = $matpels->where('nama', 'LIKE', '%'. request()->q.'%');
        }
        $matpels = $matpels->paginate($perPage);
        return SendResponse::acceptData($matpels);
    }

    /**
     * @Route(path="api/v1/matpels", methods={"POST"})
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
            'kode_mapel'    => 'required|unique:matpels,kode_mapel',
            'nama'          => 'required'
        ]);

        $data = [
            'kode_mapel'    => $request->kode_mapel,
            'nama'          => $request->nama,
            'jurusan_id' => ($request->jurusan_id != '' ? array_column($request->jurusan_id, 'id') : 0 ),
            'correctors' => ($request->correctors != '' ? array_column($request->correctors, 'id') : 0 ),
            'agama_id'  => ($request->agama_id != '' ? $request->agama_id : 0)
        ];
        $data = Matpel::create($data);
        return SendResponse::acceptData($data);
    }

    /**
     * @Route(path="api/v1/matpels/{matpel}", methods={"GET"})
     *
     * Display the specified resource.
     *
     * @param  App\Matpel $matpel
     * @return App\Actions\SendResponse
     * @author shellrean <wandinak17@gmail.com>
     */
    public function show(Matpel $matpel)
    {
        return SendResponse::acceptData($matpel);
    }

    /**
     * @Route(path="api/v1/matpels/{matpel}", methods={"PUT","PATCH"s})
     *
     * Update the specified resource in storage.
     *
     * @param Illuminate\Http\Request  $request
     * @param App\Matpel $matpel
     * @return App\Actions\SendResponse
     * @author shellrean <wandinak17@gmail.com>
     */
    public function update(Request $request, Matpel $matpel)
    {
        $request->all([
            'kode_mapel'    => 'required|unique:matpels,kode_mapel,'.$matpel->id,
            'nama'          => 'required'
        ]);
        $data = [
            'kode_mapel'    => $request->kode_mapel,
            'nama'          => $request->nama,
            'jurusan_id' => ($request->jurusan_id != '' ? array_column($request->jurusan_id, 'id') : 0 ),
            'correctors' => ($request->correctors != '' ? array_column($request->correctors, 'id') : 0 ),
            'agama_id'  => ($request->agama_id != '' ? $request->agama_id : 0)
        ];
        $matpel->update($data);
        return SendResponse::acceptData($matpel);
    }

    /**
     * @Route(path="api/v1/matpels/{matpel}", methods={"DELETE"})
     *
     * Remove the specified resource from storage.
     *
     * @param App\Matpel $matpel
     * @return App\Actions\SendResponse
     * @author shellrean <wandinak17@gmail.com>
     */
    public function destroy(Matpel $matpel)
    {
        $matpel->delete();
        return SendResponse::accept();
    }

    /**
     * @Route(path="api/v1/matpls/all", methods={"GET"})
     *
     * Display all of the resource.
     *
     * @return App\Actions\SendResponse
     * @author shellrean <wandinak17@gmail.com>
     */
    public function allData()
    {
        $matpels = Matpel::orderBy('nama')->get();
        return SendResponse::acceptData($matpels);
    }

    /**
     * @Route(path="api/v1/matpels/upload", methods={"POST"})
     *
     * @param Request $request
     * @return Response
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        DB::beginTransaction();
        try {
            Excel::import(new MatpelImport, $request->file('file'));

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return SendResponse::badRequest($e->getMessage().':'.'Pastikan tidak ada kode_mapel duplikat dan format sesuai');
        }
        return SendResponse::accept();
    }

    /**
     * @Route(path="api/v1/matpels/delete-multiple", methods={"POST"})
     *
     * Delete multiple matpel
     *
     * @param Illuminate\Http\Request $request
     * @return App\Actions\SendResponse
     * @author shellrean <wandinak17@gmail.com>
     */
    public function destroyMultiple(Request $request)
    {
        $request->validate([
            'matpel_id' => 'required'
        ]);

        DB::beginTransaction();
        try {
            Matpel::whereIn('id', $request->matpel_id)->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return SendResponse::badRequest('Error: '.$e->getMessage());
        }
        return SendResponse::accept();
    }
}
