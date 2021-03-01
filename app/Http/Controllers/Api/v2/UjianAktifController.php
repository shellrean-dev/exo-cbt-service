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
     * Memulai ujian masuk kedalam mode standby
     * @param  Request $request
     * @return \App\Actions\SendResponse
     * @author wandinak17@gmail.com
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
     * Ambil ujian peserta yang sedang dikerjakan
     * @return \App\Actions\SendResponse
     * @author wandinak17@gmail.com
     */
    public function getUjianPesertaAktif()
    {
        $peserta = request()->get('peserta-auth');

        // ambil ujian yang aktif hari ini
        $jadwals = DB::table('jadwals')->where([
            'status_ujian'  => 1,
            'tanggal'       => now()->format('Y-m-d')
        ])
        ->select('id')
        ->get();
        $jadwal_ids = $jadwals->pluck('id')->toArray();

        // ambil data siswa ujian
        // yang sudah dijalankan pada hari ini
        // tetapi belum dimulai
        // yang mana jadwal tersebut sedang aktif dan tanggal pengerjaannya hari ini
        $data = DB::table('siswa_ujians')
            ->where('peserta_id', $peserta->id)
            ->where('status_ujian', 0)
            ->whereIn('jadwal_id', $jadwal_ids)
            ->whereDate('created_at', now()->format('Y-m-d'))
            ->first();

        if(!$data) {
            return SendResponse::acceptData([]);
        }

        $res = [
            'jadwal_id'     => $data->jadwal_id,
            'status_ujian'  => $data->status_ujian
        ];

        return SendResponse::acceptData($res);
    }

    /**
     * Mulai penghitungan waktu ujian
     * @param  Request $request
     * @return \App\Actions\SendResponse
     * @author wandinak17@gmail.com
     */
    public function startUjianTime(Request $request)
    {
        $peserta = request()->get('peserta-auth');

        // Ambil data yang belum dimulai
        $data = DB::table('siswa_ujians')
            ->where('peserta_id', $peserta->id)
            ->where('status_ujian', '<>', 1)
            ->whereDate('created_at', now()->format('Y-m-d'))
            ->first();

        // Jika ini adalah pertama kali peserta 
        // Melakukan mulai ujian
        // 3 <= sedang mengerjakan
        if($data->status_ujian != 3) {
            try{
                DB::table('siswa_ujians')
                    ->where('id', $data->id)
                    ->update([
                        'mulai_ujian'   => now()->format('H:i:s'),
                        'status_ujian'  => 3,
                    ]);
            }catch(\Exception $e){
                return SendResponse::internalServerError($e->getMessage());
            }
        }

        return SendResponse::accept();
    }
    
    /**
     * Ambil data ujian siswa yang belum diselesaikan pada hari ini
     * @return \App\Actions\SendResponse
     * @author wandinak17@gmail.com
     */
    public function uncompleteUjian()
    {
        $peserta = request()->get('peserta-auth');

        // ambil ujian yang aktif hari ini
        $jadwals = DB::table('jadwals')->where([
            'status_ujian'  => 1,
            'tanggal'       => now()->format('Y-m-d')
        ])
        ->select('id')
        ->get();
        $jadwal_ids = $jadwals->pluck('id')->toArray();

        // ambil data siswa ujian
        // yang sedang dikerjakan pada hari ini
        // yang mana jadwal tersebut sedang aktif dan tanggal pengerjaannya hari ini
        $data = DB::table('siswa_ujians')
            ->where('peserta_id', $peserta->id)
            ->where('status_ujian', 3)
            ->whereIn('jadwal_id', $jadwal_ids)
            ->whereDate('created_at', now()->format('Y-m-d'))
            ->first();

        if(!$data) {
            return SendResponse::acceptData([]);
        }
        
        $res = [
            'jadwal_id'     => $data->jadwal_id,
            'status_ujian'  => $data->status_ujian
        ];

        return SendResponse::acceptData($res);
    }

    /**
     * Ambil soal dan jawaban siswa
     * @return \App\Actions\SendResponse
     * @author wandinak17@gmail.com
     */
    public function getJawabanPeserta(UjianService $ujianService)
    {
        $peserta = request()->get('peserta-auth');
        $ujian_siswa = $ujianService->getUjianSiswaBelumSelesai($peserta->id);

        if(!$ujian_siswa) {
            return SendResponse::badRequest('Terjadi kesalahan saat mengambil ujian untuk kamu, kamu tidak sedang mengikuti ujian apapun. silakan logout lalu login kembali');
        }

        // Ambil id banksoal yang terkait dalam jadwal
        // $jadwal = Jadwal::find($ujian_siswa->jadwal_id);
        $jadwal = DB::table('jadwals')
            ->where('id', $ujian_siswa->jadwal_id)
            ->first();
        // Jika jadwal yang dikerjakan siswa tidak ditemukan
        if(!$jadwal) {
            return SendResponse::badRequest('Terjadi kesalahan saat mengambil jadwal ujian untuk kamu, silakan logout lalu hubungi administrator');
        }

        // $banksoal_ids = array_column($jadwal->banksoal_id, 'jurusan','id');
        // $banksoal_ids = collect($banksoal_ids)->keys();
        // $banksoal_ids = json_decode($jadwal->banksoal_id, 'id');
        $banksoal_ids = array_column(json_decode($jadwal->banksoal_id, true), 'id');

        $banksoal_diujikan = Banksoal::with('matpel')
            ->whereIn('id', $banksoal_ids)
            ->get();
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
            return SendResponse::badRequest('Kamu tidak mendapat banksoal yang sesuai, silakan logout lalu hubungi administrator');
        }

        // Ambil jawaban siswa yang telah digenerate
        $jawaban_peserta = UjianService::getJawabanPeserta(
            $jadwal->id, 
            $peserta->id, 
            json_decode($jadwal->setting, true)['acak_opsi']
        );

        // Jika jawaban siswa belum ada di database
        if ($jawaban_peserta->count() < 1 ) {
            //------------------------------------------------------------------
            $banksoal = Banksoal::find($banksoal_id);
            $max_pg = $banksoal->jumlah_soal;
            $max_esay = $banksoal->jumlah_soal_esay;
            $max_listening = $banksoal->jumlah_soal_listening;
            $max_complex = $banksoal->jumlah_soal_ganda_kompleks;
            $max_menjodohkan = $banksoal->jumlah_menjodohkan;
            $max_isian_singkat = $banksoal->jumlah_isian_singkat;

            $setting = json_decode($jadwal->setting, true);

            // Soal Pilihan Ganda
            $pg = Soal::where([
                'banksoal_id' => $banksoal->id,
                'tipe_soal' => 1
            ]);
            if($setting['acak_soal'] == "1") {
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
            if($setting['acak_soal'] == "1") {
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
            if($setting['acak_soal'] == "1") {
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
            if($setting['acak_soal'] == "1") {
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
            // $menjodohkan = Soal::where([
            //     'banksoal_id'   => $banksoal->id,
            //     'tipe_soal'     => 5
            // ]);
            // if($setting['acak_soal'] == "1") {
            //     $menjodohkan = $menjodohkan->inRandomOrder();
            // }
            // $menjodohkan = $menjodohkan->take($max_menjodohkan)->get();

            // $soal_menjodohkan = $menjodohkan->map(function($item) use($peserta, $banksoal, $jadwal) {
            //     return [
            //         'peserta_id'    => $peserta->id,
            //         'banksoal_id'   => $banksoal->id,
            //         'soal_id'       => $item->id,
            //         'jawab'         => 0,
            //         'iscorrect'     => 0,
            //         'jadwal_id'     => $jadwal->id,
            //         'ragu_ragu'     => 0,
            //         'esay'          => ''
            //     ];
            // });

            // Soal  isian singkat
            $isian_singkat = Soal::where([
                'banksoal_id'   => $banksoal->id,
                'tipe_soal'     => 6
            ]);
            if($setting['acak_soal'] == "1") {
                $isian_singkat = $isian_singkat->inRandomOrder();
            }
            $isian_singkat = $isian_singkat->take($max_isian_singkat)->get();

            $soal_isian_singkat = $isian_singkat->map(function($item) use($peserta, $banksoal, $jadwal) {
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
                // '5' => $soal_menjodohkan->values()->toArray(),
                '6' => $soal_isian_singkat->values()->toArray(),
            ]);
            foreach ($setting['list'] as $value) {
                $soal = $list->get($value['id']);
                if($soal) {
                    $soals = array_merge($soals, $soal);
                }
            }

            // Insert
            try {
                DB::beginTransaction();
                DB::table('jawaban_pesertas')->insert($soals);
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return SendResponse::internalServerError($e->getMessage());
            }

            // Get Jawaban peserta
            $jawaban_peserta = UjianService::getJawabanPeserta(
                $jadwal->id, 
                $peserta->id, 
                $setting['acak_opsi']
            );

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

        // Jika perbedaan waktu telah melebihi 
        // waktu pengerjaan ujian
        if($diff_in_minutes > $jadwal->lama) {
            try {
                DB::beginTransaction();
                $ujian->status_ujian = 1;
                $ujian->save();
    
                $finished = UjianService::finishingUjian($banksoal_id, $jadwal->id, $peserta->id);

                if(!$finished['success']) {
                    DB::rollBack();
                    return SendResponse::badRequest($finished['message']);
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return SendResponse::internalServerError($e->getMessage());
            }
        } else {
            try {
                DB::beginTransaction();
                $ujian->sisa_waktu = $jadwal->lama-$diff_in_minutes;
                $ujian->save();
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return SendResponse::internalServerError($e->getMessage());
            }
        }

        return response()->json(['data' => $jawaban_peserta, 'detail' => $ujian]);
    }
}