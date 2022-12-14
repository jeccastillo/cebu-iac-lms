<?php

    tcpdf();
    date_default_timezone_set('Asia/Manila');
    // create new PDF document
    //$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    //$pdf = new TCPDF("P", PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    //$pdf = new TCPDF('P', 'mm', 'A4', FALSE, 'ISO-8859-1', false, true);
    //$pdf = new TCPDF('P', PDF_UNIT, array(8.5, 11), false, 'ISO-8859-1', false, true);
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, array(8.5,11), true, 'UTF-8', false, true);
    //$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, false, 'ISO-8859-1', false);
    //$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, false, 'ISO-8859-1', false);
    //$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, false, 'ISO-8859-1', false);
    
    // set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetTitle($student['strLastname'] . ", " . $student['strFirstname'] . ', ' . substr($student['strMiddlename'], 0,1). ".-". $student['strProgramCode']);
    
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
    $payment_division = $tuition['total'] / 4;
    

    
    // Set some content to print
$html = '<table border="0" cellpadding="0" style="color:#014fb3; font-size:10;">
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
            <td colspan = "3" style="font-weight: bold;text-align:center; font-size:10; letter-spacing: 10px;"></td>
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
    <table border="0" cellpadding="0" style="color:#014fb3; font-size:9;" width="528px">
     <tr>
            <td colspan ="3" style="text-align:center; color:black; font-size:10; font-weight:bold;">
                
            </td>
     </tr>
     <tr>
      <td width="80px">&nbsp;</td>
      <td width="250px">&nbsp;</td>
      <td width="113px">&nbsp;</td>
      <td width="85px" style="color: black;"></td>
     </tr>
     <tr>
      <td width="80px" >&nbsp;</td>
      <td width="250px" style="color: black;">&nbsp;' . strtoupper($student['strLastname']) . ", " . strtoupper($student['strFirstname']) . " " . substr($student['strMiddlename'], 0,1) . ".".'</td>
      <td width="113px">&nbsp;</td>
      <td width="85px" style="color: black;">&nbsp;' . $student['strStudentNumber']. '</td>
     </tr>
     <tr>
      <td width="80px">&nbsp;</td>
      <td width="250px" style="color: black;">&nbsp;'.$student['strProgramDescription'] . '</td>
      <td width="113px">&nbsp;</td>
      <td width="85px" style="color: black;">&nbsp;'. $academic_standing['year']  . '</td>
     </tr>
     <tr>
      <td width="80px">&nbsp;</td>
      <td width="250px" style="color:black;">&nbsp;' .$student['strMajor'] . '</td>
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
$html.= '<table border="0" cellpadding="0" style="color:#014fb3; font-size:8;" width="528">
        
        <tr>
            <th width="60px" ></th>
            <th width="65px" ></th>
            <th width="170px" ></th>
            <th width="30px" ></th>
            <th width="40px" ></th>
            <th width="70px" ></th>
            <th width="93px" ></th>
        </tr> ';
        $html.= '<tr>
                    <td colspan="7" rowspan="24" height="200px">';

                        $html.= '<table border="0" cellpadding="1" style="color:gray; font-size:8;" width="528">';
                        $totalUnits = 0;
                        if (empty($records))
                                    {
                                        $html.='<tr style="color: black; border-bottom: 0px solid gray;">
                                                    <td colspan="7" style="text-align:center;font-size: 11px;">No Data Available</td>
                                                </tr>';
                                    }
                            else {
                                foreach($records as $record) {
                                                                     
                                    $html.='<tr style="color: black; border-bottom: 0px solid gray;">
                                            <td width="60px" style="color: black;font-size:7px;"> ' . strtoupper($record['strSection']).'</td>
                                            <td width="65px" style="color: black;"> '.  $record['strCode'] . '</td>
                                            <td width="170px" align ="left" style="color: black;font-size:8px;"> '. $record['strDescription']. '</td>
                                            <td width="30px" align = "center" style="color: black;"> '. $record['strUnits']. '</td> ';
                                            
                                    if ($record['intFinalized'] == 3) {
                                        if($record['v3'] == 5.00){
                                            $html.='<td width="40px" align = "center" style="color: red;"> ';
                                            $html.= number_format($record['v3'], 2, '.' ,',');
                                            $html.= '</td>';
                                        }
                                        elseif ($record['v3'] == 3.50)
                                        {
                                            $html.='<td width="40px" align = "center" style="color: black;"> ';
                                            $html.= 'inc';
                                             $html.= '</td>';
                                        }
                                        else
                                        {
                                            $html.='<td width="40px" align = "center" style="color: black;"> ';
                                            $html.= number_format($record['v3'], 2, '.' ,',');
                                             $html.= '</td>';
                                        }
                                    }
                                    else
                                    {
                                         $html.= '<td width="40px" align = "center" style="color: black; "> ';
                                         $html.="-";
                                         $html.= '</td>';  
                                    }
                                    
                                        
                                        
                                    if ($record['intFinalized'] == 3) {
                                        if($record['v3'] == 5.00) {
                                            $html.= '<td width="70px" align = "center" style="color: red; ">';
                                            $html.= $record['strRemarks'];
                                            $html.= '</td>';
                                        }
                                        else {
                                            $html.= '<td width="70px" align = "center" style="color: black; ">';
                                            $html.= $record['strRemarks'];
                                            $html.= '</td>';                                            
                                        }
                                    }
                                    else {
                                            $html.= '<td width="70px" align ="center" style="color: black; ">';
                                            $html.="Not Yet Submitted";
                                            $html.= '</td>';  
                                    }
                                            

                                    $html.= '<td width="93px" style="color: black;">&nbsp;&nbsp;&nbsp;';
                                        $firstNameInitial = substr($record['strFirstname'], 0,1);
                                        $html.=$firstNameInitial.". ".$record['strLastname'];  
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
                                    foreach($records as $record) {
                                        $noOfSubjs++;
                                        if($record['v3']!= 3.50 && $record['v3'] != "0" && $record['intFinalized'] == 3 )                                         {
                                        //$productArray = array();
                                            if ($record['intBridging'] == 1){
                                                //$num_of_bridging = count($record['intBridging']);
                                                
                                                $totalUnits += $record['strUnits'];
                                                $totalUnits-=3;
                                            }
                                            else{
                                                $product = $record['strUnits'] * $record['v3']; 
                                                $products[] = $product;
                                                $totalUnits += $record['strUnits'];
                                            }     

                                        }


                                            if($record['intLab'] == 1)
                                            {
                                                $totalLab++;
                                            }

                                            $lecForLab = $totalLab * 2;
                                            $lec = $totalUnits - $lecForLab;
                                            $totalLec = $totalLab + $lec;
                                    }
                                }
                                
                                if ($totalUnits == 0)
                                {
                                    $gpa = 0.00;
                                }
                                else
                                {
                                    $gpa = round(array_sum($products) /$totalUnits, 2);    
                                }
                                
        
        
                         
        $html.='</table>
                <table border="0" cellpadding="0" width="528px">
        
                    <tr>
                    <td width="80px" height="10px">&nbsp;</td>
                    <td width="50px" height="10px" style="color: black;text-align:left;">' . $noOfSubjs . '</td>
                    <td width="150px" height="10px" colspan="2">&nbsp;</td>
                    <td width="65px" height="10px" style="color: black;text-align:center;">&nbsp;&nbsp;'. $totalUnits . '</td>
                    <td width="65px" height="10px" style="color: black;text-align:center;"></td>
                    <td width="40px" height="10px">&nbsp;</td>
                    <td width="78px" height="10px"style="color: black;text-align:center;">&nbsp; '. number_format($gpa, 2, '.' ,',') .'</td>
                    </tr>

        </table>
        
        <table border="0" cellpadding="0" style="color:#014fb3; font-size:8;" width="528px">
           <tr>
            <td colspan ="3" style="text-align:center;color:black; font-size:10;font-weight:bold;">
                
            </td>
        </tr>
        
        <tr >
            <td width="235" > </td>
            <td width="293" >
                
            </td>
        </tr>
        <tr >
            <td width="235" style="text-align:center; color:black;" > ' . strtoupper($registration['enumScholarship']). '</td>
            <td> &nbsp;</td>
           
        </tr>
        <tr>
            <td width="235" style="font-weight:bold;"> </td>
            <td width="145" style="font-weight:bold;"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </td>
            <td width="148" style="text-align:center; color:black;font-weight:bold;"></td>
        </tr>
        <tr>
            <td width="117" style=";text-align:center;"></td>
            <td width="118" style="color:black; text-align:center;">&nbsp;&nbsp;</td>
            <td width="145"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
            <td width="148" style="text-align:left; color:black;"> </td>
        </tr>
                    <tr>
                        <td width="117" style="text-align:center;"></td>';
                        
                        
                        $html.= '<td width="118"style="color:black; text-align:center;"></td>
                        <td width="145"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </td>
                        <td width="148" style="text-align:left; color:black;"></td>
                    </tr>
                    <tr>
                        <td width="117" style="text-align:center;"></td>
                        <td width="118" style="color:black; text-align:center;"></td>
                        <td width="145"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </td>
                        <td width="148" style="text-align:center; color:black;"></td>                    
                    </tr>
                    <tr>
                        <td width="117" style="text-align:center;"></td>
                            
                        <td width="118" style="color:black; text-align:center;"></td>
                        <td></td>
                        <td width="148"></td>
                    </tr>
                    <tr>
                        <td width="117" style="text-align:center;"></td>
                        <td width="118" style="color:black; text-align:center;"> </td>
                        <td></td>
                        <td width="148"></td>
                    </tr>
                    <tr>
                        <td width="117" style="text-align:center;"></td>
                        <td width="118" style="color:black; text-align:center;"></td>    <td></td>
                        <td width="148"></td>
                    </tr>
                    <tr>
                        <td width="117" style="text-align:center;"></td>
                        <td width="118" style="color:black; text-align:center;"></td>
                        <td></td>
                        <td width="148"></td>
                    </tr>
                    <tr>
                        <td width="117" style="text-align:center;"></td>
                        <td width="118" style="color:black; text-align:center;"></td>
                        <td></td>
                        <td width="148"></td>
                        
                    </tr>
                      <tr>
                        <td width="117" style="text-align:center;"></td>
                        <td width="118" style="color:black; text-align:center;"></td>
                        <td colspan="2" rowspan = "9" align="center" style="">
                          <table border="0px" width="285"  style="font-size: 8px;">
                          <tr>
                                    <td style="font-weight:bold;"></td>
                                </tr>
                                <tr>
                                    <td style="font-weight:bold;"></td>
                                </tr>
                               
                                <tr>
                                    <td style="text-align: left;"></td>
                                    
                                </tr>
                                <tr>
                                    <td align="justify" style="color:black;" width="270" height="100">
                                        <ol>
                                            
                                                
                                            
                                 
                                        </ol>
                                    </td>
                                </tr>
                                <tr>
                                    <td></td>
                                    
                                </tr>                      
                                
                            </table>
                           
                        
                        </td>
                      </tr>
                      <tr>
                        <td width="117" style="text-align:center;"></td>
                        <td width="118" style="color:black; text-align:center;"></td>
                        
                    </tr>
                      <tr>
                        <td width="117" style="text-align:center;"></td>
                        <td width="118" style="color:black; text-align:center;"></td>
                        
                    </tr>
                      <tr>
                        <td width="117" style="text-align:center;"></td>
                        <td width="118" style="color:black; text-align:center;"></td>
                        
                    </tr>
                    
                     <tr>
                        <td width="117" style="text-align:center;"></td>
                        <td width="118" style="color:black; text-align:center;"></td>
                    </tr>
                    <tr>
                        <td width="117" style="text-align:center;"></td>
                        <td width="118" style="color:black; text-align:center;"></td>
                        
                    </tr>
                      <tr>
                        <td width="117" >&nbsp;</td>
                        <td width="118" style="color:black; text-align:right;"></td>
                        
                    </tr>
                    <tr>
                        <td width="117"></td>
                        <td width="118" style=""></td>
                    </tr>
                   <tr>
                        <td width="117">&nbsp;</td>
                        <td width="118" style="color:#014fb3; text-align:center;">
                        </td>
                   </tr>
                    
         
        </table>
        <br />
        <br />
          <table border="0" cellpadding="0" width="528px" style="color:#014fb3; font-size:8;">
           <tr>
                <td width="80"></td>';
                
                
//                if($user['strFirstname'] == "JOEL MARI" &&  $user['strLastname'] == "ALCALA") {
//                    $html.='<td width="155" tyle="color:black;text-align:center;" rowspan="2">';
//                    $html.= '<img src= "'.$img_dir .'jmsign2.png"  width="90" height="18"/>';    
//                }
//                else if ($user['strFirstname'] == "ALDWIN KARLO" &&  $user['strLastname'] == "ANGCAYA") {
//                    $html.='<td width="155" tyle="color:black;text-align:center;" rowspan="2">';
//                    $html.= '<img src= "'.$img_dir .'aldwinsign.png"  width="90" height="18"/>';    
//                }
//                else if ($user['strFirstname'] == "REGGIE MAR" &&  $user['strLastname'] == "DE CASTRO") {
//                    $html.='<td width="155" tyle="color:black;text-align:center;" rowspan="2">';
//                    $html.= '<img src= "'.$img_dir .'reggiesign.png"  width="90" height="22"/>';    
//                }
//                else if ($user['strFirstname'] == "RONNIE" &&  $user['strLastname'] == "MARANAN") {
//                    $html.='<td width="155" tyle="color:black;text-align:center;" rowspan="2">';
//                    $html.= '<img src= "'.$img_dir .'ronniesign.png"  width="90" height="22"/>';    
//                }
//                else if ($user['strFirstname'] == "ANGELITO" &&  $user['strLastname'] == "CARAAN") {
//                    $html.='<td width="155" tyle="color:black;text-align:center;" rowspan="2">';
//                    $html.= '<img src= "'.$img_dir .'caraansign.png"  width="90" height="22"/>';    
//                }
//                else {
//                    $html.='<td width="155" tyle="color:black;text-align:center;vertical-align: bottom;" rowspan="2">';
//                    //$html.= '<u>' . $user['strFirstname']. '&nbsp; '. $user['strLastname'] . '</u>';
//                }
//                $html.= '</td>';
                $html.='<td width="80" style="text-align:center;"></td>
                <td width="213" style="text-align:left; font-weight:bold; text-decoration:underline;">  </td>
            </tr>
            
            <tr>
                
                <td width="80" style="text-align:left;font-size:6pt;">  </td>';
                
                
                $html.= '<td width="80" style="text-align:center;"> </td>
                <td width="213" style="text-align:center; font-weight:bold; text-decoration:underline;"> </td>
            </tr>
             <tr>
                <td width="80" style="text-align:left;font-size:6pt;"></td>
                <td width="155" style="text-align:left;font-size:6pt;color:black;">' . date('F d, Y g:i:s A ') . '</td>
                <td width="80"></td>
                <td width="213" style="text-align:center;"> </td>
            </tr>
        </table>
    ';

//$html = utf8_encode($html);
$pdf->writeHTML($html, true, false, true, false, '');
//$pdf->writeHTML($html, true, false, true, false, '');
// Print text using writeHTMLCell()
//$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

// ---------------------------------------------------------

// Close and output PDF document
// This method has several options, check the source code documentation for more information.
$pdf->Output($student['strLastname'] . ", " . $student['strFirstname'] . ', ' . substr($student['strMiddlename'], 0,1). ".-". $student['strProgramCode'] . ".pdf", 'I');

?>