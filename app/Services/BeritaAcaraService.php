<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Actions\SendResponse;
use TCPDF;

class BeritaAcaraService 
{
    private $pdf;
    private $event;

    public function __construct($event_id) 
    {
        try {
            $this->beritaAcara($event_id);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
    /**
     * Cetak laporan berita acara
     * @param int $event_id
     * @return void
     * @author <wandinak17@gmail.com>
     */
    public function beritaAcara($event_id)
    {
        try {
            $event = DB::table('event_ujians')
                ->where('id', $event_id)
                ->first();

            $this->event = $event;

            $set = DB::table('settings')
                ->where('name','set_sekolah')
                ->first();
            $setting = json_decode($set->value);
        } catch(\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        
        $this->pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf = $this->pdf;
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        $pdf->AddPage();
        $this->_setBorder($pdf);
        $this->_setHeader($pdf, 'BERITA ACARA PELAKSANAAN '.$event->name, $setting);
        $pdf->Ln(20);
        
        $txt = 'Pada hari ini ____________ tanggal ____________ bulan ___________ tahun 2021.';
        $this->_createSection($pdf, $txt);
        $pdf->Ln(8);

        $txt = 'bertempat di '.$setting->nama_sekolah;
        $this->_createSection($pdf, $txt);
        $pdf->Ln(11);

        $txt = '1. Telah diselenggarakan '.$event->name.' untuk Mata Pelajaran ___________________,';
        $this->_createSection($pdf, $txt);
        $pdf->Ln(8);
        
        $txt = 'dari pukul ___________ sampai pukul ________, Sesi _________ .';
        $this->_createSection($pdf, $txt, 6);
        $pdf->Ln(10);

        $left_column = 'Ruang';
        $null = ': _______________________________';
        $cols = [
            'Ruang',
            'Jumlah peserta seharusnya',
            'Jumlah Hadir (Ikut Ujian)',
            'Jumlah Tidak Hadir',
            'No.Peserta yang Tidak Hadir'
        ];

        foreach($cols as $col) {
            $this->_create2ColumnSection($pdf, $col, $null);
        }
        $pdf->Ln(8);

        $txt = '2. Catatan selama pelaksanaan ujian: ';
        $this->_createSection($pdf, $txt);
        $pdf->Ln(10);

        for ($i = 0; $i < 4; $i++) {
            $txt = '_______________________________________________________________________';
            $this->_createSection($pdf, $txt, 6);
            $pdf->Ln(10);
        }
        $pdf->Ln(20);

        $txt = 'Yang membuat berita acara';
        $this->_createSection($pdf, $txt, 6);
        $pdf->Ln(10);

        $left_column = 'Nama';

        $right_column = 'TTD';


        // set color for background
        $pdf->SetFillColor(255, 255, 255);

        // write the first column
        $pdf->MultiCell(90, 0, $left_column,0, 'C', 1, 0, '', '', true, 0, false, true, 0);

        // set color for background
        $pdf->SetFillColor(255, 255, 255);

        // write the second column
        $pdf->MultiCell(90, 0, $right_column, 0, 'C', 1, 1, '', '', true, 0, false, true, 0);
        $pdf->Ln(0);

        $left_column = '1. Pengawas';

        $right_column = '1. ________________________';
        // set color for background
        $pdf->SetFillColor(255, 255, 255);

        // write the first column
        $pdf->MultiCell(30, 0, $left_column,0, 'L', 1, 0, '', '', true, 0, false, true, 0);

        // set color for background
        $pdf->SetFillColor(255, 255, 255);

        // write the first column
        $pdf->MultiCell(50, 0, "______________",0, 'L', 1, 0, '', '', true, 0, false, true, 0);

        // set color for background
        $pdf->SetFillColor(255, 255, 255);

        // write the second column
        $pdf->MultiCell(90, 0, $right_column, 0, 'C', 1, 1, '', '', true, 0, false, true, 0);
        $pdf->Ln(0);
        
        $pdf->MultiCell(25, 0, "NIP",0, 'L', 1, 0, 15, '', true, 0, false, true, 0);
        // write the first column
        $pdf->MultiCell(50, 0, "______________",0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $pdf->MultiCell(30, 0, "", 0, 'C', 1, 1, '', '', true, 0, false, true, 0);
        
        $pdf->Ln(0);

        $left_column = '2. Pengawas';

        $right_column = '2. ________________________';

        // MultiCell($w, $h, $txt, $border=0, $align='J', $fill=0, $ln=1, $x='', $y='', $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0)

        // set color for background
        $pdf->SetFillColor(255, 255, 255);

        // write the first column
        $pdf->MultiCell(30, 0, $left_column,0, 'L', 1, 0, '', '', true, 0, false, true, 0);

        // set color for background
        $pdf->SetFillColor(255, 255, 255);

        // write the first column
        $pdf->MultiCell(50, 0, "______________",0, 'L', 1, 0, '', '', true, 0, false, true, 0);

        // set color for background
        $pdf->SetFillColor(255, 255, 255);

        // write the second column
        $pdf->MultiCell(90, 0, $right_column, 0, 'C', 1, 1, '', '', true, 0, false, true, 0);
        $pdf->Ln(0);
        
        $pdf->MultiCell(25, 0, "NIP",0, 'L', 1, 0, 15, '', true, 0, false, true, 0);
        // write the first column
        $pdf->MultiCell(50, 0, "______________",0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $pdf->MultiCell(30, 0, "", 0, 'C', 1, 1, '', '', true, 0, false, true, 0);
        
        $pdf->Ln(0);

        $pdf->lastPage();
    }

    /**
     * Download berita acara
     */
    public function download() 
    {
        $this->pdf->Output('Berita Acara '.$this->event->name.'.pdf', 'D');
    }

    /**
     * Tampilkan ke browser
     */
    public function show()
    {
        $this->pdf->Output('Berita Acara '.$this->event->name.'.pdf', 'I');
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
            ['width' => 0.5, 'break' => 5],
            ['width' => 0.3, 'break' => 7]
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
            $image_file = storage_path('app/public/'.$setting->logo);
            $ext = pathinfo(storage_path('app/public/'.$setting->logo), PATHINFO_EXTENSION);
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

    /**
     * Buat section pada pdf
     * @param object $pdf
     * @param string $text
     * @param int $ml
     * @return void
     */
    private function _createSection(TCPDF $pdf, $text, $ml=1)
    {
        $pdf->setCellPaddings(1, 1, 1, 1);
        $pdf->setCellMargins($ml, 1, 1, 1);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->MultiCell(0, 5,$text, 0, 'L', 1, 0, '', '', true);
    }

    /**
     * Buat 2 colom section pada pdf
     * @param object $pdf
     * @param string $text1
     * @param string $text2
     * @param int $w
     * @return void
     * @author <wandinak17@gmail.com>
     */
    private function _create2ColumnSection(TCPDF $pdf, $text1, $text2, $w = 80)
    {
        $pdf->SetFillColor(255, 255, 255);
        $pdf->MultiCell($w, 0, $text1,0, 'R', 1, 0, '', '', true, 0, false, true, 0);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->MultiCell($w, 0, $text2, 0, 'L', 1, 1, '', '', true, 0, false, true, 0);
    }
}