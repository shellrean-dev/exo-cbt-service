<?php

namespace App\Http\Controllers\Api\v1;

use App\Exports\CapaianPesertaMCUjianExport;
use App\Exports\CapaianPesertaUjianExport;
use App\Exports\LedgerPesertaHasilUjianExport;
use App\Models\dto\ResultDataTransform;
use App\Models\SoalConstant;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\URL;
use App\Exports\CapaianSiswaExport;
use Illuminate\Support\Facades\DB;
use App\Exports\HasilUjianExport;
use App\Actions\SendResponse;
use App\JawabanPeserta;
use App\Soal;
use Ramsey\Uuid\Uuid;

/**
 * ResultingController
 * @author shellrean <wandinak17@gmail.com>
 */
class ResultController extends Controller
{
    /**
     * @Route(path="api/v1/ujians/{jadwal}/result", methods={"GET"})
     *
     * Ambil data hasil ujian peserta
     *
     * @param string $jadwal_id
     * @return Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function exam(Request $request, $jadwal_id)
    {
        $jadwal = DB::table('jadwals')
            ->where('id', $jadwal_id)
            ->first();

        if (!$jadwal_id) {
            return SendResponse::badRequest('Kesalahan, data yang diminta tidak ditemukan');
        }

        $res = DB::table('hasil_ujians as t_0')
            ->join('pesertas as t_1', 't_1.id', '=','t_0.peserta_id')
            ->join('siswa_ujians as t_3', 't_3.id', '=', 't_0.ujian_id')
            ->leftJoin('group_members as t_2', 't_2.student_id', '=', 't_0.peserta_id');

        $jurusan = strval($request->get('jurusan',''));
        $group = strval($request->get('group',''));

        if (Uuid::isValid($jurusan)) {
            $res = $res->where('t_1.jurusan_id', $jurusan);
        }

        if (Uuid::isValid($group)) {
            $groupObj = DB::table('groups')
                ->where('id', $group)
                ->first();
            $parent_id = strval($groupObj->id);

            if (Uuid::isValid($parent_id)) {
                $childs = DB::table('groups')
                    ->where('parent_id', $parent_id)
                    ->select('id')
                    ->get()
                    ->pluck('id')
                    ->toArray();
                array_push($childs, $group);
                $res = $res->whereIn('t_2.group_id', $childs);
            } else {
                $res = $res->where('t_2.group_id', $group);
            }
        }
        $res = $res->where('t_0.jadwal_id', $jadwal->id)
            ->select([
                't_0.*',
                't_1.id as peserta_id',
                't_1.nama as peserta_nama',
                't_1.no_ujian as peserta_no_ujian',
                't_1.jurusan_id as peserta_jurusan_id',
                't_3.mulai_ujian',
                't_3.selesai_ujian'
            ])
            ->orderBy('t_1.no_ujian');

        if(request()->perPage != '') {
            $res = $res->paginate(request()->perPage);

            $res->getCollection()->transform([ResultDataTransform::class, 'resultExam']);
        } else {
            $res = $res->get()->map([ResultDataTransform::class, 'resultExam']);
        }

        return SendResponse::acceptData($res);
    }

    /**
     * @Route(path="api/v1/ujians/{jadwal}/result/link", methods={"GET"})
     *
     * Buat link untuk download excel hasil ujian
     * peserta
     *
     * @param string $jadwal_id
     * @return Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function examExcelLink($jadwal_id)
    {
        $jadwal = DB::table('jadwals')
            ->where('id', $jadwal_id)
            ->count();
        if (!$jadwal) {
            return SendResponse::badRequest('kesalahan, jadwal yang diminta tidak dapat ditemukan');
        }

        $jurusan = request()->q;
        $group = request()->group;
        $url = URL::temporarySignedRoute(
            'hasilujian.download.excel',
            now()->addMinutes(5),
            ['jadwal' => $jadwal_id, 'jurusan' => $jurusan, "group" => $group]
        );

        return SendResponse::acceptData($url);
    }

    /**
     * @Route(path="api/v1/ujians/{jadwal}/result/excel", methods={"GET"})
     *
     * Download excel hasil ujian peserta
     *
     * @param string $jadwal_id
     * @return Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function examExcel(Request $request, $jadwal_id)
    {
        if (! request()->hasValidSignature()) {
            return SendResponse::badRequest('Kesalahan, url tidak valid');
        }

        $jadwal = DB::table('jadwals')
            ->where('id', $jadwal_id)
            ->first();

        if (!$jadwal) {
            return SendResponse::badRequest('Kesalahan, jadwal yang diminta tidak valid');
        }

        $res = DB::table('hasil_ujians as t_0')
            ->join('pesertas as t_1', 't_1.id', '=','t_0.peserta_id')
            ->join('siswa_ujians as t_3', 't_3.id', '=', 't_0.ujian_id')
            ->leftJoin('group_members', 'group_members.student_id', '=', 't_0.peserta_id');

        $jurusan = strval($request->get('jurusan',''));
        $group = strval($request->get('group',''));

        if (Uuid::isValid($jurusan)) {
            $res = $res->where('t_1.jurusan_id', $jurusan);
        }

        if (Uuid::isValid($group)) {
            $groupObj = DB::table('groups')
                ->where('id', $group)
                ->first();
            $parent_id = strval($groupObj->id);

            if (Uuid::isValid($parent_id)) {
                $childs = DB::table('groups')
                    ->where('parent_id', $parent_id)
                    ->select('id')
                    ->get()
                    ->pluck('id')
                    ->toArray();
                array_push($childs, $group);
                $res = $res->whereIn('group_members.group_id', $childs);
            } else {
                $res = $res->where('group_members.group_id', $group);
            }
        }
        $res = $res->where('t_0.jadwal_id', $jadwal->id)
            ->select([
                't_0.*',
                't_1.id as peserta_id',
                't_1.nama as peserta_nama',
                't_1.no_ujian as peserta_no_ujian',
                't_1.jurusan_id as peserta_jurusan_id',
                't_3.mulai_ujian',
                't_3.selesai_ujian'
            ])
            ->orderBy('t_1.no_ujian')
            ->get()
            ->map([ResultDataTransform::class, 'resultExam']);

        $spreadsheet = HasilUjianExport::export($res,$jadwal->alias);
        $writer = new Xlsx($spreadsheet);

        $filename = 'Hasil ujian '.$jadwal->alias;
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.$filename.'.xlsx"');
        $writer->save('php://output');
    }

    /**
     * @Route(path="api/v1/ujians/{jadwal}/banksoal/{banksoal}/capaian-siswa", methods={"GET"})
     *
     * Ambil data capaian siswa hasil ujian
     *
     * @param string $jadwal_id
     * @param string $banksoal_id
     * @return Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function capaianSiswa($jadwal_id, $banksoal_id)
    {
        $jadwal = DB::table('jadwals')
            ->where('id', $jadwal_id)
            ->select('id')
            ->first();

        if (!$jadwal) {
            return SendResponse::badRequest('kesalahan, jadwal yang diminta tidak ditemukan');
        }

        $banksoal = DB::table('banksoals')
            ->where('id', $banksoal_id)
            ->select('id')
            ->first();

        if (!$banksoal) {
            return SendResponse::badRequest('kesalahan, banksoal yang diminta tidak ditemukan');
        }

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

    /**
     * @Route(path="api/v1/ujians/{jadwal}/banksoal/{banksoal}/capaian-siswa/link", methods={"GET"})
     *
     * Buat link untuk download excel capaian
     * siswa pada hasil ujian
     *
     * @param string $jadwal_id
     * @param string $banksoal_id
     * @return Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function capaianSiswaExcelLink($jadwal_id, $banksoal_id)
    {
        $jadwal = DB::table('jadwals')
            ->where('id', $jadwal_id)
            ->select('id')
            ->first();

        if (!$jadwal) {
            return SendResponse::badRequest('kesalahan, jadwal yang diminta tidak ditemukan');
        }

        $banksoal = DB::table('banksoals')
            ->where('id', $banksoal_id)
            ->select('id')
            ->first();

        if (!$banksoal) {
            return SendResponse::badRequest('kesalahan, banksoal yang diminta tidak ditemukan');
        }

        $jurusan = request()->q;
        $group = request()->group;
        $url = URL::temporarySignedRoute(
            'capaian.download.excel',
            now()->addMinutes(5),
            ['jadwal' => $jadwal->id, 'banksoal' => $banksoal->id,'jurusan' => $jurusan, 'group' => $group]
        );

        return SendResponse::acceptData($url);
    }

    /**
     * @Route(path="api/v1/ujians/{jadwal}/banksoal/{banksoal}/capaian-siswa-mc/link", methods={"GET"})
     *
     * Buat link untuk download excel capaian
     * siswa pada hasil ujian
     *
     * @param string $jadwal_id
     * @param string $banksoal_id
     * @return Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function capaianSiswaMCExcelLink($jadwal_id, $banksoal_id)
    {
        $jadwal = DB::table('jadwals')
            ->where('id', $jadwal_id)
            ->select('id')
            ->first();

        if (!$jadwal) {
            return SendResponse::badRequest('kesalahan, jadwal yang diminta tidak ditemukan');
        }

        $banksoal = DB::table('banksoals')
            ->where('id', $banksoal_id)
            ->select('id')
            ->first();

        if (!$banksoal) {
            return SendResponse::badRequest('kesalahan, banksoal yang diminta tidak ditemukan');
        }

        $jurusan = request()->q;
        $group = request()->group;
        $url = URL::temporarySignedRoute(
            'capaian.mc.download.excel',
            now()->addMinutes(5),
            ['jadwal' => $jadwal->id, 'banksoal' => $banksoal->id,'jurusan' => $jurusan, 'group' => $group]
        );

        return SendResponse::acceptData($url);
    }

    /**
     * @Route(path="api/v1/ujians/{jadwal}/banksoal/{banksoal}/capaian-siswa/excel", methods={"GET"})
     *
     * Download excel capaian
     * siswa pada hasil ujian
     *
     * @param string $jadwal_id
     * @param string $banksoal_id
     * @return Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function capaianSiswaExcel($jadwal_id, $banksoal_id)
    {
        $jadwal = DB::table('jadwals')
            ->where('id', $jadwal_id)
            ->select('id','alias')
            ->first();

        if (!$jadwal) {
            return SendResponse::badRequest('kesalahan, jadwal yang diminta tidak ditemukan');
        }

        $banksoal = DB::table('banksoals')
            ->where('id', $banksoal_id)
            ->select('id','kode_banksoal')
            ->first();

        if (!$banksoal) {
            return SendResponse::badRequest('kesalahan, banksoal yang diminta tidak ditemukan');
        }

        if (! request()->hasValidSignature()) {
            abort(401);
        }

        $jurusan = request()->jurusan;
        $group = request()->group;

        $soals = DB::table('soals')->where('banksoal_id', $banksoal->id)
            ->whereIn('tipe_soal', [
                SoalConstant::TIPE_PG,
                SoalConstant::TIPE_LISTENING,
                SoalConstant::TIPE_PG_KOMPLEK,
                SoalConstant::TIPE_MENJODOHKAN,
                SoalConstant::TIPE_ISIAN_SINGKAT,
                SoalConstant::TIPE_MENGURUTKAN,
                SoalConstant::TIPE_BENAR_SALAH
            ])
            ->orderBy('soals.tipe_soal')
            ->orderBy('soals.created_at')
            ->select(['id', 'tipe_soal'])
            ->get();

        $jawaban_pesertas = DB::table('jawaban_pesertas')->join('pesertas', 'pesertas.id', '=', 'jawaban_pesertas.peserta_id');
        $pesertas = DB::table('pesertas');


        if ($group != '0' && $group != '') {
            $groupObj = DB::table('groups')
                ->where('id', $group)
                ->first();
            if ($groupObj->parent_id == 0) {
                $childs = DB::table('groups')
                    ->where('parent_id', $group)
                    ->select('id')
                    ->get()
                    ->pluck('id')
                    ->toArray();
                array_push($childs, $group);
                $jawaban_pesertas = $jawaban_pesertas->join('group_members', 'group_members.student_id', '=', 'jawaban_pesertas.peserta_id')->whereIn('group_members.group_id', $childs);
                $pesertas = $pesertas->join('group_members', 'group_members.student_id', '=', 'pesertas.id')->whereIn('group_members.group_id', $childs);
            } else {
                $jawaban_pesertas = $jawaban_pesertas->join('group_members', 'group_members.student_id', '=', 'jawaban_pesertas.peserta_id')->where('group_members.group_id', $group);
                $pesertas = $pesertas->join('group_members', 'group_members.student_id', '=', 'pesertas.id')->where('group_members.group_id', $group);
            }
        }

        if ($jurusan != '0' && $jurusan != '') {
            $jurusan = explode(',',$jurusan);
            $jawaban_pesertas = $jawaban_pesertas->whereIn('pesertas.jurusan_id', $jurusan);
            $pesertas = $pesertas->whereIn('pesertas.jurusan_id', $jurusan);
        }

        $jawaban_pesertas = $jawaban_pesertas->select([
            'jawaban_pesertas.id',
            'jawaban_pesertas.soal_id',
            'jawaban_pesertas.peserta_id',
            'jawaban_pesertas.iscorrect',
            'jawaban_pesertas.answered'
        ])->get();

        $new_jawaban_peserta = [];
        foreach ($jawaban_pesertas as $jawaban) {
            $new_jawaban_peserta[$jawaban->soal_id.'|'.$jawaban->peserta_id] = $jawaban;
        }

        $pesertas = $pesertas->select([
            'pesertas.id',
            'pesertas.no_ujian',
            'pesertas.nama'
        ])->get();

        $data = [
            'pesertas' => $pesertas,
            'jawaban_pesertas' => $new_jawaban_peserta,
            'soals' => $soals
        ];

        $spreadsheet = CapaianPesertaUjianExport::run($data, $banksoal->kode_banksoal, $jadwal->alias);
        $writer = new Xlsx($spreadsheet);

        $filename = 'Capaian siswa '.$banksoal->kode_banksoal.' '.$jadwal->alias;
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.$filename.'.xlsx"');
        $writer->save('php://output');
    }

    /**
     * @Route(path="api/v1/ujians/{jadwal}/banksoal/{banksoal}/capaian-siswa-mc/excel", methods={"GET"})
     *
     * Download excel capaian
     * siswa pada hasil ujian
     *
     * @param string $jadwal_id
     * @param string $banksoal_id
     * @return Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function capaianSiswaMCExcel($jadwal_id, $banksoal_id)
    {
        $jadwal = DB::table('jadwals')
            ->where('id', $jadwal_id)
            ->select('id','alias')
            ->first();

        if (!$jadwal) {
            return SendResponse::badRequest('kesalahan, jadwal yang diminta tidak ditemukan');
        }

        $banksoal = DB::table('banksoals')
            ->where('id', $banksoal_id)
            ->select('id','kode_banksoal')
            ->first();

        if (!$banksoal) {
            return SendResponse::badRequest('kesalahan, banksoal yang diminta tidak ditemukan');
        }

        if (! request()->hasValidSignature()) {
            abort(401);
        }

        $jurusan = request()->jurusan;
        $group = request()->group;

        $soals = DB::table('soals')->where('banksoal_id', $banksoal->id)
            ->where('tipe_soal', SoalConstant::TIPE_PG)
            ->orderBy('soals.tipe_soal')
            ->orderBy('soals.created_at')
            ->select(['id', 'tipe_soal'])
            ->get();

        $jawaban_pesertas = DB::table('jawaban_pesertas')
            ->join('soals', 'soals.id', 'jawaban_pesertas.soal_id')
            ->join('pesertas', 'pesertas.id', '=', 'jawaban_pesertas.peserta_id')
            ->where('soals.tipe_soal', SoalConstant::TIPE_PG);
        $pesertas = DB::table('pesertas');

        if ($group != '0' && $group != '') {
            $groupObj = DB::table('groups')
                ->where('id', $group)
                ->first();
            if ($groupObj->parent_id == '' || $groupObj->parent_id == '0') {
                $childs = DB::table('groups')
                    ->where('parent_id', $group)
                    ->select('id')
                    ->get()
                    ->pluck('id')
                    ->toArray();
                array_push($childs, $group);
                $jawaban_pesertas = $jawaban_pesertas->join('group_members', 'group_members.student_id', '=', 'jawaban_pesertas.peserta_id')->whereIn('group_members.group_id', $childs);
                $pesertas = $pesertas->join('group_members', 'group_members.student_id', '=', 'pesertas.id')->whereIn('group_members.group_id', $childs);
            } else {
                $jawaban_pesertas = $jawaban_pesertas->join('group_members', 'group_members.student_id', '=', 'jawaban_pesertas.peserta_id')->where('group_members.group_id', $group);
                $pesertas = $pesertas->join('group_members', 'group_members.student_id', '=', 'pesertas.id')->where('group_members.group_id', $group);
            }
        }

        if ($jurusan != '0' && $jurusan != '') {
            $jurusan = explode(',',$jurusan);
            $jawaban_pesertas = $jawaban_pesertas->whereIn('pesertas.jurusan_id', $jurusan);
            $pesertas = $pesertas->whereIn('pesertas.jurusan_id', $jurusan);
        }

        $jawaban_pesertas = $jawaban_pesertas->select([
            'jawaban_pesertas.id',
            'jawaban_pesertas.jawab',
            'jawaban_pesertas.soal_id',
            'jawaban_pesertas.peserta_id',
            'jawaban_pesertas.iscorrect',
            'jawaban_pesertas.answered'
        ])->get();

        $new_jawaban_peserta = [];
        foreach ($jawaban_pesertas as $jawaban) {
            $new_jawaban_peserta[$jawaban->soal_id.'|'.$jawaban->peserta_id] = $jawaban;
        }

        $pesertas = $pesertas->select([
            'pesertas.id',
            'pesertas.no_ujian',
            'pesertas.nama'
        ])->get();

        $data = [
            'pesertas' => $pesertas,
            'jawaban_pesertas' => $new_jawaban_peserta,
            'soals' => $soals
        ];

        $spreadsheet = CapaianPesertaMCUjianExport::run($data, $banksoal->kode_banksoal, $jadwal->alias);
        $writer = new Xlsx($spreadsheet);

        $filename = 'Capaian siswa [MC]'.$banksoal->kode_banksoal.' '.$jadwal->alias;
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.$filename.'.xlsx"');
        $writer->save('php://output');
    }

    /**
     * @Route(path="api/v1/ujians/hasil/{hasil}", methods={"GET"})
     *
     * Abmil data hasil ujian peserta detail
     * dengan jawabannya
     *
     * @param string $hasil_id
     * @return Response
     * @author shellrean <wandinak17@gmial.com>
     */
    public function hasilUjianDetail($hasil_id)
    {
        $hasil = DB::table('hasil_ujians')
            ->where('id', $hasil_id)
            ->select('peserta_id', 'jadwal_id')
            ->first();

        if (!$hasil) {
            return SendResponse::badRequest('kesalahan, data yang diminta tidak dapat ditemukan');
        }

        $jawaban = JawabanPeserta::with(['peserta' => function($query) {
            $query->select('id','nama','no_ujian');
        },'esay_result','soal','soal.jawabans'])
        ->where([
            'peserta_id'    => $hasil->peserta_id,
            'jadwal_id'     => $hasil->jadwal_id
        ])
        ->get();

        $data = $jawaban->map([ResultDataTransform::class, 'resultUjianDetail']);
        return SendResponse::acceptData($data);
    }

