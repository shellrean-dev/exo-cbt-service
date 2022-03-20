<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

class LedgerPesertaHasilUjianExport extends ExportExcel
{
    public const LISTENING_CELL__BENAR = "C";
    public const LISTENING_CELL__SALAH = "D";

    public const PG_CELL__BENAR = "E";
    public const PG_CELL__SALAH = "F";

    public const PG_KOMPLEKS_CELL__BENAR = "G";
    public const PG_KOMPLEKS_CELL__SALAH = "H";

    public const ISIAN_SINGKAT_CELL__BENAR = "I";
    public const ISIAN_SINGKAT_CELL__SALAH = "J";

    public const MENJODOHKAN_CELL__BENAR = "K";
    public const MENJODOHKAN_CELL__SALAH = "L";

    public const MENGURUTKAN_CELL__BENAR = "M";
    public const MENGURUTKAN_CELL__SALAH = "N";

    public const BENAR_SALAH_CELL__BENAR = "O";
    public const BENAR_SALAH_CELL__SALAH = "P";

    public const KOSONG_CELL = "Q";
    public const POINT_ESAY_CELL = "R";
    public const POINT_ARGUMENT_CELL = "S";
    public const HASIL_AKHIR_CELL = "T";

    public static function export($datas, $peserta)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('B1', 'NAMA PESERTA ');
        $sheet->getStyle('B1')->getAlignment()->setWrapText(true);
        $sheet->getRowDimension('1')->setRowHeight(35);
        $sheet->getStyle('B1')->getAlignment()->setVertical('center');

        $sheet->setCellValue('C1', $peserta->nama);
        $sheet->getStyle('C1')->getAlignment()->setWrapText(true);
        $sheet->mergeCells('C1:H1');
        $sheet->getStyle('C1')->getAlignment()->setVertical('center');

        $sheet->setCellValue('B2', 'NO UJIAN');
        $sheet->getStyle('B2')->getAlignment()->setWrapText(true);
        $sheet->getRowDimension('2')->setRowHeight(35);
        $sheet->getStyle('B2')->getAlignment()->setVertical('center');

        $sheet->setCellValue('C2', $peserta->no_ujian);
        $sheet->getStyle('C2')->getAlignment()->setWrapText(true);
        $sheet->mergeCells('C2:H2');
        $sheet->getStyle('C2')->getAlignment()->setVertical('center');

        $sheet->getRowDimension('6')->setRowHeight(170);
        $sheet->getColumnDimension('B')->setWidth(35);

        $sheet->setCellValue('A6', 'NO');
        $sheet->setCellValue('B6', 'NAMA UJIAN');
        $sheet->getStyle('A6:B6')->applyFromArray(self::styleGeneral());

        $sheet->setCellValue(self::LISTENING_CELL__BENAR.'6', 'LISTENING (BENAR)');
        $sheet->getStyle(self::LISTENING_CELL__BENAR.'6')->applyFromArray(self::styleGeneral());
        $sheet->getStyle(self::LISTENING_CELL__BENAR.'6')->getAlignment()->setTextRotation(90);
        $sheet->getStyle(self::LISTENING_CELL__BENAR.'6')->getAlignment()->setWrapText(true);

        $sheet->setCellValue(self::LISTENING_CELL__SALAH.'6', 'LISTENING (SALAH)');
        $sheet->getStyle(self::LISTENING_CELL__SALAH.'6')->applyFromArray(self::styleGeneral());
        $sheet->getStyle(self::LISTENING_CELL__SALAH.'6')->getAlignment()->setTextRotation(90);
        $sheet->getStyle(self::LISTENING_CELL__SALAH.'6')->getAlignment()->setWrapText(true);

        $sheet->setCellValue(self::PG_CELL__BENAR.'6', 'PILIHAN GANDA (BENAR)');
        $sheet->getStyle(self::PG_CELL__BENAR.'6')->applyFromArray(self::styleGeneral());
        $sheet->getStyle(self::PG_CELL__BENAR.'6')->getAlignment()->setTextRotation(90);
        $sheet->getStyle(self::PG_CELL__BENAR.'6')->getAlignment()->setWrapText(true);

