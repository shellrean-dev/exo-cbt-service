<?php

namespace App\Imports;

use App\Actions\SendResponse;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Ramsey\Uuid\Uuid;

class JawabanPesertaEsayImport implements ToCollection, WithStartRow
{
    private $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function collection(Collection $rows)
    {
        $jawaban_peserta_ids = [];
        foreach($rows as $row) {
            if($row->filter()->isNotEmpty()) {
                array_push($jawaban_peserta_ids, $row[0]);
            }
        }

        $jawaban_pesertas = DB::table('jawaban_pesertas as a')
            ->whereIn('a.id', $jawaban_peserta_ids)
            ->join('banksoals as b', 'b.id', '=', 'a.banksoal_id')
            ->select([
                'a.id',
                'a.banksoal_id',
                'a.jadwal_id',
                'a.peserta_id',
                'b.persen',
                'b.jumlah_soal_esay'])
            ->get();

        $failed = 0;
        foreach ($rows as $row) {
            if($row->filter()->isNotEmpty()) {
                if (!Uuid::isValid($row[0])) {
                    continue;
                }

                if (floatval($row[4]) > 1 || floatval($row[4]) < 0) {
                    continue;
                }

                # cek apakah jawaban pernah disimpan sebelumnya
                $exists = DB::table('penilaian_esay')
                    ->where('jawab_id', $row[0])
                    ->first();
                if ($exists) {
                    continue;
                }

                # ambil data jawaban peserta
                $jawab = $jawaban_pesertas->where('id', $row[0])->first();
                if (!$jawab) {
                    continue;
                }

                $hasil = DB::table('hasil_ujians')->where([
                    'banksoal_id'   => $jawab->banksoal_id,
                    'jadwal_id'     => $jawab->jadwal_id,
                    'peserta_id'    => $jawab->peserta_id,
                ])->first();
                if (!$hasil) {
                    continue;
                }

                # Hitung total pertanyaan
                $jml_esay =  $jawab->jumlah_soal_esay;

                $persen = json_decode($jawab->persen,true);

                # Esay nilai argument
                if($row[4] != 0) {
                    $point =  ($row[4]/$jml_esay)*$persen['esay'];
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
                        'corrected_by'  => $this->user->id,
                        'point'         => $row[4],
                        'created_at'    => now(),
                        'updated_at'    => now()
                    ]);

                    DB::commit();
                } catch (Exception $e) {
                    DB::rollBack();
                    $failed ++;
                }
            }
        }
    }

    public function startRow(): int
    {
        return 4;
    }
}
