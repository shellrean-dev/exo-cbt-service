<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Actions\SendResponse;
use Illuminate\Http\Request;
use App\Matpel;

class MatpelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @author shellrean <wandinak17@gmail.com>
     * @return  \App\Actions\SendResponse
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @author shellrean <wandinak17@gmail.com>
     * @return  \App\Actions\SendResponse
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @author shellrean <wandinak17@gmail.com>
     * @return  \App\Actions\SendResponse
     */
    public function show(Matpel $matpel)
    {
        return SendResponse::acceptData($matpel);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @author shellrean <wandinak17@gmail.com>
     * @return  \App\Actions\SendResponse
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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @author shellrean <wandinak17@gmail.com>
     * @return  \App\Actions\SendResponse
     */
    public function destroy(Matpel $matpel)
    {
        $matpel->delete();
        return SendResponse::accept();
    }

    /**
     * Display all of the resource.
     *
     * @author shellrean <wandinak17@gmail.com>
     * @return  \App\Actions\SendResponse
     */
    public function allData()
    {
        $matpels = Matpel::orderBy('nama')->get();
        return SendResponse::acceptData($matpels);
    }
}
