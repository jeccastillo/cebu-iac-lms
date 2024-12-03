<?php

    tcpdf();
    // create new PDF document
    //$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    //$pdf = new TCPDF("P", PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, array(8.5,11), true, 'UTF-8', false, true);

    // set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetTitle("Blank Student Registration Form");
    
    // set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
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
    //$payment_division = $tuition['total'] / 4;
    
    // Set some content to print
$html = '<table border="0" cellpadding="0" style="color:#014fb3; font-size:10;">
        <tr>
            <td width="64" align="right"><img src= "'.$img_dir .'tagaytayseal.png"  width="50" height="50"/></td>
            <td width="400" style="text-align: center; line-height:100%">
             
             <font style="font-family:Calibri Light; font-size: 10;font-weight: bold;">iACADEMY Inc.</font><br />
			 <font style="font-family:Calibri Light; font-size: 10;">Filinvest Cebu Cyberzone Tower 2 Salinas Drive corner W. Geonzon St., Brgy. Apas, Lahug, Cebu City</font><br />
             <font style="font-family:Calibri Light; font-size: 10;">Telephone No: (046) 483-0470 / (046) 483-0672</font><br />
            </td>
            <td width="64" align="left" valign="middle"><img src= "'.$img_dir .'iacademy-logo.png"  width="50" height="50"/></td>
        </tr>
        <tr>
            <td colspan = "3" style="font-weight: bold;text-align:center; font-size:10;"> CERTIFICATE OF REGISTRATION</td>
        </tr>
        <tr>
            <td colspan = "3" style="text-align:center; color:#014fb3; font-size: 10;">__________________________</td>
        </tr>
        <tr>
        <td colspan="3" style="font-size:10;">
        </td>
        </tr>
    </table>
     <br />
    <table border="0" cellpadding="0" style="color:#014fb3; font-size:9; border: 0px solid #014fb3;" width="528px">
     <tr>
            <td colspan ="3" style="text-align:center; background-color: #014fb3; color:white; font-size:10;">
                REGISTRATION INFORMATION
            </td>
     </tr>
     <tr>
      <td width="80px" style="border-right: 0px solid #014fb3;">&nbsp;</td>
      <td width="250px">&nbsp;</td>
      <td width="113px" style="border-left: 0px solid #014fb3;">&nbsp;DATE:</td>
      <td width="85px" style="color: black;"> </td>
     </tr>
     <tr>
      <td width="80px" style="border-right: 0px solid #014fb3;">&nbsp;NAME:</td>
      <td width="250px" style="color: black;">&nbsp;</td>
      <td width="113px" style="border-left: 0px solid #014fb3;">&nbsp;STUDENT NUMBER:</td>
      <td width="85px" style="color: black;">&nbsp;</td>
     </tr>
     <tr>
      <td width="80px" style="border-right: 0px solid #014fb3;">&nbsp;PROGRAM:</td>
      <td width="250px" style="color: black;">&nbsp;</td>
      <td width="113px" style="border-left: 0px solid #014fb3;">&nbsp;YEAR LEVEL:</td>
      <td width="85px" style="color: black;">&nbsp;</td>
     </tr>
     <tr>
      <td width="80px" style="border-right: 0px solid #014fb3;">&nbsp;MAJOR:</td>
      <td width="250px">&nbsp;</td>
      <td width="113px" style="border-left: 0px solid #014fb3;">&nbsp;REGISTRATION STATUS:</td>
      <td width="85px" style="color: black;text-transform:capitalize;">&nbsp;</td>
     </tr>
     <tr>
        <td style="border-right: 0px solid #014fb3;">&nbsp;</td>
        <td>&nbsp;</td>
        <td style="border-left: 0px solid #014fb3;">&nbsp;</td>
        <td>&nbsp;</td>
     </tr>
    </table> '; 
