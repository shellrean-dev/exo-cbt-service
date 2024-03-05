<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * Export jawaban peserta
 *
 * @author shellrean <wandinak17@gmail.com>
 * @since 2.0.1 <latte>
 */
class JawabanPesertaExport extends ExportExcel
{
    public static function export($data, $kode)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'JAWABAN ESAY PESERTA BANKSOAL: '.$kode);
        $sheet->getStyle('A1')->getAlignment()->setWrapText(true);
        $sheet->mergeCells('A1:C1');
        $sheet->getRowDimension('1')->setRowHeight(52);
        $sheet->getStyle('A1')->getAlignment()->setVertical('center');

        $sheet->getRowDimension('3')->setRowHeight(170);
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(50);
        $sheet->getColumnDimension('C')->setWidth(50);
        $sheet->getColumnDimension('D')->setWidth(50);
        $sheet->getColumnDimension('E')->setWidth(16);

        $sheet->setCellValue('A3', 'UNIQUE ID (JANGAN DIUBAH)');
        $sheet->setCellValue('B3', 'PERTANYAAN');
        $sheet->setCellValue('C3', 'RUJUKAN');
        $sheet->setCellValue('D3', 'JAWABAN PESERTA');
        $sheet->setCellValue('E3', 'NILAI (MIN 0, MAX 1)');
        $sheet->getStyle('A3:E3')->applyFromArray(self::styleGeneral());

        $row = 4;
        foreach ($data as $key => $value) {
            $sheet->getRowDimension($row)->setRowHeight(100);

            $sheet->setCellValue('A'.$row, $value->id);
            $sheet->getStyle('A'.$row)->applyFromArray(self::styleGeneral());
            $sheet->getStyle('A'.$row)->getAlignment()->setVertical('top');

            $sheet->setCellValue('B'.$row, strip_tags($value->pertanyaan));
            $sheet->getStyle('B'.$row)->applyFromArray(self::styleGeneral());
            $sheet->getStyle('B'.$row)->getAlignment()->setWrapText(true);
            $sheet->getStyle('B'.$row)->getAlignment()->setVertical('top');

            $sheet->setCellValue('C'.$row, strip_tags($value->rujukan));
            $sheet->getStyle('C'.$row)->applyFromArray(self::styleGeneral());
            $sheet->getStyle('C'.$row)->getAlignment()->setWrapText(true);
            $sheet->getStyle('C'.$row)->getAlignment()->setVertical('top');

            $sheet->setCellValue('D'.$row, strip_tags("'".$value->esay));
            $sheet->getStyle('D'.$row)->applyFromArray(self::styleGeneral());
            $sheet->getStyle('D'.$row)->getAlignment()->setWrapText(true);
            $sheet->getStyle('D'.$row)->getAlignment()->setVertical('top');

            $sheet->setCellValue('E'.$row, 0.0);
            $sheet->getStyle('E'.$row)->applyFromArray(self::styleYellow());
            $sheet->getStyle('E'.$row)->getAlignment()->setVertical('top');

            $row++;
        }

        return $spreadsheet;
    }
}