    /**
     * @Route(path="ujians-ledger/{event_id}/{no_ujian}/link", methods={"GET"})
     *
     * Buat link untuk download excel hasil ujian
     * peserta
     *
     * @param string $jadwal_id
     * @return Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function hasilUjianLedgerPesertaLink($event_id, $no_ujian)
    {
        $event = DB::table('event_ujians')
            ->where('id', $event_id)
            ->count();
        if (!$event) {
            return SendResponse::badRequest('kesalahan, event yang diminta tidak dapat ditemukan');
        }

        $peserta = DB::table('pesertas')
            ->where('no_ujian', $no_ujian)
            ->first();
        if (!$peserta) {
            return SendResponse::badRequest('kesalahan, peserta yang diminta tidak dapat ditemukan');
        }

        $url = URL::temporarySignedRoute(
            'ledger.peserta.download.excel',
            now()->addMinutes(5),
            ['event_id' => $event_id, 'peserta_id' => $peserta->id]
        );

        return SendResponse::acceptData($url);
    }

    /**
     * @Route(path="ujians-ledger/{event_id}/{peserta_id}/excel", methods={"GET"})
     *
     * Download excel hasil ujian peserta ledger
     *
     * @param string $event_id
     * @param string $peserta_id
     * @return Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function hasilUjianLedgerPeserta($event_id, $peserta_id)
    {
        if (! request()->hasValidSignature()) {
            return SendResponse::badRequest('Kesalahan, url tidak valid');
        }

        $ujians = DB::table('jadwals')
            ->where('event_id', $event_id)
            ->select('id')
            ->get()
            ->pluck('id');
        $ujians = $ujians->toArray();

        $resulsts = DB::table('hasil_ujians as t_0')
            ->join('jadwals as t_1', 't_1.id', 't_0.jadwal_id')
            ->where('t_0.peserta_id', $peserta_id)
            ->whereIn('t_0.jadwal_id', $ujians)
            ->orderBy('t_0.created_at')
            ->select([
                't_0.*',
                't_1.alias'
            ])
            ->get();

        $peserta = DB::table('pesertas')->where('id', $peserta_id)->first();

        $spreadsheet = LedgerPesertaHasilUjianExport::export($resulsts, $peserta);
        $writer = new Xlsx($spreadsheet);

        $filename = 'LGR_'.$peserta->no_ujian.'_'.$peserta->nama;
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.$filename.'.xlsx"');
        $writer->save('php://output');
    }
}
