<?php

namespace App\Http\Controllers\Api\v1;

use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\URL;
use App\Exports\CapaianSiswaExport;
use App\Exports\HasilUjianExport;
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
            'alias'             => 'required',
            'banksoal_id'       => 'required|array',
            'setting'           => 'required|array'
        ]);

        $data = [
            'mulai'             => date('H:i:s', strtotime($request->mulai)),
            'lama'              => $request->lama*60,
            'tanggal'           => date('Y-m-d',strtotime($request->tanggal)),
            'status_ujian'      => 0,
            'alias'             => $request->alias,
            'event_id'          => $request->event_id == '' ? 0 : $request->event_id,
            'setting'           => $request->setting
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
    public function show(Jadwal $ujian)
    {
        return SendResponse::acceptData($ujian);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Jadwal $ujian)
    {
        $request->validate([
            'tanggal'       => 'required',
            'mulai'         => 'required',
            'lama'          => 'required',
            'alias'         => 'required',
            'banksoal_id'       => 'required|array',
            'setting'           => 'required|array'
        ]);

        $data = [
            'mulai'         => date('H:i:s', strtotime($request->mulai)),
            'lama'          => $request->lama*60,
            'tanggal'       => date('Y-m-d', strtotime($request->tanggal)),
            'alias'         => $request->alias,
            'event_id'      => $request->event_id == '' ? 0 : $request->event_id,
            'setting'           => $request->setting
        ];

        if($request->banksoal_id != '') {
            $fill = array();
            foreach ($request->banksoal_id as $banksoal) {
                $fush = [
                    'id'        => $banksoal['id'],
                    'jurusan'   => $banksoal['matpel']['jurusan_id']
                ];
                array_push($fill, $fush);
            }

            $data['banksoal_id'] = $fill;
        }

        $ujian->update($data);

        return SendResponse::accept();
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

        $exists = JawabanPeserta::where( function ($query) use ($has) {
            $query->whereNotIn('id', $has)
            ->whereHas('pertanyaan', function($q) {
                $q->where('tipe_soal','=', '2');
            })
            ->whereNotNull('esay');
        })
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
        ->get()
        ->pluck('jawab_id');

        $exists = JawabanPeserta::where( function ($query) use ($has, $banksoal) {
            $query->whereNotIn('id', $has)
            ->whereHas('pertanyaan', function($q) {
                $q->where('tipe_soal','=', '2');
            })
            ->whereNotNull('esay')
            ->where('banksoal_id', $banksoal->id);
        })
        ->with(['pertanyaan' => function($q) {
            $q->select(['id','rujukan','pertanyaan']);
        }])
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

        if($sames->count() > 1) {
            foreach($sames as $same) {
                $hasil = HasilUjian::where([
                    'banksoal_id'   => $same->banksoal_id,
                    'jadwal_id'     => $same->jadwal_id,
                    'peserta_id'    => $same->peserta_id,
                ])->first();

                // Check total question
                $pg_jmlh = $same->banksoal->jumlah_soal;
                $listening_jmlh = $same->banksoal->jumlah_soal_listening;
                $jml_esay =  $same->banksoal->jumlah_soal_esay;

                $hasil_listening = 0;
                if($hasil->jumlah_benar_listening > 0) {
                    $hasil_listening = ($hasil->jumlah_benar_listening/$listening_jmlh)*$same->banksoal->persen['listening'];
                }
                $hasil_pg = 0;
                if($hasil->jumlah_benar > 0) {
                    $hasil_pg = ($hasil->jumlah_benar/$pg_jmlh)*$same->banksoal->persen['pilihan_ganda'];
                }
                $hasil_ganda = $hasil_listening+$hasil_pg;

                if($request->val != 0) {
                    $hasil_esay = $hasil->point_esay + ($request->val/$jml_esay);
                } else {
                    $hasil_esay = $hasil->point_esay;
                }

                $hasil_val = ($hasil_ganda)+($hasil_esay*$same->banksoal->persen['esay']);

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

        // Check total question
        $pg_jmlh = $jawab->banksoal->jumlah_soal;
        $listening_jmlh = $jawab->banksoal->jumlah_soal_listening;
        $jml_esay =  $jawab->banksoal->jumlah_soal_esay;

        $hasil_listening = 0;
        if($hasil->jumlah_benar_listening > 0) {
            $hasil_listening = ($hasil->jumlah_benar_listening/$listening_jmlh)*$jawab->banksoal->persen['listening'];
        }
        $hasil_pg = 0;
        if($hasil->jumlah_benar > 0) {
            $hasil_pg = ($hasil->jumlah_benar/$pg_jmlh)*$jawab->banksoal->persen['pilihan_ganda'];
        }
        $hasil_ganda = $hasil_listening+$hasil_pg;

        if($request->val != 0) {
            $hasil_esay = $hasil->point_esay + ($request->val/$jml_esay);
        } else {
            $hasil_esay = $hasil->point_esay;
        }
        $hasil_val = ($hasil_ganda)+($hasil_esay*$jawab->banksoal->persen['esay']);

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
        }]);

        $jurusan = request()->jurusan;

        if ($jurusan != 0 ) {
            $res->whereHas('peserta', function($query) use ($jurusan) {
                $query->where('jurusan_id', $jurusan);
            });
        }

        $res->where('jadwal_id', $jadwal->id)
            ->orderBy('peserta_id');

        if(request()->perPage != '') {
            $res = $res->paginate(request()->perPage);
        } else {
            $res = $res->get();
        }

        return SendResponse::acceptData($res);
    }

    /**
     *
     */
    public function getResultExcel(Request $request, Jadwal $jadwal)
    {
        if (! $request->hasValidSignature()) {
            abort(401);
        }

        $jurusan = request()->jurusan;

        $jurusan = explode(',',$jurusan);

        $res = HasilUjian::with(['peserta' => function ($query) use ($jurusan) {
            $query->select('id','nama','no_ujian');
        }])
        ->whereHas('peserta', function($query) use ($jurusan) {
            $query->whereIn('jurusan_id', $jurusan);
        })
        ->where('jadwal_id', $jadwal->id)
        ->orderBy('peserta_id')
        ->get();

        $spreadsheet = HasilUjianExport::export($res,$jadwal->alias);
        $writer = new Xlsx($spreadsheet);

        $filename = 'Hasil ujian '.$jadwal->alias;
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.$filename.'.xlsx"');
        $writer->save('php://output');
    }

    /**
     *
     */
    public function getResultExcelLink(Jadwal $jadwal)
    {
        $jurusan = request()->q;

        $url = URL::temporarySignedRoute(
            'hasilujian.download.excel', now()->addMinutes(5),['jadwal' => $jadwal->id, 'jurusan' => $jurusan]
        );

        return SendResponse::acceptData($url);
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

    public function getCapaianSiswaExcel(Request $request, Jadwal $jadwal, Banksoal $banksoal)
    {
        if (! $request->hasValidSignature()) {
            abort(401);
        }

        $soals = Soal::where(function($query) use($banksoal) {
            $query->where('banksoal_id', $banksoal->id)
            ->where('tipe_soal','!=','2');
        })->get();

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
        ->select('id','iscorrect','peserta_id', 'soal_id')
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
            'soals' => $soals
        ];

        $spreadsheet = CapaianSiswaExport::export($data, $banksoal->kode_banksoal, $jadwal->alias);
        $writer = new Xlsx($spreadsheet);

        $filename = 'Capaian siswa '.$banksoal->kode_banksoal.' '.$jadwal->alias;
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.$filename.'.xlsx"');
        $writer->save('php://output');
    }

    public function getCapaianSiswaExcelLink(Jadwal $jadwal, Banksoal $banksoal)
    {
        $url = URL::temporarySignedRoute(
            'capaian.download.excel', now()->addMinutes(5),['jadwal' => $jadwal->id, 'banksoal' => $banksoal->id]
        );

        return SendResponse::acceptData($url);
    }

    public function getHasilUjianDetail(HasilUjian $hasil)
    {
        $jawaban = JawabanPeserta::with(['esay_result','soal','soal.jawabans'])
        ->where([
            'peserta_id'    => $hasil->peserta_id,
            'jadwal_id'     => $hasil->jadwal_id
        ])
        ->get();

        $data = $jawaban->map(function($item) {
            return [
                'banksoal_id' => $item->banksoal_id,
                'esay' => $item->esay,
                'esay_result' => $item->esay_result,
                'id' => $item->id,
                'iscorrect' => $item->iscorrect,
                'jadwal_id' => $item->jawab_id,
                'jawab' => $item->jawab,
                'jawab_complex' => $item->jawab_complex,
                'peserta_id' => $item->peserta_id,
                'ragu_ragu' => $item->ragu_ragu,
                'similiar' => $item->similiar,
                'soal' => $item->soal,
                'soal_id' => $item->soal_id,
                'updated_at' => $item->updated_at,
            ];
        });

        return SendResponse::acceptData($data);
    }
}
