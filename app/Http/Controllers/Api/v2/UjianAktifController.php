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
            ->whereIn('status_ujian', [0,3])
            ->whereIn('jadwal_id', $jadwal_ids)
            ->whereDate('created_at', now()->format('Y-m-d'))
            ->select('jadwal_id', 'status_ujian')
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
     * Memulai ujian masuk kedalam mode standby
     * @param  Request $request
     * @return \App\Actions\SendResponse
     * @author wandinak17@gmail.com
     */
    public function startUjian(Request $request)
    {
        // validasi jadwal ujian yang diminta
        $request->validate([
            'jadwal_id'     => 'required|exists:jadwals,id'
        ]);

        // cari jadwal ujian yang diminta
        $ujian = DB::table('jadwals')
            ->where('id', $request->jadwal_id)
            ->first();

        // jika token diaktifkan
        $setting = json_decode($ujian->setting, true);
        if($setting['token'] == "1") {
            // Ambil token
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
                    return SendResponse::badRequest('Token yang kamu masukkan tidak sesuai, cek token lalu submit kembali');
                }
                if($token->status == 0) {
                    return SendResponse::badRequest('Status token belum dirilis, minta administrator untuk merilis token');
                }
            } else {
                DB::table('tokens')->insert([
                    'token'     => strtoupper(Str::random(6)),
                    'status'    => '0',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                return SendResponse::badRequest('Token yang kamu masukkan tidak sesuai, cek token lalu submit kembali');
            }
        }
        $peserta = request()->get('peserta-auth');

        // cek pengaturan sesi
        if($ujian->event_id != '0') {
            $schedule = DB::table('sesi_schedules')
                ->where([
                    'jadwal_id' => $ujian->id,
                    'sesi'      => $ujian->sesi
                ])
                ->first();
            if($schedule) {
                if(!in_array($peserta->id, json_decode($schedule->peserta_ids, true))){
                    return SendResponse::badRequest('Anda tidak ada di dalam sesi '.$ujian->sesi.' bila anda merasa seharusnya berada di sesi ini, hubungi administrator');
                }
            } else {
                return SendResponse::badRequest('Sesi belum ditentukan, hubungi administrator');
            }
        }
        else {
            if($peserta->sesi != $ujian->sesi) {
                return SendResponse::badRequest('Anda tidak ada didalam sesi '.$ujian->sesi.' bila anda merasa seharusnya berada di sesi ini, hubungi administrator');
            }
        }

        $data = DB::table('siswa_ujians')
            ->where('peserta_id', $peserta->id)
            ->where('jadwal_id', $request->jadwal_id)
            ->where('status_ujian', 0)
            ->first();

        if($data) {
            return SendResponse::accept('mata ujian diambil dari data sebelumnya');
        }

        try {
            DB::table('siswa_ujians')->insert([
                'peserta_id'        => $peserta->id,
                'jadwal_id'         => $request->jadwal_id,
                'mulai_ujian'       => '',
                'mulai_ujian_shadow'=> '',
                'sisa_waktu'        => $ujian->lama,
                'status_ujian'      => 0,
                'uploaded'          => 0,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        } catch (\Exception $e) {
            return SendResponse::internalServerError("Terjadi kesalahan 500. ".$e->getMessage());
        }

        return SendResponse::accept('mata ujian diambil dengan mulai ujian baru');
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

        if (!$data) {
            return SendResponse::badRequest('Kami tidak dapat mengambil ujian untuk kamu, kamu tidak sedang mengikuti ujian apapun. silakan logout lalu login kembali');
        }

        // Jika ini adalah pertama kali peserta 
        // Melakukan mulai ujian
        // 3 <= sedang mengerjakan
        if($data->status_ujian != 3) {
            try{
                DB::table('siswa_ujians')
                    ->where('id', $data->id)
                    ->update([
                        'mulai_ujian'       => now()->format('H:i:s'),
                        'mulai_ujian_shadow'=> now()->format('H:i:s'),
                        'status_ujian'      => 3,
                    ]);
            }catch(\Exception $e){
                return SendResponse::internalServerError($e->getMessage());
            }
        }

        return SendResponse::accept();
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
        $jadwal = DB::table('jadwals')
            ->where('id', $ujian_siswa->jadwal_id)
            ->first();
        // Jika jadwal yang dikerjakan siswa tidak ditemukan
        if(!$jadwal) {
            return SendResponse::badRequest('Terjadi kesalahan saat mengambil jadwal ujian untuk kamu, silakan logout lalu hubungi administrator');
        }

        $banksoal_ids = array_column(json_decode($jadwal->banksoal_id, true), 'id');
        
        $banksoal_diujikan = DB::table('banksoals')
            ->join('matpels','banksoals.matpel_id','=','matpels.id')
            ->whereIn('banksoals.id', $banksoal_ids)
            ->select('banksoals.id','matpels.agama_id','matpels.jurusan_id')
            ->get();
        $banksoal_id = '';

        // Cari id banksoal yang dapat dipakai oleh siswwa
        foreach($banksoal_diujikan as $bk) {
            $banksoal = UjianService::getBanksoalPeserta($bk, $peserta);
            if(!$banksoal['success']) {
                continue;
            }
            if ($banksoal['data'] != '') {
                $banksoal_id = $banksoal['data'];
                break;
            }
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
            // $banksoal = Banksoal::find($banksoal_id);
            $banksoal = DB::table('banksoals')
                ->where('id', $banksoal_id)
                ->first();

            // Ambil maximal dari tiap tiap tipe soal
            $max_pg = $banksoal->jumlah_soal;
            $max_esay = $banksoal->jumlah_soal_esay;
            $max_listening = $banksoal->jumlah_soal_listening;
            $max_complex = $banksoal->jumlah_soal_ganda_kompleks;
            $max_menjodohkan = $banksoal->jumlah_menjodohkan;
            $max_isian_singkat = $banksoal->jumlah_isian_singkat;

            // Ambil setting dari jadwal
            $setting = json_decode($jadwal->setting, true);

            // Ambil soal tipe : ganda
            
            // $pg = Soal::where([
            //     'banksoal_id' => $banksoal->id,
            //     'tipe_soal' => 1
            // ]);
            $pg = DB::table('soals')->where([
                'banksoal_id'   => $banksoal->id,
                'tipe_soal'     => 1
            ]);

            // Acak soal bila di sett
            if($setting['acak_soal'] == "1") {
                $pg = $pg->inRandomOrder();
            }

            // Ambil soal sebanyak maximum
            $pg = $pg->take($max_pg)->get();
            $soal_pg = [];

            // Buat collection untuk jawaban siswa
            // $soal_pg = $pg->map(function($item) use($peserta, $banksoal, $jadwal) {
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
            foreach($pg as $item) {
                array_push($soal_pg, [
                    'peserta_id'    => $peserta->id,
                    'banksoal_id'   => $banksoal->id,
                    'soal_id'       => $item->id,
                    'jawab'         => 0,
                    'iscorrect'     => 0,
                    'jadwal_id'     => $jadwal->id,
                    'ragu_ragu'     => 0,
                    'esay'          => ''
                ]);
            }

            // Ambil soal tipe :esay
            // $esay = Soal::where([
            //     'banksoal_id'   => $banksoal->id,
            //     'tipe_soal'     => 2
            // ]);
            $esay = DB::table('soals')->where([
                'banksoal_id'   => $banksoal->id,
                'tipe_soal'     => 2
            ]);

            //Acak soal bila di set
            if($setting['acak_soal'] == "1") {
                $esay = $esay->inRandomOrder();
            }

            // Ambil soal sebanyak maximum
            $esay = $esay->take($max_esay)->get();

            // Buat collection untuk jawaban siswa
            $soal_esay = [];
            // $soal_esay = $esay->map(function($item) use($peserta, $banksoal, $jadwal) {
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
            foreach($esay as $item) {
                array_push($soal_esay, [
                    'peserta_id'    => $peserta->id,
                    'banksoal_id'   => $banksoal->id,
                    'soal_id'       => $item->id,
                    'jawab'         => 0,
                    'iscorrect'     => 0,
                    'jadwal_id'     => $jadwal->id,
                    'ragu_ragu'     => 0,
                    'esay'          => ''
                ]);
            }

            // Ambil soal: Listening
            // $listening = Soal::where([
            //     'banksoal_id'   => $banksoal->id,
            //     'tipe_soal'     => 3
            // ]);
            $listening = DB::table('soals')->where([
                'banksoal_id'   => $banksoal->id,
                'tipe_soal'     => 3
            ]);

            // Acak soal bila di set
            if($setting['acak_soal'] == "1") {
                $listening = $listening->inRandomOrder();
            }

            // Ambil soal sebanyak maximum
            $listening = $listening->take($max_listening)->get();

            // Buat collection untuk jawaban siswa
            $soal_listening = [];
            // $soal_listening = $listening->map(function($item) use($peserta, $banksoal, $jadwal) {
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
            foreach($listening as $item) {
                array_push($soal_listening, [
                    'peserta_id'    => $peserta->id,
                    'banksoal_id'   => $banksoal->id,
                    'soal_id'       => $item->id,
                    'jawab'         => 0,
                    'iscorrect'     => 0,
                    'jadwal_id'     => $jadwal->id,
                    'ragu_ragu'     => 0,
                    'esay'          => ''
                ]);
            }

            // Ambil soal: Multichoice complex
            // $complex = Soal::where([
            //     'banksoal_id'   => $banksoal->id,
            //     'tipe_soal'     => 4
            // ]);
            $complex = DB::table('soals')->where([
                'banksoal_id'   => $banksoal->id,
                'tipe_soal'     => 4
            ]);

            // Acak soal bila di set
            if($setting['acak_soal'] == "1") {
                $complex = $complex->inRandomOrder();
            }

            // Ambil soal sebanyak maximum
            $complex = $complex->take($max_complex)->get();

            // Buat collection untuk jawaban siswa
            $soal_complex = [];
            // $soal_complex = $complex->map(function($item) use($peserta, $banksoal, $jadwal) {
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
            foreach($complex as $item) {
                array_push($soal_complex, [
                    'peserta_id'    => $peserta->id,
                    'banksoal_id'   => $banksoal->id,
                    'soal_id'       => $item->id,
                    'jawab'         => 0,
                    'iscorrect'     => 0,
                    'jadwal_id'     => $jadwal->id,
                    'ragu_ragu'     => 0,
                    'esay'          => ''
                ]);
            }

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

            // Ambil soal:  isian singkat
            // $isian_singkat = Soal::where([
            //     'banksoal_id'   => $banksoal->id,
            //     'tipe_soal'     => 6
            // ]);
            $isian_singkat = DB::table('soals')->where([
                'banksoal_id'   => $banksoal->id,
                'tipe_soal'     => 6
            ]);

            // Acak soal bila di set
            if($setting['acak_soal'] == "1") {
                $isian_singkat = $isian_singkat->inRandomOrder();
            }

            // Ambil soal sebanyak maximum
            $isian_singkat = $isian_singkat->take($max_isian_singkat)->get();

            // Buat collection untuk jawaban siswa
            $soal_isian_singkat = [];
            // $soal_isian_singkat = $isian_singkat->map(function($item) use($peserta, $banksoal, $jadwal) {
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
            foreach($isian_singkat as $item) {
                array_push($soal_isian_singkat, [
                    'peserta_id'    => $peserta->id,
                    'banksoal_id'   => $banksoal->id,
                    'soal_id'       => $item->id,
                    'jawab'         => 0,
                    'iscorrect'     => 0,
                    'jadwal_id'     => $jadwal->id,
                    'ragu_ragu'     => 0,
                    'esay'          => ''
                ]);
            }

            // Gabungkan semua collection dari tipe soal
            $soals = [];
            $list = collect([
                '1' => $soal_pg,
                '2' => $soal_esay,
                '3' => $soal_listening,
                '4' => $soal_complex,
                // '5' => $soal_menjodohkan,
                '6' => $soal_isian_singkat,
            ]);
            foreach ($setting['list'] as $value) {
                $soal = $list->get($value['id']);
                if($soal) {
                    $soals = array_merge($soals, $soal);
                }
            }

            // Insert ke database sebagai jawaban siswa
            try {
                DB::beginTransaction();
                DB::table('jawaban_pesertas')->insert($soals);
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return SendResponse::internalServerError($e->getMessage());
            }

            // Ambil jawaban siswa
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
        $start = Carbon::createFromFormat('H:i:s', $ujian->mulai_ujian_shadow);
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