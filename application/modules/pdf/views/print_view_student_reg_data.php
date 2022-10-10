<?php

    tcpdf();
    // create new PDF document
    //$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    //$pdf = new TCPDF("P", PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, array(8.5,11), true, 'UTF-8', false, true);

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
$html = '<table border="0" cellpadding="0" style="color:maroon; font-size:10;">
        <tr>
            <td width="64" align="right"></td>
            <td width="400" style="text-align: center; line-height:100%">
             <font style="font-family:Calibri Light; font-size: 10;"></font><br />
             <font style="font-family:Calibri Light; font-size: 10;font-weight: bold;"></font><br />
			 <font style="font-family:Calibri Light; font-size: 10;"></font><br />
             <font style="font-family:Calibri Light; font-size: 10;"></font><br />
            </td>
            <td width="64" align="left" valign="middle"></td>
        </tr>
        <tr>
            <td colspan = "3" style="font-weight: bold;text-align:center; font-size:10;"> </td>
        </tr>
         <tr>
            <td colspan = "3" height="18px" style="font-weight: bold;text-align:center; font-size:10;"> </td>
        </tr>
        <tr>
            <td colspan = "3" style="text-align:center; color:black; font-size: 10;"> A.Y. ' .$active_sem['strYearStart']."-".$active_sem['strYearEnd'] . ", " . $active_sem['enumSem'].' Semester' . '</td>
        </tr>
        <tr>
        <td colspan="3" style="font-size:10;">
        </td>
        </tr>
    </table>
   
    <table border="0" cellpadding="0" style="color:maroon; font-size:9;" width="528px">
     <tr>
            <td colspan ="3" style="text-align:center;color:white; font-size:10;">
                
            </td>
     </tr>
     <tr>
      <td width="80px">&nbsp;</td>
      <td width="250px">&nbsp;</td>
      <td width="113px">&nbsp;</td>
      <td width="85px" style="color: black;"> '. $registration['dteRegistered']. '</td>
     </tr>
     <tr>
      <td width="80px">&nbsp;</td>
      <td width="250px" style="color: black;">&nbsp;' . strtoupper($student['strLastname']) . ", " . strtoupper($student['strFirstname']) . " " . substr($student['strMiddlename'], 0,1) . ".".'</td>
      <td width="113px">&nbsp;</td>
      <td width="85px" style="color: black;">&nbsp;' . $student['strStudentNumber']. '</td>
     </tr>
     <tr>
      <td width="80px">&nbsp;</td>
      <td width="250px" style="color: black;">&nbsp;'.$student['strProgramDescription'] . '</td>
      <td width="113px">&nbsp;</td>
      <td width="85px" style="color: black;">&nbsp;'. $academic_standing['year'] . '</td>
     </tr>
     <tr>
      <td width="80px">&nbsp;</td>
      <td width="250px" style="color:black;">&nbsp;' .$student['strMajor'].'</td>
      <td width="113px">&nbsp;</td>
      <td width="85px" style="color: black;text-transform:capitalize;">&nbsp;'.$registration['enumRegistrationStatus'].'</td>
     </tr>
     <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
     </tr>
    </table> '; 
