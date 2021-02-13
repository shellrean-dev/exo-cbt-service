<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use \Illuminate\Support\Facades\DB;
use App\Services\UjianService;
use App\Actions\SendResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\JawabanPeserta;
use App\SesiSchedule;
use App\SiswaUjian;
use Carbon\Carbon;
use App\Banksoal;
use App\Jadwal;
use App\Token;
use App\Soal;

class UjianAktifController extends Controller
{
    /**
     * [startUjian description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function startUjian(Request $request)
    {
        $request->validate([
            'jadwal_id'     => 'required|exists:jadwals,id'
        ]);

        $ujian = Jadwal::find($request->jadwal_id);
        if($ujian->setting['token'] == "1") {
            $token = Token::orderBy('id')->first();
            if($token) {
                $to = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', now());
                $from = $token->updated_at->format('Y-m-d H:i:s');
                $differ = $to->diffInSeconds($from);
                if($differ > 900) {
                    $token->token = strtoupper(Str::random(6));
                    $token->status = '0';
                    $token->save();
                }
                if($token->token != $request->token) {
                    return SendResponse::badRequest('Token tidak sesuai');
                }
                if($token->status == 0) {
                    return SendResponse::badRequest('Status token belum dirilis');
                }
            }
        }
        $peserta = request()->get('peserta-auth');

        if($ujian->event_id != '0') {
            $schedule = SesiSchedule::where([
                'jadwal_id' => $ujian->id,
                'sesi'      => $ujian->sesi
            ])->first();
            if($schedule) {
                if(!in_array($peserta->id, $schedule->peserta_ids)){
                    return SendResponse::badRequest('Anda tidak ada didalam sesi '.$ujian->sesi);
                }
            } else {
                return SendResponse::badRequest('Sesi belum ditentukan, hubungi administrator');
            }
        }
        else {
            if($peserta->sesi != $ujian->sesi) {
                return SendResponse::badRequest('Anda tidak ada didalam sesi '.$ujian->sesi);
            }
        }

        $data = SiswaUjian::where(function($query) use($peserta, $request) {
            $query->where('peserta_id', $peserta->id)
            ->where('jadwal_id', $request->jadwal_id)
            ->where('status_ujian','=',0);
        })->first();

        if($data) {
            return SendResponse::accept();
        }

        $peserta = SiswaUjian::create([
            'peserta_id'        => $peserta->id,
            'jadwal_id'         => $request->jadwal_id,
            'mulai_ujian'       => '',
            'sisa_waktu'        => $ujian->lama,
            'status_ujian'      => 0,
            'uploaded'          => 0
        ]);

        return SendResponse::accept();
    }

    /**
     * [getUjianPesertaAktif description]
     * @return [type] [description]
     */
    public function getUjianPesertaAktif()
    {
        $peserta = request()->get('peserta-auth');
        $jadwals = Jadwal::where('status_ujian',1)->get();
        $jadwal_ids = $jadwals->pluck('id')->toArray();

        $data = SiswaUjian::where(function($query) use($peserta, $jadwal_ids) {
            $query->where('peserta_id', $peserta->id)
            ->where('status_ujian','=',0)
            ->whereIn('jadwal_id', $jadwal_ids);
        })->first();
        if(!$data) {
            $data = [];
        }

        return SendResponse::acceptData($data);
    }

