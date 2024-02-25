<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\SoalConstant;
use Illuminate\Support\Facades\DB;
use App\Actions\SendResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\JawabanSoal;
use App\Directory;
use App\Banksoal;
use App\Soal;
use Carbon\Carbon;

/**
 * BanksoalController
 *
 * @author shellrean <wandinak17@gmail.com>
 */
class BanksoalController extends Controller
{
    /**
     * @Route(path="api/v1/banksoals", methods={"GET"})
     *
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function index()
    {
        $user = request()->user();
        $perPage = request()->perPage ?: '';
        $banksoal = DB::table('banksoals as t_0')
            ->join('matpels as t_1', 't_0.matpel_id', 't_1.id')
            ->join('users as t_2', 't_0.author', 't_2.id')
            ->leftJoin('users as t_3', 't_0.lock_by', 't_3.id')
            ->select([
                't_0.id',
                't_0.is_locked',
                't_0.jumlah_benar_salah',
                't_0.jumlah_isian_singkat',
                't_0.jumlah_mengurutkan',
                't_0.jumlah_menjodohkan',
                't_0.jumlah_pilihan',
                't_0.jumlah_pilihan_listening',
                't_0.jumlah_setuju_tidak',
                't_0.jumlah_soal',
                't_0.jumlah_soal_esay',
                't_0.jumlah_soal_ganda_kompleks',
                't_0.jumlah_soal_listening',
                't_0.kode_banksoal',
                't_0.persen',
                't_1.nama as matpel_name',
                't_2.name as created_by',
                't_2.email as email_creator',
                't_3.email as email_locker'
            ])->orderByDesc('t_0.created_at');

        if (request()->q != '') {
            $banksoal = $banksoal->where('t_0.kode_banksoal', 'LIKE', '%'. request()->q.'%')->orWhere('t_1.nama', 'LIKE', '%'. request()->q.'%');
        }
        if ($user->role != 'admin') {
            $banksoal = $banksoal->where('author',$user->id);
        }
        if($perPage != '') {
            $ids = $banksoal->get('t_0.id')->pluck('id');
            $inputted = DB::table('soals')->whereIn('banksoal_id', $ids)->select([
                DB::raw('count(1) as total'),
                'banksoal_id',
                'tipe_soal'
            ])
            ->groupBy('banksoal_id')
            ->groupBy('tipe_soal')
            ->get();
            $inputted = $inputted->groupBy('banksoal_id');

            $banksoal = $banksoal->paginate($perPage);
            $banksoal->getCollection()->transform(function ($item) use ($inputted) {
                $input = $inputted->get($item->id, []);
                $pg_inputted = 0;
                $pg_komplek_inputted = 0;
                $listening_inputted = 0;
                $menjodohkan_inputted = 0;
                $isian_singkat_inputted = 0;
                $esay_inputted = 0;
                $urutan_inputted = 0;
                $benar_salah_inputted = 0;
                $setuju_tidak_inputted = 0;

                $total = 0;
                foreach($input as $var2) {
                    $total += $var2->total;
                    if($var2->tipe_soal == SoalConstant::TIPE_PG) {
                        $pg_inputted += $var2->total;

                    } else if($var2->tipe_soal == SoalConstant::TIPE_ESAY) {
                        $esay_inputted += $var2->total;

                    } else if($var2->tipe_soal == SoalConstant::TIPE_LISTENING) {
                        $listening_inputted += $var2->total;

                    } else if($var2->tipe_soal == SoalConstant::TIPE_PG_KOMPLEK) {
                        $pg_komplek_inputted += $var2->total;

                    } else if($var2->tipe_soal == SoalConstant::TIPE_MENJODOHKAN) {
                        $menjodohkan_inputted += $var2->total;

                    } else if($var2->tipe_soal == SoalConstant::TIPE_ISIAN_SINGKAT) {
                        $isian_singkat_inputted += $var2->total;

                    } else if($var2->tipe_soal == SoalConstant::TIPE_MENGURUTKAN) {
                        $urutan_inputted += $var2->total;

                    } else if($var2->tipe_soal == SoalConstant::TIPE_BENAR_SALAH) {
                        $benar_salah_inputted += $var2->total;

                    } else if($var2->tipe_soal == SoalConstant::TIPE_SETUJU_TIDAK) {
                        $setuju_tidak_inputted += $var2->total;

                    }
                }

                $item->persen = json_decode($item->persen);

                $item->inputed = $total;
                $item->pg_inputted = $pg_inputted;
                $item->pg_komplek_inputted = $pg_komplek_inputted;
                $item->listening_inputted = $listening_inputted;
                $item->menjodohkan_inputted = $menjodohkan_inputted;
                $item->isian_singkat_inputted = $isian_singkat_inputted;
                $item->esay_inputted = $esay_inputted;
                $item->urutan_inputted = $urutan_inputted;
                $item->benar_salah_inputted = $benar_salah_inputted;
                $item->setuju_tidak_inputted = $setuju_tidak_inputted;

                return $item;
            });
        } else {
            $banksoal = $banksoal->get();
        }
        return SendResponse::acceptData($banksoal);
    }

    /**
     * @Route(path="api/v1/banksoals", methods={"POST"})
     *
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function store(Request $request)
    {
        $user = request()->user();
        $request->validate([
            'kode_banksoal'     => 'required|unique:banksoals,kode_banksoal',
            'matpel_id'         => 'required|exists:matpels,id',
            'jumlah_soal'       => 'required|int',
            'jumlah_pilihan'    => 'required|int',
            'jumlah_soal_listening' => 'required|int',
            'jumlah_pilihan_listening' => 'required|int',
            'jumlah_soal_ganda_kompleks' => 'required|int',
            'jumlah_isian_singkat' => 'required|int',
            'jumlah_menjodohkan' => 'required|int',
            'jumlah_mengurutkan' => 'required|int',
            'jumlah_benar_salah' => 'required|int',
            'jumlah_setuju_tidak' => 'required|int',
            'persen'            => 'required|array'
        ]);

        $point = 0;
        foreach(array_values($request->persen) as $item) {
            $point += intval($item);
        }
        if ($point != 100) {
            return SendResponse::badRequest('Persentase harus 100 %');
        }

        DB::beginTransaction();

        try {
            $direk = Directory::create([
                'name'      => $request->kode_banksoal,
                'slug'      => Str::slug($request->kode_banksoal, '-')
            ]);

            $data = [
                'kode_banksoal'     => $request->kode_banksoal,
                'matpel_id'         => $request->matpel_id,
                'author'            => $user->id,
                'jumlah_soal'       => $request->jumlah_soal,
                'jumlah_pilihan'    => $request->jumlah_pilihan,
                'jumlah_soal_esay'  => $request->jumlah_soal_esay,
                'jumlah_soal_listening' => $request->jumlah_soal_listening,
                'jumlah_pilihan_listening' => $request->jumlah_pilihan_listening,
                'jumlah_soal_ganda_kompleks' => $request->jumlah_soal_ganda_kompleks,
                'jumlah_isian_singkat' => $request->jumlah_isian_singkat,
                'jumlah_menjodohkan' => $request->jumlah_menjodohkan,
                'jumlah_mengurutkan' => $request->jumlah_mengurutkan,
                'jumlah_benar_salah' => $request->jumlah_benar_salah,
                'jumlah_setuju_tidak' => $request->jumlah_setuju_tidak,
                'persen'            => $request->persen,
                'directory_id'      => $direk->id
            ];

            $res = Banksoal::create($data);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return SendResponse::badRequest($e->getMessage());
        }
        return SendResponse::acceptData($res);
    }

    /**
     * @Route(path="api/v1/banksoal/{id}", methods={"GET"})
     *
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function show(Banksoal $banksoal)
    {
        $banksoal = Banksoal::with('matpel')->where('id', $banksoal->id)->first();
        return SendResponse::acceptData($banksoal);
    }

    /**
     * @Route(path="api/v1/banksoals/{id}", methods={"PUT","PATCH"})
     *
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Banksoal  $banksoal
     * @return \Illuminate\Http\Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function update(Request $request, Banksoal $banksoal)
    {
        $request->validate([
            'kode_banksoal'     => 'required|unique:banksoals,kode_banksoal,'.$banksoal->id,
            'jumlah_soal'       => 'required|int',
            'jumlah_pilihan'    => 'required|int',
            'jumlah_soal_listening' => 'required|int',
            'jumlah_pilihan_listening' => 'required|int',
            'jumlah_soal_ganda_kompleks' => 'required|int',
            'jumlah_isian_singkat' => 'required|int',
            'jumlah_menjodohkan' => 'required|int',
            'jumlah_mengurutkan' => 'required|int',
            'jumlah_benar_salah' => 'required|int',
            'persen'            => 'required|array'
        ]);

        if ($banksoal->is_locked) {
            return SendResponse::badRequest('Banksoal sedang dikunci');
        }

        $point = 0;
        foreach(array_values($request->persen) as $item) {
            $point += intval($item);
        }
        if ($point != 100) {
            return SendResponse::badRequest('Persentase harus 100 %');
        }

        $banksoal->kode_banksoal = $request->kode_banksoal;
        if(gettype($request->matpel_id) == 'array') {
            $banksoal->matpel_id = $request->matpel_id['id'];
        }

        $banksoal->jumlah_soal = $request->jumlah_soal;
        $banksoal->jumlah_pilihan = $request->jumlah_pilihan;
        $banksoal->jumlah_soal_esay = $request->jumlah_soal_esay;
        $banksoal->jumlah_soal_listening = $request->jumlah_soal_listening;
        $banksoal->jumlah_pilihan_listening = $request->jumlah_pilihan_listening;
        $banksoal->jumlah_soal_ganda_kompleks = $request->jumlah_soal_ganda_kompleks;
        $banksoal->jumlah_isian_singkat = $request->jumlah_isian_singkat;
        $banksoal->jumlah_menjodohkan = $request->jumlah_menjodohkan;
        $banksoal->jumlah_mengurutkan = $request->jumlah_mengurutkan;
        $banksoal->jumlah_benar_salah = $request->jumlah_benar_salah;
        $banksoal->jumlah_setuju_tidak = $request->jumlah_setuju_tidak;
        $banksoal->persen = $request->persen;
        $banksoal->save();

        return SendResponse::acceptData($banksoal);
    }

    /**
     * @Route(path="api/v1/banksoals/{id}", methods={"DELETE"})
     *
     * Remove the specified resource from storage.
     *
     * @param  \App\Banksoal  $banksoal
     * @return \Illuminate\Http\Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function destroy(Banksoal $banksoal)
    {
        if ($banksoal->is_locked) {
            return SendResponse::badRequest('Banksoal sedang dikunci');
        }

        DB::beginTransaction();

        try {
            $banksoal->delete();
            Directory::find($banksoal->directory_id)->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return SendResponse::badRequest($e->getMessage());
        }
        return SendResponse::accept();
    }

    /**
     * @Route(path="api/v1/banksoals/all", methods={"GET"})
     *
     * Display all data.
     *
     * @return \Illuminate\Http\Response
     * @author shellrean <wandinak17@gmail.com>
     * @deprecated
     */
    public function allData()
    {
        $user = request()->user();
        $banksoal = Banksoal::with(['matpel'])->orderBy('created_at', 'DESC');
        if ($user->role != 'admin') {
            $banksoal = $banksoal->where('author',$user->id);
        }
        $banksoal = $banksoal->get();
        return SendResponse::acceptData($banksoal);
    }

