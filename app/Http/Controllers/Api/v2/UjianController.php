<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Services\UjianService;
use App\Actions\SendResponse;
use Illuminate\Http\Request;
use App\JawabanPeserta;
use App\SiswaUjian;
use App\HasilUjian;
use App\JawabanSoal;
use Carbon\Carbon;
use App\Soal;

class UjianController extends Controller
{
    /**
     * Simpan/Update jawaban siswa pada ujian aktif
     *
     * @param Illuminate\Http\Request
     * @return Illuminate\Http\Response
     * @author shellrean <wandnak17@gmail.com>
     */
    public function store(Request $request)
    {
        $request->validate([
            'jawaban_id' => 'required',
            'index'     => 'required'
        ]);

        $peserta = request()->get('peserta-auth');

        // Ambil jawaban peserta
        $find = DB::table('jawaban_pesertas')
            ->where('id', $request->jawaban_id)
            ->first();
        
        if (!$find) {
            return SendResponse::badRequest('Kami tidak dapat menemukan data dari jawaban kamu.');
        }

        // ambil ujian yang aktif hari ini
        $ujian = $this->_getUjianCurrent($peserta);

        if (!$ujian) {
            return SendResponse::badRequest('Kami tidak dapat menemukan ujian yang sedang kamu kerjakan, mungkin jadawal ini sedang tidak aktif. silakan logout lalu hubungi administrator.');
        }

        if($ujian) {
            $this->_kurangiWaktu($ujian);
        }

        // Jika yang dikirimkan adalah esay
        if(isset($request->essy)) {
            try {
                DB::table('jawaban_pesertas')
                    ->where('id', $find->id)
                    ->update([
                        'esay'  => $request->essy
                    ]);
            } catch (\Exception $e) {
                return SendResponse::internalServerError('Terjadi kesalahan 500. '.$e->getMessage());
            }

            $send = [
                'id'    => $find->id,
                'banksoal_id' => $find->banksoal_id,
                'soal_id' => $find->soal_id,
                'jawab' => $find->jawab,
                'jawab_complex' => json_decode($find->jawab_complex, true),
                'esay' => $request->esay,
                'ragu_ragu' => $find->ragu_ragu,
            ];

            return response()->json(['data' => $send,'index' => $request->index]);
        }

        // Jika yang dikirimkan adalah isian singkat
        if(isset($request->isian)) {
            // $jwb_soals = JawabanSoal::where('soal_id', $find->soal_id)->get();
            $jwb_soals = DB::table('jawaban_soals')
                ->where('soal_id', $find->soal_id)
                ->get();

            foreach($jwb_soals as $jwb) {
                $jwb_strip = strip_tags($jwb->text_jawaban);
                if (trim($jwb_strip) == trim($request->isian)) {
                    $find->iscorrect = 1;
                    break;
                }
                $find->iscorrect = 0;
            }

            try {
                DB::table('jawaban_pesertas')
                    ->where('id', $find->id)
                    ->update([
                        'iscorrect' => $find->iscorrect,
                        'esay'      => $request->isian,
                    ]);
            } catch (\Exception $e) {
                return SendResponse::internalServerError('Terjadi kesalahan 500. '.$e->getMessage());
            }

            // $find->esay = $request->isian;
            // $find->save();

            $send = [
                'id'    => $find->id,
                'banksoal_id' => $find->banksoal_id,
                'soal_id' => $find->soal_id,
                'jawab' => $find->jawab,
                'jawab_complex' => json_decode($find->jawab_complex, true),
                // 'jawab_complex' => $find->jawab_complex,
                'esay' => $find->esay,
                'ragu_ragu' => $find->ragu_ragu,
            ];

            return response()->json(['data' => $send,'index' => $request->index]);
        }

        // Jika yang dikirimkan adalah jawaban komleks
        if(is_array($request->jawab_complex)) {
            $soal_complex = Soal::with(['jawabans' => function($query) {
                $query->where('correct', 1);
            }])
            ->where("id", $find->soal_id)->first();
            if ($soal_complex) {
                $array = $soal_complex->jawabans->map(function($item){
                    return $item->id;
                })->toArray();
                $correct = 0;
                $complex = array_diff( $request->jawab_complex, [0] );
                if (array_diff($array,$complex) == array_diff($complex,$array)) {
                    $correct = 1;
                }
                $find->iscorrect = $correct;
            }

            try {
                DB::table('jawaban_pesertas')
                    ->where('id', $find->id)
                    ->update([
                        'jawab_complex' => json_encode($request->jawab_complex),
                        'iscorrect'     => $find->iscorrect,
                    ]);
                $find->jawab_complex = json_encode($request->jawab_complex);
            } catch (\Exception $e) {
                return SendResponse::internalServerError('Terjadi kesalahan 500. '.$e->getMessage());
            }
            // $find->jawab_complex = json_encode($request->jawab_complex);
            // $find->save();
            $send = [
                'id'    => $find->id,
                'banksoal_id' => $find->banksoal_id,
                'soal_id' => $find->soal_id,
                'jawab' => $find->jawab,
                'jawab_complex' => json_decode($find->jawab_complex, true),
                // 'jawab_complex' => $find->jawab_complex,
                'esay' => $find->esay,
                'ragu_ragu' => $find->ragu_ragu,
            ];
            return response()->json(['data' => $send,'index' => $request->index]);
        }

        // Jika yang dikirimkan adalah pilihan ganda
        $kj = DB::table('jawaban_soals')
            ->where('id', $request->jawab)
            ->select('correct')
            ->first();
        if(!$kj) {
            $send = [
                'id'    => $find->id,
                'banksoal_id' => $find->banksoal_id,
                'soal_id' => $find->soal_id,
                'jawab' => $find->jawab,
                'jawab_complex' => json_decode($find->jawab_complex, true),
                // 'jawab_complex' => $find->jawab_complex,
                'esay' => $find->esay,
                'ragu_ragu' => $find->ragu_ragu,
            ];
            return response()->json(['data' => $send,'index' => $request->index]);
        }
        // $find->jawab = $request->jawab;
        // $find->iscorrect = $kj->correct;
        // $find->save();

        try {
            DB::table('jawaban_pesertas')
                ->where('id', $find->id)
                ->update([
                    'jawab'         => $request->jawab,
                    'iscorrect'     => $kj->correct,
                ]);
            $find->jawab = $request->jawab;
        } catch (\Exception $e) {
            return SendResponse::internalServerError('Terjadi kesalahan 500. '.$e->getMessage());
        }
        
        $send = [
            'id'    => $find->id,
            'banksoal_id' => $find->banksoal_id,
            'soal_id' => $find->soal_id,
            'jawab' => $find->jawab,
            'jawab_complex' => json_decode($find->jawab_complex, true),
            // 'jawab_complex' => $find->jawab_complex,
            'esay' => $find->esay,
            'ragu_ragu' => $find->ragu_ragu,
        ];
    	return response()->json(['data' => $send,'index' => $request->index]);

    }

