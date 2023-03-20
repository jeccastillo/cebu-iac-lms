<?php

    tcpdf();
    // create new PDF document
    //$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    //$pdf = new TCPDF("P", PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf = new TCPDF('L', 'in', array(6.5,5.7), true, 'UTF-8', false, true);

    // set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetTitle($student['strLastname'] . ", " . $student['strFirstname'] . ', ' . substr($student['strMiddlename'], 0,1). ".-". $student['strProgramCode']);
    
    // set margins
    $pdf->SetMargins(.35, 1.1, .35);
    //$pdf->SetAutoPageBreak(TRUE, 6);
    
   //font setting
    //$pdf->SetFont('calibril_0', '', 10, '', 'false');
    
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetAutoPageBreak(false);
    // Add a page
    // This method has several options, check the source code documentation for more information.
    $pdf->AddPage();
    $payment_division = $tuition['total'] / 4;
    $mname = isset($student['strMiddlename'])?$student['strMiddlename']:'';
    // Set some content to print
$html = '<table border="0" cellpadding="0">
            <tr style="line-height:12px;">';
            if ($student['enumScholarship']=="resident scholar")
            {
                $html .= '<td style="font-size:10px;text-align:center;color:red;font-weight:bold; background-color:yellow;" width="80%">' . strtoupper($registration['enumScholarship']). '</td>';
            }
            else
            {
                $html .= '<td style="font-size:10px;text-align:center;color:red;font-weight:bold;" width="80%">' . strtoupper($registration['enumScholarship']). '</td>';
            }
                
               $html .= '<td style="font-size:9px;" width="20%">'.date("m-d-Y", strtotime($registration['date_enlisted'])).'</td>
            </tr>
        </table>';
$html .= '<table border="0" cellpadding="0">
            <tr style="line-height:47px;">
                <td style="font-size:9px;" width="72%"></td>
            </tr>
        </table>';
$html .= '<table border="0" cellpadding="0">
            <tr style="line-height:12px;">
                <td width="10%"></td>
                <td style="font-size:9px;" width="65%">'.$student['strLastname'] . ", " . $student['strFirstname']. " " . $mname . '</td>
                <td width="10%"></td>
                <td style="font-size:9px;" width="28%">'.$student['strStudentNumber'].'</td>
            </tr>
        </table>';
$html .= '<table border="0" cellpadding="0">
            <tr style="line-height:14px;">
                <td style="font-size:9px;" width="72%"></td>
            </tr>
        </table>';
$html .= '<table border="0" cellpadding="0">
            <tr style="line-height:12px;">
                <td width="10%"></td>
                <td style="font-size:9px;" width="60%">'.$student['strProgramDescription'].'</td>
                <td width="10%"></td>
                <td style="font-size:9px;" width="28%">'.$student['strMajor'].'</td>
            </tr>
        </table>';
$html .= '<table border="0" cellpadding="0">
            <tr style="line-height:18px;">
                <td style="font-size:9px;" width="72%"></td>
            </tr>
        </table>';
$html .= '<table border="0" cellpadding="0">
            <tr style="line-height:12px;">
                <td style="font-size:9px;" width="60%">'.$active_sem['enumSem'].' - '.$active_sem['strYearStart'].' - '.$active_sem['strYearEnd'].'</td>
            </tr>
        </table>';
$html .= '<table border="0" cellpadding="0">
            <tr style="line-height:45px;">
                <td style="font-size:9px;" width="72%"></td>
            </tr>
        </table>';
for($i=0;$i<12;$i++){
    $section = isset($records[$i]['strSection'])?$records[$i]['strSection']:'';
    $crs = isset($records[$i]['strCode'])?$records[$i]['strCode']:'';
    $dsc = isset($records[$i]['strDescription'])?ellipsize($records[$i]['strDescription'],40):'';
    $unts = isset($records[$i]['strUnits'])?$records[$i]['strUnits']:'';
    
    $html .= '<table border="0" cellpadding="0">
            <tr style="line-height:17px;">
                <td style="font-size:8px;" width="25%">'.' '.$section.'</td>
                <td style="font-size:8px;" width="60%">'. $crs . " " . $dsc.'</td>
                <td style="font-size:8px;" width="12%">'.$unts.'</td>
            </tr>
        </table>';
    
}

    $units = 0;
    $totalUnits = 0;
    $totalLab = 0;
    $totalLec = 0;
    $lec = 0;
    $lecForLab = 0;
    $totalNoSubjects = 0;
    $noOfSubjs = 0;
    foreach($records as $record){
    $noOfSubjs++;
    $units += $record['strUnits'];
        if($record['intLab'] == 1)
        {
            $totalLab++;
        }

        $lecForLab = $totalLab * 2;
        $lec = $units - $lecForLab;
        $totalLec = $totalLab + $lec;
    }     


