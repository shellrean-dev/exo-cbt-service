<?php

namespace App\Http\Controllers\Api\v1;

use App\Actions\SendResponse;
use App\Competence;
use App\Http\Controllers\Controller;
use App\Matpel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * CompetenceController Controller
 * @author shellrean <wandinak17@gmail.com>
 */
class CompetenceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $competences = Competence::queryBuilder()
            ->orderByDesc('created_at')
            ->get();
        return SendResponse::acceptData($competences);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'code'      => 'required',
            'matpel_id' => 'required',
            'name'      => 'required'
        ]);
        $matpel = DB::table('matpels')
            ->where('id', $request->matpel_id)
            ->first();
        if(!$matpel) {
            return SendResponse::badRequest();
        }

        Competence::create([
            'code'      => $request->code,
            'matpel_id' => $matpel->id,
            'name'      => $request->name
        ]);

        return SendResponse::accept();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
