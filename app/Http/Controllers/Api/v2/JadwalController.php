<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


use App\Jadwal;

use Illuminate\Support\Facades\Validator;

class JadwalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $jadwal = Jadwal::orderBy('created_at','DESC');
        $jadwal = $jadwal->paginate(10);

        return response()->json(['data' => $jadwal]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'banksoal_id'       => 'required|exists:banksoals,id',
            'tanggal'           => 'required|date',
            'mulai'             => 'required|string',
            'berakhir'          => 'required|string',
            'lama'              => 'required|int',
            'token'             => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        $dat = [
            'banksoal_id'       => $request->banksoal_id,
            'tanggal'           => $request->tanggal,
            'mulai'             => $request->mulai,
            'berakhir'          => $request->berakhir,
            'lama'              => $request->lama,
            'token'             => $request->token,
            'status_ujian'      => $request->status_ujian
        ];

        $res = Jadwal::create($dat);

        return response()->json(['data' => $res]);
    }

    /**
     * Get data of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getday()
    {
        $data = Jadwal::with([
            'banksoal','banksoal.matpel'
        ])->where([
            'tanggal'       => now()->format('Y-m-d'),
            'status_ujian'  => 1
        ])->first();

        return response()->json(['data' => $data]);
    }
}
