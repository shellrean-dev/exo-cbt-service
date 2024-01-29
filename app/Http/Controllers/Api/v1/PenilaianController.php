<?php

namespace App\Http\Controllers\Api\v1;

use App\Exports\JawabanPesertaExport;
use App\Http\Controllers\Controller;
use App\Imports\GroupMemberImport;
use App\Imports\JawabanPesertaEsayImport;
use App\Models\SoalConstant;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Actions\SendResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
        $user = request()->user();
        try {
            $exists = DB::select('SELECT distinct s.banksoal_id
                FROM jawaban_pesertas
                JOIN soals s on jawaban_pesertas.soal_id = s.id
                WHERE s.tipe_soal=2
                AND jawaban_pesertas.id not in
                (select penilaian_esay.jawab_id from penilaian_esay where penilaian_esay.banksoal_id=jawaban_pesertas.banksoal_id)');

            $exists = array_column($exists, 'banksoal_id');

            // Ambil banksoal yang ada
            $banksoal = DB::table('banksoals')
                ->whereIn('banksoals.id', $exists)
                ->join('matpels', 'banksoals.matpel_id', '=', 'matpels.id')
                ->select('banksoals.id', 'banksoals.kode_banksoal','matpels.nama as nama_matpel','matpels.correctors')
                ->get();

            // Filter banksoal untuk mencegah
            // dari selsain pengoreksi
            $filtered = $banksoal->reject(function ($value, $key) use($user) {
                $correctors = json_decode($value->correctors, true);
                if (count($correctors) < 1) {
                    return true;
                }

                return !in_array($user->id, $correctors);
            })->values();
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
            $user = request()->user();

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
            })->values();

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
        $user = request()->user();
        try {
            $banksoal = DB::table('banksoals')
                ->where('id', $banksoal_id)
                ->first();
            if (!$banksoal) {
                return SendResponse::badRequest('Tidak dapat menemukan banksoal yang diminta');
            }

            // Ambil jawaban yang telah dikoreksi
            $has = DB::table('penilaian_esay')
                ->where('banksoal_id', $banksoal->id)
                ->select('jawab_id')
                ->get()
                ->pluck('jawab_id');

            // Jawaban peserta yang belum dikoreksi
            $exists = DB::table('jawaban_pesertas')
                ->whereNotIn('jawaban_pesertas.id', $has)
                ->join('soals', 'jawaban_pesertas.soal_id','=','soals.id')
                ->where('soals.tipe_soal', SoalConstant::TIPE_ESAY)
                ->whereNotNull('jawaban_pesertas.esay')
                ->where('soals.banksoal_id', $banksoal->id)
                ->select('jawaban_pesertas.id','soals.banksoal_id','soals.audio','jawaban_pesertas.esay','soals.pertanyaan','soals.rujukan')
                ->paginate(30);
            return SendResponse::acceptData($exists);
        } catch (Exception $e) {
            return SendResponse::internalServerError('Kesalahan 500. '.$e->getMessage());
        }
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
        $jawab = DB::table('jawaban_pesertas as a')
            ->where('a.id', $request->id)
            ->join('banksoals as b', 'b.id', '=', 'a.banksoal_id')
            ->select([
                'a.id',
                'a.banksoal_id',
                'a.jadwal_id',
                'a.peserta_id',
                'b.persen',
                'b.jumlah_soal_esay'])
            ->first();

        if (!$jawab) {
            return SendResponse::badRequest('Tidak dapat menemukan data yang diminta');
        }

        $user = request()->user();

        $exists = DB::table('penilaian_esay')
            ->where('jawab_id', $request->id)
            ->first();
        if ($exists) {
            return SendResponse::badRequest('Esay telah diberi nilai sebelumnya');
        }

        $hasil = DB::table('hasil_ujians')->where([
            'banksoal_id'   => $jawab->banksoal_id,
            'jadwal_id'     => $jawab->jadwal_id,
            'peserta_id'    => $jawab->peserta_id,
        ])->first();

        # Hitung total pertanyaan
        $jml_esay =  $jawab->jumlah_soal_esay;

        $persen = json_decode($jawab->persen,true);

        # Esay nilai argument
        if($request->val != 0) {
            $point =  ($request->val/$jml_esay)*$persen['esay'];
            $hasil_esay = $hasil->point_esay + $point;
        } else {
            $hasil_esay = $hasil->point_esay;
        }

        try {
            DB::beginTransaction();
            DB::table('hasil_ujians')
                ->where('id', $hasil->id)
                ->update([
                    'point_esay'    => $hasil_esay
                ]);
            DB::table('penilaian_esay')->insert([
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

        $user = request()->user();

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


    /**
     * @Route(path="api/v1/ujians/esay/{banksoal}/koreksi-offline/link", methods={"GET"})
     *
     * @param $banksoal_id
     * @return \Illuminate\Http\Response
     */
    public function getJawabanPesertaEsayExcelLink($banksoal_id)
    {
        $banksoal = DB::table('banksoals')
            ->where('id', $banksoal_id)
            ->first();
        if (!$banksoal) {
            return SendResponse::badRequest('Banksoal tidak ditemukan');
        }

        $url = URL::temporarySignedRoute(
            'koreksi.offline.download.excel',
            now()->addMinutes(5),
            ['banksoal' => $banksoal_id]
        );

        return SendResponse::acceptData($url);
    }

    /**
     * @Route(path="api/v1/ujians/esay/{banksoal}/koreksi-offline/excel", methods={"GET"})
     *
     * @param Request $request
     * @param $banksoal_id
     * @return \Illuminate\Http\Response|void
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function jawabanPesertaEsayExcel(Request $request, $banksoal_id)
    {
        if (! request()->hasValidSignature()) {
            return SendResponse::badRequest('Kesalahan, url tidak valid');
        }

        $banksoal = DB::table('banksoals')
            ->where('id', $banksoal_id)
            ->first();

        if (!$banksoal) {
            return SendResponse::badRequest('Kesalahan, banksoal yang diminta tidak valid');
        }

        # Ambil jawaban yang telah dikoreksi
        $has = DB::table('penilaian_esay')
            ->where('banksoal_id', $banksoal->id)
            ->select('jawab_id')
            ->get()
            ->pluck('jawab_id');

        # Jawaban peserta yang belum dikoreksi
        $data = DB::table('jawaban_pesertas')
            ->whereNotIn('jawaban_pesertas.id', $has)
            ->join('soals', 'jawaban_pesertas.soal_id','=','soals.id')
            ->where('soals.tipe_soal', SoalConstant::TIPE_ESAY)
            ->whereNotNull('jawaban_pesertas.esay')
            ->where('soals.banksoal_id', $banksoal->id)
            ->select([
                'jawaban_pesertas.id',
                'soals.banksoal_id',
                'soals.audio',
                'jawaban_pesertas.esay',
                'soals.pertanyaan',
                'soals.rujukan'
            ])
            ->get();

        $spreadsheet = JawabanPesertaExport::export($data, $banksoal->kode_banksoal);
        $writer = new Xlsx($spreadsheet);

        $filename = 'Jawaban Esay Peserta Banksoal- '.$banksoal->kode_banksoal;
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.$filename.'.xlsx"');
        $writer->save('php://output');
    }

    /**
     * @Route (path="api/v1/ujians/esay/{banksoal}/koreksi-offline/upload", methods={"POST"})
     *
     * @param Request $request
     * @return \Illuminate\Http\Response|void
     */
    public function storeNilaiEsayExcel(Request $request)
    {
        $request->validate([
            'file'      => 'required|mimes:xlsx,xls'
        ]);

        try {
            $user = $request->user();
            Excel::import(new JawabanPesertaEsayImport($user), $request->file('file'));
        } catch (\Exception $e) {
            return SendResponse::internalServerError('kesalahan 500.'.$e->getMessage());
        }
    }
}
