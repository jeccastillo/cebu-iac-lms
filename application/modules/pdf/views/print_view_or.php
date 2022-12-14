<?php

    tcpdf();
    // create new PDF document
    //$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    //$pdf = new TCPDF("P", PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf = new TCPDF("P", PDF_UNIT, array(4, 8), true, 'UTF-8', false);

    // set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetTitle($transactions[0]['intORNumber'] . "-" . $student['strLastname'].', '.$student['strFirstname'].' '.$student['strMiddlename']);
    
    
    // set margins
    $pdf->SetMargins(.1, .1, .1);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    //$pdf->SetAutoPageBreak(TRUE, 6);
    
   //font setting
    //$pdf->SetFont('calibril_0', '', 10, '', 'false');
    
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    // Add a page
    // This method has several options, check the source code documentation for more information.
    $pdf->AddPage();
    
    
    // Set some content to print
$html = '<table border="0" cellpadding="0" style="color:black; font-size:12;" width="100%">
            <tr >
                <td style="height:512px;"><table border="0" cellpadding="0" style="color:black; font-size:12;" width="100%">
                        <tr>
                            <td style="height:100px;" ><table border="1" cellpadding="0" style="color:black;" width="100%">
                                                    <tr>
                                                        <td></td>
                                                    </tr>
                                                    <tr style="text-align:center;font-family:Times; font-size:12pt;font-weight:bold;">
                                                        <td>OFFICIAL RECEIPT</td>
                                                    </tr>
                                                    <tr style="text-align:center;font-family:Calibri;font-size:9pt;">
                                                        <td>Republic of the Philippines</td>
                                                    </tr>
                                                    <tr style="text-align:center;font-family:Calibri;font-size:10pt; font-weight:bold;">
                                                        <td>Province of Cavite</td>
                                                    </tr>
                                                    <tr style="text-align:center;font-family:Calibri;font-size:10pt; font-weight:bold;">
                                                        <td>City of Makati</td>
                                                    </tr>
                                                    <tr style="text-align:center;font-family:Calibri;font-size:10pt; font-weight:bold;">
                                                        <td>OFFICE OF THE TREASURER</td>
                                                    </tr>
                                                    <tr style="text-align:center;font-family:Calibri;font-size:10pt; font-weight:bold;">
                                                        <td></td>
                                                    </tr>
                                                    <tr style="text-align:center;font-family:Calibri;font-size:8pt; font-weight:bold;">
                                                        <td><table border="1" cellpadding="0" style="color:black;" width="100%">
                                                                <tr>
                                                                    <td style="height:30px; width:120px; vertical-align: center;" > Accountable Form No. 51 <br /> Revised January, 1992</td>
                                                                    <td style="font-family:Times; font-size: 12pt; width:153px;">ORIGINAL</td>
                                                                </tr>
                                                                <tr>
                                                                    <td style="height:55px;text-align:left;" > NO <br /></td>
                                                                    <td style="height:55px;text-align:left;"> DATE <br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                                   '.$transactions[0]['dtePaid'].'
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td style="height:40px;text-align:left;" colspan="2"> PAYOR: <br /><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.
    $student['strLastname'].', '.$student['strFirstname'].' '.$student['strMiddlename']
    .'</td>
                                                                    
                                                                </tr>
                                                                <tr>
                                                                    <td colspan="2"><table border="1" cellpadding="0">
                                                                    <tr style="text-align: center;font-size:6pt; ">
                                                                        <td style="width: 130px;height: 30px;">NATURE OF COLLECTION</td>
                                                                        <td style="width: 52px;">FUND AND ACCOUNT <br />CODE</td>
                                                                        <td style="width: 91px;" colspan="2">AMOUNT</td></tr>
                                                                    </table>
                                                                    </td>
                                                                </tr>
                                                                
                                                                <tr>
                                                                    <td style="width:273px; height:150px;" colspan="2"><table border="1" cellpadding="0">';
                    foreach($transactions as $trans){
          
                            $html.= '<tr style="text-align: left;font-size:8pt; ">
                                        <td style="width: 130px;height: 17px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $trans['strTransactionType']. '</td>
                                        <td style="width: 52px;"></td>
                                        <td style="width: 91px;" colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.  $trans['intAmountPaid'].'</td>
                                    </tr>';
                                                                        
                    }
                                                                        
                            $html.='</table></td>
                                                                    
                                                                </tr>
                                                                                                                  
                                                                </table>
                                                        </td>
                                                    </tr>
                                                    </table>
                            </td>
                        </tr>
                
                    </table>   
                             
                </td>
            </tr>
        </table>';

$html = utf8_encode($html);
// Print text using writeHTMLCell()
$pdf->writeHTML($html);
//$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

// ---------------------------------------------------------

// Close and output PDF document
// This method has several options, check the source code documentation for more information.
$pdf->Output($transactions[0]['intORNumber'] . "-" . $student['strLastname'].', '.$student['strFirstname'].' '.$student['strMiddlename']. ".pdf", 'I');

?>