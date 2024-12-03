<?php

    tcpdf();
    date_default_timezone_set('Asia/Manila');
    // create new PDF document
    //$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    //$pdf = new TCPDF("P", PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    //$pdf = new TCPDF('P', 'mm', 'A4', FALSE, 'ISO-8859-1', false, true);
    //$pdf = new TCPDF('P', PDF_UNIT, array(8.5, 11), false, 'ISO-8859-1', false, true);
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    //$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, false, 'ISO-8859-1', false);
    //$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, false, 'ISO-8859-1', false);
    //$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, false, 'ISO-8859-1', false);
    
    // set document information
    $pdf->SetCreator(PDF_CREATOR);
    //$pdf->SetTitle($studentInfo);
    
    // set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    //$pdf->SetAutoPageBreak(TRUE, 6);
    
   //font setting
    $pdf->SetFont('helvetica', '', 11, '', true);
    //$pdf->SetFont('calibril_0', '', 10, '', 'false');
    
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    // Add a page
    // This method has several options, check the source code documentation for more information.
    $pdf->AddPage();
    

    
    // Set some content to print
$html = '
        <table border="0" cellspacing="0" cellpadding="0" style="color:#333; font-size:9;">
            <tr>
                <td width="100%" align="center" style="text-align:center;vertical-align: middle;"><img src= "https://i.ibb.co/XW1DRVT/iacademy-logo.png"  width="100" height="29"/></td>
            </tr>
            <tr>            
                <td colspan = "3" width="100%" style="text-align: center;">             
                    
                </td>
            </tr>        
            <tr>            
                <td colspan = "3" width="100%" style="text-align: center;line-height:1">             
                    <font style="font-family:Calibri Light; font-size: 14;font-weight: bold;">iACADEMY, Inc. </font><br /><br />
                    <font style="font-family:Calibri Light; font-size: 10;">Filinvest Cebu Cyberzone Tower 2 Salinas Drive corner W. Geonzon St., Brgy. Apas, Lahug, Cebu City</font><br />             
                </td>           
            </tr>
            <tr>
                <td colspan = "3" style="font-weight: bold;text-align:center; font-size:11; border-bottom:1px solid #333;">SCHEDULE</td>
            </tr>    
        </table>
        <table border="0" cellpadding="0" style="color:#014fb3; font-size:10;">        
        <tr>
            <td colspan = "3" style="text-align:center; color:black; font-size: 10;"> A.Y. ' .$active_sem['strYearStart']."-".$active_sem['strYearEnd'] . ", " . $active_sem['enumSem'].' Semester' . '</td>
        </tr>
        <tr>
            <td colspan = "3" style="text-align:center; color:black; font-size: 10;"><table border="0" cellpadding="0" style="color:#014fb3; font-size:10;">
                    <tr>
                        <td colspan="4"></td>
                    </tr>
                    <tr>
                        <td width="80" style="text-align: left;">Name of Faculty: </td>
                        <td width="320" style="text-align: left;color:black;">' . $facultyName . '</td>
                        <td width="100"style="text-align: left;">No. of preparation: </td>
                        <td width="27" style="text-align: left;"></td>
                    </tr>
                    <tr>
                        <td width="80" style="text-align: left;">School: </td>
                        <td width="320" style="text-align: left;color:black;">'. $facultyDept .' </td>
                        <td width="100" style="text-align: left;">No. of Contract Hours: </td>
                        <td width="27" style="text-align: left;"></td>
                    </tr>
                </table>
            </td>
                
        </tr>
        <tr>
        <td colspan="3" style="font-size:10;">
        </td>
        </tr>
    </table>';

$html .= $sched;
$html .= '<table border="0" cellpadding="0" style="color:#014fb3; font-size:10;">
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">Conforme: </td>
                        <td style="text-align: center;"> Recommending Approval:</td>
                        <td style="text-align: center;">Approved: </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td style="text-align: center;color:black;">'. $facultyName . '</td>
                        <td style="text-align: center;color:black;"></td>
                        <td style="text-align: center;color:black;"></td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">Faculty</td>
                        <td style="text-align: center;">School Dean</td>
                        <td style="text-align: center;"></td>
                    </tr>
                </table>';
//$html = utf8_encode($html);
$pdf->writeHTML($html, true, false, true, false, '');
//$pdf->writeHTML($html, true, false, true, false, '');
// Print text using writeHTMLCell()
//$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

// ---------------------------------------------------------

// Close and output PDF document
// This method has several options, check the source code documentation for more information.
$pdf->Output($facultyName. ".pdf", 'I');

?>