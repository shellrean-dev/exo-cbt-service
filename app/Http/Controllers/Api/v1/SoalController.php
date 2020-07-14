<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use App\Imports\BanksoalImport;
use App\Actions\SendResponse;
use Illuminate\Http\Request;
use App\JawabanSoal;
use App\Banksoal;
use App\Soal;

class SoalController extends Controller
{
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
            'banksoal_id'   => 'required|exists:banksoals,id',
            'correct'       => 'required_if:tipe_soal,1',
            'pertanyaan'    => 'required'
        ]);

        DB::beginTransaction();

        try {
            $soal = Soal::create([
                'banksoal_id'   => $request->banksoal_id,
                'pertanyaan'    => $request->pertanyaan,
                'tipe_soal'     => $request->tipe_soal,
                'rujukan'       => $request->rujukan,
                'audio'         => $request->audio,
                'direction'     => $request->direction
            ]);

            if($request->tipe_soal != 2) {
                foreach($request->pilihan as $key=>$pilihan) {
                    JawabanSoal::create([
                        'soal_id'       => $soal->id,
                        'text_jawaban'  => $pilihan,
                        'correct'       => ($request->correct == $key ? '1' : '0')
                    ]);
                }
            } 

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return SendResponse::badRequest($e->getMessage());
        }

        return SendResponse::accept();
    }

    /**
     * 
     */
    public function storePaste(Request $request)
    {
        switch ($request->tipe_soal) {
            case '1':
                $collection = collect(explode("***", $request->soal));
                $arr = "ABCDEF";
                $data = $collection->map(function ($item, $key) use ($arr) {
                    $pil = explode("##", $item);
                    return [
                        'soal' => $pil[0],
                        'jawab' => strrpos($arr, $pil[1]),
                        'pilihan' => array_slice($pil, 2)
                    ];
                });

                foreach ($data as $key => $value) {
                    DB::beginTransaction();
                    try {
                        $soal = Soal::create([
                            'banksoal_id'   => $request->banksoal_id,
                            'pertanyaan'    => trim(preg_replace('/\s+/', ' ', $value['soal'])),
                            'tipe_soal'     => $request->tipe_soal,
                            'rujukan'       => ''
                        ]);

                        foreach($value['pilihan'] as $key=>$pilihan) {
                            JawabanSoal::create([
                                'soal_id'       => $soal->id,
                                'text_jawaban'  => trim(preg_replace('/\s+/', ' ', $pilihan)),
                                'correct'       => ($value['jawab'] == $key ? '1' : '0')
                            ]);
                        }

                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollback();
                        return response()->json(['message' => $e->getMessage()], 400);
                    }
                }
                return response()->json(['satus' => 'success']);

                break;
            case '2':
                $collection = collect(explode("***", $request->soal));

                $data = $collection->map(function ($item, $key) {
                    return [
                        'soal' => $item
                    ];
                });

                foreach ($data as $key => $value) {
                    DB::beginTransaction();
                    try {
                        $soal = Soal::create([
                            'banksoal_id'   => $request->banksoal_id,
                            'pertanyaan'    => trim(preg_replace('/\s+/', ' ', $value['soal'])),
                            'tipe_soal'     => $request->tipe_soal,
                            'rujukan'       => ''
                        ]);

                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollback();
                        return response()->json(['message' => $e->getMessage()], 400);
                    }
                }
                return response()->json(['satus' => 'success']);

                break;
            default:
                # code...
                break;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @author shellrean <wandinak17@gmail.com>
     * @return  \App\Actions\SendResponse
     */
    public function show(Soal $soal)
    {
        $soal = Soal::with('jawabans')->find($soal->id);
        return SendResponse::acceptData($soal);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Soal $soal)
    {
        $request->validate([
            'banksoal_id'   => 'required|exists:banksoals,id',
            'correct'       => 'required_if:tipe_soal,1',
            'pertanyaan'    => 'required'
        ]);

        DB::beginTransaction();

        try {
            $soal->pertanyaan = $request->pertanyaan;
            $soal->audio = $request->audio;
            $soal->direction = $request->direction;
            $soal->tipe_soal = $request->tipe_soal;
            $soal->rujukan = $request->rujukan;
            $soal->save();

            if($request->tipe_soal != 2 ) {
            DB::table('jawaban_soals')->where('soal_id',$request->soal_id)->delete();
                foreach($request->pilihan as $key=>$pilihan) {
                    JawabanSoal::create([
                        'soal_id'       => $soal->id,
                        'text_jawaban'  => $pilihan,
                        'correct'       => ($request->correct == $key ? '1' : '0')
                    ]);
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return SendResponse::badRequest($e->getMessage());
        }

        return SendResponse::accept();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Soal $soal)
    {
        DB::beginTransaction();

        try {
            JawabanSoal::where('soal_id', $soal->id)->delete();
            $soal->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return SendResponse::badRequest($e->getMessage());
        }
        return SendResponse::accept();
    }

    /**
     * Get soal by banksoal
     *
     * @param int $id
     * @author shellrean <wandinak17@gmail.com>
     * @return  \App\Actions\SendResponse
     */
    public function getSoalByBanksoal(Banksoal $banksoal)
    {
        $soal = Soal::with('jawabans')->where('banksoal_id',$banksoal->id);
        if (request()->q != '') {
            $soal = $soal->where('pertanyaan', 'LIKE', '%'. request()->q.'%');
        }

        if (request()->perPage != '') {
            $soal = $soal->paginate(request()->perPage);
        } else {
            $soal = $soal->get();
        }
        return SendResponse::acceptData($soal);
    }

    /**
     * Get soal by banksoal
     *
     * @param int $id
     * @author shellrean <wandinak17@gmail.com>
     * @return  \App\Actions\SendResponse
     */
    public function getSoalByBanksoalAll(Banksoal $banksoal)
    {
        $soal = Soal::with('jawabans')->where('banksoal_id',$banksoal->id)->get();
        return SendResponse::acceptData($soal);
    }

    /**
     * Get soal by banksoal analys
     *
     * @param int $id
     * @author shellrean <wandinak17@gmail.com>
     * @return  \App\Actions\SendResponse
     */
    public function getSoalByBanksoalAnalys(Banksoal $banksoal)
    {
        $soal = Soal::where('banksoal_id',$banksoal->id)->get()
        ->makeVisible('diagram')
        ->makeVisible('analys');
        return SendResponse::acceptData($soal);
    }

    /**
     * [import description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function import(Request $request, Banksoal $banksoal)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        DB::beginTransaction();
        try {
            Excel::import(new BanksoalImport($banksoal->id), $request->file('file'));

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return SendResponse::badRequest($e->getMessage());
        }
        return SendResponse::accept();
    }
}
