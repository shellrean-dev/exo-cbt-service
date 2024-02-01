<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Actions\SendResponse;
use TCPDF;

class AbsensiUjianService {

    private $pdf;
    private $jadwal_id;
    private $sesi;
    private $event;

    public function __construct($jadwal_id, $sesi)
    {
        $this->jadwal_id = $jadwal_id;
        $this->sesi = $sesi;
    }

    public function generate()
    {
        try {
            $jadwal = DB::table('jadwals')
                ->where('id', $this->jadwal_id)
                ->select('id','alias','event_id')
                ->first();

            if (!$jadwal) {
                throw new \Exception('jadwal tidak ditemukan');
            }

            $event = DB::table('event_ujians')
                ->where('id', $jadwal->event_id)
                ->first();

            if (!$event) {
                throw new \Exception('event ujian tidak ditemukan');
            }

            $this->event = $event;

            $sesi = DB::table('sesi_schedules')
                ->where('jadwal_id', $this->jadwal_id)
                ->where('sesi', $this->sesi)
                ->select('id','peserta_ids')
                ->first();

            if (!$sesi) {
                throw new \Exception('sesi tidak ditemukan');
            }

            $students = DB::table('pesertas')
                ->whereIn('id', json_decode($sesi->peserta_ids, true))
                ->select('id','no_ujian','nama')
                ->get();

            $set = DB::table('settings')
                ->where('name','set_sekolah')
                ->first();

            if (!$set) {
                throw new \Exception('setting tidak ditemukan');
            }
            $setting = json_decode($set->value);
        } catch(\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        $this->pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf = $this->pdf;
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Extraordinary CBT');
        $pdf->SetTitle('ABSENSI '.$event->name.' SESI '.$this->sesi);

        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        $pdf->setFontSubsetting(true);
        $pdf->SetFont('dejavusans', '', 9, '', true);
        $pdf->setPrintHeader(false);

        $pdf->AddPage();
        // $this->_setBorder($pdf);
        $this->_setHeader($pdf, 'DAFTAR HADIR <br />'.$event->name, $setting);
        $pdf->Ln(20);

        $this->_setHeaderDetail($pdf);
        $pdf->Ln(10);

        $data = [
            ['no_ujian' => '0101']
        ];

        $table = "";
        foreach($students as $idx => $student) {
            if ($idx % 2 == 0) {
                $ttd = '<table>
                    <tr>
                        <td>'.($idx+1).'.</td>
                        <td></td>
                    </tr>
                </table>';
            } else {
                $ttd = '<table>
                    <tr>
                        <td></td>
                        <td>'.($idx+1).'.</td>
                    </tr>
                </table>';
            }
            $table .= '<tr nobr="true">
                <td>'.($idx+1).'</td>
                <td>'.$student->no_ujian.'</td>
                <td>'.$student->nama.'</td>
                <td>'.$ttd.'</td>
                <td></td>
            </tr>';
        }

        $html = <<<EOD
        <table cellspacing="0" cellpadding="5" border="0.3" style="border-color:#cccccc;">
            <tr>
                <td width="50">No</td>
                <td>No Ujian</td>
                <td width="160">Nama</td>
                <td>Tanda Tangan</td>
                <td>Ket</td>
            </tr>
            {$table}
        </table>
        EOD;

        // Print text using writeHTMLCell()
        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);


        $pdf->lastPage();
    }

    public function show()
    {
        $this->pdf->Output('Absensi '.$this->event->name.' sesi-'.$this->sesi.'.pdf', 'I');
    }

    public function download()
    {
        $this->pdf->Output('Absensi '.$this->event->name.' sesi-'.$this->sesi.'.pdf', 'D');
    }

    /**
     * Set border pada page
     *
     * @param object $pdf
     * @return void
     * @author <wandinak17@gmail.com>
     */
    private function _setBorder(TCPDF $pdf)
    {
        $data = [
            ['width' => 0.5, 'break' => 3],
            ['width' => 0.3, 'break' => 4]
        ];

        $pageWidth = $pdf->getPageWidth();
        $pageHeight = $pdf->getPageHeight();

        foreach($data as $set) {
            $pdf->SetLineStyle( array( 'width' => $set['width'], 'color' => array(0,0,0)));
            $pdf->Line($set['break'],$set['break'],$pageWidth-$set['break'],$set['break']);
            $pdf->Line($pageWidth-$set['break'],$set['break'],$pageWidth-$set['break'],$pageHeight-$set['break']);
            $pdf->Line($set['break'],$pageHeight-$set['break'],$pageWidth-$set['break'],$pageHeight-$set['break']);
            $pdf->Line($set['break'],$set['break'],$set['break'],$pageHeight-$set['break']);
        }
    }

    /**
     * Ambil logo pada sekolah
     *
     * @param object $setting
     * @return void
     * @author <wandinak17@gmail.com>
     */
    private function _getLogo($setting)
    {
        if ($setting->logo == '') {
            $image_file = public_path('img/exo.jpg');
            $ext = 'jpg';
        } else {
            $image_file = public_path('storage/'.$setting->logo);
            $ext = pathinfo(public_path('storage/'.$setting->logo), PATHINFO_EXTENSION);
        }
        return ['img' => $image_file, 'ext' => $ext];
    }

    /**
     * Buat header default
     * @param object $pdf
     * @param string $title
     * @param array $setting
     * @return void
     * @author <wandinak17@gmail.com>
     */
    private function _setHeader(TCPDF $pdf, $title, $setting)
    {
        $logo = $this->_getLogo($setting);

        $pdf->Image($logo['img'], 10, 10, 14, '', $logo['ext']);
        $tahun_ajaran = '';
        $month = now()->month;
        if ($month <= 6) {
            $tahun_ajaran = now()->add(-1,'year')->format('Y').'/'.now()->format('Y');
        } else {
            $tahun_ajaran = now()->format('Y').'/'.now()->add(1, 'year')->format('Y');
        }
        $html = '<h4>'.$title.'<br /> TAHUN PELAJARAN '.$tahun_ajaran.'</h4>';
        $pdf->writeHTMLCell(
            $w=0,
            $h=0,
            $x=0,
            $y=10,
            $html,
            $border=0,
            $ln=0,
            $fill=false,
            $reseth=true,
            $align='C'
        );
    }

    private function _setHeaderDetail(TCPDF $pdf)
    {
        $pdf->SetFillColor(255, 255, 255);
        $pdf->MultiCell(40, 0, "HARI",0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $pdf->MultiCell(80, 0, ": _________ TANGGAL: ____________",0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $pdf->MultiCell(20, 0, "PUKUL",0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $pdf->MultiCell(50, 0, ": __________________ ",0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $pdf->Ln(6);
        $pdf->MultiCell(40, 0, "RUANG",0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $pdf->MultiCell(80, 0, ": _______________________________",0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $pdf->MultiCell(20, 0, "SESI",0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $pdf->MultiCell(50, 0, ": ".$this->sesi." ",0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $pdf->Ln(6);
        $pdf->MultiCell(40, 0, "MATA PELAJARAN",0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $pdf->MultiCell(80, 0, ": _______________________________",0, 'L', 1, 0, '', '', true, 0, false, true, 0);
    }
}
