<?php
namespace App\Exports;
use TCPDF;

class KartuPesertaPdf {
    private $pdf;

    public function __construct()
    {

    }

    public function download()
    {
        
    }

    public function show()
    {
        $this->pdf->Output('KARTU PESERTA.pdf', 'I');
    }

    public function generate()
    {
        $this->pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf = $this->pdf;
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Extraordinary CBT');
        
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        $pdf->SetFont('dejavusans', '', 9, '', true);
        $pdf->setFontSubsetting(true);
        $pdf->setPrintHeader(false);

        $pdf->AddPage();
        $subtable = '<table border="1" cellspacing="6" cellpadding="4"><tr><td>a</td><td>b</td></tr><tr><td>c</td><td>d</td></tr></table>';

        $html = '<h2>HTML TABLE:</h2>
        <table cellspacing="3" cellpadding="4">
            <tr>
                <th>
                ljfsdlfjdsfdsfsd</th>
                <th>fdsfdsfdsfdsfdsfds</th>
            </tr>
            <tr>
                <td>
                    <table border="1">
                        <tr>
                            <td>
                                KARTU LOGIN PESERTA
                            </td>
                        </tr>
                    </table>
                </td>
                <td></td>
            </tr>
        </table>';
        
        // output the HTML content
        $pdf->writeHTML($html, true, false, true, false, '');

        // $pdf->AddPage();

        // // create some HTML content
        // $html = '<h1>Image alignments on HTML table</h1>
        // <table cellpadding="1" cellspacing="1" border="1" style="text-align:center;">
        // <tr><td><img src="images/logo_example.png" border="0" height="41" width="41" /></td></tr>
        // <tr style="text-align:left;"><td><img src="images/logo_example.png" border="0" height="41" width="41" align="top" /></td></tr>
        // <tr style="text-align:center;"><td><img src="images/logo_example.png" border="0" height="41" width="41" align="middle" /></td></tr>
        // <tr style="text-align:right;"><td><img src="images/logo_example.png" border="0" height="41" width="41" align="bottom" /></td></tr>
        // <tr><td style="text-align:left;"><img src="images/logo_example.png" border="0" height="41" width="41" align="top" /></td></tr>
        // <tr><td style="text-align:center;"><img src="images/logo_example.png" border="0" height="41" width="41" align="middle" /></td></tr>
        // <tr><td style="text-align:right;"><img src="images/logo_example.png" border="0" height="41" width="41" align="bottom" /></td></tr>
        // </table>';

        // // output the HTML content
        // $pdf->writeHTML($html, true, false, true, false, '');

        $pdf->lastPage();
    }
}