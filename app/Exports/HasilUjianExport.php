<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\DB;

class HasilUjianExport
{
    public static function export($datas, $kode)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                ]
            ],
        ];

        $styleArray2 = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                ]
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => array('argb' => 'FFFF99')
            ],
        ];

        $styleArray3 = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                ]
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => array('argb' => 'FFCC99')
            ],
        ];

        $sheet->setCellValue('A1', 'HASIL UJIAN KODE: '.$kode);
        $sheet->getStyle('A1')->getAlignment()->setWrapText(true);
        $sheet->mergeCells('A1:C1');
        $sheet->getRowDimension('1')->setRowHeight(52);
        $sheet->getStyle('A1')->getAlignment()->setVertical('center');

        $sheet->getRowDimension('3')->setRowHeight(170);
        $sheet->getColumnDimension('C')->setWidth(45);
        $sheet->getColumnDimension('B')->setWidth(35);

        $sheet->setCellValue('A3', 'NO');
        $sheet->setCellValue('B3', 'NOMOR UJIAN');

        $sheet->setCellValue('C3', 'NAMA');
        $sheet->getStyle('A3:C3')->applyFromArray($styleArray);

        $sheet->setCellValue('D3', 'LISTENING (BENAR)');
        $sheet->getStyle('D3')->applyFromArray($styleArray2);
        $sheet->getStyle('D3')->getAlignment()->setTextRotation(90);
        $sheet->getStyle('D3')->getAlignment()->setWrapText(true);

        $sheet->setCellValue('E3', 'LISTENING (SALAH)');
        $sheet->getStyle('E3')->applyFromArray($styleArray);
        $sheet->getStyle('E3')->getAlignment()->setTextRotation(90);
        $sheet->getStyle('E3')->getAlignment()->setWrapText(true);

        $sheet->setCellValue('F3', 'PILIHAN GANDA (BENAR)');
        $sheet->getStyle('F3')->applyFromArray($styleArray2);
        $sheet->getStyle('F3')->getAlignment()->setTextRotation(90);
        $sheet->getStyle('F3')->getAlignment()->setWrapText(true);

        $sheet->setCellValue('G3', 'PILIHAN GANDA (SALAH)');
        $sheet->getStyle('G3')->applyFromArray($styleArray);
        $sheet->getStyle('G3')->getAlignment()->setTextRotation(90);
        $sheet->getStyle('G3')->getAlignment()->setWrapText(true);

        $sheet->setCellValue('H3', 'PILIHAN GANDA KOMPLEKS (BENAR)');
        $sheet->getStyle('H3')->applyFromArray($styleArray2);
        $sheet->getStyle('H3')->getAlignment()->setTextRotation(90);
        $sheet->getStyle('H3')->getAlignment()->setWrapText(true);

        $sheet->setCellValue('I3', 'PILIHAN GANDA KOMPLEKS (SALAH)');
        $sheet->getStyle('I3')->applyFromArray($styleArray);
        $sheet->getStyle('I3')->getAlignment()->setTextRotation(90);
        $sheet->getStyle('I3')->getAlignment()->setWrapText(true);

        $sheet->setCellValue('J3', 'ISIAN SINGKAT (BENAR)');
        $sheet->getStyle('J3')->applyFromArray($styleArray2);
        $sheet->getStyle('J3')->getAlignment()->setTextRotation(90);
        $sheet->getStyle('J3')->getAlignment()->setWrapText(true);

        $sheet->setCellValue('K3', 'ISIAN SINGKAT (SALAH)');
        $sheet->getStyle('K3')->applyFromArray($styleArray);
        $sheet->getStyle('K3')->getAlignment()->setTextRotation(90);
        $sheet->getStyle('K3')->getAlignment()->setWrapText(true);

        $sheet->setCellValue('L3', 'MENJODOHKAN (BENAR)');
        $sheet->getStyle('L3')->applyFromArray($styleArray2);
        $sheet->getStyle('L3')->getAlignment()->setTextRotation(90);
        $sheet->getStyle('L3')->getAlignment()->setWrapText(true);

        $sheet->setCellValue('M3', 'MENJODOHKAN (SALAH)');
        $sheet->getStyle('M3')->applyFromArray($styleArray);
        $sheet->getStyle('M3')->getAlignment()->setTextRotation(90);
        $sheet->getStyle('M3')->getAlignment()->setWrapText(true);

        $sheet->setCellValue('N3', 'KOSONG');
        $sheet->getStyle('N3')->applyFromArray($styleArray2);
        $sheet->getStyle('N3')->getAlignment()->setTextRotation(90);
        $sheet->getStyle('N3')->getAlignment()->setWrapText(true);

        $sheet->setCellValue('O3', 'POINT ESAY');
        $sheet->getStyle('O3')->applyFromArray($styleArray2);
        $sheet->getStyle('O3')->getAlignment()->setTextRotation(90);
        $sheet->getStyle('O3')->getAlignment()->setWrapText(true);

        $sheet->setCellValue('P3', 'HASIL AKHIR');
        $sheet->getStyle('P3')->applyFromArray($styleArray2);
        $sheet->getStyle('P3')->getAlignment()->setTextRotation(90);
        $sheet->getStyle('P3')->getAlignment()->setWrapText(true);

        $column_header = 'D';

        $row = 4;
        $column_header++;
        foreach ($datas as $key => $value) {
            $sheet->setCellValue('A'.$row, $key+1);
            $sheet->getStyle('A'.$row)->applyFromArray($styleArray);

            $sheet->setCellValue('B'.$row, $value->peserta->no_ujian);
            $sheet->getStyle('B'.$row)->applyFromArray($styleArray);

            $sheet->setCellValue('C'.$row, $value->peserta->nama);
            $sheet->getStyle('C'.$row)->applyFromArray($styleArray);

            $sheet->setCellValue('D'.$row, $value->jumlah_benar_listening);
            $sheet->getStyle('D'.$row)->applyFromArray($styleArray);

            $sheet->setCellValue('E'.$row, $value->jumlah_salah_listening);
            $sheet->getStyle('E'.$row)->applyFromArray($styleArray);

            $sheet->setCellValue('F'.$row, $value->jumlah_benar);
            $sheet->getStyle('F'.$row)->applyFromArray($styleArray);

            $sheet->setCellValue('G'.$row, $value->jumlah_salah);
            $sheet->getStyle('G'.$row)->applyFromArray($styleArray);

            $sheet->setCellValue('H'.$row, $value->jumlah_benar_complek);
            $sheet->getStyle('H'.$row)->applyFromArray($styleArray);

            $sheet->setCellValue('I'.$row, $value->jumlah_salah_complek);
            $sheet->getStyle('I'.$row)->applyFromArray($styleArray);

            $sheet->setCellValue('J'.$row, $value->jumlah_benar_isian_singkat);
            $sheet->getStyle('J'.$row)->applyFromArray($styleArray);

            $sheet->setCellValue('K'.$row, $value->jumlah_salah_isian_singkat);
            $sheet->getStyle('K'.$row)->applyFromArray($styleArray);

            $sheet->setCellValue('L'.$row, $value->jumlah_benar_menjodohkan);
            $sheet->getStyle('L'.$row)->applyFromArray($styleArray);

            $sheet->setCellValue('M'.$row, $value->jumlah_salah_menjodohkan);
            $sheet->getStyle('M'.$row)->applyFromArray($styleArray);

            $sheet->setCellValue('N'.$row, $value->tidak_diisi);
            $sheet->getStyle('N'.$row)->applyFromArray($styleArray);

            $sheet->setCellValue('O'.$row, $value->point_esay);
            $sheet->getStyle('O'.$row)->applyFromArray($styleArray);

            $sheet->setCellValue('P'.$row, $value->hasil);
            $sheet->getStyle('P'.$row)->applyFromArray($styleArray);
        }

        return $spreadsheet;
    }
}