        $sheet->setCellValue(self::PG_CELL__SALAH.'6', 'PILIHAN GANDA (SALAH)');
        $sheet->getStyle(self::PG_CELL__SALAH.'6')->applyFromArray(self::styleGeneral());
        $sheet->getStyle(self::PG_CELL__SALAH.'6')->getAlignment()->setTextRotation(90);
        $sheet->getStyle(self::PG_CELL__SALAH.'6')->getAlignment()->setWrapText(true);

        $sheet->setCellValue(self::PG_KOMPLEKS_CELL__BENAR.'6', 'PILIHAN GANDA KOMPLEKS (BENAR)');
        $sheet->getStyle(self::PG_KOMPLEKS_CELL__BENAR.'6')->applyFromArray(self::styleGeneral());
        $sheet->getStyle(self::PG_KOMPLEKS_CELL__BENAR.'6')->getAlignment()->setTextRotation(90);
        $sheet->getStyle(self::PG_KOMPLEKS_CELL__BENAR.'6')->getAlignment()->setWrapText(true);

        $sheet->setCellValue(self::PG_KOMPLEKS_CELL__SALAH.'6', 'PILIHAN GANDA KOMPLEKS (SALAH)');
        $sheet->getStyle(self::PG_KOMPLEKS_CELL__SALAH.'6')->applyFromArray(self::styleGeneral());
        $sheet->getStyle(self::PG_KOMPLEKS_CELL__SALAH.'6')->getAlignment()->setTextRotation(90);
        $sheet->getStyle(self::PG_KOMPLEKS_CELL__SALAH.'6')->getAlignment()->setWrapText(true);

        $sheet->setCellValue(self::ISIAN_SINGKAT_CELL__BENAR.'6', 'ISIAN SINGKAT (BENAR)');
        $sheet->getStyle(self::ISIAN_SINGKAT_CELL__BENAR.'6')->applyFromArray(self::styleGeneral());
        $sheet->getStyle(self::ISIAN_SINGKAT_CELL__BENAR.'6')->getAlignment()->setTextRotation(90);
        $sheet->getStyle(self::ISIAN_SINGKAT_CELL__BENAR.'6')->getAlignment()->setWrapText(true);

        $sheet->setCellValue(self::ISIAN_SINGKAT_CELL__SALAH.'6', 'ISIAN SINGKAT (SALAH)');
        $sheet->getStyle(self::ISIAN_SINGKAT_CELL__SALAH.'6')->applyFromArray(self::styleGeneral());
        $sheet->getStyle(self::ISIAN_SINGKAT_CELL__SALAH.'6')->getAlignment()->setTextRotation(90);
        $sheet->getStyle(self::ISIAN_SINGKAT_CELL__SALAH.'6')->getAlignment()->setWrapText(true);

        $sheet->setCellValue(self::MENJODOHKAN_CELL__BENAR.'6', 'MENJODOHKAN (BENAR)');
        $sheet->getStyle(self::MENJODOHKAN_CELL__BENAR.'6')->applyFromArray(self::styleGeneral());
        $sheet->getStyle(self::MENJODOHKAN_CELL__BENAR.'6')->getAlignment()->setTextRotation(90);
        $sheet->getStyle(self::MENJODOHKAN_CELL__BENAR.'6')->getAlignment()->setWrapText(true);

        $sheet->setCellValue(self::MENJODOHKAN_CELL__SALAH.'6', 'MENJODOHKAN (SALAH)');
        $sheet->getStyle(self::MENJODOHKAN_CELL__SALAH.'6')->applyFromArray(self::styleGeneral());
        $sheet->getStyle(self::MENJODOHKAN_CELL__SALAH.'6')->getAlignment()->setTextRotation(90);
        $sheet->getStyle(self::MENJODOHKAN_CELL__SALAH.'6')->getAlignment()->setWrapText(true);

        $sheet->setCellValue(self::MENGURUTKAN_CELL__BENAR.'6', 'MENGURUTKAN (BENAR)');
        $sheet->getStyle(self::MENGURUTKAN_CELL__BENAR.'6')->applyFromArray(self::styleGeneral());
        $sheet->getStyle(self::MENGURUTKAN_CELL__BENAR.'6')->getAlignment()->setTextRotation(90);
        $sheet->getStyle(self::MENGURUTKAN_CELL__BENAR.'6')->getAlignment()->setWrapText(true);

