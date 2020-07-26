<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use App\Services\UjianService;
use App\Actions\SendResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\JawabanPeserta;
use App\SiswaUjian;
use Carbon\Carbon;
use App\Banksoal;
use App\Jadwal;
use App\Token;

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
            'jadwal_id'     => 'required|exists:jadwals,id',
            'token'         => 'required'
        ]);

        $ujian = Jadwal::find($request->jadwal_id);
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

        $peserta = request()->get('peserta-auth');

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

        $data = SiswaUjian::where(function($query) use($peserta) {
            $query->where('peserta_id', $peserta->id)
            ->where('status_ujian','=',0);
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
    public function getJawabanPeserta()
    {
        $peserta = request()->get('peserta-auth');

        $data = SiswaUjian::where(function($query) use($peserta) {
            $query->where('peserta_id', $peserta->id)
            ->where('status_ujian','=',3);
        })->first();

        if(!$data) {
            return SendResponse::badRequest('Anda memasuki ujian ini secara ilegal');
        }

        $jadwal = Jadwal::find($data->jadwal_id);

        $ids = array_column($jadwal->banksoal_id, 'jurusan','id');
        $ids = collect($ids)->keys();
        
        $bks = Banksoal::with('matpel','matpel')->whereIn('id', $ids)->get();
        $banksoal_id = '';

        foreach($bks as $bk) {
            $banksoal = UjianService::getBanksoalPeserta($bk, $peserta);
            if(!$banksoal['success']) {
                continue;
            }
            $banksoal_id = $banksoal['data']; 
        }

        if($banksoal_id == '') {
            return SendResponse::badRequest('Anda tidak mendapat banksoal yang sesuai');
        }

        $id = $banksoal_id;
        $jadwal_id = $data->jadwal_id;
        $user_id = $peserta->id;
        
        $find = UjianService::getJawabanPeserta($jadwal_id, $user_id);

        if ($find->count() < 1 ) {

            $all = Banksoal::with(['pertanyaans','pertanyaans.jawabans'])->where('id',$id)->first();

            $max_soal = $all->jumlah_soal;
            $max_essay = $all->jumlah_soal_esay;
            $i = 1;

            foreach($all->pertanyaans as $p) {
                if($p->tipe_soal != 3) {
                    continue;
                }
                JawabanPeserta::create([
                    'peserta_id'    => $user_id, 
                    'banksoal_id'   => $id, 
                    'soal_id'       => $p->id, 
                    'jawab'         => 0, 
                    'iscorrect'     => 0,
                    'jadwal_id'     => $jadwal_id,
                    'ragu_ragu'     => 0,
                    'esay'          => ''
                ]);
                if ($i++ == $max_soal) break;
            }

            $collection = $all->pertanyaans;
            $perta = $collection->shuffle();

            if($perta != null) {
                foreach($perta as $p) {
                    if($p->tipe_soal != 1) {
                        continue;
                    }
                    JawabanPeserta::create([
                        'peserta_id'    => $user_id, 
                        'banksoal_id'   => $id, 
                        'soal_id'       => $p->id, 
                        'jawab'         => 0, 
                        'iscorrect'     => 0,
                        'jadwal_id'     => $jadwal_id,
                        'ragu_ragu'     => 0,
                        'esay'          => ''
                    ]);

                    if ($i++ == $max_soal) break;
                }
            }

            if ($max_essay != null && $max_essay > 0) {
                foreach($perta as $p) {
                    if($p->tipe_soal != 2) {
                        continue;
                    }
                    
                    JawabanPeserta::create([
                        'peserta_id'    => $user_id, 
                        'banksoal_id'   => $id, 
                        'soal_id'       => $p->id, 
                        'jawab'         => 0, 
                        'iscorrect'     => 0,
                        'jadwal_id'     => $jadwal_id,
                        'ragu_ragu'     => 0,
                        'esay'          => ''
                    ]);
    
                    if ($i++ == $max_essay) break;
                }
            }

            $find = UjianService::getJawabanPeserta($jadwal_id, $user_id);

            return response()->json(['data' => $find, 'detail' => $data]);
        }
        
        $ujian = SiswaUjian::where([
            'jadwal_id'     => $jadwal_id,
            'peserta_id'    => $user_id
        ])->first();

        $deUjian = Jadwal::find($jadwal_id);

        $start = Carbon::createFromFormat('H:i:s', $ujian->mulai_ujian);
        $now = Carbon::createFromFormat('H:i:s', Carbon::now()->format('H:i:s'));

        $diff_in_minutes = $start->diffInSeconds($now);

        if($diff_in_minutes > $deUjian->lama) {
    
            $ujian->status_ujian = 1;
            $ujian->save();
            
            $finished = UjianService::finishingUjian($id, $jadwal_id, $user_id);
            if(!$finished['success']) {
                return SendResponse::badRequest($finished['message']);
            }
            
            return response()->json(['data' => $find, 'detail' => $ujian]);
        }
        
        $ujian->sisa_waktu = $deUjian->lama-$diff_in_minutes;
        $ujian->save();

        return response()->json(['data' => $find, 'detail' => $ujian]);
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
