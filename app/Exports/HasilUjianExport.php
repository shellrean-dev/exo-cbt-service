<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\DB;

/**
 * Export hasil ujian
 *
 * @author shellrean <wandinak17@gmail.com>
 * @since 2.0.1 <latte>
 */
class HasilUjianExport extends ExportExcel
{
    public const LISTENING_CELL__BENAR = "D";
    public const LISTENING_CELL__SALAH = "E";

    public const PG_CELL__BENAR = "F";
    public const PG_CELL__SALAH = "G";

    public const PG_KOMPLEKS_CELL__BENAR = "H";
    public const PG_KOMPLEKS_CELL__SALAH = "I";

    public const ISIAN_SINGKAT_CELL__BENAR = "J";
    public const ISIAN_SINGKAT_CELL__SALAH = "K";

    public const MENJODOHKAN_CELL__BENAR = "L";
    public const MENJODOHKAN_CELL__SALAH = "M";

    public const MENGURUTKAN_CELL__BENAR = "N";
    public const MENGURUTKAN_CELL__SALAH = "O";

    public const BENAR_SALAH_CELL__BENAR = "P";
    public const BENAR_SALAH_CELL__SALAH = "Q";

    public const KOSONG_CELL = "R";
    public const POINT_ESAY_CELL = "S";
    public const POINT_ARGUMENT_CELL = "T";
    public const HASIL_AKHIR_CELL = "U";

    public const MULAI_UJIAN = "V";
    public const SELESAI_UJIAN = "W";
    public const PENGERJAAN = "X";

    public static function export($datas, $kode)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

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
        $sheet->getStyle('A3:C3')->applyFromArray(self::styleGeneral());

        $sheet->setCellValue(self::LISTENING_CELL__BENAR.'3', 'LISTENING (BENAR)');
        $sheet->getStyle(self::LISTENING_CELL__BENAR.'3')->applyFromArray(self::styleYellow());
        $sheet->getStyle(self::LISTENING_CELL__BENAR.'3')->getAlignment()->setTextRotation(90);
        $sheet->getStyle(self::LISTENING_CELL__BENAR.'3')->getAlignment()->setWrapText(true);

        $sheet->setCellValue(self::LISTENING_CELL__SALAH.'3', 'LISTENING (SALAH)');
        $sheet->getStyle(self::LISTENING_CELL__SALAH.'3')->applyFromArray(self::styleGeneral());
        $sheet->getStyle(self::LISTENING_CELL__SALAH.'3')->getAlignment()->setTextRotation(90);
        $sheet->getStyle(self::LISTENING_CELL__SALAH.'3')->getAlignment()->setWrapText(true);

        $sheet->setCellValue(self::PG_CELL__BENAR.'3', 'PILIHAN GANDA (BENAR)');
        $sheet->getStyle(self::PG_CELL__BENAR.'3')->applyFromArray(self::styleYellow());
        $sheet->getStyle(self::PG_CELL__BENAR.'3')->getAlignment()->setTextRotation(90);
        $sheet->getStyle(self::PG_CELL__BENAR.'3')->getAlignment()->setWrapText(true);

        $sheet->setCellValue(self::PG_CELL__SALAH.'3', 'PILIHAN GANDA (SALAH)');
        $sheet->getStyle(self::PG_CELL__SALAH.'3')->applyFromArray(self::styleGeneral());
        $sheet->getStyle(self::PG_CELL__SALAH.'3')->getAlignment()->setTextRotation(90);
        $sheet->getStyle(self::PG_CELL__SALAH.'3')->getAlignment()->setWrapText(true);

        $sheet->setCellValue(self::PG_KOMPLEKS_CELL__BENAR.'3', 'PILIHAN GANDA KOMPLEKS (BENAR)');
        $sheet->getStyle(self::PG_KOMPLEKS_CELL__BENAR.'3')->applyFromArray(self::styleYellow());
        $sheet->getStyle(self::PG_KOMPLEKS_CELL__BENAR.'3')->getAlignment()->setTextRotation(90);
        $sheet->getStyle(self::PG_KOMPLEKS_CELL__BENAR.'3')->getAlignment()->setWrapText(true);

        $sheet->setCellValue(self::PG_KOMPLEKS_CELL__SALAH.'3', 'PILIHAN GANDA KOMPLEKS (SALAH)');
        $sheet->getStyle(self::PG_KOMPLEKS_CELL__SALAH.'3')->applyFromArray(self::styleGeneral());
        $sheet->getStyle(self::PG_KOMPLEKS_CELL__SALAH.'3')->getAlignment()->setTextRotation(90);
        $sheet->getStyle(self::PG_KOMPLEKS_CELL__SALAH.'3')->getAlignment()->setWrapText(true);