$html.= '<table border="0" cellpadding="0" style="color:maroon; font-size:8;" width="528">
   
        <tr>
            <th width="80px" style="text-align:left;"></th>
            <th width="65px" style="text-align:lef;"></th>
            <th width="180px" style="text-align:center;"></th>
            <th width="30px" style="text-align:center;"></th>
            <th width="173px" style="text-align:center;"></th>
        </tr> ';
        $html.= '
                <tr><td colspan="5"> </td> </tr>
                <tr>
                    <td colspan="5" rowspan="24" height="200px">';

                        $html.= '<table border="0" cellpadding="0" style="color:maroon; font-size:8;" width="528">';
                        $totalUnits = 0;
                                foreach($records as $record) {

                                    $html.='<tr style="color: black;">
                                            <td width="80px"> &nbsp;&nbsp;' . $record['strSection'].'</td>
                                            <td width="65px"> &nbsp;&nbsp;'.  $record['strCode'] . '</td>
                                            <td width="180px" align ="left"> '. $record['strDescription']. '</td>
                                            <td width="30px" align = "left"> &nbsp;&nbsp;'. $record['strUnits']. '</td> ';
                                            $html.= '<td width="173px">';

                                        foreach($record['schedule'] as $sched) {
                                            if(!empty($record['schedule']))

                                                $html.= date('g:ia',strtotime($sched['dteStart'])).'-'.date('g:ia',strtotime($sched['dteEnd']))." ".$sched['strDay']." ".$sched['strRoomCode'] . ";";                    
                                        }
                                            $html.= '</td>';
                                        $html.='</tr>';
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
                <table border="0" cellpadding="0" style="color:maroon; font-size:8;" width="528px">
        
                    <tr style="font-weight:bold;">
                    <td width="80px">&nbsp; </td>
                    <td width="75px" style="color: black;text-align:left;">' . $noOfSubjs . '</td>
                    <td width="80px">&nbsp;</td>
                     <td width="45px" style="color: black;text-align:center;">'. $totalLec . '</td>
                    <td width="55px">&nbsp;</td>
                    <td width="45px" style="color: black;text-align:center;">' . $totalLab . '</td>
                    <td width="70px">&nbsp;</td>
                    <td width="78px" style="color: black;text-align:center;">' . $units . '</td>
                    </tr>

        </table>
        
        <table border="0" cellpadding="0" style="color:maroon; font-size:8;" width="528px">
        <tr>
            <td colspan="3"> &nbsp;</td>
        </tr>
        <tr>
            <td colspan="3"> &nbsp;</td>
        </tr>
        <tr>
            <td width="235" style="text-align:center; color:black;" > ' . strtoupper($registration['enumScholarship']). '</td>
            <td width="145"> &nbsp;</td>
            <td width="148"> &nbsp;</td>
        </tr>
        <tr>
            <td width="235"> </td>
            <td width="145"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </td>
            <td width="148" style="text-align:center; color:black;">' . number_format($payment_division, 2, '.' ,',') . ' </td>
        </tr>
        <tr>
            <td width="80">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
            <td width="155" style="color:black; text-align:right;"> ' . number_format($tuition['tuition'], 2, '.' ,',') . ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </td>
            <td width="145"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </td>
            <td width="148" style="text-align:center; color:black;">' .  number_format($payment_division, 2, '.' ,',').  '</td>
        </tr>
                    <tr>
                        <td width="80">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>';
                            $total_misc = 0;
                            foreach($tuition['misc_fee'] as $key=>$val) {
                                $total_misc += $val;
                            }
                        
                        $html.= '<td width="155"style="color:black; text-align:right;"> ' . number_format($total_misc, 2, '.', ',') . ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  </td>
                        <td width="145"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </td>
                        <td width="148" style="text-align:center; color:black;">' . number_format($payment_division, 2, '.' ,','). '</td>
                    </tr>
                    <tr>
                        <td width="80">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                        <td width="155" style="color:black; text-align:right;">' .  number_format($tuition['id_fee'], 2, '.' ,','). ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  </td>
                        <td width="145"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </td>
                        <td width="148" style="text-align:center; color:black;">' .  number_format($payment_division, 2, '.' ,',') . '</td>                    
                    </tr>
                    <tr>
                        <td width="80">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                            
                        <td width="155" style="color:black; text-align:right;">' . number_format($tuition['lab'], 2, '.', ',') . ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;   </td>
                        <td></td>
                        <td width="148"></td>
                    </tr>
                    <tr>
                        <td width="80">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                        <td width="155" style="color:black; text-align:right;"> ' .  number_format($tuition['srf'], 2, '.' ,','). ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </td>
                        <td></td>
                        <td width="148"></td>
                    </tr>
                    <tr>
                        <td width="80">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                        <td width="155" style="color:black; text-align:right;">' .  number_format($tuition['sfdf'], 2, '.' ,','). ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </td>    
                        <td></td>
                        <td width="148"></td>
                    </tr>
                    <tr>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                        <td width="155" style="color:black; text-align:right;">' .  number_format($tuition['athletic'], 2, '.' ,','). ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </td>
                        <td></td>
                        <td width="148"></td>
                    </tr>
                    <tr>
                        <td width="80" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                        <td width="155" style="color:black; text-align:right;">' .  number_format($tuition['csg']['student_handbook']+$tuition['csg']['student_publication'], 2, '.' ,','). ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </td>
                        <td></td>
                        <td width="148"></td>
                        
                    </tr>
                      <tr>
                        <td width="80">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                        <td width="155" style="color:black; text-align:right;"></td>
                        <td colspan="2" rowspan = "9" align="center">
                          <table border="0" width="285" style="font-size: 7px;">
                                <tr>
                                    <td style="font-weight:bold;"></td>
                                </tr>
                                <tr>
                                    <td style="text-align: left;"></td>
                                </tr>
                                <tr>
                                    <td align="justify" height="100">
                                        
                                    </td>
                                </tr>
                            </table>
                           
                        
                        </td>
                      </tr>
                      <tr>
                        <td width="80" >&nbsp;</td>
                        <td style="color:black; text-align:right;"></td>
                        
                    </tr>
                      <tr>
                        <td width="80" >&nbsp;</td>
                        <td width="155" style="color:black; text-align:right;"></td>
                        
                    </tr>
                      <tr>
                        <td width="80">&nbsp;</td>
                        <td width="155" style="color:black; text-align:right;"></td>
                        
                    </tr>
                    
                     <tr>
                        <td width="80">&nbsp;</td>
                        <td width="155" style="color:black; text-align:right;">' .  number_format($tuition['total'], 2, '.' ,','). ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td width="155" style="color:black; text-align:right;"></td>
                        
                    </tr>
                      <tr>
                        <td >&nbsp;</td>
                        <td width="155" style="color:black; text-align:right;"></td>
                        
                    </tr>
                     <tr>
                        <td></td>
                        <td style="text-align:center;"><img height="25px" src="'.$img_dir.'signat-Accounting.png" /></td>
                    </tr>
                   <tr>
                        <td>&nbsp;</td>
                        <td width="155" style="vertical-align: middle; color:maroon; text-align:right;">&nbsp;&nbsp;</td>
                   </tr>
                    
         
        </table>
        <br />
        <br />
        
        <table border="0" cellpadding="0" width="528px" style="color:maroon; font-size:8;">
             <tr>
                <td width="80"></td>
                <td width="155" style="text-align:center"><img height="16px" src="'.$img_dir.$deanSignature.'" /></td>
                <td width="80" style="text-align:center;"></td>
                <td width="213" style="text-align:center; font-weight:bold; text-decoration:underline;"><img height="16px" src="'.$img_dir.'signat-Registrar2.png" /></td>
            </tr>
             <tr>
                <td width="80"> </td>
                <td width="155" style="text-align:center;"> </td>
                <td width="80"></td>
                <td width="213" style="text-align:center;"> </td>
            </tr>
        </table>
    ';

//$html = utf8_encode($html);
// Print text using writeHTMLCell()
$pdf->writeHTML($html);
//$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

// ---------------------------------------------------------

// Close and output PDF document
// This method has several options, check the source code documentation for more information.
$pdf->Output($student['strLastname'] . ", " . $student['strFirstname'] . ', ' . substr($student['strMiddlename'], 0,1). ".-". $student['strProgramCode'] . ".pdf", 'I');

?>