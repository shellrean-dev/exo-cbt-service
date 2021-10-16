<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\SoalConstant;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Actions\SendResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * PenilaianController
 * @author shellrean <wandinak17@gmail.com>
 */
class PenilaianController extends Controller
{
    /**
     * @Route(path="api/v1/ujians/esay/exists", methods={"GET"})
     *
     * Ambil data jawaban esay peserta
     * yang belum dikoreksi
     *
     * @return \Illuminate\Http\Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function getExistEsay()
    {
        $user = request()->user('api');
        try {
            // Ambil semua jawaban yang telah dikoreksi
            $has = DB::table('jawaban_esays')
                ->select('jawab_id')
                ->get()
                ->pluck('jawab_id')
                ->unique();

            // Ambil banksoal yang digunakan oleh peserta esay
            $exists = DB::table('jawaban_pesertas')
                ->whereNotIn('jawaban_pesertas.id', $has)
                ->join('soals', 'jawaban_pesertas.soal_id','=','soals.id')
                ->where('soals.tipe_soal', '2')
                ->whereNotNull('jawaban_pesertas.esay')
                ->select('soals.banksoal_id')
                ->get()
                ->pluck('banksoal_id')
                ->unique();

            // Ambil banksoal yang ada
            $banksoal = DB::table('banksoals')
                ->whereIn('banksoals.id', $exists)
                ->join('matpels', 'banksoals.matpel_id', '=', 'matpels.id')
                ->select('banksoals.id', 'banksoals.kode_banksoal','matpels.nama as nama_matpel','matpels.correctors')
                ->get();

            // Filter banksoal untuk mencegah
            // dari selsain pengoreksi
            $filtered = $banksoal->reject(function ($value, $key) use($user) {
                return !in_array($user->id, json_decode($value->correctors, true));
            });
        } catch (Exception $e) {
            return SendResponse::internalServerError('Kesalahan 500. '.$e->getMessage());
        }

        return SendResponse::acceptData($filtered);
    }

    /**
     * @Route(path="api/v1/ujians/argument/exists", methods={"GET"})
     *
     * Ambil data jawaban setuju/tidak peserta
     * yang belum dikoreksi
     *
     * @return \Illuminate\Http\Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function getBanksoalExistArgument()
    {
        try {
            $user = request()->user('api');

            # Ambil semua jawaban yang telah dikoreksi
            $has = DB::table('penilaian_argument')
                ->select('jawab_id')
                ->get()
                ->pluck('jawab_id')
                ->unique();

            # Ambil banksoal yang digunakan oleh peserta tipe argumen
            $exists = DB::table('jawaban_pesertas')
                ->whereNotIn('jawaban_pesertas.id', $has)
                ->join('soals', 'jawaban_pesertas.soal_id','=','soals.id')
                ->where('soals.tipe_soal', '9')
                ->where('jawaban_pesertas.setuju_tidak', '!=', '[]')
                ->select('soals.banksoal_id')
                ->get()
                ->pluck('banksoal_id')
                ->unique();

            # Ambil banksoal yang ada
            $banksoal = DB::table('banksoals')
                ->whereIn('banksoals.id', $exists)
                ->join('matpels', 'banksoals.matpel_id', '=', 'matpels.id')
                ->select('banksoals.id', 'banksoals.kode_banksoal','matpels.nama as nama_matpel','matpels.correctors')
                ->get();

            # Hanya pengoreksi yang dapat data ini
            $filtered = $banksoal->reject(function ($value, $key) use($user) {
                return !in_array($user->id, json_decode($value->correctors, true));
            });

            return SendResponse::acceptData($filtered);
        } catch (Exception $e) {
            return SendResponse::internalServerError('Kesalahan 500. ['.$e->getMessage().']');
        }
    }

    /**
     * @Route(path="api/v1/ujians/esay/{banksoal}/koreksi", methods={"GET"})
     *
     * Ambil data jawaban esay peserta
     * dari banksoal yang diminta
     *
     * @param string $banksoal_id
     * @return \Illuminate\Http\Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function getExistEsayByBanksoal($banksoal_id)
    {
        try {
            $banksoal = DB::table('banksoals')
                ->where('id', $banksoal_id)
                ->first();
            if (!$banksoal) {
                return SendResponse::badRequest('Tidak dapat menemukan banksoal yang diminta');
            }

            // Ambil jawaban yang telah dikoreksi
            $has = DB::table('jawaban_esays')
                ->where('banksoal_id', $banksoal->id)
                ->select('jawab_id')
                ->get()
                ->pluck('jawab_id');

            // Jawaban peserta yang belum dikoreksi
            $exists = DB::table('jawaban_pesertas')
                ->whereNotIn('jawaban_pesertas.id', $has)
                ->join('soals', 'jawaban_pesertas.soal_id','=','soals.id')
                ->where('soals.tipe_soal', '2')
                ->whereNotNull('jawaban_pesertas.esay')
                ->where('soals.banksoal_id', $banksoal->id)
                ->select('jawaban_pesertas.id','soals.banksoal_id','soals.audio','jawaban_pesertas.esay','soals.pertanyaan','soals.rujukan')
                ->paginate(30);
        } catch (Exception $e) {
            return SendResponse::internalServerError('Kesalahan 500. '.$e->getMessage());
        }

        return SendResponse::acceptData($exists);
    }

    /**
     * @Route(path="api/v1/ujians/argument/{banksoal}/koreksi", methods={"GET"})
     *
     * Ambil data jawaban argument peserta
     * dari banksoal yang diminta
     *
     * @param string $banksoal_id
     * @return \Illuminate\Http\Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function getExistArgumentByBanksoal($banksoal_id)
    {
        try {
            $banksoal = DB::table('banksoals')
                ->where('id', $banksoal_id)
                ->first();
            if (!$banksoal) {
                return SendResponse::badRequest('Tidak dapat menemukan banksoal yang diminta');
            }

            # Get jawabans soal
            $jawaban_soal = DB::table('jawaban_soals as a')
                ->join('soals as s','s.id','=','a.soal_id')
                ->select(['a.id','a.soal_id','a.text_jawaban', 's.pertanyaan'])
                ->where([
                    's.banksoal_id' => $banksoal_id,
                    's.tipe_soal' => SoalConstant::TIPE_SETUJU_TIDAK
                ])
                ->get();

            # Ambil jawaban yang telah dikoreksi
            $has = DB::table('penilaian_argument')
                ->where('banksoal_id', $banksoal->id)
                ->select('jawab_id')
                ->get()
                ->pluck('jawab_id');

            # Jawaban peserta yang belum dikoreksi
            $exists = DB::table('jawaban_pesertas')
                ->whereNotIn('jawaban_pesertas.id', $has)
                ->join('soals', 'jawaban_pesertas.soal_id','=','soals.id')
                ->where('soals.tipe_soal', '9')
                ->where('jawaban_pesertas.setuju_tidak', '!=', '[]')
                ->where('soals.banksoal_id', $banksoal->id)
                ->select(
                    'jawaban_pesertas.id',
                    'soals.banksoal_id',
                    'soals.audio',
                    'jawaban_pesertas.setuju_tidak',
                    'soals.pertanyaan'
                )
                ->paginate(30);

            $exists->getCollection()->transform(function ($item) use ($jawaban_soal) {
                $item->setuju_tidak = json_decode($item->setuju_tidak, true);

                foreach ($item->setuju_tidak as $k => $v) {
                    $item->setuju_tidak[$k]['detil'] = $jawaban_soal->where('id', $k)->first();
                }
                return $item;
            });

            return SendResponse::acceptData($exists);
        } catch (Exception $e) {
            return SendResponse::internalServerError('Kesalahan 500. ['.$e->getMessage().']');
        }
    }

    /**
     * @Route(path="api/v1/ujians/esay/input", methods={"POST"})
     *
     * Simpan nilai esay
     *
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function storeNilaiEsay(Request $request)
    {
        $request->validate([
            'val'   => 'required|numeric|min:0|max:1',
            'id'    => 'required'
        ]);

        $jawab = DB::table('jawaban_pesertas')
            ->where('jawaban_pesertas.id', $request->id)
            ->join('banksoals', 'jawaban_pesertas.banksoal_id', '=', 'banksoals.id')
            ->select('jawaban_pesertas.id', 'jawaban_pesertas.esay','jawaban_pesertas.banksoal_id','jawaban_pesertas.soal_id','jawaban_pesertas.jadwal_id','banksoals.jumlah_soal','banksoals.jumlah_soal_listening','banksoals.jumlah_soal_ganda_kompleks','banksoals.jumlah_isian_singkat','banksoals.jumlah_soal_esay','banksoals.persen','jawaban_pesertas.peserta_id')
            ->first();

        if (!$jawab) {
            return SendResponse::badRequest('Tidak dapat menemukan data yang diminta');
        }

        $user = request()->user('api');

        $has = DB::table('jawaban_esays')
            ->where('banksoal_id', $jawab->banksoal_id)
            ->select('jawab_id')
            ->pluck('jawab_id');

        $sames = DB::table('jawaban_pesertas')
            ->whereNotIn('jawaban_pesertas.id', $has)
            ->where('jawaban_pesertas.esay', $jawab->esay)
            ->where('jawaban_pesertas.banksoal_id', $jawab->banksoal_id)
            ->where('jawaban_pesertas.soal_id', $jawab->soal_id)
            ->join('banksoals', 'jawaban_pesertas.banksoal_id', '=', 'banksoals.id')
            ->select('jawaban_pesertas.id', 'jawaban_pesertas.esay','jawaban_pesertas.banksoal_id','jawaban_pesertas.soal_id','jawaban_pesertas.jadwal_id','banksoals.jumlah_soal','banksoals.jumlah_soal_listening','banksoals.jumlah_soal_ganda_kompleks','banksoals.jumlah_isian_singkat','banksoals.jumlah_soal_esay','banksoals.persen', 'jawaban_pesertas.peserta_id')
            ->get();

        if ($sames->count() > 1) {
            foreach($sames as $same) {
                $hasil = DB::table('hasil_ujians')->where([
                    'banksoal_id'   => $same->banksoal_id,
                    'jadwal_id'     => $same->jadwal_id,
                    'peserta_id'    => $same->peserta_id,
                ])->first();

                // Hitung total pertanyaan
                $pg_jmlh = $same->jumlah_soal;
                $listening_jmlh = $same->jumlah_soal_listening;
                $complex_jmlh = $same->jumlah_soal_ganda_kompleks;
                $isian_singkat_jmlh = $same->jumlah_isian_singkat;
                $jml_esay =  $same->jumlah_soal_esay;

                $persen = json_decode($same->persen,true);

                // Hitung hasil listening
                $hasil_listening = 0;
                if($hasil->jumlah_benar_listening > 0) {
                    $hasil_listening = ($hasil->jumlah_benar_listening/$listening_jmlh)*$persen['listening'];
                }

                // Hitung hasil pilihan ganda
                $hasil_pg = 0;
                if($hasil->jumlah_benar > 0) {
                    $hasil_pg = ($hasil->jumlah_benar/$pg_jmlh)*$persen['pilihan_ganda'];
                }

                // Hitung hasil pilihan ganda complex
                $hasil_complex = 0;
                if($hasil->jumlah_benar_complek > 0) {
                    $hasil_complex = ($hasil->jumlah_benar_complek/$complex_jmlh)*$persen['pilihan_ganda_komplek'];
                }

                // Hitung hasil isian singkat
                $hasil_isian_singkat = 0;
                if($hasil->jumlah_benar_isian_singkat > 0) {
                    $hasil_isian_singkat = ($hasil->jumlah_benar_isian_singkat/$isian_singkat_jmlh)*$persen['isian_singkat'];
                }

                $hasil_ganda = $hasil_listening+$hasil_pg+$hasil_complex+$hasil_isian_singkat;

                if($request->val != 0) {
                    $hasil_esay = $hasil->point_esay + ($request->val/$jml_esay);
                } else {
                    $hasil_esay = $hasil->point_esay;
                }

                $hasil_val = ($hasil_ganda)+($hasil_esay*$persen['esay']);

                try {
                    DB::beginTransaction();
                    DB::table('hasil_ujians')
                        ->where('id', $hasil->id)
                        ->update([
                            'point_esay'    => $hasil_esay,
                            'hasil'         => $hasil_val,
                        ]);

                    DB::table('jawaban_esays')->insert([
                        'id'            => Str::uuid()->toString(),
                        'banksoal_id'   => $same->banksoal_id,
                        'peserta_id'    => $same->peserta_id,
                        'jawab_id'      => $same->id,
                        'corrected_by'  => $user->id,
                        'point'         => $request->val,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);

                    DB::commit();
                    return SendResponse::accept();
                } catch (Exception $e) {
                    DB::rollBack();
                    return SendResponse::internalServerError('Kesalahan 500.'.$e->getMessage());
                }
            }
        }

        $hasil = DB::table('hasil_ujians')->where([
            'banksoal_id'   => $jawab->banksoal_id,
            'jadwal_id'     => $jawab->jadwal_id,
            'peserta_id'    => $jawab->peserta_id,
        ])->first();

        // Hitung total pertanyaan
        $pg_jmlh = $jawab->jumlah_soal;
        $listening_jmlh = $jawab->jumlah_soal_listening;
        $complex_jmlh = $jawab->jumlah_soal_ganda_kompleks;
        $isian_singkat_jmlh = $jawab->jumlah_isian_singkat;
        $jml_esay =  $jawab->jumlah_soal_esay;

        $persen = json_decode($jawab->persen,true);

        // Hitung hasil listening
        $hasil_listening = 0;
        if($hasil->jumlah_benar_listening > 0) {
            $hasil_listening = ($hasil->jumlah_benar_listening/$listening_jmlh)*$persen['listening'];
        }

        // Hitung hasil pilihan ganda
        $hasil_pg = 0;
        if($hasil->jumlah_benar > 0) {
            $hasil_pg = ($hasil->jumlah_benar/$pg_jmlh)*$persen['pilihan_ganda'];
        }

        // Hitung hasil pilihan ganda complex
        $hasil_complex = 0;
        if($hasil->jumlah_benar_complek > 0) {
            $hasil_complex = ($hasil->jumlah_benar_complek/$complex_jmlh)*$persen['pilihan_ganda_komplek'];
        }

        // Hitung hasil isian singkat
        $hasil_isian_singkat = 0;
        if($hasil->jumlah_benar_isian_singkat > 0) {
            $hasil_isian_singkat = ($hasil->jumlah_benar_isian_singkat/$isian_singkat_jmlh)*$persen['isian_singkat'];
        }

        $hasil_ganda = $hasil_listening+$hasil_pg+$hasil_complex+$hasil_isian_singkat;

        if($request->val != 0) {
            $hasil_esay = $hasil->point_esay + ($request->val/$jml_esay);
        } else {
            $hasil_esay = $hasil->point_esay;
        }

        $hasil_val = ($hasil_ganda)+($hasil_esay*$persen['esay']);

        try {
            DB::beginTransaction();
            DB::table('hasil_ujians')
                ->where('id', $hasil->id)
                ->update([
                    'point_esay'    => $hasil_esay,
                    'hasil'         => $hasil_val,
                ]);
            DB::table('jawaban_esays')->insert([
                'id'            => Str::uuid()->toString(),
                'banksoal_id'   => $jawab->banksoal_id,
                'peserta_id'    => $jawab->peserta_id,
                'jawab_id'      => $jawab->id,
                'corrected_by'  => $user->id,
                'point'         => $request->val,
                'created_at'    => now(),
                'updated_at'    => now()
            ]);

            DB::commit();
            return SendResponse::accept();
        } catch (Exception $e) {
            DB::rollBack();
            return SendResponse::internalServerError('Kesalahan 500.'.$e->getMessage());
        }
    }

    /**
     * @Route(path="api/v1/ujians/argument/input", methods={"POST"})
     *
     * Simpan nilai argument
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function storeNilaiArgument(Request $request)
    {
        $request->validate([
            'val'   => 'required|numeric|min:0|max:1',
            'id'    => 'required'
        ]);

        $jawab = DB::table('jawaban_pesertas as a')
            ->where('a.id', $request->id)
            ->join('banksoals as b', 'b.id', '=', 'a.banksoal_id')
            ->select([
                'a.id',
                'a.banksoal_id',
                'a.jadwal_id',
                'a.peserta_id',
                'b.persen',
                'b.jumlah_setuju_tidak'])
            ->first();

        if (!$jawab) {
            return SendResponse::badRequest('Tidak dapat menemukan data yang diminta');
        }

        $user = request()->user('api');

        $exists = DB::table('penilaian_argument')
            ->where('jawab_id', $request->id)
            ->first();
        if ($exists) {
            return SendResponse::badRequest('Argument telah diberi nilai sebelumnya');
        }

        $hasil = DB::table('hasil_ujians')->where([
            'banksoal_id'   => $jawab->banksoal_id,
            'jadwal_id'     => $jawab->jadwal_id,
            'peserta_id'    => $jawab->peserta_id,
        ])->first();

        # Hitung total pertanyaan
        $jml_setuju_tidak =  $jawab->jumlah_setuju_tidak;

        $persen = json_decode($jawab->persen,true);

        # Penghitungan nilai argument
        if($request->val != 0) {
            $point =  ($request->val/$jml_setuju_tidak)*$persen['setuju_tidak'];
            $hasil_argument = $hasil->point_setuju_tidak + $point;
        } else {
            $hasil_argument = $hasil->point_setuju_tidak;
        }

        try {
            DB::beginTransaction();
            DB::table('hasil_ujians')
                ->where('id', $hasil->id)
                ->update([
                    'point_setuju_tidak'    => $hasil_argument
                ]);
            DB::table('penilaian_argument')->insert([
                'id'            => Str::uuid()->toString(),
                'banksoal_id'   => $jawab->banksoal_id,
                'peserta_id'    => $jawab->peserta_id,
                'jawab_id'      => $jawab->id,
                'corrected_by'  => $user->id,
                'point'         => $request->val,
                'created_at'    => now(),
                'updated_at'    => now()
            ]);

            DB::commit();
            return SendResponse::accept();
        } catch (Exception $e) {
            DB::rollBack();
            return SendResponse::internalServerError('Kesalahan 500.'.$e->getMessage());
        }
    }
}