    /**
     * @Route(path="api/v1/banksoals/{id}/analys", methods={"GET"})
     *
     * Get analys banksoal
     *
     * @param  Banksoal $banksoal
     * @return \Illuminate\Http\Response
     */
    public function getAnalys(Banksoal $banksoal)
    {
        $soal = DB::table('soals')
            ->where('soals.banksoal_id', $banksoal->id)
            ->orderBy('soals.tipe_soal')
            ->orderBy('soals.created_at')
            ->select([
                'soals.id',
                'soals.pertanyaan',
                'soals.tipe_soal'
            ])
            ->get();

        $soal_ids = $soal->pluck('id')->toArray();
        $jawaban_pesertas = DB::table('jawaban_pesertas')
            ->whereIn('soal_id', $soal_ids)
            ->get();
        $jawaban_pesertas = $jawaban_pesertas->map(function ($item) {
            $item->setuju_tidak = json_decode($item->setuju_tidak, true);
            $item->benar_salah = json_decode($item->benar_salah, true);
            return $item;
        });

        $jawaban_soals = DB::table('jawaban_soals')
            ->whereIn('soal_id', $soal_ids)
            ->get();

        $fill = [];
        foreach ($soal as $val) {
            $jawaban_peserta = $jawaban_pesertas->where('soal_id', $val->id)->values();

            $penjawab = $jawaban_peserta->count();
            $salah = $jawaban_peserta->where('iscorrect', '0')->count();
            $benar = $jawaban_peserta->where('iscorrect', '1')->count();

            $jawaban_soal = $jawaban_soals
                ->where('soal_id', $val->id)
                ->values();

            $fill[] = [
                'soal'      => $val->pertanyaan,
                'tipe_soal' => $val->tipe_soal,
                'penjawab'  => $penjawab,
                'salah'     => $salah,
                'benar'     => $benar,
                'jawaban'   => $jawaban_soal->map(function ($item) use($jawaban_peserta) {
                    $argument = [
                        'setuju' => 0,
                        'tidak' => 0
                    ];
                    $benar_salah = [
                        'benar' => 0,
                        'salah' => 0
                    ];
                    foreach ($jawaban_peserta as $v) {
                        if ($v->setuju_tidak != null && count($v->setuju_tidak) > 0) {
                            if (isset($v->setuju_tidak[$item->id]['val']) && $v->setuju_tidak[$item->id]['val'] == 1) {
                                $argument['setuju'] += 1;
                            } else {
                                $argument['tidak'] += 1;
                            }
                        }
                        if ($v->benar_salah != null && count($v->benar_salah) > 0) {
                            if (isset($v->benar_salah[$item->id]) && $v->benar_salah[$item->id] == 1) {
                                $benar_salah['benar'] += 1;
                            } else {
                                $benar_salah['salah'] += 1;
                            }
                        }
                    }

                    $curr_jawaban_peserta = $jawaban_peserta->where('jawab',$item->id)->values();

                    return [
                        'text'      => $item->text_jawaban,
                        'iscorrect' => $item->correct,
                        'penjawab'  => $curr_jawaban_peserta->count(),
                        'argument'  => $argument,
                        'benar_salah' => $benar_salah
                    ];
                })
            ];
        }
        return SendResponse::acceptData($fill);
    }