        $sheet->setCellValue(self::MENGURUTKAN_CELL__SALAH.'6', 'MENGURUTKAN (SALAH)');
        $sheet->getStyle(self::MENGURUTKAN_CELL__SALAH.'6')->applyFromArray(self::styleGeneral());
        $sheet->getStyle(self::MENGURUTKAN_CELL__SALAH.'6')->getAlignment()->setTextRotation(90);
        $sheet->getStyle(self::MENGURUTKAN_CELL__SALAH.'6')->getAlignment()->setWrapText(true);

        $sheet->setCellValue(self::BENAR_SALAH_CELL__BENAR.'6', 'BENAR/SALAH (BENAR)');
        $sheet->getStyle(self::BENAR_SALAH_CELL__BENAR.'6')->applyFromArray(self::styleGeneral());
        $sheet->getStyle(self::BENAR_SALAH_CELL__BENAR.'6')->getAlignment()->setTextRotation(90);
        $sheet->getStyle(self::BENAR_SALAH_CELL__BENAR.'6')->getAlignment()->setWrapText(true);

        $sheet->setCellValue(self::BENAR_SALAH_CELL__SALAH.'6', 'BENAR/SALAH (SALAH)');
        $sheet->getStyle(self::BENAR_SALAH_CELL__SALAH.'6')->applyFromArray(self::styleGeneral());
        $sheet->getStyle(self::BENAR_SALAH_CELL__SALAH.'6')->getAlignment()->setTextRotation(90);
        $sheet->getStyle(self::BENAR_SALAH_CELL__SALAH.'6')->getAlignment()->setWrapText(true);

        $sheet->setCellValue(self::KOSONG_CELL.'6', 'KOSONG');
        $sheet->getStyle(self::KOSONG_CELL.'6')->applyFromArray(self::styleYellow());
        $sheet->getStyle(self::KOSONG_CELL.'6')->getAlignment()->setTextRotation(90);
        $sheet->getStyle(self::KOSONG_CELL.'6')->getAlignment()->setWrapText(true);

        $sheet->setCellValue(self::POINT_ESAY_CELL.'6', 'POINT ESAY');
        $sheet->getStyle(self::POINT_ESAY_CELL.'6')->applyFromArray(self::styleGeneral());
        $sheet->getStyle(self::POINT_ESAY_CELL.'6')->getAlignment()->setTextRotation(90);
        $sheet->getStyle(self::POINT_ESAY_CELL.'6')->getAlignment()->setWrapText(true);

        $sheet->setCellValue(self::POINT_ARGUMENT_CELL.'6', 'POINT SETUJU/TIDAK');
        $sheet->getStyle(self::POINT_ARGUMENT_CELL.'6')->applyFromArray(self::styleGeneral());
        $sheet->getStyle(self::POINT_ARGUMENT_CELL.'6')->getAlignment()->setTextRotation(90);
        $sheet->getStyle(self::POINT_ARGUMENT_CELL.'6')->getAlignment()->setWrapText(true);

        $sheet->setCellValue(self::HASIL_AKHIR_CELL.'6', 'HASIL AKHIR');
        $sheet->getStyle(self::HASIL_AKHIR_CELL.'6')->applyFromArray(self::styleYellow());
        $sheet->getStyle(self::HASIL_AKHIR_CELL.'6')->getAlignment()->setTextRotation(90);
        $sheet->getStyle(self::HASIL_AKHIR_CELL.'6')->getAlignment()->setWrapText(true);

