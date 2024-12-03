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


    
    // Set some content to print
$html = '<table border="0" cellpadding="0" style="color:#014fb3; font-size:10;">
        <tr>
            <td width="64" align="right"><img src= "'.$img_dir .'tagaytayseal.png"  width="50" height="50"/></td>
            <td width="400" style="text-align: center; line-height:100%">
             
             <font style="font-family:Calibri Light; font-size: 10;font-weight: bold;">iACADEMY, Inc.</font><br />
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
      <td width="80px" style="border-right: 0px solid #014fb3;">&nbsp;</td>
      <td width="250px">&nbsp;</td>
      <td width="113px" style="border-left: 0px solid #014fb3;">&nbsp;DATE:</td>
      <td width="85px" style="color: black;"> '. $registration['date_enlisted']. '</td>
     </tr>
     <tr>
      <td width="80px" style="border-right: 0px solid #014fb3;">&nbsp;NAME:</td>
      <td width="250px" style="color: black;">&nbsp;' . strtoupper($student['strLastname']) . ", " . strtoupper($student['strFirstname']) . " " . substr($student['strMiddlename'], 0,1) . ".".'</td>
      <td width="113px" style="border-left: 0px solid #014fb3;">&nbsp;STUDENT NUMBER:</td>
      <td width="85px" style="color: black;">&nbsp;' . $student['strStudentNumber']. '</td>
     </tr>
     <tr>
      <td width="80px" style="border-right: 0px solid #014fb3;">&nbsp;PROGRAM:</td>
      <td width="250px" style="color: black;">&nbsp;'.$student['strProgramDescription'] . '</td>
      <td width="113px" style="border-left: 0px solid #014fb3;">&nbsp;YEAR LEVEL:</td>
      <td width="85px" style="color: black;">&nbsp;'. $academic_standing['year'] . '</td>
     </tr>
     <tr>
      <td width="80px" style="border-right: 0px solid #014fb3;">&nbsp;MAJOR:</td>
      <td width="250px" style="color:black;">&nbsp;' .$student['strMajor'] . '</td>
      <td width="113px" style="border-left: 0px solid #014fb3;">&nbsp;REGISTRATION STATUS:</td>
      <td width="85px" style="color: black;text-transform:capitalize;">&nbsp;'.$registration['enumRegistrationStatus'].'</td>
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
            <th width="80px" style="text-align:left;">SECTION</th>
            <th width="65px" style="text-align:lef;">COURSE CODE</th>
            <th width="180px" style="text-align:center;">COURSE DESCRIPTION</th>
            <th width="30px" style="text-align:center;">UNITS</th>
            <th width="173px" style="text-align:center;">SCHEDULE</th>
        </tr> ';
        $html.= '
                <tr><td colspan="5"> </td> </tr>
                <tr>
                    <td colspan="5" rowspan="24" height="210px">';

                        $html.= '<table border="0" cellpadding="0" style="color:#014fb3; font-size:8;" width="528">';
                        $totalUnits = 0;
                        if (empty($records)){
                            $html.='<tr style="color: black; border-bottom: 0px solid gray;">
                                                    <td colspan="7" style="text-align:center;font-size: 11px;">No Data Available</td>
                                                </tr>';
                        }
                        else {
                                foreach($records as $record) {

                                    $html.='<tr style="color: black;">
                                            <td width="80px"> ' . $record['strSection'].'</td>
                                            <td width="65px"> '.  $record['strCode'] . '</td>
                                            <td width="180px" align ="left"> '. $record['strDescription']. '</td>
                                            <td width="30px" align = "center"> '. $record['strUnits']. '</td> ';
                                            $html.= '<td width="173px">';

                                        foreach($record['schedule'] as $sched) {
                                            if(!empty($record['schedule']))

                                                $html.= date('g:ia',strtotime($sched['dteStart'])).'  '.date('g:ia',strtotime($sched['dteEnd']))." ".$sched['strDay']." ".$sched['strRoomCode'] . " ";                    
                                        }
                                            $html.= '</td>';
                                        $html.='</tr>';
                                }
                        }
                        $html.= '</table>';

            $html.= '</td> 
            </tr>';
                                $units = 0;
                                $totalUnits = 0;
                                $totalLab = 0;
                                $totalLec = 0;
                                $lec = 0;
                                $lecForLab = 0;
                                $totalNoSubjects = 0;
                                $noOfSubjs = 0;
                         if (empty($records)) {
                                    $msg = "no data";
                                }
                        else {
                                foreach($records as $record){
                                    $noOfSubjs++;
                                    $units += $record['strUnits'];
                                        if($record['intLab']  > 0)
                                        {
                                            $totalLab += ceil($record['intLab']/3);
                                        }

                                        $lecForLab = $totalLab * 2;
                                        $lec = $units - $lecForLab;
                                        $totalLec = $totalLab + $lec;
                                    }     
                                }    

                         
        $html.='</table>
                <table border="0" cellpadding="0" style="color:#014fb3; font-size:8; border: 0px solid #014fb3;" width="528px">
        
                    <tr style="background-color:#ffff99 ; font-weight:bold;">
                    <td width="80px">&nbsp;SUBJECTS: </td>
                    <td width="75px" style="color: black;text-align:left;">' . $noOfSubjs . '</td>
                    <td width="80px">&nbsp;LEC. UNITS: </td>
                     <td width="45px" style="color: black;text-align:center;">'. $totalLec . '</td>
                    <td width="55px">&nbsp;LAB UNITS: </td>
                    <td width="45px" style="color: black;text-align:center;">' . $totalLab . '</td>
                    <td width="70px">&nbsp;TOTAL CREDITS: </td>
                    <td width="78px" style="color: black;text-align:center;">' . $units . '</td>
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
            <td width="235" style="text-align:center; color:black; border: 0px solid #014fb3;" > ' . strtoupper($student['enumScholarship']). '</td>
            <td> &nbsp;TERMS OF PAYMENT</td>
           
        </tr>
        <tr>
            <td width="235" style="border: 0px solid #014fb3;"> ASSESSMENT OF FEES:</td>
            <td width="145"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;First Payment: </td>
            <td width="148" style="text-align:center; color:black;">' . number_format($payment_division, 2, '.' ,',') . ' </td>
        </tr>
        <tr>
            <td width="95" style="border-right: 0px solid #014fb3;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Tuition Fee:</td>
            <td width="140" style="color:black; text-align:right; border-right: 0px solid #014fb3;"> ' . number_format($tuition['tuition'], 2, '.' ,',') . ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </td>
            <td width="145"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Second Payment: </td>
            <td width="148" style="text-align:center; color:black;">' .  number_format($payment_division, 2, '.' ,',').  '</td>
        </tr>
                    <tr>
                        <td width="95" style="border-right: 0px solid #014fb3;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;NSTP Fee:</td>';
                               $html.= '<td width="140"style="color:black; text-align:right;border-right: 0px solid #014fb3;">'. (number_format($nstp_fee, 2, '.', ',') / 2 ). '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  </td>';
                             
                   
                        
                        
                        $html.= '<td width="145"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Third Payment: </td>
                        <td width="148" style="text-align:center; color:black;">' . number_format($payment_division, 2, '.' ,','). '</td>
                    </tr>
                    <tr>
                        <td width="95" style="border-right: 0px solid #014fb3;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Athletic Fee:</td>
                        <td width="140" style="color:black; text-align:right; border-right: 0px solid #014fb3;"> ' .  number_format($tuition['athletic'], 2, '.' ,','). ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  </td>
                        <td width="145"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Fourth Payment: </td>
                        <td width="148" style="text-align:center; color:black;">' .  number_format($payment_division, 2, '.' ,',') . '</td>                    
                    </tr>
                    <tr>
                        <td width="95" style="border-right: 0px solid #014fb3;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Computer Fees:</td>
                            
                        <td width="140" style="color:black; text-align:right;border-right: 0px solid #014fb3;">' . number_format($tuition['lab'], 2, '.', ',') . ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;   </td>
                        <td></td>
                        <td width="148"></td>
                    </tr>
                    <tr>
                        <td width="95" style="border-right: 0px solid #014fb3;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Cultural Fees:</td>
                        <td width="140" style="color:black; text-align:right;border-right: 0px solid #014fb3;"> ' .  number_format($tuition['srf'], 2, '.' ,','). ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </td>
                        <td></td>
                        <td width="148"></td>
                    </tr>
                    <tr>
                        <td width="95" style="border-right: 0px solid #014fb3;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Development Fees:</td>
                        <td width="140" style="color:black; text-align:right; border-right: 0px solid #014fb3;">' .  number_format(($tuition['sfdf']+$tuition['csg']['student_publication']), 2, '.' ,','). ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </td>    
                        <td style="border-bottom: 0px solid #014fb3;"></td>
                        <td width="148" style="border-bottom: 0px solid #014fb3;"></td>
                    </tr>

                    <tr>
                    <td width="95" style="border-right: 0px solid #014fb3;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Admission Fees:</td>
                    <td width="140" style="color:black; text-align:right; border-right: 0px solid #014fb3;">' .  number_format($tuition['misc_fee']['Entrance Exam Fee'], 2, '.' ,','). ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </td>    
                    <td colspan="2" rowspan="15" align="center" style="border-right: 0px solid #014fb3;">
                    <table border="0" width="285" style="font-size: 7px;" >
                    <tr>
                        <td style="font-weight:bold;">RULES ON FEES</td>
                    </tr>
                    <tr>
                        <td style="text-align: left;">TERMS OF PAYMENT</td>
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
                    <td width="95" style="border-right: 0px solid #014fb3;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Guidance Fees:</td>
                    <td width="140" style="color:black; text-align:right; border-right: 0px solid #014fb3;">' .  number_format($tuition['misc_fee']['Guidance Fee'], 2, '.' ,','). ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </td>    
                 
                    </tr>

                    <tr>
                    <td width="95" style="border-right: 0px solid #014fb3;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Handbook Fees:</td>
                    <td width="140" style="color:black; text-align:right; border-right: 0px solid #014fb3;">' .  number_format($tuition['csg']['student_handbook'], 2, '.' ,','). ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </td>    
                  
                    </tr>

                    <tr>
                    <td width="95" style="border-right: 0px solid #014fb3;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Library Fees:</td>
                    <td width="140" style="color:black; text-align:right; border-right: 0px solid #014fb3;">' .  number_format($tuition['misc_fee']['Library Fee'], 2, '.' ,','). ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </td>    
               
                    </tr>

                    <tr>
                    <td width="95" style="border-right: 0px solid #014fb3;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Medical/Dental Fees:</td>
                    <td width="140" style="color:black; text-align:right; border-right: 0px solid #014fb3;">' .  number_format($tuition['misc_fee']['Medical and Dental Fee'], 2, '.' ,','). ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </td>    
             
                    </tr>
                    
                    <tr>
                    <td width="95" style="border-right: 0px solid #014fb3;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Registration Fees:</td>
                    <td width="140" style="color:black; text-align:right; border-right: 0px solid #014fb3;">' .  number_format($tuition['misc_fee']['Registration'], 2, '.' ,','). ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </td>
               
                    
                    </tr>
                    <tr>
                        <td width="95" style="border-right: 0px solid #014fb3;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;School I.D. Fee:</td>
                        <td width="140" style="color:black; text-align:right; border-right: 0px solid #014fb3;">' .  number_format($tuition['id_fee'], 2, '.' ,','). ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </td>
                
                    </tr>
                      <tr>
                        <td width="95" style="border-right: 0px solid #014fb3;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Other Fines:</td>
                        <td width="140" style="color:black; text-align:right; border-right: 0px solid #014fb3;"></td>
                      </tr>
                     <tr>
                        <td width="95" style="border: 0px solid #014fb3;">&nbsp;TOTAL:</td>
                        <td width="140" style="color:black; text-align:right;border: 0px solid #014fb3;">' .  number_format($tuition['total'] - ($nstp_fee / 2), 2, '.', ','). ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </td>
                    </tr>
                   
                    <tr>
                        <td style="border-right: 0px solid #014fb3;"></td>
                        <td style="border-right: 0px solid #014fb3;text-align:center;"><img height="25px" src="'.$img_dir.'signat-Accounting.png" /></td>
                    </tr>
                   <tr>
                        <td style="border: 0px solid #014fb3;">&nbsp;ASSESSED BY:</td>
                        <td  width="140" style="vertical-align: middle; color:#014fb3; text-align:center; border: 0px solid #014fb3;"> <b>Ms. Elisa G. Chacon</b>&nbsp;&nbsp;<br / ><span> Cashier&nbsp;&nbsp; </span></td>
                   </tr>
                    
         
        </table>
        <br />
        <br />
        
        <table border="0" cellpadding="0" width="528px" style="color:#014fb3; font-size:8; border: 0px solid #014fb3;">       
            <tr>
                <td width="95"> Verified by: </td>
                <td width="140" style="text-align:center"><img height="16px" src="'.$img_dir.$deanSignature.'" /></td>
                <td width="80" style="text-align:center;"> Approved by:</td>
                <td width="213" style="text-align:center; font-weight:bold; text-decoration:underline;"><img height="16px" src="'.$img_dir.'signat-Registrar2.png" /></td>
            </tr>
             <tr>
                <td width="95"> </td>
                <td width="140" style="text-align:center;"> School Dean</td>
                <td width="80"></td>
                <td width="213" style="text-align:center;"> College Registrar</td>
            </tr>
        </table>
    ';

$html = utf8_encode($html);
$pdf->writeHTML($html);

//$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

// ---------------------------------------------------------

// Close and output PDF document
// This method has several options, check the source code documentation for more information.
//$pdf->Output($student['strLastname'] . ", " . $student['strFirstname'] . ', ' . substr($student['strMiddlename'], 0,1). ".-". $student['strProgramCode'] . ".pdf", 'I');
$fname = $dirname.'/'.$student['strLastname'] . "-" . $student['strFirstname'] . '-' . substr($student['strMiddlename'], 0,1). "-". $student['strProgramCode'] . ".pdf";
$fname = str_replace(' ','',$fname);
$pdf->Output($fname, 'F');

?>