    /**
     * [startUjianTime description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function startUjianTime(Request $request)
    {
        $peserta = request()->get('peserta-auth');
        $data = SiswaUjian::where(function($query) use($peserta) {
            $query->where('peserta_id', $peserta->id)
            ->where('status_ujian','=',0);
        })->first();

        if($data->status_ujian != 3) {
            $data->mulai_ujian = now()->format('H:i:s');
            $data->status_ujian = 3;
            $data->save();
        }
        return SendResponse::accept();
    }


    /**
     * [getJawabanPeserta description]
     * @return [type] [description]
     */
    public function getJawabanPeserta(UjianService $ujianService)
    {
        $peserta = request()->get('peserta-auth');
        $ujian_siswa = $ujianService->getUjianSiswaBelumSelesai($peserta->id);

        if(!$ujian_siswa) {
            return SendResponse::badRequest('Anda memasuki ujian ini secara ilegal');
        }

        // Ambil id banksoal yang terkait dalam jadwal
        $jadwal = Jadwal::find($ujian_siswa->jadwal_id);
        if(!$jadwal) {
            return SendResponse::badRequest('Anda memasuki ujian ini secara ilegal');
        }

        $banksoal_ids = array_column($jadwal->banksoal_id, 'jurusan','id');
        $banksoal_ids = collect($banksoal_ids)->keys();

        $banksoal_diujikan = Banksoal::with('matpel')->whereIn('id', $banksoal_ids)->get();
        $banksoal_id = '';

        // Cari id banksoal yang dapat dipakai oleh siswwa
        foreach($banksoal_diujikan as $bk) {
            $banksoal = UjianService::getBanksoalPeserta($bk, $peserta);
            if(!$banksoal['success']) {
                continue;
            }
            $banksoal_id = $banksoal['data'];
        }

        // Jika tidak dapat menemukan banksoal_id
        if($banksoal_id == '') {
            return SendResponse::badRequest('Anda tidak mendapat banksoal yang sesuai, silakan hubungi administrator');
        }

        $jawaban_peserta = UjianService::getJawabanPeserta($jadwal->id, $peserta->id, $jadwal->setting['acak_opsi']);

        // Jika jawaban siswa belum ada di database
        if ($jawaban_peserta->count() < 1 ) {
            //------------------------------------------------------------------
            $banksoal = Banksoal::find($banksoal_id);
            $max_pg = $banksoal->jumlah_soal;
            $max_esay = $banksoal->jumlah_soal_esay;
            $max_listening = $banksoal->jumlah_soal_listening;
            $max_complex = $banksoal->jumlah_soal_ganda_kompleks;
            $max_menjodohkan = $banksoal->jumlah_menjodohkan;


            // Soal Pilihan Ganda
            $pg = Soal::where([
                'banksoal_id' => $banksoal->id,
                'tipe_soal' => 1
            ]);
            if($jadwal->setting['acak_soal'] == "1") {
                $pg = $pg->inRandomOrder();
            }
            $pg = $pg->take($max_pg)->get();

            $soal_pg = $pg->map(function($item) use($peserta, $banksoal, $jadwal) {
                return [
                    'peserta_id'    => $peserta->id,
                    'banksoal_id'   => $banksoal->id,
                    'soal_id'       => $item->id,
                    'jawab'         => 0,
                    'iscorrect'     => 0,
                    'jadwal_id'     => $jadwal->id,
                    'ragu_ragu'     => 0,
                    'esay'          => ''
                ];
            });

            // Soal Esay
            $esay = Soal::where([
                'banksoal_id'   => $banksoal->id,
                'tipe_soal'     => 2
            ]);
            if($jadwal->setting['acak_soal'] == "1") {
                $esay = $esay->inRandomOrder();
            }
            $esay = $esay->take($max_esay)->get();

            $soal_esay = $esay->map(function($item) use($peserta, $banksoal, $jadwal) {
                return [
                    'peserta_id'    => $peserta->id,
                    'banksoal_id'   => $banksoal->id,
                    'soal_id'       => $item->id,
                    'jawab'         => 0,
                    'iscorrect'     => 0,
                    'jadwal_id'     => $jadwal->id,
                    'ragu_ragu'     => 0,
                    'esay'          => ''
                ];
            });

            // Soal Listening
            $listening = Soal::where([
                'banksoal_id'   => $banksoal->id,
                'tipe_soal'     => 3
            ]);
            if($jadwal->setting['acak_soal'] == "1") {
                $listening = $listening->inRandomOrder();
            }
            $listening = $listening->take($max_listening)->get();

            $soal_listening = $listening->map(function($item) use($peserta, $banksoal, $jadwal) {
                return [
                    'peserta_id'    => $peserta->id,
                    'banksoal_id'   => $banksoal->id,
                    'soal_id'       => $item->id,
                    'jawab'         => 0,
                    'iscorrect'     => 0,
                    'jadwal_id'     => $jadwal->id,
                    'ragu_ragu'     => 0,
                    'esay'          => ''
                ];
            });

            // Soal Multichoice complex
            $complex = Soal::where([
                'banksoal_id'   => $banksoal->id,
                'tipe_soal'     => 4
            ]);
            if($jadwal->setting['acak_soal'] == "1") {
                $complex = $complex->inRandomOrder();
            }
            $complex = $complex->take($max_complex)->get();

            $soal_complex = $complex->map(function($item) use($peserta, $banksoal, $jadwal) {
                return [
                    'peserta_id'    => $peserta->id,
                    'banksoal_id'   => $banksoal->id,
                    'soal_id'       => $item->id,
                    'jawab'         => 0,
                    'iscorrect'     => 0,
                    'jadwal_id'     => $jadwal->id,
                    'ragu_ragu'     => 0,
                    'esay'          => ''
                ];
            });

            // Soal  menjodohkan
            $menjodohkan = Soal::where([
                'banksoal_id'   => $banksoal->id,
                'tipe_soal'     => 5
            ]);
            if($jadwal->setting['acak_soal'] == "1") {
                $menjodohkan = $menjodohkan->inRandomOrder();
            }
            $menjodohkan = $menjodohkan->take($max_menjodohkan)->get();

            $soal_menjodohkan = $menjodohkan->map(function($item) use($peserta, $banksoal, $jadwal) {
                return [
                    'peserta_id'    => $peserta->id,
                    'banksoal_id'   => $banksoal->id,
                    'soal_id'       => $item->id,
                    'jawab'         => 0,
                    'iscorrect'     => 0,
                    'jadwal_id'     => $jadwal->id,
                    'ragu_ragu'     => 0,
                    'esay'          => ''
                ];
            });

            // Merges dengan urutan
            $soals = [];
            $list = collect([
                '1' => $soal_pg->values()->toArray(),
                '2' => $soal_esay->values()->toArray(),
                '3' => $soal_listening->values()->toArray(),
                '4' => $soal_complex->values()->toArray(),
                '5' => $soal_menjodohkan->values()->toArray(),
            ]);
            foreach ($jadwal->setting['list'] as $value) {
                $soal = $list->get($value['id']);
                if($soal) {
                    $soals = array_merge($soals, $soal);
                }
            }

            // // Insert
            DB::table('jawaban_pesertas')->insert($soals);

            // Get Jawaban peserta
            $jawaban_peserta = UjianService::getJawabanPeserta($jadwal->id, $peserta->id, $jadwal->setting['acak_opsi']);

            return response()->json(['data' => $jawaban_peserta, 'detail' => $ujian_siswa]);
        }

        // Get siswa ujian detail
        $ujian = SiswaUjian::where([
            'jadwal_id'     => $jadwal->id,
            'peserta_id'    => $peserta->id
        ])->first();

        // Check perbedaan waktu
        $start = Carbon::createFromFormat('H:i:s', $ujian->mulai_ujian);
        $now = Carbon::createFromFormat('H:i:s', Carbon::now()->format('H:i:s'));
        $diff_in_minutes = $start->diffInSeconds($now);

        if($diff_in_minutes > $jadwal->lama) {
            $ujian->status_ujian = 1;
            $ujian->save();

            $finished = UjianService::finishingUjian($banksoal_id, $jadwal->id, $peserta->id);
            if(!$finished['success']) {
                return SendResponse::badRequest($finished['message']);
            }
        } else {
            $ujian->sisa_waktu = $jadwal->lama-$diff_in_minutes;
            $ujian->save();
        }

        return response()->json(['data' => $jawaban_peserta, 'detail' => $ujian]);
    }

    /**
     * [uncompleteUjian description]
     * @return [type] [description]
     */
    public function uncompleteUjian()
    {
        $peserta = request()->get('peserta-auth');

        $data = SiswaUjian::where(function($query) use($peserta) {
            $query->where('peserta_id', $peserta->id)
            ->where('status_ujian','=',3);
        })->first();
        if(!$data) {
            $data= [];
        }
        return SendResponse::acceptData($data);
    }
}
