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
use Illuminate\Support\Str;
use App\JawabanSoal;
use App\Directory;
use App\Banksoal;
use App\Soal;

/**
 * SoalController
 * @author shellrean <wandinak17@gmail.com>
 */
class SoalController extends Controller
{
    /**
     * @Route(path="api/v1/soals", methods={"POST"})
     *
     * Store a newly created resource in storage.
     *
     * @param Illuminate\Http\Request  $request
     * @return App\Actions\SendResponse
     * @author shellrean <wandinak17@gmail.com>
     */
    public function store(Request $request)
    {
        $request->validate([
            'banksoal_id'   => 'required|exists:banksoals,id',
            'correct'       => 'required_if:tipe_soal,1',
            'selected'      => 'required_if:tipe_soal,4|array',
            'pertanyaan'    => 'required',
            'layout'        => 'required',
            'case_sensitive' => 'required_if:tipe_soal,6'
        ]);

        $banksoal = DB::table('banksoals')
            ->where('id', $request->banksoal_id)
            ->select(['id','is_locked'])
            ->first();
        if (!$banksoal) {
            return SendResponse::badRequest('Banksoal tidak ditemukan');
        }

        if ($banksoal->is_locked) {
            return SendResponse::badRequest('Banksoal sedang dikunci');
        }

        DB::beginTransaction();

        try {
            $data = [
                'id'            => Str::uuid()->toString(),
                'banksoal_id'   => $request->banksoal_id,
                'pertanyaan'    => $request->pertanyaan,
                'tipe_soal'     => $request->tipe_soal,
                'rujukan'       => $request->rujukan,
                'audio'         => $request->audio,
                'direction'     => $request->direction,
                'layout'        => $request->layout
            ];

            if (isset($request->case_sensitive)) {
                $data['case_sensitive'] = $request->case_sensitive;
            }

            $soal = Soal::create($data);

            if(in_array($request->tipe_soal, [1,3,4,5,6,7])) {
                $data = [];
                foreach($request->pilihan as $key => $pilihan) {
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
                        'id'            => Str::uuid()->toString(),
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
     * @Route(path="api/v1/soals/paste", methods={"POST"})
     *
     * @param Illuminate\Http\Request $request
     */
    public function storePaste(Request $request)
    {
        $banksoal = DB::table('banksoals')
            ->where('id', $request->banksoal_id)
            ->select(['id','is_locked'])
            ->first();
        if (!$banksoal) {
            return SendResponse::badRequest('Banksoal tidak ditemukan');
        }

        if ($banksoal->is_locked) {
            return SendResponse::badRequest('Banksoal sedang dikunci');
        }
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
     * @Route(path="api/v1/soals/{soal}", methods={"GET"})
     *
     * Display the specified resource.
     *
     * @param App\Soal $soal
     * @return App\Actions\SendResponse
     * @author shellrean <wandinak17@gmail.com>
     */
    public function show(Soal $soal)
    {
        $banksoal = DB::table('banksoals')
            ->where('id', $soal->banksoal_id)
            ->select(['id','is_locked'])
            ->first();
        if (!$banksoal) {
            return SendResponse::badRequest('Banksoal tidak ditemukan');
        }

        if ($banksoal->is_locked) {
            return SendResponse::badRequest('Banksoal sedang dikunci');
        }
        $soal = Soal::with('jawabans')->find($soal->id);
        return SendResponse::acceptData($soal);
    }

    /**
     * @Route(path="api/v1/soals/{soal}", methods={"PUT", "PATCH"})
     *
     * Update the specified resource in storage.
     *
     * @param Illuminate\Http\Request  $request
     * @param App\Soal $soal
     * @return Illuminate\Http\Response
     */
    public function update(Request $request, Soal $soal)
    {
        $request->validate([
            'banksoal_id'   => 'required|exists:banksoals,id',
            'correct'       => 'required_if:tipe_soal,1',
            'selected'      => 'required_if:tipe_soal,4|array',
            'pertanyaan'    => 'required',
            'layout'        => 'required',
            'case_sensitive' => 'required_if:tipe_soal,6'
        ]);

        $banksoal = DB::table('banksoals')
            ->where('id', $request->banksoal_id)
            ->select(['id','is_locked'])
            ->first();
        if (!$banksoal) {
            return SendResponse::badRequest('Banksoal tidak ditemukan');
        }

        if ($banksoal->is_locked) {
            return SendResponse::badRequest('Banksoal sedang dikunci');
        }

        DB::beginTransaction();

        try {
            $soal->pertanyaan = $request->pertanyaan;
            $soal->audio = $request->audio;
            $soal->direction = $request->direction;
            $soal->tipe_soal = $request->tipe_soal;
            $soal->rujukan = $request->rujukan;
            $soal->layout = $request->layout;

            if (isset($request->case_sensitive)) {
                $soal->case_sensitive = $request->case_sensitive;
            }

            $soal->save();

            if(in_array($request->tipe_soal, [1,3,4,5,6,7])) {
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
                        'id'            => Str::uuid()->toString(),
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
     * @Route(path="api/v1/soals/{soal}", methods={"DELETE"})
     *
     * Remove the specified resource from storage.
     *
     * @param  App\Soal $soal
     * @return Illuminate\Http\Response
     */
    public function destroy(Soal $soal)
    {
        $banksoal = DB::table('banksoals')
            ->where('id', $soal->banksoal_id)
            ->select(['id','is_locked'])
            ->first();

        if ($banksoal->is_locked) {
            return SendResponse::badRequest('Banksoal sedang dikunci');
        }
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
     * @Route(path="api/v1/soals/delete/multiple", methods={"GET"})
     *
     * Delete multiple question
     *
     * @return App\Actions\SendResponse
     * @author shellrean <wandinak17@gmail.com>
     */
    public function multipleDestroy()
    {
        DB::beginTransaction();
        try {
            $q = request()->q;
            $ids = explode(',', $q);

            DB::table('jawaban_soals')
                ->whereIn('soal_id', $ids)
                ->delete();
            DB::table('soals')
                ->whereIn('id', $ids)
                ->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return SendResponse::internalServerError(sprintf('kesalahan 500 (%s)', $e->getMessage()));
        }
        return SendResponse::accept();
    }

    /**
     * @Route(path="api/v1/soals/banksoal/{banksoal}", methods={"GET"})
     *
     * Get soal by banksoal
     *
     * @param App\Banksoal $banksoal
     * @return App\Actions\SendResponse
     * @author shellrean <wandinak17@gmail.com>
     */
    public function getSoalByBanksoal(Banksoal $banksoal)
    {
        if ($banksoal->is_locked) {
            return SendResponse::badRequest('Banksoal sedang dikunci');
        }

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
     * @Route(path="api/v1/soals/banksoal/{banksoal}/all", methods={"GET"})
     *
     * Get soal by banksoal
     *
     * @param App\Banksoal $banksoal
     * @return App\Actions\SendResponse
     * @author shellrean <wandinak17@gmail.com>
     */
    public function getSoalByBanksoalAll(Banksoal $banksoal)
    {
        if ($banksoal->is_locked) {
            return SendResponse::badRequest('Banksoal sedang dikunci');
        }
        $soal = Soal::with('jawabans')->where('banksoal_id',$banksoal->id)->get();
        return SendResponse::acceptData($soal);
    }

    /**
     * @Route(path="api/v1/soals/banksoal/{banksoal}/analys", methods={"GET"})
     *
     * Get soal by banksoal analys
     *
     * @param App\Banksoal $banksoal
     * @return App\Actions\SendResponse
     * @author shellrean <wandinak17@gmail.com>
     */
    public function getSoalByBanksoalAnalys(Banksoal $banksoal)
    {
        $soal = Soal::where('banksoal_id',$banksoal->id)->get()
        ->makeVisible('diagram')
        ->makeVisible('analys');
        return SendResponse::acceptData($soal);
    }

    /**
     * @Route(path="api/v1/soals/banksoal/{banksoal}/upload", methods={"POST"})
     *
     * @param Illuminate\Http\Request $request
     * @param App\Banksoal $banksoal
     * @return App\Actions\SendResponse
     */
    public function import(Request $request, Banksoal $banksoal)
    {
        if ($banksoal->is_locked) {
            return SendResponse::badRequest('Banksoal sedang dikunci');
        }

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
     * @param Illuminate\http\Request $request
     * @param App\Services\WordService $wordService
     * @return App\Actions\SendResponse;
     * @author shellrean <wandinak17@gmail.com>
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
     * @param Illuminate\http\Request $request
     * @return App\Actions\SendResponse;
     * @author shellrean <wandinak17@gmail.com>
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
                $soal_id = Str::uuid()->toString();

                $soal = [
                    'id'            => $soal_id,
                    'banksoal_id'   => $banksoal->id,
					'tipe_soal'     => $question['type'],
					'pertanyaan'    => $question['pertanyaan'],
					'created_at'	=> now(),
					'updated_at'	=> now(),
                ];

                DB::table('soals')->insert($soal);

                foreach($question['options'] as $key => $opt) {
                    $isCorrect = in_array($key, $question['correct']) ? 1 : 0;
                    array_push($options, [
                        'id'            => Str::uuid()->toString(),
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
     * @Route(path="api/v1/soals/import-word/{banksoal}", methods={"POST"})
     *
     * Import soal from docx
     *
     * @param Illuminate\http\Request $request
     * @return App\Actions\SendResponse;
     * @author shellrean <wandinak17@gmail.com>
     */
    public function wordImport(Request $request, $banksoal_id)
    {
        $request->validate([
            'file' => 'required|mimes:docx',
            'format' => 'required'
        ]);

        $banksoal = DB::table('banksoals')
            ->where('id', $banksoal_id)
            ->select('id','directory_id', 'is_locked')
            ->first();

        if (!$banksoal) {
            return SendResponse::badRequest('kesalahan, banksoal tidak ditemukan');
        }

        if ($banksoal->is_locked) {
            return SendResponse::badRequest('Banksoal sedang dikunci');
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
