<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use App\Imports\BanksoalImport;
use App\Services\ExoProcessDoc;
use App\Actions\SendResponse;
use App\Services\WordService;
use App\Services\SoalService;
use Illuminate\Http\Request;
use App\JawabanSoal;
use App\Directory;
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
            'selected'      => 'required_if:tipe_soal,4|array',
            'pertanyaan'    => 'required',
            'layout'        => 'required'
        ]);

        DB::beginTransaction();

        try {
            $soal = Soal::create([
                'banksoal_id'   => $request->banksoal_id,
                'pertanyaan'    => $request->pertanyaan,
                'tipe_soal'     => $request->tipe_soal,
                'rujukan'       => $request->rujukan,
                'audio'         => $request->audio,
                'direction'     => $request->direction,
                'layout'        => $request->layout
            ]);

            if(in_array($request->tipe_soal, [1,3,4,5,6])) {
                $data = [];
                foreach($request->pilihan as $key=>$pilihan) {
                    if(in_array($request->tipe_soal, [1,3])) { // The tipe soal is PG, Listening
                        $correct = $request->correct == $key ? '1' : '0';
                    }
                    else if($request->tipe_soal == 4) { // The tipe soal is PG Komplek
                        $correct = in_array($key, $request->selected) ? '1' : '0';
                    }
                    else {
                        $correct = '0';
                    }

                    // If type question menjodohkan
                    if ($request->tipe_soal == 5) {
                        $pair = [
                            "a"  => [
                                "id"    => "a".uniqid(),
                                "text"  => $pilihan["a"]
                            ],
                            "b"  => [
                                "id"    => "b".uniqid(),
                                "text"  => $pilihan["b"]
                            ]
                        ];
                        $pilihan = json_encode($pair);
                    }

                    array_push($data, [
                        'soal_id'       => $soal->id,
                        'text_jawaban'  => $pilihan,
                        'correct'       => $correct,
                    ]);
                }
                DB::table('jawaban_soals')->insert($data);
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
            'selected'      => 'required_if:tipe_soal,4|array',
            'pertanyaan'    => 'required',
            'layout'        => 'required'
        ]);

        DB::beginTransaction();

        try {
            $soal->pertanyaan = $request->pertanyaan;
            $soal->audio = $request->audio;
            $soal->direction = $request->direction;
            $soal->tipe_soal = $request->tipe_soal;
            $soal->rujukan = $request->rujukan;
            $soal->layout = $request->layout;
            $soal->save();

            if(in_array($request->tipe_soal, [1,3,4,5,6])) {
                DB::table('jawaban_soals')->where('soal_id',$request->soal_id)->delete();
                $data = [];
                foreach($request->pilihan as $key=>$pilihan) {
                    if(in_array($request->tipe_soal, [1,3])) { // The tipe soal is PG, Listening
                        $correct = $request->correct == $key ? '1' : '0';
                    }
                    else if($request->tipe_soal == 4) { // The tipe soal is PG Komplek
                        $correct = in_array($key, $request->selected) ? '1' : '0';
                    }
                    else {
                        $correct = '0';
                    }

                    // If type question menjodohkan
                    if ($request->tipe_soal == 5) {
                        $pair = [
                            "a"  => [
                                "id"    => "a".uniqid(),
                                "text"  => $pilihan["a"]
                            ],
                            "b"  => [
                                "id"    => "b".uniqid(),
                                "text"  => $pilihan["b"]
                            ]
                        ];
                        $pilihan = json_encode($pair);
                    }

                    array_push($data, [
                        'soal_id'       => $soal->id,
                        'text_jawaban'  => $pilihan,
                        'correct'       => $correct,
                    ]);
                }
                DB::table('jawaban_soals')->insert($data);
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

        if (request()->t != '') {
            $soal = $soal->where("tipe_soal", request()->t);
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

    /**
     * Import soal from docx
     *
     * @author shellrean <wandinak17@gmail.com>
     * @param \Illuminate\http\Request $request
     * @param \App\Services\WordService $wordService
     * @return \App\Actions\SendResponse;
     */
    private function _formatStandart($request, $banksoal)
    {
        $wordService = new WordService();
        $soalService = new SoalService();

        $dir = Directory::find($banksoal->directory_id);

        $file = $request->file('file');
        $nama_file = time().$file->getClientOriginalName();
        $path = $file->storeAs('public/'.$dir->slug,$nama_file);

        $file = storage_path('app/'.$path);

        $read = $wordService->wordFileImport($file, $dir);
        if(!$read) {
            return SendResponse::badRequest("Can't read file doc");
        }
        $insert = $soalService->importQues($read, $banksoal->id);

        if(!$insert['success']) {
            return SendResponse::badRequest($insert['message']);
        }

        return SendResponse::accept();
    }

    /**
     * Import soal from docx
     *
     * @author shellrean <wandinak17@gmail.com>
     * @param \Illuminate\http\Request $request
     * @return \App\Actions\SendResponse;
     */
    private function _formatTabled($request, $banksoal)
    {
        $dir = DB::table('directories')
            ->where('id', $banksoal->directory_id)
            ->first();
        if (!$dir) {
            return SendResponse::badRequest('kesalahan, directory tidak ditemukan');
        }

        $file = $request->file('file');
        $nama_file = time().$file->getClientOriginalName();
        $path = $file->storeAs('public/'.$dir->slug,$nama_file);

        $file = storage_path('app/'.$path);

        DB::beginTransaction();
        try {
            $exoProc = new ExoProcessDoc('1',$file, $dir);
            $data = $exoProc->render();
            $questions = $data['data'];
            $files = $data['files'];
            
            $options = [];
            foreach($questions as $key => $question) {
                $soal = [
                    'banksoal_id'   => $banksoal->id,
					'tipe_soal'     => $question['type'],
					'pertanyaan'    => $question['pertanyaan'],
					'created_at'	=> now(),
					'updated_at'	=> now(),
                ];

                $soal_id = DB::table('soals')->insertGetId($soal);

                foreach($question['options'] as $key => $opt) {
                    $isCorrect = in_array($key, $question['correct']) ? 1 : 0;
                    array_push($options, [
                        'soal_id'       => $soal_id,
                        'text_jawaban'  => $opt,
                        'correct'       => $isCorrect,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                }
            }
            if (count($options) > 0) {
                DB::table('jawaban_soals')->insert($options);
            }
            if (count($files) > 0) {
                DB::table('files')->insert($files);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return SendResponse::badRequest('kesalahan 500 ('.$e->getMessage().')');
        }
        return SendResponse::accept();
    }

    /**
     * Import soal from docx
     *
     * @author shellrean <wandinak17@gmail.com>
     * @param \Illuminate\http\Request $request
     * @return \App\Actions\SendResponse;
     */
    public function wordImport(Request $request, $banksoal_id)
    {
        $request->validate([
            'file' => 'required|mimes:docx',
            'format' => 'required'
        ]);

        $banksoal = DB::table('banksoals')
            ->where('id', $banksoal_id)
            ->select('id','directory_id')
            ->first();

        if (!$banksoal) {
            return SendResponse::badRequest('kesalahan, banksoal tidak ditemukan');
        }

        if($request->format == '1') {
            return $this->_formatStandart($request, $banksoal);
        }
        if ($request->format == '2') {
            return $this->_formatTabled($request, $banksoal);
        }

        return SendResponse::badRequest('format tidak sesuai');
    }
}