        $row = 7;
        foreach ($datas as $key => $value) {
            $sheet->setCellValue('A'.$row, $key+1);
            $sheet->getStyle('A'.$row)->applyFromArray(self::styleGeneral());

            $sheet->setCellValue('B'.$row, $value->alias);
            $sheet->getStyle('B'.$row)->applyFromArray(self::styleGeneral());

            $sheet->setCellValue(self::LISTENING_CELL__BENAR.$row, $value->jumlah_benar_listening);
            $sheet->getStyle(self::LISTENING_CELL__BENAR.$row)->applyFromArray(self::styleGeneral());

            $sheet->setCellValue(self::LISTENING_CELL__SALAH.$row, $value->jumlah_salah_listening);
            $sheet->getStyle(self::LISTENING_CELL__SALAH.$row)->applyFromArray(self::styleGeneral());

            $sheet->setCellValue(self::PG_CELL__BENAR.$row, $value->jumlah_benar);
            $sheet->getStyle(self::PG_CELL__BENAR.$row)->applyFromArray(self::styleGeneral());

            $sheet->setCellValue(self::PG_CELL__SALAH.$row, $value->jumlah_salah);
            $sheet->getStyle(self::PG_CELL__SALAH.$row)->applyFromArray(self::styleGeneral());

            $sheet->setCellValue(self::PG_KOMPLEKS_CELL__BENAR.$row, $value->jumlah_benar_complek);
            $sheet->getStyle(self::PG_KOMPLEKS_CELL__BENAR.$row)->applyFromArray(self::styleGeneral());

            $sheet->setCellValue(self::PG_KOMPLEKS_CELL__SALAH.$row, $value->jumlah_salah_complek);
            $sheet->getStyle(self::PG_KOMPLEKS_CELL__SALAH.$row)->applyFromArray(self::styleGeneral());

            $sheet->setCellValue(self::ISIAN_SINGKAT_CELL__BENAR.$row, $value->jumlah_benar_isian_singkat);
            $sheet->getStyle(self::ISIAN_SINGKAT_CELL__BENAR.$row)->applyFromArray(self::styleGeneral());

            $sheet->setCellValue(self::ISIAN_SINGKAT_CELL__SALAH.$row, $value->jumlah_salah_isian_singkat);
            $sheet->getStyle(self::ISIAN_SINGKAT_CELL__SALAH.$row)->applyFromArray(self::styleGeneral());

            $sheet->setCellValue(self::MENJODOHKAN_CELL__BENAR.$row, $value->jumlah_benar_menjodohkan);
            $sheet->getStyle(self::MENJODOHKAN_CELL__BENAR.$row)->applyFromArray(self::styleGeneral());

            $sheet->setCellValue(self::MENJODOHKAN_CELL__SALAH.$row, $value->jumlah_salah_menjodohkan);
            $sheet->getStyle(self::MENJODOHKAN_CELL__SALAH.$row)->applyFromArray(self::styleGeneral());

            $sheet->setCellValue(self::MENGURUTKAN_CELL__BENAR.$row, $value->jumlah_benar_mengurutkan);
            $sheet->getStyle(self::MENGURUTKAN_CELL__BENAR.$row)->applyFromArray(self::styleGeneral());

            $sheet->setCellValue(self::MENGURUTKAN_CELL__SALAH.$row, $value->jumlah_salah_mengurutkan);
            $sheet->getStyle(self::MENGURUTKAN_CELL__SALAH.$row)->applyFromArray(self::styleGeneral());

            $sheet->setCellValue(self::BENAR_SALAH_CELL__BENAR.$row, $value->jumlah_benar_benar_salah);
            $sheet->getStyle(self::BENAR_SALAH_CELL__BENAR.$row)->applyFromArray(self::styleGeneral());

            $sheet->setCellValue(self::BENAR_SALAH_CELL__SALAH.$row, $value->jumlah_salah_benar_salah);
            $sheet->getStyle(self::BENAR_SALAH_CELL__SALAH.$row)->applyFromArray(self::styleGeneral());

            $sheet->setCellValue(self::KOSONG_CELL.$row, $value->tidak_diisi);
            $sheet->getStyle(self::KOSONG_CELL.$row)->applyFromArray(self::styleGeneral());

            $sheet->setCellValue(self::POINT_ESAY_CELL.$row, $value->point_esay);
            $sheet->getStyle(self::POINT_ESAY_CELL.$row)->applyFromArray(self::styleGeneral());

            $sheet->setCellValue(self::POINT_ARGUMENT_CELL.$row, $value->point_setuju_tidak);
            $sheet->getStyle(self::POINT_ARGUMENT_CELL.$row)->applyFromArray(self::styleGeneral());

            $sheet->setCellValue(self::HASIL_AKHIR_CELL.$row, $value->hasil+$value->point_setuju_tidak+$value->point_esay);
            $sheet->getStyle(self::HASIL_AKHIR_CELL.$row)->applyFromArray(self::styleGeneral());
            $row++;
        }

        return $spreadsheet;
    }
}