        $sheet->setCellValue(self::ISIAN_SINGKAT_CELL__BENAR.'3', 'ISIAN SINGKAT (BENAR)');
        $sheet->getStyle(self::ISIAN_SINGKAT_CELL__BENAR.'3')->applyFromArray(self::styleYellow());
        $sheet->getStyle(self::ISIAN_SINGKAT_CELL__BENAR.'3')->getAlignment()->setTextRotation(90);
        $sheet->getStyle(self::ISIAN_SINGKAT_CELL__BENAR.'3')->getAlignment()->setWrapText(true);

        $sheet->setCellValue(self::ISIAN_SINGKAT_CELL__SALAH.'3', 'ISIAN SINGKAT (SALAH)');
        $sheet->getStyle(self::ISIAN_SINGKAT_CELL__SALAH.'3')->applyFromArray(self::styleGeneral());
        $sheet->getStyle(self::ISIAN_SINGKAT_CELL__SALAH.'3')->getAlignment()->setTextRotation(90);
        $sheet->getStyle(self::ISIAN_SINGKAT_CELL__SALAH.'3')->getAlignment()->setWrapText(true);

        $sheet->setCellValue(self::MENJODOHKAN_CELL__BENAR.'3', 'MENJODOHKAN (BENAR)');
        $sheet->getStyle(self::MENJODOHKAN_CELL__BENAR.'3')->applyFromArray(self::styleYellow());
        $sheet->getStyle(self::MENJODOHKAN_CELL__BENAR.'3')->getAlignment()->setTextRotation(90);
        $sheet->getStyle(self::MENJODOHKAN_CELL__BENAR.'3')->getAlignment()->setWrapText(true);

        $sheet->setCellValue(self::MENJODOHKAN_CELL__SALAH.'3', 'MENJODOHKAN (SALAH)');
        $sheet->getStyle(self::MENJODOHKAN_CELL__SALAH.'3')->applyFromArray(self::styleGeneral());
        $sheet->getStyle(self::MENJODOHKAN_CELL__SALAH.'3')->getAlignment()->setTextRotation(90);
        $sheet->getStyle(self::MENJODOHKAN_CELL__SALAH.'3')->getAlignment()->setWrapText(true);

        $sheet->setCellValue(self::MENGURUTKAN_CELL__BENAR.'3', 'MENGURUTKAN (BENAR)');
        $sheet->getStyle(self::MENGURUTKAN_CELL__BENAR.'3')->applyFromArray(self::styleYellow());
        $sheet->getStyle(self::MENGURUTKAN_CELL__BENAR.'3')->getAlignment()->setTextRotation(90);
        $sheet->getStyle(self::MENGURUTKAN_CELL__BENAR.'3')->getAlignment()->setWrapText(true);

        $sheet->setCellValue(self::MENGURUTKAN_CELL__SALAH.'3', 'MENGURUTKAN (SALAH)');
        $sheet->getStyle(self::MENGURUTKAN_CELL__SALAH.'3')->applyFromArray(self::styleGeneral());
        $sheet->getStyle(self::MENGURUTKAN_CELL__SALAH.'3')->getAlignment()->setTextRotation(90);
        $sheet->getStyle(self::MENGURUTKAN_CELL__SALAH.'3')->getAlignment()->setWrapText(true);

        $sheet->setCellValue(self::BENAR_SALAH_CELL__BENAR.'3', 'BENAR/SALAH (BENAR)');
        $sheet->getStyle(self::BENAR_SALAH_CELL__BENAR.'3')->applyFromArray(self::styleYellow());
        $sheet->getStyle(self::BENAR_SALAH_CELL__BENAR.'3')->getAlignment()->setTextRotation(90);
        $sheet->getStyle(self::BENAR_SALAH_CELL__BENAR.'3')->getAlignment()->setWrapText(true);

        $sheet->setCellValue(self::BENAR_SALAH_CELL__SALAH.'3', 'BENAR/SALAH (SALAH)');
        $sheet->getStyle(self::BENAR_SALAH_CELL__SALAH.'3')->applyFromArray(self::styleGeneral());
        $sheet->getStyle(self::BENAR_SALAH_CELL__SALAH.'3')->getAlignment()->setTextRotation(90);
        $sheet->getStyle(self::BENAR_SALAH_CELL__SALAH.'3')->getAlignment()->setWrapText(true);