    /**
     * @Route(path="api/v1/banksoals/{id}/duplikat, methods={"GET"})
     *
     * Duplikat banksoal
     *
     * @param Banksoal $banksoal
     * @return \Illuminate\Http\Response
     * @since 2.0.0
     */
    public function duplikat(Banksoal $banksoal)
    {
        if ($banksoal->is_locked) {
            return SendResponse::badRequest('Banksoal sedang dikunci');
        }

        DB::beginTransaction();
        try {
            $soals = Soal::with(['jawabans'])
                ->orderBy('created_at','ASC')
                ->where('banksoal_id', $banksoal->id)
                ->get();
            $direk = Directory::create([
                'name'      => $banksoal->kode_banksoal.' (Copy)',
                'slug'      => Str::slug($banksoal->kode_banksoal.' (Copy)', '-')
            ]);
            $data = [
                'kode_banksoal'     => $banksoal->kode_banksoal.' (Copy)',
                'matpel_id'         => $banksoal->matpel_id,
                'author'            => $banksoal->author,
                'jumlah_soal'       => $banksoal->jumlah_soal,
                'jumlah_pilihan'    => $banksoal->jumlah_pilihan,
                'jumlah_soal_esay'  => $banksoal->jumlah_soal_esay,
                'jumlah_soal_listening' => $banksoal->jumlah_soal_listening,
                'jumlah_pilihan_listening' => $banksoal->jumlah_pilihan_listening,
                'jumlah_soal_ganda_kompleks' => $banksoal->jumlah_soal_ganda_kompleks,
                'jumlah_isian_singkat' => $banksoal->jumlah_isian_singkat,
                'jumlah_menjodohkan' => $banksoal->jumlah_menjodohkan,
                'jumlah_mengurutkan' => $banksoal->jumlah_mengurutkan,
                'jumlah_benar_salah' => $banksoal->jumlah_benar_salah,
                'jumlah_setuju_tidak' => $banksoal->jumlah_setuju_tidak,
                'persen'            => $banksoal->persen,
                'directory_id'      => $direk->id
            ];
            $newBanksoal = Banksoal::create($data);

            foreach($soals as $soal){
                $newSoal = Soal::create([
                    'banksoal_id'   => $newBanksoal->id,
                    'pertanyaan'    => $soal->pertanyaan,
                    'tipe_soal'     => $soal->tipe_soal,
                    'rujukan'       => $soal->rujukan,
                    'audio'         => $soal->audio,
                    'direction'     => $soal->direction
                ]);
                if($newSoal->tipe_soal != SoalConstant::TIPE_ESAY) {
                    foreach($soal->jawabans as $key=>$pilihan) {
                        JawabanSoal::create([
                            'soal_id'       => $newSoal->id,
                            'text_jawaban'  => $pilihan->text_jawaban,
                            'correct'       => $pilihan->correct
                        ]);
                    }
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return SendResponse::internalServerError($e->getMessage());
        }

        return SendResponse::accept('Banksoal berhasil digandakan');
    }

    /**
     * @Route(path="api/v1/banksols/{id}/lock", methods={"POST"})
     *
     * Lock banksoal
     *
     * @param Banksoal $banksoal
     * @return \Illuminate\Http\Response
     * @since 3.0.0
     */
    public function lock(Banksoal $banksoal, Request  $request) {
        $user = request()->user();
        $request->validate([
            'key_lock' => 'required'
        ]);

        if ($banksoal->is_locked) {
            return SendResponse::badRequest('Banksoal dikunci');
        }

        $banksoal->is_locked = 1;
        $banksoal->key_lock = bcrypt($request->key_lock);
        $banksoal->lock_by = $user->id;
        $banksoal->save();

        return SendResponse::accept('Banksoal berhasil dikunci');
    }

    /**
     * @Route(path="api/v1/banksols/{id}/unlock", methods={"POST"})
     *
     * Lock banksoal
     *
     * @param Banksoal $banksoal
     * @return \Illuminate\Http\Response
     * @since 3.0.0
     */
    public function unlock(Banksoal $banksoal, Request  $request) {
        $request->validate([
            'key_lock' => 'required'
        ]);

        if ($banksoal->is_locked == 0) {
            return SendResponse::accept('Bansoal tidak dikunci');
        }

        if (Hash::check($request->key_lock, $banksoal->key_lock)) {
            $banksoal->is_locked = 0;
            $banksoal->key_lock = null;
            $banksoal->lock_by = null;
            $banksoal->save();

            return SendResponse::accept('Banksoal berhasil dibuka');
        }

        return SendResponse::badRequest('Kata sandi banksoal salah');
    }
}
