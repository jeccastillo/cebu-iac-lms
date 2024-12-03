<?php

    tcpdf();
    // create new PDF document
    //$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    //$pdf = new TCPDF("P", PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf = new TCPDF("P", PDF_UNIT, array(8.5, 11), true, 'UTF-8', false);

    // set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetTitle($student['strLastname'] . ", " . $student['strFirstname'] . ', ' . substr($student['strMiddlename'], 0,1). ".-". $student['strProgramCode']);
    
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
    $payment_division = $tuition['total'] / 4;
    
   // $imgfile = $img_dir
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
            <td colspan = "3" style="text-align:center; color:black; font-size: 10;"> A.Y. ' .$active_sem['strYearStart']."-".$active_sem['strYearEnd'] . ", " . $active_sem['enumSem'].' Semester' . '</td>
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
      <td width="80px">&nbsp;</td>
      <td width="250px">&nbsp;</td>
      <td width="113px">&nbsp;DATE:</td>
      <td width="85px" style="color: black;"> '. $registration['date_enlisted']. '</td>
     </tr>
     <tr>
      <td width="80px">&nbsp;NAME:</td>
      <td width="250px" style="color: black;">&nbsp;' . strtoupper($student['strLastname']) . ", " . strtoupper($student['strFirstname']) . " " . substr($student['strMiddlename'], 0,1) . ".".'</td>
      <td width="113px">&nbsp;STUDENT NUMBER:</td>
      <td width="85px" style="color: black;">&nbsp;' . $student['strStudentNumber']. '</td>
     </tr>
     <tr>
      <td width="80px">&nbsp;PROGRAM:</td>
      <td width="250px" style="color: black;">&nbsp;'.$student['strProgramDescription'] . '</td>
      <td width="113px">&nbsp;YEAR LEVEL:</td>
      <td width="85px" style="color: black;">&nbsp;'. $registration['intYearLevel'] . '</td>
     </tr>
     <tr>
      <td width="80px">&nbsp;MAJOR:</td>
      <td width="250px">&nbsp;</td>
      <td width="113px">&nbsp;REGISTRATION STATUS:</td>
      <td width="85px" style="color: black;text-transform:capitalize;">&nbsp;'.$registration['enumRegistrationStatus'].'</td>
     </tr>
     <tr>
        <td colspan="4">&nbsp;</td>
     </tr>
    </table>
    
    <table border="0" cellpadding="0" style="color:#014fb3; font-size:8; border: 0px solid #014fb3;" width="528px">
   
        <tr>
            <th width="80px" style="text-align:center;">SECTION</th>
            <th width="75px" style="text-align:center;">COURSE CODE</th>
            <th width="180px" style="text-align:center;">COURSE DESCRIPTION</th>
            <th width="45px" style="text-align:center;">UNITS</th>
            <th width="148px" style="text-align:center;">SCHEDULE</th>
        </tr>
    
          <tr align="center">
           <td width="80px" ></td>
            <td width="75px"></td>
            <td width="180px" align ="left"></td>
            <td width="45px"></td>
            <td width="148px"></td>                    
        </tr>
          <tr>
           <td width="80px" ></td>
            <td width="75px"></td>
            <td width="180px"></td>
            <td width="45px"></td>
            <td width="148px"></td>                    
        </tr> 
          <tr>
           <td width="80px" ></td>
            <td width="75px"></td>
            <td width="180px"></td>
            <td width="45px"></td>
            <td width="148px"></td>                    
        </tr> 
          <tr>
           <td width="80px" ></td>
            <td width="75px"></td>
            <td width="180px"></td>
            <td width="45px"></td>
            <td width="148px"></td>                    
        </tr> 
          <tr>
           <td width="80px" ></td>
            <td width="75px"></td>
            <td width="180px"></td>
            <td width="45px"></td>
            <td width="148px"></td>                    
        </tr> 
          <tr>
           <td width="80px" ></td>
            <td width="75px"></td>
            <td width="180px"></td>
            <td width="45px"></td>
            <td width="148px"></td>                    
        </tr> 
          <tr>
           <td width="80px" ></td>
            <td width="75px"></td>
            <td width="180px"></td>
            <td width="45px"></td>
            <td width="148px"></td>                    
        </tr> 
          <tr>
           <td width="80px" ></td>
            <td width="75px"></td>
            <td width="180px"></td>
            <td width="45px"></td>
            <td width="148px"></td>                    
        </tr> 
          <tr>
           <td width="80px" ></td>
            <td width="75px"></td>
            <td width="180px"></td>
            <td width="45px"></td>
            <td width="148px"></td>                    
        </tr> 
          <tr>
           <td width="80px" ></td>
            <td width="75px"></td>
            <td width="180px"></td>
            <td width="45px"></td>
            <td width="148px"></td>                    
        </tr> 
          <tr>
           <td width="80px" ></td>
            <td width="75px"></td>
            <td width="180px"></td>
            <td width="45px"></td>
            <td width="148px"></td>                    
        </tr> 
         <tr>
           <td width="80px" ></td>
            <td width="75px"></td>
            <td width="180px"></td>
            <td width="45px"></td>
            <td width="148px"></td>                    
        </tr>
        </table>
        
        <table border="1" cellpadding="0" style="color:#014fb3; font-size:8; border: 0px solid #014fb3;" width="528px">
        <tr style="background-color:#ffff99 ; font-weight:bold;">
            <td width="80px">&nbsp;SUBJECTS </td>
            <td width="75px"></td>
            <td width="80px">&nbsp;LEC. UNITS </td>
            <td width="45px"></td>
            <td width="55px">&nbsp;LAB UNITS </td>
            <td width="45px"></td>
            <td width="70px">&nbsp;TOTAL CREDITS </td>
            <td width="78px"></td>
        </tr>
        </table>
        
        <table border="0" cellpadding="0" style="color:#014fb3; font-size:8; border: 0px solid #014fb3;" width="528px">
           <tr>
            <td colspan ="3" style="text-align:center; background-color: #014fb3; color:white; font-size:10;">
                BILLING INFORMATION
            </td>
        </tr>
        
        <tr>
            <td width="235"> SCHOLARSHIP GRANT:</td>
            <td width="293">
                PAYMENT DETAILS:
            </td>
        </tr>
        <tr>
            <td width="235" style="text-align:center; color:black;"> ' . strtoupper($student['enumScholarship']). '</td>
            <td> &nbsp;TERMS OF PAYMENT</td>
           
        </tr>
        <tr>
            <td width="235"> ASSESSMENT OF FEES:</td>
            <td width="145"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;First Payment: </td>
            <td width="148" style="text-align:center; color:black;">' . number_format($payment_division, 2, '.' ,',') . ' </td>
        </tr>
        <tr>
            <td width="80">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Tuition Fee:</td>
            <td width="155" style="color:black; text-align:right;"> ' . number_format($tuition['tuition'], 2, '.' ,',') . ' &nbsp; </td>
            <td width="145"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Second Payment: </td>
            <td width="148" style="text-align:center; color:black;">' .  number_format($payment_division, 2, '.' ,',').  '</td>
        </tr>
                    <tr>
                        <td width="80">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Misc. Fee:</td>
                        <td width="155"style="color:black; text-align:right;"></td>
                        <td width="145"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Third Payment: </td>
                        <td width="148" style="text-align:center; color:black;">' . number_format($payment_division, 2, '.' ,','). '</td>
                    </tr>
                    <tr>
                        <td width="80">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;I.D. Fee:</td>
                        <td width="155" style="color:black; text-align:right;"> ' .  number_format($tuition['id_fee'], 2, '.' ,','). ' &nbsp; </td>
                        <td width="145"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Fourth Payment: </td>
                        <td width="148" style="text-align:center; color:black;">' .  number_format($payment_division, 2, '.' ,',') . '</td>                    
                    </tr>
                    <tr>
                        <td width="80">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Laboratory Fees:</td>
                        <td width="155" style="color:black; text-align:right;"></td>
                        <td></td>
                        <td width="148"></td>
                    </tr>
                    <tr>
                        <td width="80">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;SRF:</td>
                        <td width="155" style="color:black; text-align:right;"> ' .  number_format($tuition['srf'], 2, '.' ,','). ' &nbsp;</td>
                        <td></td>
                        <td width="148"></td>
                    </tr>
                    <tr>
                        <td width="80">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;SFDF:</td>
                        <td width="155" style="color:black; text-align:right;">' .  number_format($tuition['sfdf'], 2, '.' ,','). ' &nbsp;</td>    <td></td>
                        <td width="148"></td>
                    </tr>
                    <tr>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Athletic Fee</td>
                        <td width="155" style="color:black; text-align:right;">' .  number_format($tuition['athletic'], 2, '.' ,','). ' &nbsp;</td>
                        <td></td>
                        <td width="148"></td>
                    </tr>
                    <tr>
                        <td width="80">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;CSG:</td>
                        <td width="155" style="color:black; text-align:right;">' .  number_format($tuition['csg']['student_handbook']+$tuition['csg']['student_publication'], 2, '.' ,','). ' &nbsp;</td>
                        <td></td>
                        <td width="148"></td>
                        
                    </tr>
                      <tr>
                        <td width="80">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Other Fines:</td>
                        <td width="155" style="color:black; text-align:right;"></td>
                        <td colspan="2" rowspan = "9" align="center">
                          <table border="0" width="285" style="font-size: 7px;">
                                <tr>
                                    <td>RULES ON FEES</td>
                                </tr>
                                <tr>
                                    <td>TERMS OF PAYMENT</td>
                                </tr>
                                <tr>
                                    <td align="justify">
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
                        <td width="80">&nbsp;</td>
                        <td style="color:black; text-align:right;"></td>
                        
                    </tr>
                      <tr>
                        <td width="80">&nbsp;</td>
                        <td width="155" style="color:black; text-align:right;"></td>
                        
                    </tr>
                      <tr>
                        <td width="80">&nbsp;</td>
                        <td width="155" style="color:black; text-align:right;"></td>
                        
                    </tr>
                    
                     <tr>
                        <td width="80">&nbsp;TOTAL:</td>
                        <td width="155" style="color:black; text-align:right;">' .  number_format($tuition['total'], 2, '.' ,','). ' &nbsp;</td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td width="155" style="color:black; text-align:right;"></td>
                        
                    </tr>
                      <tr>
                        <td>&nbsp;</td>
                        <td width="155" style="color:black; text-align:right;"></td>
                        
                    </tr>
                    <tr>
                        <td>&nbsp;ASSESSED BY:</td>
                        <td width="155" style="color:#014fb3; text-align:right;font-weight: bold;">Ms. Elisa G. Chacon&nbsp;&nbsp;</td>
                        
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td width="155" style="color:#014fb3; text-align:right;">Cashier&nbsp;&nbsp;</td>
                        
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
$pdf->Output($student['strLastname'] . ", " . $student['strFirstname'] . ', ' . substr($student['strMiddlename'], 0,1). ".-". $student['strProgramCode'] . ".pdf", 'I');

?>