<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Actions\SendResponse;
use Illuminate\Http\Request;
use App\Jurusan;
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
     *  @return  App\Actions\SendResponse
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
     *  @return App\Actions\SendResponse
     *  @author shellrean <wandinak17@gmail.com>
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required',
        ]);

        Jurusan::create([
            'nama' => $request->nama
        ]);
        return SendResponse::accept();
    }

    /**
     * @Route(path="api/v1/jurusans/{jurusan}", methods={"GET"})
     * 
     * Get jurusan by id
     *
     * @param App\Jurusan $jurusan
     * @return  App\Actions\SendResponse
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
     * @param Illuminate\Http\Request $request
     * @param App\Jurusan $jurusan
     * @return App\Actions\SendResponse
     * @author shellrean <wandinak17@gmail.com>
     * 
     */
    public function update(Request $request, Jurusan $jurusan)
    {
        $request->validate([
            'nama' => 'required',
        ]);

        $jurusan->nama = $request->nama;
        $jurusan->save();

        return SendResponse::acceptData($jurusan);
    }

    /**
     * @Route(path="api/v1/jurusans/{jurusan}", methods={"DELETE"})
     * 
     * Delete Jurusan by id
     *
     * @param App\Jurusan $jurusan
     * @return App\Actions\SendResponse
     * @author shellrean <wandinak17@gmail.com>
     */
    public function destroy(Jurusan $jurusan)
    {
        $jurusan->delete();
        return SendResponse::accept();
    }

    /**
     * @Route(path="api/v1/jurusans/all", methods={"GET"})
     * 
     * Get all jurusan 
     *
     * @return  \App\Actions\SendResponse
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
     * @param Iluminate\Http\Request
     * @return App\Actions\SendResponse
     * @author shellrean <wandinak17@gmail.com>
     */
    public function destroyMultiple(Request $request)
    {
        $request->validate([
            'jurusan_id'    => 'required|array'
        ]);

        $jurusans = $request->jurusan_id;

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
