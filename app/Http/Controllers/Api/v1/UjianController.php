<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CapaianExport;
use App\Actions\SendResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\JawabanPeserta;
use App\JawabanEsay;
use App\HasilUjian;
use App\Banksoal;
use App\Jadwal;
use App\Soal;

class UjianController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $ujian = Jadwal::with('event')->orderBy('created_at', 'DESC');
        if (request()->q != '') {
            $ujian = $ujian->where('alias', 'LIKE', '%'. request()->q.'%');
        }
        if (request()->perPage != '') {
            $ujian = $ujian->paginate(request()->perPage);
        } else {
            $ujian = $ujian->paginate(20);
        }
        $ujian->makeHidden('banksoal_id'); 
        return SendResponse::acceptData($ujian);
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
            'tanggal'           => 'required',
            'mulai'             => 'required',
            'lama'              => 'required|int',
        ]);

        $data = [
            'mulai'             => date('H:i:s', strtotime($request->mulai)),
            'lama'              => $request->lama*60,
            'tanggal'           => date('Y-m-d',strtotime($request->tanggal)),
            'status_ujian'      => 0,
            'alias'             => $request->alias,
            'event_id'          => $request->event_id
        ];

        if($request->banksoal_id != '') {
            $fill = array();
            foreach($request->banksoal_id as $banksol) {
                $fush = [
                    'id' => $banksol['id'],
                    'jurusan' => $banksol['matpel']['jurusan_id']
                ];
                array_push($fill, $fush);
            }   

            $data['banksoal_id'] = $fill;
        }

        if($request->server_id != '') { 
            $fill = array();
            foreach($request->server_id as $server) {
                array_push($fill, $server['server_name']);
            }   

            $data['server_id'] = $fill;
        }

        Jadwal::create($data);

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
    public function destroy(Jadwal $ujian)
    {
        $ujian->delete();
        return SendResponse::accept();
    }

    /**
     * Set status ujian.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function setStatus(Request $request)
    {
        $jadwal = Jadwal::find($request->id);
        if($jadwal) {
            $jadwal->status_ujian = $request->status;
            $jadwal->save();

            return SendResponse::accept();
        }
        return SendResponse::notFound();
    }

    /**
     * Get all ujian without pagination
     *
     * @return \Illuminate\http\Response
     */
    public function allData()
    {
        $ujians = Jadwal::orderBy('id','desc')->get();
        return SendResponse::acceptData($ujians);
    }

    /**
     * [getActive description]
     * @return [type] [description]
     */
    public function getActive()
    {
        $ujian = Jadwal::with('event')->where('status_ujian',1)->get();
        return SendResponse::acceptData($ujian);
    }

    /**
     * [getExistEsay description]
     * @return [type] [description]
     */
    public function getExistEsay()
    {
        $has = JawabanEsay::all()->pluck('jawab_id')->unique();
        $user = request()->user('api');

        $exists = JawabanPeserta::whereNotNull('esay')
        ->whereNotIn('id', $has)
        ->get()
        ->pluck('banksoal_id')
        ->unique();

        $banksoal = Banksoal::with('matpel')->whereIn('id', $exists)->get()
        ->makeHidden('jumlah_soal')
        ->makeHidden('jumlah_pilihan')
        ->makeHidden('matpel_id')
        ->makeHidden('directory_id')
        ->makeHidden('inputed')
        ->makeVisible('koreksi');

        $filtered = $banksoal->reject(function ($value, $key) use($user) {
            return !in_array($user->id, $value->matpel->correctors);
        });

        return SendResponse::acceptData($filtered->values()->all());
    }

    /**
     * [getExistEsayByBanksoal description]
     * @param  Banksoal $banksoal [description]
     * @return [type]             [description]
     */
    public function getExistEsayByBanksoal(Banksoal $banksoal)
    {
        $has = JawabanEsay::where('banksoal_id', $banksoal->id)
        ->get()->pluck('jawab_id');
        
        $exists = JawabanPeserta::whereNotIn('id', $has)
        ->with(['pertanyaan' => function($q) {
            $q->select(['id','rujukan','pertanyaan']);
        }])
        ->whereNotNull('esay')
        ->where('banksoal_id', $banksoal->id)
        ->paginate(30);

        return SendResponse::acceptData($exists);
    }

    /**
     * [storeNilaiEsay description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function storeNilaiEsay(Request $request)
    {
        $request->validate([
            'val'   => 'required|numeric|min:0|max:1',
            'id'        => 'required|exists:jawaban_pesertas,id'
        ]);

        $jawab = JawabanPeserta::find($request->id);

        $user = request()->user('api'); 

        $has = JawabanEsay::where('banksoal_id', $jawab->banksoal_id)
        ->get()->pluck('jawab_id');
        
        $sames = JawabanPeserta::whereNotIn('id',$has)
        ->where([
            'esay' => $jawab->esay, 
            'banksoal_id' => $jawab->banksoal_id, 
            'soal_id' => $jawab->soal_id
        ])
        ->get();

        if($sames) {
            foreach($sames as $same) {
                $hasil = HasilUjian::where([
                    'banksoal_id'   => $same->banksoal_id,
                    'jadwal_id'     => $same->jadwal_id,
                    'peserta_id'    => $same->peserta_id,
                ])->first();

                $jmlh = $same->banksoal->jumlah_soal;
                $jml_esay =  $same->banksoal->jumlah_soal_esay;

                if($hasil->jumlah_benar == 0) {
                    $hasil_ganda = 0;
                } else {
                    $hasil_ganda = ($hasil->jumlah_benar/$jmlh);
                }

                if($request->val != 0) {
                    $hasil_esay = $hasil->point_esay + ($request->val/$jml_esay);
                } else {
                    $hasil_esay = $hasil->point_esay;
                }
                
                if($jml_esay != 0) {
                    $hasil_val = ($hasil_ganda*70)+(($hasil_esay)*30);
                } else {
                    $hasil_val = $hasil_ganda*100;   
                }
                $hasil->point_esay = $hasil_esay;
                $hasil->hasil = $hasil_val;
                $hasil->save();

                JawabanEsay::create([
                    'banksoal_id'   => $same->banksoal_id,
                    'peserta_id'    => $same->peserta_id,
                    'jawab_id'      => $same->id,
                    'corrected_by'  => $user->id,
                    'point'         => $request->val
                ]);
            }

            return SendResponse::accept();
        }

        $hasil = HasilUjian::where([
            'banksoal_id'   => $jawab->banksoal_id,
            'jadwal_id'     => $jawab->jadwal_id,
            'peserta_id'    => $jawab->peserta_id
        ])->first();

        $jmlh = $jawab->banksoal->jumlah_soal;
        $jml_esay =  $jawab->banksoal->jumlah_soal_esay;

        if($hasil->jumlah_benar == 0) {
            $hasil_ganda = 0;
        } else {
            $hasil_ganda = ($hasil->jumlah_benar/$jmlh);
        }

        $hasil_esay = $hasil->point_esay + ($request->val/$jml_esay);
        if($jml_esay != 0) {
            $hasil_val = ($hasil_ganda*70)+(($hasil_esay)*30);
        } else {
            $hasil_val = $hasil_ganda*100;   
        }
        $hasil->point_esay = $hasil_esay;
        $hasil->hasil = $hasil_val;
        $hasil->save();

        JawabanEsay::create([
            'banksoal_id'   => $jawab->banksoal_id,
            'peserta_id'    => $jawab->peserta_id,
            'jawab_id'      => $jawab->id,
            'corrected_by'  => $user->id,
            'point'         => $request->val
        ]);

        return SendResponse::accept();
    }

    /**
     * [getResult description]
     * @param  Jadwal $jadwal [description]
     * @return [type]         [description]
     */
    public function getResult(Jadwal $jadwal)
    {
        $res = HasilUjian::with(['peserta' => function ($query) {
            $query->select('id','nama','no_ujian');
        }])
        ->where('jadwal_id', $jadwal->id)
        ->orderBy('peserta_id');

        if(request()->perPage != '') {
            $res = $res->paginate(request()->perPage);
        } else {
            $res = $res->get();
        }

        return SendResponse::acceptData($res);
    }

    /**
     * [getBanksoalByJadwal description]
     * @param  Jadwal $jadwal [description]
     * @return [type]         [description]
     */
    public function getBanksoalByJadwal(Jadwal $jadwal)
    {
        $res = HasilUjian::where('jadwal_id', $jadwal->id)
        ->get()->pluck('banksoal_id');

        $bankSoal = Banksoal::find($res);
        return SendResponse::acceptData($bankSoal); 
    }

    /**
     * [getCapaianSiswa description]
     * @param  Jadwal   $jadwal   [description]
     * @param  Banksoal $banksoal [description]
     * @return [type]             [description]
     */
    public function getCapaianSiswa(Jadwal $jadwal, Banksoal $banksoal)
    {
        $soal = Soal::where(function($query) use($banksoal) {
            $query->where('banksoal_id', $banksoal->id)
            ->where('tipe_soal','!=','2');
        })->count();

        $sss = JawabanPeserta::with(['peserta' => function($query) {
            $query->select('id','nama','no_ujian');
        }])
        ->whereHas('pertanyaan', function($query) {
            $query->where('tipe_soal','!=','2');
        })
        ->where([
            'banksoal_id' => $banksoal->id,
            'jadwal_id' => $jadwal->id
        ])
        ->orderBy('soal_id')
        ->select('id','iscorrect','peserta_id')
        ->get();

        $grouped = $sss->groupBy('peserta_id');

        $fill = $grouped->map(function($value, $key) {
            return [
                'peserta' => [ 
                    'no_ujian' => $value[0]->peserta->no_ujian,
                    'nama' => $value[0]->peserta->nama 
                ],
                'data' => $value
            ];
        });
        $data = [
            'pesertas' => $fill,
            'soal' => $soal
        ];

        return SendResponse::acceptData($data);
    }

    public function getCapaianSiswaExcel(Jadwal $jadwal, Banksoal $banksoal)
    {
        $soal = Soal::where(function($query) use($banksoal) {
            $query->where('banksoal_id', $banksoal->id)
            ->where('tipe_soal','!=','2');
        })->count();

        $sss = JawabanPeserta::with(['peserta' => function($query) {
            $query->select('id','nama','no_ujian');
        }])
        ->whereHas('pertanyaan', function($query) {
            $query->where('tipe_soal','!=','2');
        })
        ->where([
            'banksoal_id' => $banksoal->id,
            'jadwal_id' => $jadwal->id
        ])
        ->orderBy('soal_id')
        ->select('id','iscorrect','peserta_id')
        ->get();

        $grouped = $sss->groupBy('peserta_id');

        $fill = $grouped->map(function($value, $key) {
            return [
                'peserta' => [ 
                    'no_ujian' => $value[0]->peserta->no_ujian,
                    'nama' => $value[0]->peserta->nama 
                ],
                'data' => $value
            ];
        });
        $data = [
            'pesertas' => $fill,
            'soal' => $soal
        ];

        $export = new CapaianExport($data);

        return Excel::download($export, 'capaian_siswa_'.$banksoal->kode_banksoal.'.xlsx');
    }
}
