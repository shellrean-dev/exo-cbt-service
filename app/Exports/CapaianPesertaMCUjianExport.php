<?php

namespace App\Exports;

use App\Models\SoalConstant;
use App\Utils\SoalUtil;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class CapaianPesertaMCUjianExport extends ExportExcel
{
    /**
     * @param $datas
     * @param string $banksoal
     * @param string $jadwal
     * @return Spreadsheet
     * @throws Exception
     */
    public static function run($datas, string $banksoal, string $jadwal)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $jawab_ids = [];
        foreach ($datas['jawaban_pesertas'] as $key => $value) {
            $jawab_ids[$value->jawab] = true;
        }

        $jawab_soal = DB::table('jawaban_soals')->whereIn('id', array_keys($jawab_ids))->get()
            ->keyBy('id');

        $sheet->setCellValue('A1', 'CAPAIAN SISWA [MC]'.chr(13).$banksoal.' '.$jadwal);
        $sheet->getStyle('A1')->getAlignment()->setWrapText(true);
        $sheet->getStyle('A1')->getAlignment()->setVertical('center');
        $sheet->mergeCells("A1:C1");
        $sheet->getRowDimension('1')->setRowHeight(52);
        $sheet->getRowDimension('2')->setRowHeight(139);

        $sheet->setCellValue('A3', 'NO');
        $sheet->setCellValue('B3', 'NO UJIAN');
        $sheet->setCellValue('C3', 'NAMA');

        $sheet->getColumnDimension('B')->setWidth(35);
        $sheet->getColumnDimension('C')->setWidth(45);

        $sheet->getStyle('A3:C3')->applyFromArray(self::styleGeneral());

        $column_header = 'D';
        foreach ($datas['soals'] as $key => $value) {
            $sheet->setCellValue($column_header.'3', $key+1);
            $sheet->getStyle($column_header.'3')->applyFromArray(self::styleYellow());

            $sheet->setCellValue($column_header.'2', SoalUtil::tipeSoalTextOf(intval($value->tipe_soal)));
            $sheet->getStyle($column_header.'2')->getAlignment()->setTextRotation(90);
            $sheet->getStyle($column_header.'2')->applyFromArray(self::styleYellow());

            $column_header++;
        }

        $sheet->setCellValue($column_header.'3', 'TOTAL');
        $sheet->getStyle($column_header.'3')->applyFromArray(self::styleYellowDark());

        $row = 4;
        foreach ($datas['pesertas'] as $key => $value) {
            $sheet->setCellValue('A'.$row, $key+1);
            $sheet->getStyle('A'.$row)->applyFromArray(self::styleGeneral());

            $sheet->setCellValue('B'.$row, "'".$value->no_ujian);
            $sheet->getStyle('B'.$row)->applyFromArray(self::styleGeneral());

            $sheet->setCellValue('C'.$row, $value->nama);
            $sheet->getStyle('C'.$row)->applyFromArray(self::styleGeneral());

            $count = 0;

            $column = 'D';
            foreach ($datas['soals'] as $skey => $svalue) {
                $jawaban_konkrit = false;
                if (isset($datas['jawaban_pesertas'][$svalue->id.'|'.$value->id])) {
                    $jawaban_konkrit = $datas['jawaban_pesertas'][$svalue->id.'|'.$value->id];
                }

                $label_mark = "-";
                if ($jawaban_konkrit) {
                    $count += intval($jawaban_konkrit->iscorrect);
                    if ($svalue->tipe_soal == SoalConstant::TIPE_PG) {
                        $label_mark = $jawab_soal->get($jawaban_konkrit->jawab, (object)['label_mark' => '-'])->label_mark;
                    }
                }

                if($jawaban_konkrit) {
                    if($jawaban_konkrit->answered) {
//                        $sheet->setCellValue($column.$row, $jawaban_konkrit->iscorrect);
                        $sheet->setCellValue($column.$row, $label_mark);
                        if($jawaban_konkrit->iscorrect == 0) {
                            $sheet->getStyle($column.$row)->applyFromArray(self::styleBad());
                        } else {
                            $sheet->getStyle($column.$row)->applyFromArray(self::styleGeneral());
                        }
                    } else {
                        $sheet->setCellValue($column.$row, 'x');
                        $sheet->getStyle($column.$row)->getAlignment()->setHorizontal('right');
                        $sheet->getStyle($column.$row)->applyFromArray(self::styleYellow());
                    }
                } else {
                    $sheet->setCellValue($column.$row, '-');
                    $sheet->getStyle($column.$row)->applyFromArray(self::styleGeneral());
                }
                $column++;
            }
            $sheet->setCellValue($column.$row, $count);
            $sheet->getStyle($column.$row)->applyFromArray(self::styleGeneral());
            $sheet->getStyle($column.$row)->getAlignment()->setHorizontal('center');
            $row++;
        }

        return $spreadsheet;
    }
}