    /**
     * Set ragu ragu in siswa
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @author shellrean <wandnak17@gmail.com>
     */
    public function setRagu(Request $request)
    {
        $peserta = request()->get('peserta-auth');

        $find = DB::table('jawaban_pesertas')
            ->where('id', $request->jawaban_id)
            ->first();

        if(!isset($request->ragu_ragu)) {
            return response()->json(['data' => $send,'index' => $request->index]);
        }

        $ujian = $this->_getUjianCurrent($peserta);
        
        if (!$ujian) {
            return SendResponse::badRequest('Kami tidak dapat menemukan ujian yang sedang anda kamu kerjakan, mungkin jadawl ini sedang tidak aktif. silakan logout lalu hubungi administrator.');
        }
        if($ujian) {
            $this->_kurangiWaktu($ujian);
        }

        try {
            DB::table('jawaban_pesertas')
                ->where('id', $find->id)
                ->update([
                    'ragu_ragu' => $request->ragu_ragu
                ]);
        } catch (\Exception $e) {
            return SendResponse::internalServerError('Terjadi kesalahan 500. '.$e->getMessage());
        }

        $send = $find->only('id','banksoal_id','soal_id','jawab','esay','ragu_ragu');

        return response()->json(['data' => $send,'index' => $request->index]);
    }

    /**
     * Selesaikan ujian
     *
     * @param Illuminate\Http\Request
     * @return Illuminate\Http\Response
     * @author shellrean <wandnak17@gmail.com>
     */
    public function selesai()
    {
        $peserta = request()->get('peserta-auth');

        $ujian = $this->_getUjianCurrent($peserta);

        // Cek apakah hasil ujian pernah di generate sebelumnya
        $hasilUjian = DB::table('hasil_ujians')
            ->where([
                'peserta_id'    => $peserta->id,
                'jadwal_id'     => $ujian->jadwal_id,
            ])
            ->count();

        if($hasilUjian > 0) {
            return SendResponse::badRequest('Ujian ini telah diselesaikan. silakan logout, laporkan perihal ini kepada andministrator');
        }

        $jawaban = DB::table('jawaban_pesertas')
            ->where([
                'jadwal_id'     => $ujian->jadwal_id,
                'peserta_id'    => $peserta->id
            ])
            ->select('banksoal_id')
            ->first();

        try {
            DB::beginTransaction();
            $finished = UjianService::finishingUjian($jawaban->banksoal_id, $ujian->jadwal_id, $peserta->id);
            if(!$finished['success']) {
                DB::rollback();
                return SendResponse::badRequest($finished['message']);
            }
            DB::table('siswa_ujians')
                ->where('id', $ujian->id)
                ->update([
                    'status_ujian'  => 1,
                ]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return SendResponse::internalServerError('Terjadi kesalahan 500. '.$e->getMessage());
        }
        return SendResponse::accept();
    }

    /**
     * Kurangi waktu siswa
     * @param object $ujian
     * @return object
     * @author shellrean <wandnak17@gmail.com>
     */
    private function _kurangiWaktu($ujian)
    {
        $deUjian = DB::table('jadwals')
            ->where('id', $ujian->jadwal_id)
            ->first();
        $start = Carbon::createFromFormat('H:i:s', $ujian->mulai_ujian_shadow);
        $now = Carbon::createFromFormat('H:i:s', Carbon::now()->format('H:i:s'));
        $diff_in_minutes = $start->diffInSeconds($now);

        try {
            DB::table('siswa_ujians')
                ->where('id', $ujian->id)
                ->update([
                    'sisa_waktu'    => $deUjian->lama-$diff_in_minutes
                ]);
        } catch (\Exception $e) {
            return SendResponse::internalServerError('Terjadi kesalahan 500. '.$e->getMessage());
        }
    }

    /**
     * Ambil ujian aktif saat ini 
     * @param object $peserta
     * @return object
     * @author shellrean <wandnak17@gmail.com>
     */
    private function _getUjianCurrent($peserta)
    {
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
        $ujian = DB::table('siswa_ujians')
            ->where('peserta_id', $peserta->id)
            ->where('status_ujian', 3)
            ->whereIn('jadwal_id', $jadwal_ids)
            ->whereDate('created_at', now()->format('Y-m-d'))
            ->first();

        return $ujian;
    }
}

