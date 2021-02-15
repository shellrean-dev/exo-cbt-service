<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
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
     * Store data ujian to table
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

        $find = JawabanPeserta::where([
            'id'            => $request->jawaban_id
        ])->first();

        $kj = JawabanSoal::find($request->jawab);

        $ujian = SiswaUjian::where(function($query) use($peserta) {
            $query->where('peserta_id', $peserta->id)
            ->where('status_ujian','=',3);
        })->first();

        if($ujian) {
            UjianService::kurangiSisaWaktu($ujian);
        }

        if(isset($request->essy)) {
            $find->esay = $request->essy;
            $find->save();

            $send = [
                'id'    => $find->id,
                'banksoal_id' => $find->banksoal_id,
                'soal_id' => $find->soal_id,
                'jawab' => $find->jawab,
                'jawab_complex' => json_decode($find->jawab_complex, true),
                'esay' => $find->esay,
                'ragu_ragu' => $find->ragu_ragu,
            ];

            return response()->json(['data' => $send,'index' => $request->index]);
        }

        if(isset($request->isian)) {
            $jwb_soals = JawabanSoal::where('soal_id', $find->soal_id)->get();
            foreach($jwb_soals as $jwb) {
                $jwb_strip = strip_tags($jwb->text_jawaban);
                if (trim($jwb_strip) == trim($request->isian)) {
                    $find->iscorrect = 1;
                    break;
                }
                $find->iscorrect = 0;
            }

            $find->esay = $request->isian;
            $find->save();

            $send = [
                'id'    => $find->id,
                'banksoal_id' => $find->banksoal_id,
                'soal_id' => $find->soal_id,
                'jawab' => $find->jawab,
                'jawab_complex' => json_decode($find->jawab_complex, true),
                'esay' => $find->esay,
                'ragu_ragu' => $find->ragu_ragu,
            ];

            return response()->json(['data' => $send,'index' => $request->index]);
        }

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
            $find->jawab_complex = json_encode($request->jawab_complex);
            $find->save();
            $send = [
                'id'    => $find->id,
                'banksoal_id' => $find->banksoal_id,
                'soal_id' => $find->soal_id,
                'jawab' => $find->jawab,
                'jawab_complex' => json_decode($find->jawab_complex, true),
                'esay' => $find->esay,
                'ragu_ragu' => $find->ragu_ragu,
            ];
            return response()->json(['data' => $send,'index' => $request->index]);
        }

        if(!$kj) {
            $send = [
                'id'    => $find->id,
                'banksoal_id' => $find->banksoal_id,
                'soal_id' => $find->soal_id,
                'jawab' => $find->jawab,
                'jawab_complex' => json_decode($find->jawab_complex, true),
                'esay' => $find->esay,
                'ragu_ragu' => $find->ragu_ragu,
            ];
            return response()->json(['data' => $send,'index' => $request->index]);
        }
        $find->jawab = $request->jawab;
        $find->iscorrect = $kj->correct;
        $find->save();
        $send = [
            'id'    => $find->id,
            'banksoal_id' => $find->banksoal_id,
            'soal_id' => $find->soal_id,
            'jawab' => $find->jawab,
            'jawab_complex' => json_decode($find->jawab_complex, true),
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

        $find = JawabanPeserta::where([
            'id'            => $request->jawaban_id
        ])->first();

        if(!isset($request->ragu_ragu)) {
            return response()->json(['data' => $send,'index' => $request->index]);
        }

        $ujian = SiswaUjian::where(function($query) use($peserta) {
            $query->where('peserta_id', $peserta->id)
            ->where('status_ujian','=',3);
        })->first();

        if($ujian) {
            UjianService::kurangiSisaWaktu($ujian);
        }

        $find->ragu_ragu = $request->ragu_ragu;
        $find->save();

        $send = $find->only('id','banksoal_id','soal_id','jawab','esay','ragu_ragu');

        return response()->json(['data' => $send,'index' => $request->index]);
    }

    /**
     * Finish
     *
     * @param Illuminate\Http\Request
     * @return Illuminate\Http\Response
     */
    public function selesai()
    {
        $peserta = request()->get('peserta-auth');

        $ujian = SiswaUjian::where(function($query) use($peserta) {
            $query->where('peserta_id', $peserta->id)
            ->where('status_ujian','=',3);
        })->first();


        $hasilUjian = HasilUjian::where([
            'peserta_id'    => $peserta->id,
            'jadwal_id'     => $ujian->jadwal_id,
        ])->first();

        if($hasilUjian) {
            return SendResponse::accept();
        }

        $jawaban = JawabanPeserta::where([
            'jadwal_id'     => $ujian->jadwal_id,
            'peserta_id'    => $peserta->id
        ])->first();

        $finished = UjianService::finishingUjian($jawaban->banksoal_id, $ujian->jadwal_id, $peserta->id);
        if(!$finished['success']) {
            return SendResponse::badRequest($finished['message']);
        }
        $ujian->status_ujian = 1;
        $ujian->save();
        return SendResponse::accept();
    }
}

