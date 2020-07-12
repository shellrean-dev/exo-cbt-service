<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;

use App\Matpel;

class MatpelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Matpel::paginate(10);

        return response()->json(['data' => $data]);
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
            'kode_mapel'    => 'required|unique:matpels,kode_mapel',
            'nama'          => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()],422);
        }

        $data = [
            'kode_mapel'    => $request->kode_mapel,
            'nama'          => $request->nama
        ];

        $data = Matpel::create($data);

        return response()->json(['data' => $data]);
    }
}
