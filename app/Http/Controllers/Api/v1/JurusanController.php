<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Actions\SendResponse;
use Illuminate\Http\Request;
use App\Jurusan;

class JurusanController extends Controller
{
    /**
     *  Display list of the source
     *  
     *  @author shellrean <wandinak17@gmail.com>
     *  @return  \App\Actions\SendResponse
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
     * Store jurusan new
     *
     *  @author shellrean <wandinak17@gmail.com>
     *  @return  \App\Actions\SendResponse
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
     * Get jurusan by id
     *
     *  @author shellrean <wandinak17@gmail.com>
     *  @return  \App\Actions\SendResponse
     */
    public function show(Jurusan $jurusan)
    {
        return SendResponse::acceptData($jurusan);
    }

    /**
     * Update jurusan by id
     *
     *  @author shellrean <wandinak17@gmail.com>
     *  @return  \App\Actions\SendResponse
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
     * Delete Jurusan by id
     *
     *  @author shellrean <wandinak17@gmail.com>
     *  @return  \App\Actions\SendResponse
     */
    public function destroy(Jurusan $jurusan)
    {
        $jurusan->delete();
        return SendResponse::accept();
    }

    /**
     *  Get all jurusan 
     *
     *  @author shellrean <wandinak17@gmail.com>
     *  @return  \App\Actions\SendResponse
     */
    public function allData()
    {
        $jurusans = Jurusan::orderBy('id')->get();
        return SendResponse::acceptData($jurusans);
    }
}   