$html.= '<table border="0" cellpadding="0" style="color:#014fb3; font-size:8; border: 0px solid #014fb3;" width="528">
   
        <tr>
            <th width="80px" style="text-align:left; border: 0px solid #014fb3;">SECTION</th>
            <th width="65px" style="text-align:center;border: 0px solid #014fb3;">COURSE CODE</th>
            <th width="180px" style="text-align:center;border: 0px solid #014fb3;">COURSE DESCRIPTION</th>
            <th width="30px" style="text-align:center;border: 0px solid #014fb3;">UNITS</th>
            <th width="173px" style="text-align:center;border: 0px solid #014fb3;">SCHEDULE</th>
        </tr> ';
        $html.= '
                <tr>
                    <td width="80px" style="border-right: 0px solid #014fb3;"> </td>
                    <td width="65px" style="border-right: 0px solid #014fb3;"></td>
                    <td width="180px" style="border-right: 0px solid #014fb3;"></td>
                    <td width="30px" style="border-right: 0px solid #014fb3;"></td>
                    <td width="173px" style="border-right: 0px solid #014fb3;"></td>
                </tr>
                <tr>
                    <td width="80px" height="210" style="border-right: 0px solid #014fb3;"></td>
                    <td width="65px" height="210" style="border-right: 0px solid #014fb3;"></td>
                    <td width="180px" height="210" style="border-right: 0px solid #014fb3;"></td>
                    <td width="30px" height="210" style="border-right: 0px solid #014fb3;"></td>
                    <td width="173px" height="210" style="border-right: 0px solid #014fb3;"></td>';
              $html.= '</tr> ';
         
        $html.='</table>
                <table border="0" cellpadding="0" style="color:#014fb3; font-size:8; border: 0px solid #014fb3;" width="528px">
        
                    <tr style="background-color:#ffff99 ; font-weight:bold;">
                    <td width="80px">&nbsp;SUBJECTS: </td>
                    <td width="75px" style="color: black;text-align:left;"></td>
                    <td width="80px">&nbsp;LEC. UNITS: </td>
                     <td width="45px" style="color: black;text-align:center;"></td>
                    <td width="55px">&nbsp;LAB UNITS: </td>
                    <td width="45px" style="color: black;text-align:center;"></td>
                    <td width="70px">&nbsp;TOTAL CREDITS: </td>
                    <td width="78px" style="color: black;text-align:center;"></td>
                    </tr>

        </table>
        
        <table border="0" cellpadding="0" style="color:#014fb3; font-size:8; border: 0px solid #014fb3;" width="528px">
           <tr>
            <td colspan ="3" style="text-align:center; background-color: #014fb3; color:white; font-size:10;">
                BILLING INFORMATION
            </td>
        </tr>
        
        <tr style="border: 0px solid #014fb3;">
            <td width="235" style="border: 0px solid #014fb3;"> SCHOLARSHIP GRANT:</td>
            <td width="293" style="border: 0px solid #014fb3;">
                PAYMENT DETAILS:
            </td>
        </tr>
        <tr style="border: 0px solid #014fb3;">
            <td width="235" style="text-align:center; color:black; border: 0px solid #014fb3;" ></td>
            <td> &nbsp;TERMS OF PAYMENT</td>
           
        </tr>
        <tr>
            <td width="235" style="border: 0px solid #014fb3;"> ASSESSMENT OF FEES:</td>
            <td width="145"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;First Payment: </td>
            <td width="148" style="text-align:center; color:black;"></td>
        </tr>
        <tr>
            <td width="80" style="border-right: 0px solid #014fb3;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Tuition Fee:</td>
            <td width="155" style="color:black; text-align:right; border-right: 0px solid #014fb3;"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </td>
            <td width="145"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Second Payment: </td>
            <td width="148" style="text-align:center; color:black;"></td>
        </tr>
                    <tr>
                        <td width="80" style="border-right: 0px solid #014fb3;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Misc. Fee:</td>';
                        $html.= '<td width="155"style="color:black; text-align:right;border-right: 0px solid #014fb3;">  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  </td>
                        <td width="145"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Third Payment: </td>
                        <td width="148" style="text-align:center; color:black;"></td>
                    </tr>
                    <tr>
                        <td width="80" style="border-right: 0px solid #014fb3;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;I.D. Fee:</td>
                        <td width="155" style="color:black; text-align:right; border-right: 0px solid #014fb3;"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  </td>
                        <td width="145"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Fourth Payment: </td>
                        <td width="148" style="text-align:center; color:black;"></td>                    
                    </tr>
                    <tr>
                        <td width="80" style="border-right: 0px solid #014fb3;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Laboratory Fees:</td>
                            
                        <td width="155" style="color:black; text-align:right;border-right: 0px solid #014fb3;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;   </td>
                        <td></td>
                        <td width="148"></td>
                    </tr>
                    <tr>
                        <td width="80" style="border-right: 0px solid #014fb3;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;SRF:</td>
                        <td width="155" style="color:black; text-align:right;border-right: 0px solid #014fb3;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </td>
                        <td></td>
                        <td width="148"></td>
                    </tr>
                    <tr>
                        <td width="80" style="border-right: 0px solid #014fb3;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;SFDF:</td>
                        <td width="155" style="color:black; text-align:right; border-right: 0px solid #014fb3;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </td>    <td></td>
                        <td width="148"></td>
                    </tr>
                    <tr>
                        <td style="border-right: 0px solid #014fb3;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Athletic Fee</td>
                        <td width="155" style="color:black; text-align:right; border-right: 0px solid #014fb3;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </td>
                        <td></td>
                        <td width="148"></td>
                    </tr>
                    <tr>
                        <td width="80" style="border-right: 0px solid #014fb3;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;CSG:</td>
                        <td width="155" style="color:black; text-align:right; border-right: 0px solid #014fb3;"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </td>
                        <td style="border-bottom: 0px solid #014fb3;"></td>
                        <td width="148" style="border-bottom: 0px solid #014fb3;"></td>
                        
                    </tr>
                      <tr>
                        <td width="80" style="border-right: 0px solid #014fb3;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Other Fines:</td>
                        <td width="155" style="color:black; text-align:right; border-right: 0px solid #014fb3;"></td>
                        <td colspan="2" rowspan = "9" align="center" style="border-right: 0px solid #014fb3;">
                          <table border="0" width="285" style="font-size: 7px;">
                                <tr>
                                    <td style="font-weight:bold;">RULES ON FEES</td>
                                </tr>
                                <tr>
                                    <td style="text-align: left;">TERMS OF PAYMENT</td>
                                </tr>
                                <tr>
                                    <td align="justify" height="100">
                                        <ol>
                                            <li>All fees are payable in cash upon enrollment or by installment basis. For the installment plan agreement are as follows:</li>
                                                <ol type="a">
                                                    <li>Twenty five percent (25%) of the total fees shall be collected upon enrollment.</li>
                                                    <li>Remaining seventy five percent (75%), will be divided into each terms one week before the examination.</li>
                                                </ol>
                                            <li>Students who paid  in cash basis are entitled for a "refund" or "withdraw" policy within a month (30 days) after the date of registration under the following conditions:</li>
                                                 <ol type="a">
                                                    <li>80% of the amount paid when he withdraws within the first week after the registrations, whether he has attended class or not.</li>
                                                    <li>50% of the amount paid when he withdraws within the second, third, fourth week after the registration, whether he has attended class or not.</li>
                                                    <li>No refund will be made 30 days after registration.</li>
                                                </ol>
                                              
                                        
                                        </ol>
                                    </td>
                                </tr>
                            </table>
                           
                        
                        </td>
                      </tr>
                      <tr>
                        <td width="80" style="border-right: 0px solid #014fb3;">&nbsp;</td>
                        <td style="color:black; text-align:right; border-right: 0px solid #014fb3;"></td>
                        
                    </tr>
                      <tr>
                        <td width="80" style="border-right: 0px solid #014fb3;">&nbsp;</td>
                        <td width="155" style="color:black; text-align:right; border-right: 0px solid #014fb3;"></td>
                        
                    </tr>
                      <tr>
                        <td width="80" style="border-right: 0px solid #014fb3;">&nbsp;</td>
                        <td width="155" style="color:black; text-align:right; border-right: 0px solid #014fb3;"></td>
                        
                    </tr>
                    
                     <tr>
                        <td width="80" style="border: 0px solid #014fb3;">&nbsp;TOTAL:</td>
                        <td width="155" style="color:black; text-align:right;border: 0px solid #014fb3;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </td>
                    </tr>
                    <tr>
                        <td style="border-right: 0px solid #014fb3;">&nbsp;</td>
                        <td width="155" style="color:black; text-align:right; border-right: 0px solid #014fb3;"></td>
                        
                    </tr>
                      <tr>
                        <td style="border-right: 0px solid #014fb3;">&nbsp;</td>
                        <td width="155" style="color:black; text-align:right; border-right: 0px solid #014fb3;"></td>
                        
                    </tr>
                    <tr>
                        <td style="border-right: 0px solid #014fb3;"></td>
                        <td style="border-right: 0px solid #014fb3;"></td>
                    </tr>
                   <tr>
                        <td style="border: 0px solid #014fb3;">&nbsp;ASSESSED BY:</td>
                        <td width="155" style="vertical-align: middle; color:#014fb3; text-align:center; border: 0px solid #014fb3; " > <b>Ms. Elisa G. Chacon</b>&nbsp;&nbsp;<br / ><span> Cashier&nbsp;&nbsp;</span></td>
                   </tr>
                    
         
        </table>
        <br />
        <br />
        
        <table border="0" cellpadding="0" width="528px" style="color:#014fb3; font-size:8; border: 0px solid #014fb3;">            
            <tr>
                <td width="80"> Verified by: </td>
                <td width="155"></td>
                <td width="80" style="text-align:center;"> Approved by:</td>
                <td width="213" style="text-align:center; font-weight:bold; text-decoration:underline;"></td>
            </tr>
             <tr>
                <td width="80"> </td>
                <td width="155" style="text-align:center;"> School Dean</td>
                <td width="80"></td>
                <td width="213" style="text-align:center;"> College Registrar</td>
            </tr>
        </table>
    ';

$html = utf8_encode($html);
// Print text using writeHTMLCell()
$pdf->writeHTML($html);
//$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

// ---------------------------------------------------------

// Close and output PDF document
// This method has several options, check the source code documentation for more information.
$pdf->Output("Blank Student Registration Form.pdf", 'I');

?>