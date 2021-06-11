<?php

namespace App\Http\Controllers\Api\v1;

use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\URL;
use App\Exports\CapaianSiswaExport;
use Illuminate\Support\Facades\DB;
use App\Exports\HasilUjianExport;
use App\Actions\SendResponse;
use App\JawabanPeserta;
use App\HasilUjian;
use App\Soal;

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
     * @return App\Actions\SendResponse
     * @author shellrean <wandinak17@gmail.com>
     */
    public function exam($jadwal_id)
    {
        $jadwal = DB::table('jadwals')
            ->where('id', $jadwal_id)
            ->first();

        if (!$jadwal_id) {
            return SendResponse::badRequest('Kesalahan, data yang diminta tidak ditemukan');
        }
        
        $res = HasilUjian::with(['peserta' => function ($query) {
            $query->select('id','nama','no_ujian');
        }]);

        $jurusan = request()->jurusan;
        $group = request()->group;

        if ($jurusan != 0 && $jurusan != '') {
            $res->whereHas('peserta', function($query) use ($jurusan) {
                $query->where('jurusan_id', $jurusan);
            });
        }

        if ($group != 0 && $group != '') {
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
                $res->whereHas('group', function($query) use ($childs) {
                    $query->whereIn('group_id', $childs);
                });
            } else {
                $res->whereHas('group', function($query) use ($group) {
                    $query->where('group_id', $group);
                });
            }
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
     * @Route(path="api/v1/ujians/{jadwal}/result/link", methods={"GET"})
     * 
     * Buat link untuk download excel hasil ujian 
     * peserta
     * 
     * @param string $jadwal_id
     * @return App\Actions\SendResponse
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
     * @return App\Actions\SendResponse
     * @author shellrean <wandinak17@gmail.com>
     */
    public function examExcel($jadwal_id)
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

        $jurusan = request()->jurusan;
        $group = request()->group;

        $res = HasilUjian::with(['peserta' => function ($query) use ($jurusan) {
            $query->select('id','nama','no_ujian');
        }]);

        if ($jurusan != 0 && $jurusan != '') {
            $jurusan = explode(',',$jurusan);
            $res->whereHas('peserta', function($query) use ($jurusan) {
                $query->whereIn('jurusan_id', $jurusan);
            });
        }


        if ($group != 0 && $group != '') {
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
                $res->whereHas('group', function($query) use ($childs) {
                    $query->whereIn('group_id', $childs);
                });
            } else {
                $res->whereHas('group', function($query) use ($group) {
                    $query->where('group_id', $group);
                });
            }
        }
        
        $res = $res->where('jadwal_id', $jadwal->id)
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
     * @Route(path="api/v1/ujians/{jadwal}/banksoal/{banksoal}/capaian-siswa", methods={"GET"})
     * 
     * Ambil data capaian siswa hasil ujian
     * 
     * @param string $jadwal_id
     * @param string $banksoal_id
     * @return App\Actions\SendResponse
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
     * @return App\Actions\SendResponse
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
     * @Route(path="api/v1/ujians/{jadwal}/banksoal/{banksoal}/capaian-siswa/excel", methods={"GET"})
     * 
     * Download excel capaian
     * siswa pada hasil ujian
     * 
     * @param string $jadwal_id
     * @param string $banksoal_id
     * @return App\Actions\SendResponse
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

        $soals = Soal::where(function($query) use($banksoal) {
            $query->where('banksoal_id', $banksoal->id)
            ->where('tipe_soal','!=','2');
        })->get();

        $sss = JawabanPeserta::with(['peserta' => function($query) {
            $query->select('id','nama','no_ujian');
        }]);

        if ($group != 0 && $group != '') {
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
                $sss->whereHas('group', function($query) use ($childs) {
                    $query->whereIn('group_id', $childs);
                });
            } else {
                $sss->whereHas('group', function($query) use ($group) {
                    $query->where('group_id', $group);
                });
            }
        }

        if ($jurusan != 0 && $jurusan != '') {
            $jurusan = explode(',',$jurusan);
            $sss->whereHas('peserta', function($query) use ($jurusan) {
                $query->whereIn('jurusan_id', $jurusan);
            });
        }

        
        $sss = $sss->whereHas('pertanyaan', function($query) {
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

    /**
     * @Route(path="api/v1/ujians/hasil/{hasil}", methods={"GET"})
     * 
     * Abmil data hasil ujian peserta detail
     * dengan jawabannya
     * 
     * @param string $hasil_id
     * @return App\Actions\SendResponse
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
                'peserta_nama'  => $item->peserta->nama,
                'peserta_no_ujian' => $item->peserta->no_ujian,
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