        $sheet->setCellValue(self::KOSONG_CELL.'3', 'KOSONG');
        $sheet->getStyle(self::KOSONG_CELL.'3')->applyFromArray(self::styleYellow());
        $sheet->getStyle(self::KOSONG_CELL.'3')->getAlignment()->setTextRotation(90);
        $sheet->getStyle(self::KOSONG_CELL.'3')->getAlignment()->setWrapText(true);

        $sheet->setCellValue(self::POINT_ESAY_CELL.'3', 'POINT ESAY');
        $sheet->getStyle(self::POINT_ESAY_CELL.'3')->applyFromArray(self::styleYellow());
        $sheet->getStyle(self::POINT_ESAY_CELL.'3')->getAlignment()->setTextRotation(90);
        $sheet->getStyle(self::POINT_ESAY_CELL.'3')->getAlignment()->setWrapText(true);

        $sheet->setCellValue(self::POINT_ARGUMENT_CELL.'3', 'POINT SETUJU/TIDAK');
        $sheet->getStyle(self::POINT_ARGUMENT_CELL.'3')->applyFromArray(self::styleYellow());
        $sheet->getStyle(self::POINT_ARGUMENT_CELL.'3')->getAlignment()->setTextRotation(90);
        $sheet->getStyle(self::POINT_ARGUMENT_CELL.'3')->getAlignment()->setWrapText(true);

        $sheet->setCellValue(self::HASIL_AKHIR_CELL.'3', 'HASIL AKHIR');
        $sheet->getStyle(self::HASIL_AKHIR_CELL.'3')->applyFromArray(self::styleYellow());
        $sheet->getStyle(self::HASIL_AKHIR_CELL.'3')->getAlignment()->setTextRotation(90);
        $sheet->getStyle(self::HASIL_AKHIR_CELL.'3')->getAlignment()->setWrapText(true);

        $sheet->setCellValue(self::MULAI_UJIAN.'3', 'MULAI UJIAN');
        $sheet->getStyle(self::MULAI_UJIAN.'3')->applyFromArray(self::styleGood());
        $sheet->getStyle(self::MULAI_UJIAN.'3')->getAlignment()->setTextRotation(90);
        $sheet->getStyle(self::MULAI_UJIAN.'3')->getAlignment()->setWrapText(true);

        $sheet->setCellValue(self::SELESAI_UJIAN.'3', 'SELESAI UJIAN');
        $sheet->getStyle(self::SELESAI_UJIAN.'3')->applyFromArray(self::styleGood());
        $sheet->getStyle(self::SELESAI_UJIAN.'3')->getAlignment()->setTextRotation(90);
        $sheet->getStyle(self::SELESAI_UJIAN.'3')->getAlignment()->setWrapText(true);

        $sheet->setCellValue(self::PENGERJAAN.'3', 'WAKTU PENGERJAAN UJIAN');
        $sheet->getStyle(self::PENGERJAAN.'3')->applyFromArray(self::styleGood());
        $sheet->getStyle(self::PENGERJAAN.'3')->getAlignment()->setTextRotation(90);
        $sheet->getStyle(self::PENGERJAAN.'3')->getAlignment()->setWrapText(true);

        $row = 4;
        foreach ($datas as $key => $value) {
            $sheet->setCellValue('A'.$row, $key+1);
            $sheet->getStyle('A'.$row)->applyFromArray(self::styleGeneral());

            $sheet->setCellValue('B'.$row, $value->peserta->no_ujian);
            $sheet->getStyle('B'.$row)->applyFromArray(self::styleGeneral());

            $sheet->setCellValue('C'.$row, $value->peserta->nama);
            $sheet->getStyle('C'.$row)->applyFromArray(self::styleGeneral());

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

            $sheet->setCellValue(self::MULAI_UJIAN.$row, $value->ujian->mulai);
            $sheet->getStyle(self::MULAI_UJIAN.$row)->applyFromArray(self::styleGeneral());

            $sheet->setCellValue(self::SELESAI_UJIAN.$row, $value->ujian->selesai);
            $sheet->getStyle(self::SELESAI_UJIAN.$row)->applyFromArray(self::styleGeneral());

            $sheet->setCellValue(self::PENGERJAAN.$row, $value->ujian->pengerjaan);
            $sheet->getStyle(self::PENGERJAAN.$row)->applyFromArray(self::styleGeneral());

            $row++;
        }

        return $spreadsheet;
    }
}
