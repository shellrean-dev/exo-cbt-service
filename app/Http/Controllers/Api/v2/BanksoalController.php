<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\BanksoalCollection;
use App\Banksoal;

use Illuminate\Support\Facades\Validator;

class BanksoalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $banksoal = Banksoal::orderBy('created_at', 'DESC');
        if (request()->q != '') {
            $banksoal = $banksoal->where('kode_banksoal', 'LIKE', '%'. request()->q.'%');
        }

        $banksoal = $banksoal->paginate(10);
        return new BanksoalCollection($banksoal);
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
            'kode_banksoal'     => 'required|unique:banksoals,kode_banksoal',
            'kelas_id'          => 'required|exists:kelas,id',
            'matpel_id'         => 'required|exists:matpels,id',
            'author'            => 'required|exists:users,id'
        ]); 

        if($validator->fails()) {
            return response()->json(['errors' => $validator->errors()],200);
        }

        $data = [
            'kode_banksoal'     => $request->kode_banksoal,
            'kelas_id'          => $request->kelas_id,
            'matpel_id'         => $request->matpel_id,
            'author'            => $request->author
        ];

        $res = Banksoal::create($data);

        return response()->json(['data' => $res]);
    }

}
