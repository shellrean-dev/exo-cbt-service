<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\DB;

/**
 * Capaian siswa export
 *
 * @author shellrean <wandinak17@gmail.com>
 * @since 2.0.1 <latte>
 */
class CapaianSiswaExport extends ExportExcel
{
    public static function export($datas, string $banksoal, string $jadwal)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'CAPAIAN SISWA '.chr(13).$banksoal.' '.$jadwal);
        $sheet->getStyle('A1')->getAlignment()->setWrapText(true);
        $sheet->getStyle('A1')->getAlignment()->setVertical('center');
        $sheet->mergeCells("A1:C1");
        $sheet->getRowDimension('1')->setRowHeight(52);

        $sheet->setCellValue('A3', 'NO');
        $sheet->setCellValue('B3', 'NO UJIAN');
        $sheet->setCellValue('C3', 'NAMA');

        $sheet->getColumnDimension('B')->setWidth(35);
        $sheet->getColumnDimension('C')->setWidth(45);

        $sheet->getStyle('A3:C3')->applyFromArray(self::styleGeneral());

        $column_header = 'D';
        foreach (range(1, $datas['soals']->count()) as $index) {
            $sheet->setCellValue($column_header.'3', $index);
            $sheet->getStyle($column_header.'3')->applyFromArray(self::styleYellow());

            $column_header++;
        }
        $sheet->setCellValue($column_header.'3', 'TOTAL');
        $sheet->getStyle($column_header.'3')->applyFromArray(self::styleYellowDark());

        $row = 4;
        $column_header++;
        $no = 1;
        foreach ($datas['pesertas'] as $key => $fil) {
            $sheet->setCellValue('A'.$row, $no);
            $sheet->getStyle('A'.$row)->applyFromArray(self::styleGeneral());

            $sheet->setCellValue('B'.$row, $fil['peserta']['no_ujian']);
            $sheet->getStyle('B'.$row)->applyFromArray(self::styleGeneral());

            $sheet->setCellValue('C'.$row, $fil['peserta']['nama']);
            $sheet->getStyle('C'.$row)->applyFromArray(self::styleGeneral());

            $count = 0;

            $column = 'D';
            foreach ($datas['soals'] as $soal) {
                $filtered = collect($fil['data'])->firstWhere('soal_id', $soal->id);
                if($filtered != '') {
                    $count += $filtered->iscorrect;
                }

                $sheet->setCellValue($column.$row, $filtered != '' ? $filtered->iscorrect : '-');
                $sheet->getStyle($column.$row)->applyFromArray(self::styleGeneral());
                $column++;
            }
            $sheet->setCellValue($column.$row, $count);
            $sheet->getStyle($column.$row)->applyFromArray(self::styleGeneral());
            $sheet->getStyle($column.$row)->getAlignment()->setHorizontal('center');

            $row++;
            $no++;
        }

        return $spreadsheet;
    }
}