$html .= '<table border="0" cellpadding="0">
            <tr style="line-height:17px;">
                <td style="font-size:10px;text-align:left;color:red;font-weight:bold;" width="25%">&nbsp;&nbsp;&nbsp;' . $noOfSubjs . '</td>
                <td style="font-size:8px;" width="60%"></td>
                <td style="font-size:10px;color:red;font-weight:bold;" width="12%">'.$units.'</td>
            </tr>
        </table>';

/*
$html.= '<table border="0" cellpadding="0" style="color:#014fb3; font-size:8;" width="528">
   
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
                    <td colspan="5" rowspan="24" height="210px">';

                        $html.= '<table border="0" cellpadding="0" style="color:#014fb3; font-size:8;" width="528">';
                        $totalUnits = 0;
                                foreach($records as $record) {

                                    $html.='<tr style="color: black;">
                                            <td width="80px"> ' . $record['strSection'].'</td>
                                            <td width="65px"> '.  $record['strCode'] . '</td>
                                            <td width="180px" align ="left"> '. $record['strDescription']. '</td>
                                            <td width="30px" align = "center"> '. $record['strUnits']. '</td> ';
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
                                foreach($records as $record){
                                    $noOfSubjs++;
                                    $units += $record['strUnits'];
                                        if($record['intLab'] == 1)
                                        {
                                            $totalLab++;
                                        }

                                        $lecForLab = $totalLab * 2;
                                        $lec = $units - $lecForLab;
                                        $totalLec = $totalLab + $lec;
                                }     
        
       
                         
        $html.='</table>
                <table border="0" cellpadding="0" style="color:#014fb3; font-size:8;" width="528px">
        
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
        
        <table border="0" cellpadding="0" style="color:#014fb3; font-size:8;" width="528px">
           <tr>
            <td colspan ="3" style="text-align:center; color:white; font-size:10;">
               
            </td>
        </tr>
        
        <tr>
            <td width="235"> </td>
            <td width="293">
            </td>
        </tr>
        <tr>
            <td width="235" style="text-align:center; color:black;" > ' . strtoupper($student['enumScholarship']). '</td>
            <td> &nbsp;</td>
           
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
                        <td></td>
                    </tr>
                   <tr>
                        <td>&nbsp;</td>
                        <td width="155" style="vertical-align: middle; color:#014fb3; text-align:right;">&nbsp;&nbsp;</td>
                   </tr>
                    
         
        </table>
        <br />
        <br />
        
        <table border="0" cellpadding="0" width="528px" style="color:#014fb3; font-size:8;">
            <tr>
                <td colspan="4"> </td
            
            </tr>
            <tr>
                <td width="80"> </td>
                <td width="155"> </td>
                <td width="80" style="text-align:center;"> </td>
                <td width="213" style="text-align:center; font-weight:bold; text-decoration:underline;"> </td>
            </tr>
             <tr>
                <td width="80"> </td>
                <td width="155" style="text-align:center;"> </td>
                <td width="80"></td>
                <td width="213" style="text-align:center;"> </td>
            </tr>
        </table>
    ';*/

//$html = utf8_encode($html);
// Print text using writeHTMLCell()
$pdf->writeHTML($html);
//$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

// ---------------------------------------------------------

// Close and output PDF document
// This method has several options, check the source code documentation for more information.
$pdf->Output($student['strLastname'] . ", " . $student['strFirstname'] . ', ' . substr($student['strMiddlename'], 0,1). ".-". $student['strProgramCode'] . ".pdf", 'I');

?>