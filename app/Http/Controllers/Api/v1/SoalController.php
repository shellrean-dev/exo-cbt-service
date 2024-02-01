<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\SoalConstant;
use App\Services\ExoProcessHtml;
use Illuminate\Http\Response;
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
     * @param Request $request
     * @return Response
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

            if (in_array($request->tipe_soal, [SoalConstant::TIPE_BENAR_SALAH, SoalConstant::TIPE_SETUJU_TIDAK])) {
                $data['layout'] = SoalConstant::LAYOUT_KEBAWAH_TABEL;
            }

            if (in_array($request->tipe_soal, [SoalConstant::TIPE_MENJODOHKAN, SoalConstant::TIPE_MENGURUTKAN])) {
                $data['layout'] = SoalConstant::LAYOUT_KEBAWAH_STANDARD;
            }

            if (isset($request->case_sensitive)) {
                $data['case_sensitive'] = $request->case_sensitive;
            }

            $soal = Soal::create($data);

            if(in_array(intval($request->tipe_soal), [
                SoalConstant::TIPE_PG,
                SoalConstant::TIPE_LISTENING,
                SoalConstant::TIPE_PG_KOMPLEK,
                SoalConstant::TIPE_MENJODOHKAN,
                SoalConstant::TIPE_ISIAN_SINGKAT,
                SoalConstant::TIPE_MENGURUTKAN,
                SoalConstant::TIPE_BENAR_SALAH,
                SoalConstant::TIPE_SETUJU_TIDAK
            ])) {
                $data = [];
                $time_offset = 0;
                $labelMark = "A";
                foreach($request->pilihan as $key => $pilihan) {
                    if(in_array($request->tipe_soal, [SoalConstant::TIPE_PG,SoalConstant::TIPE_LISTENING])) {
                        $correct = $request->correct == $key ? '1' : '0';
                    }
                    else if(in_array($request->tipe_soal, [SoalConstant::TIPE_PG_KOMPLEK,SoalConstant::TIPE_BENAR_SALAH])) {
                        $correct = in_array($key, $request->selected) ? '1' : '0';
                    }
                    else {
                        $correct = '0';
                    }

                    if ($request->tipe_soal == SoalConstant::TIPE_MENJODOHKAN) {
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

                    $data[] = [
                        'id' => Str::uuid()->toString(),
                        'soal_id' => $soal->id,
                        'text_jawaban' => $pilihan,
                        'correct' => $correct,
                        'label_mark' => $labelMark++,
                        'created_at' => now()->addSeconds($time_offset)
                    ];

                    $time_offset++;
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
     * @route(path="api/v1/soals/bulk", methods={"POST"})
     *
     * Store new resource multi bulk
     */
    public function storeBulk(Request $request)
    {
        $soals = $request->soals;
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
            $pertanyaans = [];
            $options = [];

            foreach ($soals as $key => $soal) {
                $soal_id = Str::uuid()->toString();
                $pertanyaans[$key] = [
                    'id'            => $soal_id,
                    'banksoal_id'   => $banksoal->id,
                    'tipe_soal'     => SoalConstant::TIPE_PG,
                    'pertanyaan'    => $soal['pertanyaan'],
                    'created_at'    => now(),
                    'updated_at'    => now()
                ];

                $label_mark = "A";
                foreach ($soal['pilihan'] as $pilihan) {
                    $options[] = [
                        'id'            => Str::uuid()->toString(),
                        'soal_id'       => $soal_id,
                        'text_jawaban'  => $pilihan['text'],
                        'label_mark'    => $label_mark++,
                        'correct'       => $pilihan['is_correct'] ? 1 : 0,
                        'created_at'    => now(),
                        'updated_at'    => now()
                    ];
                }
            }

            DB::table('soals')->insert($pertanyaans);
            DB::table('jawaban_soals')->insert($options);

            DB::commit();
            return SendResponse::accept();
        } catch (\Exception $e) {
            DB::rollback();
            return SendResponse::badRequest($e->getMessage());
        }
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

                        $label_mark = "A";
                        foreach($value['pilihan'] as $key=>$pilihan) {
                            JawabanSoal::create([
                                'soal_id'       => $soal->id,
                                'label_mark'    => $label_mark++,
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
     * @param Soal $soal
     * @return Response
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
     * @param Request $request
     * @param Soal $soal
     * @return Response
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

            $soal->layout = $request->layout;

            if (in_array($soal->tipe_soal, [SoalConstant::TIPE_BENAR_SALAH, SoalConstant::TIPE_SETUJU_TIDAK])) {
                $soal->layout = SoalConstant::LAYOUT_KEBAWAH_TABEL;
            }

            if (in_array($request->tipe_soal, [SoalConstant::TIPE_MENJODOHKAN, SoalConstant::TIPE_MENGURUTKAN])) {
                $soal->layout = SoalConstant::LAYOUT_KEBAWAH_STANDARD;
            }

            $soal->pertanyaan = $request->pertanyaan;
            $soal->audio = $request->audio;
            $soal->direction = $request->direction;
            $soal->tipe_soal = $request->tipe_soal;
            $soal->rujukan = $request->rujukan;

            if (isset($request->case_sensitive)) {
                $soal->case_sensitive = $request->case_sensitive;
            }

            $soal->save();

            if(in_array(intval($request->tipe_soal), [
                SoalConstant::TIPE_PG,
                SoalConstant::TIPE_LISTENING,
                SoalConstant::TIPE_PG_KOMPLEK,
                SoalConstant::TIPE_MENJODOHKAN,
                SoalConstant::TIPE_ISIAN_SINGKAT,
                SoalConstant::TIPE_MENGURUTKAN,
                SoalConstant::TIPE_BENAR_SALAH,
                SoalConstant::TIPE_SETUJU_TIDAK
            ])) {
                DB::table('jawaban_soals')->where('soal_id',$request->soal_id)->delete();
                $data = [];
                $time_offset = 0;
                $label_mark = "A";
                foreach($request->pilihan as $key=>$pilihan) {
                    if(in_array($request->tipe_soal, [SoalConstant::TIPE_PG,SoalConstant::TIPE_LISTENING])) {
                        $correct = $request->correct == $key ? '1' : '0';
                    }
                    else if(in_array($request->tipe_soal, [SoalConstant::TIPE_PG_KOMPLEK,SoalConstant::TIPE_BENAR_SALAH])) {
                        $correct = in_array($key, $request->selected) ? '1' : '0';
                    }
                    else {
                        $correct = '0';
                    }

                    if ($request->tipe_soal == SoalConstant::TIPE_MENJODOHKAN) {
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

                    $data[] = [
                        'id' => Str::uuid()->toString(),
                        'soal_id' => $soal->id,
                        'text_jawaban' => $pilihan,
                        'correct' => $correct,
                        'label_mark' => $label_mark++,
                        'created_at' => now()->addSeconds($time_offset)
                    ];

                    $time_offset++;
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
     * @param Soal $soal
     * @return Response
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
     * @return Response
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
     * @param \App\Banksoal $banksoal
     * @return Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function getSoalByBanksoal(Banksoal $banksoal)
    {
        if ($banksoal->is_locked) {
            return SendResponse::badRequest('Banksoal sedang dikunci');
        }

        $soal = Soal::with('jawabans')->where('banksoal_id',$banksoal->id)->orderBy('created_at');
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
     * @param Banksoal $banksoal
     * @return Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function getSoalByBanksoalAll(Banksoal $banksoal)
    {
        if ($banksoal->is_locked) {
            return SendResponse::badRequest('Banksoal sedang dikunci');
        }
        $soal = Soal::with('jawabans')->where('banksoal_id',$banksoal->id)->orderBy('created_at')->get();

        $soal = $soal->map(function($item) {
            if($item->tipe_soal == SoalConstant::TIPE_MENJODOHKAN) {

                $jawabans = [];
                foreach($item->jawabans as $key => $jwb) {
                    $jawabans[$key] = $jwb;
                    $decoded2 = json_decode($jwb->text_jawaban, true);

                    $tableled = '<table style="border: 1px solid black !important;">
                        <tr><td style="border: 1px solid black !important;">'.$decoded2['a']['text'].'</td>
                        <td style="border: 1px solid black !important">'.$decoded2['b']['text'].'</td></tr></table>';
                    $jawabans[$key]->text_jawaban = $tableled;
                }
            }
            return $item;
        })->values();

        return SendResponse::acceptData($soal);
    }

    /**
     * @Route(path="api/v1/soals/banksoal/{banksoal}/analys", methods={"GET"})
     *
     * Get soal by banksoal analys
     *
     * @param \App\Banksoal $banksoal
     * @return Response
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
     * @param \App\Banksoal $banksoal
     * @return Response
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
     * @param \App\Services\WordService $wordService
     * @return Response;
     * @author shellrean <wandinak17@gmail.com>
     */
    private function _formatStandart($request, $banksoal)
    {
        $wordService = new WordService();
        $soalService = new SoalService();

        $dir = Directory::find($banksoal->directory_id);

        $file = $request->file('file');
        $nama_file = time().$file->getClientOriginalName();
        $path = $file->storeAs(sprintf('exec171200/%s', $dir->slug), $nama_file);

        $file = public_path('storage/'.$path);
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
     * @return Response;
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
        $path = $file->storeAs(sprintf('exec171200/%s', $dir->slug), $nama_file);

        $file = public_path('storage/'.$path);

        DB::beginTransaction();
        try {
            $exoProc = new ExoProcessDoc('1',$file, $dir);
            $data = $exoProc->render();
            $questions = $data['data'];
            $files = $data['files'];

            $options = [];
            $time_offset = 0;
            foreach($questions as $key => $question) {
                $soal_id = Str::uuid()->toString();

                $soal = [
                    'id'            => $soal_id,
                    'banksoal_id'   => $banksoal->id,
					'tipe_soal'     => $question['type'],
					'pertanyaan'    => $question['pertanyaan'],
					'created_at'	=> now()->addSeconds($time_offset),
					'updated_at'	=> now(),
                ];

                if (in_array(intval($soal['tipe_soal']), [SoalConstant::TIPE_BENAR_SALAH, SoalConstant::TIPE_SETUJU_TIDAK])) {
                    $soal['layout'] = SoalConstant::LAYOUT_KEBAWAH_TABEL;

                } else if (in_array(intval($soal['tipe_soal']), [SoalConstant::TIPE_MENJODOHKAN, SoalConstant::TIPE_MENGURUTKAN])) {
                    $soal['layout'] = SoalConstant::LAYOUT_KEBAWAH_STANDARD;

                }

                DB::table('soals')->insert($soal);

                $time_offset_var2 = 0;
                $label_mark = "A";
                foreach($question['options'] as $key => $opt) {
                    if($soal['tipe_soal'] == SoalConstant::TIPE_BENAR_SALAH) {
                        $isCorrect = $question['correct_benar_salah'][$key];
                    } else {
                        $isCorrect = in_array($key, $question['correct']) ? 1 : 0;
                    }
                    $options[] = [
                        'id' => Str::uuid()->toString(),
                        'soal_id' => $soal_id,
                        'text_jawaban' => $opt,
                        'correct' => $isCorrect,
                        'label_mark' => $label_mark++,
                        'created_at' => now()->addSeconds($time_offset_var2),
                        'updated_at' => now(),
                    ];
                    $time_offset_var2++;
                }
                if ($soal['tipe_soal'] == SoalConstant::TIPE_MENJODOHKAN) {
                    foreach($question['options_menjodohkan'] as $option) {
                        $pair = [];
                        if(isset($option[0]) && isset($option[1])) {
                            $pair["a"] = [
                                "id"        => "a".uniqid(),
                                "text"      => $option[0]
                            ];
                            $pair["b"] = [
                                "id"        => "b".uniqid(),
                                "text"      => $option[1]
                            ];

                            $options[] = [
                                'id' => Str::uuid()->toString(),
                                'soal_id' => $soal_id,
                                'text_jawaban' => json_encode($pair),
                                'correct' => 0,
                                'label_mark' => '',
                                'created_at' => now()->addSeconds($time_offset_var2),
                                'updated_at' => now(),
                            ];

                            $time_offset_var2++;
                        }
                    }
                }
                if ($soal['tipe_soal'] == SoalConstant::TIPE_MENGURUTKAN) {
                    foreach($question['options_mengurutkan'] as $option) {
                        $options[] = [
                            'id' => Str::uuid()->toString(),
                            'soal_id' => $soal_id,
                            'text_jawaban' => $option,
                            'correct' => 0,
                            'label_mark' => '',
                            'created_at' => now()->addSeconds($time_offset_var2),
                            'updated_at' => now(),
                        ];
                        $time_offset_var2++;
                    }
                }

                $time_offset++;
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
     * @return Response;
     * @author shellrean <wandinak17@gmail.com>
     */
    public function wordImport(Request $request, $banksoal_id)
    {
        $request->validate([
            'file' => 'required|mimes:docx,zip',
            'fmt' => 'required'
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

        if($request->fmt == '1') {
            return $this->_formatStandart($request, $banksoal);
        }
        if ($request->fmt == '2') {
            return $this->_formatTabled($request, $banksoal);
        }
        if ($request->fmt == '3') {
            return $this->_formatHTMLTable($request, $banksoal);
        }

        return SendResponse::badRequest('format tidak sesuai');
    }

    private function _formatHTMLTable($request, $banksoal)
    {
        $dir = DB::table('directories')
            ->where('id', $banksoal->directory_id)
            ->first();
        if (!$dir) {
            return SendResponse::badRequest('kesalahan, directory tidak ditemukan');
        }

        $file = $request->file('file');
        $original_name = $file->getClientOriginalName();
        $nama_file = time().$file->getClientOriginalName();
        $path = $file->storeAs(sprintf('exec171200/%s', $dir->slug), $nama_file);

        $file = public_path('storage/'.$path);

        DB::beginTransaction();
        try {
            $exoProc = new ExoProcessHtml($file, $dir, $original_name);
            $data = $exoProc->render();

            $questions = $data['data'];
            $files = $data['files'];

            $options = [];
            $time_offset = 0;
            foreach($questions as $question) {
                $soal_id = Str::uuid()->toString();

                $soal = [
                    'id'            => $soal_id,
                    'banksoal_id'   => $banksoal->id,
                    'tipe_soal'     => $question['type'],
                    'pertanyaan'    => $question['pertanyaan'],
                    'created_at'	=> now()->addSeconds($time_offset),
                    'updated_at'	=> now(),
                ];

                if (in_array(intval($soal['tipe_soal']), [SoalConstant::TIPE_BENAR_SALAH, SoalConstant::TIPE_SETUJU_TIDAK])) {
                    $soal['layout'] = SoalConstant::LAYOUT_KEBAWAH_TABEL;

                } else if (in_array(intval($soal['tipe_soal']), [SoalConstant::TIPE_MENJODOHKAN, SoalConstant::TIPE_MENGURUTKAN])) {
                    $soal['layout'] = SoalConstant::LAYOUT_KEBAWAH_STANDARD;

                }

                DB::table('soals')->insert($soal);

                $time_offset_var2 = 0;
                $label_mark = "A";
                foreach($question['options'] as $key => $opt) {
                    if($soal['tipe_soal'] == SoalConstant::TIPE_BENAR_SALAH) {
                        $isCorrect = $question['correct_benar_salah'][$key];
                    } else {
                        $isCorrect = in_array($key, $question['correct']) ? 1 : 0;
                    }
                    $options[] = [
                        'id' => Str::uuid()->toString(),
                        'soal_id' => $soal_id,
                        'text_jawaban' => $opt,
                        'correct' => $isCorrect,
                        'label_mark' => $label_mark++,
                        'created_at' => now()->addSeconds($time_offset_var2),
                        'updated_at' => now(),
                    ];
                    $time_offset_var2++;
                }

                if ($soal['tipe_soal'] == SoalConstant::TIPE_MENJODOHKAN) {
                    foreach($question['options_menjodohkan'] as $option) {
                        $pair = [];
                        if(isset($option[0]) && isset($option[1])) {
                            $pair["a"] = [
                                "id"        => "a".uniqid(),
                                "text"      => $option[0]
                            ];
                            $pair["b"] = [
                                "id"        => "b".uniqid(),
                                "text"      => $option[1]
                            ];

                            $options[] = [
                                'id' => Str::uuid()->toString(),
                                'soal_id' => $soal_id,
                                'text_jawaban' => json_encode($pair),
                                'correct' => 0,
                                'label_mark' => null,
                                'created_at' => now()->addSeconds($time_offset_var2),
                                'updated_at' => now(),
                            ];

                            $time_offset_var2++;
                        }
                    }
                }
                if ($soal['tipe_soal'] == SoalConstant::TIPE_MENGURUTKAN) {
                    foreach($question['options_mengurutkan'] as $option) {
                        $options[] = [
                            'id' => Str::uuid()->toString(),
                            'soal_id' => $soal_id,
                            'text_jawaban' => $option,
                            'correct' => 0,
                            'label_mark' => null,
                            'created_at' => now()->addSeconds($time_offset_var2),
                            'updated_at' => now(),
                        ];
                        $time_offset_var2++;
                    }
                }

                $time_offset++;
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
}
