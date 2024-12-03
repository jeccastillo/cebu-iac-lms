<?php

    tcpdf();
    // create new PDF document
    //$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    //$pdf = new TCPDF("P", PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

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


    
    // Set some content to print
$html = '<table border="0" cellpadding="0" style="color:#333; font-size:10;">
            <tr>
                <td width="100%" align="center" style="text-align:center;vertical-align: middle;"><img src= "https://i.ibb.co/XW1DRVT/iacademy-logo.png"  width="150" height="44"/></td>
            </tr>         
            <tr>            
                <td colspan = "3" width="100%" style="text-align: center; vertical-align: middle; line-height:100%">             
                    <font style="font-family:Calibri Light; font-size: 16;font-weight: bold;">iACADEMY, Inc. </font><br /><br />
                    <font style="font-family:Calibri Light; font-size: 10;">Filinvest Cebu Cyberzone Tower 2 Salinas Drive corner W. Geonzon St., Brgy. Apas, Lahug, Cebu City</font><br />             
                </td>           
            </tr>               
            <tr>
                <td colspan = "3" style="font-weight: bold;text-align:center; font-size:12;">SUBJECTS ENLISTED</td>
            </tr>
            <tr>
                <td colspan = "3" style="text-align:center; color:black; font-size: 10;"> A.Y. ' .$active_sem['strYearStart']."-".$active_sem['strYearEnd'] . ", " . $active_sem['enumSem'].' Semester' . '<br /><br /></td>                
            </tr>
            <tr>
                <td colspan="3" style="font-size:10;">
                </td>
            </tr>
        </table>
    ';
$html.= '<table border="0" cellpadding="0" style="color:#333; font-size:8;" width="528px">
     
     <tr>
      <td width="80px" >&nbsp;NAME:</td>
      <td width="250px" style="color: black;">&nbsp;' . strtoupper($student['strLastname']) . ", " . strtoupper($student['strFirstname']) . " " . strtoupper($student['strMiddlename']).'</td>
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
      <td width="250px" style="color:black;">&nbsp;' .$student['strMajor'] . '</td>
      <td width="113px">&nbsp;REGISTRATION STATUS:</td>
      <td width="85px" style="color: black;text-transform:capitalize;">&nbsp;'.$registration['enumRegistrationStatus'].'</td>
     </tr>
     </table><br /><br />';
    
//if($prev_records!=null){
$html.= '<table border="0" cellpadding="0" style="color:#333; font-size:8;" width="528px">

            <tr height="200px">
                <td width="259px" height="180px"><table border="0" cellpadding="0" width="259px" style="border: solid 0px maroon;">
                <tr><th colspan="3" align="center" style="border-bottom: 0px solid #333;">Course TAKEN from the previous Term</th></tr>
                        <tr>
                            <th width="60px" style="text-align:center;font-weight:bold;">Course Code</th>
                            <th width="164px" style="text-align:center;font-weight:bold;">Course Description</th>
                            <th width="35px" style="text-align:center;font-weight:bold;">Grade</th>
                        </tr>';
                
                $html.= '<tr><td><table border="0" cellpadding="2" style="color:gray; font-size:8; border:solid 0px maroon;" width="264px">';-
                            $totalUnits = 0;
                          
                                for($i=0;$i<13;$i++) {
                                    $s = isset($prev_records[$i]['strCode'])?ellipsize($prev_records[$i]['strCode'],8):'';
                                    $s2 = isset($prev_records[$i]['strDescription'])?ellipsize($prev_records[$i]['strDescription'],30):'';
                                    $s3 = isset($prev_records[$i]['v3'])?number_format($prev_records[$i]['v3'], 2, '.' ,','):'';
                                    $html.='<tr style="color: black;;">
                                                <td width="60px" style="color: black; border: 0px solid #333"> '.$s. '</td>
                                                <td width="164px" align ="left" style="color: black; border: 0px solid #333;"> '. $s2. '</td>
                                                <td width="35px" align = "center" style="color: black; border: 0px solid #333"> '. $s3. '</td>';
                                    $html.='</tr>';
                                }                           
                        $html.= '</table>';

                    $html.= '</td> 
                    </tr>
                </table>
                </td>
                
                
                <td width="10px"></td>
                
                
                            
                <td width="259px"><table border="0" cellpadding="0" width="259px" style="border: solid 0px maroon;">
                <tr><th colspan="3" align="center" style="border-bottom: 0px solid #333;">Course to ENROLL this Term</th></tr>
                        <tr>

                            <th width="60px" style="text-align:center;font-weight:bold;">Course Code</th>
                            <th width="164px" style="text-align:center;font-weight:bold;">Course Description</th>
                            <th width="35px" style="text-align:center;font-weight:bold;">Unit/s</th>
                        </tr>';
            
                $html.= '<tr><td><table border="0" cellpadding="2" style="color:gray; font-size:8; border:solid 0px maroon;" width="264px">';
                            $totalUnits = 0;
                            
                                
                                  for($i=0;$i<13;$i++) {
                                      $s = isset($advised[$i]['strCode'])?ellipsize($advised[$i]['strCode'],9):'';
                                      $s2 = isset($advised[$i]['strDescription'])?ellipsize($advised[$i]['strDescription'],30):'';
                                      $s3 = isset($advised[$i]['strUnits'])?$advised[$i]['strUnits']:'';
                                      if(isset($advised[$i]['strUnits'])){
                                        $s3  = ($s3 == 0)?'('.$advised[$i]['intLectHours'].')':$s3;  
                                        $totalUnits += $advised[$i]['strUnits'];
                                      }
                                    $html.='<tr style="color: black;">
                                            <td width="60px" style="color: black;border: 0px solid #333;"> '.$s. '</td>
                                            <td width="164px" align ="left" style="color: black;border: 0px solid #333;"> '.$s2. '</td>
                                            <td width="35px" align = "center" style="color: black; border: 0px solid #333"> '.$s3. '</td>';
                                        $html.='</tr>';
                                }
                            
                        $html.= '<tr style="color: black;">
                                    <td width="60px"></td>
                                    <td width="164px" style="text-align: right;border-right: 0px solid #333;font-weight:bold;">Total Units:</td>
                                    <td width="35px" style="text-align: center;font-weight:bold;">'. $totalUnits . '</td>
                                        
                        </tr>';
                        $html.= '</table>';
                    $html.= '</td> 
                    </tr>
                </table>
                
                
                </td>
            </tr> 
            
            </table>';
  
    
$html.= '<br /><br /><table border="0" cellpadding="0" style="color:#333; font-size:8;" width="528px">

            <tr height="100px">
                <td width="264px">Advised by:</td>
                <td width="10px"></td>
                <td width="264px" style="text-align: right;">Approved by:</td>
            </tr>
             <tr height="100px">
                <td width="264px" style="text-align: left;"></td>
                <td width="10px"></td>
                <td width="264px"></td>
            </tr>
            <tr height="100px">
                <td width="264px" style="text-align: left;">__________________________</td>
                <td width="10px"></td>
                <td width="264px" style="font-weight:bold; text-decoration:underline;text-align: right;"></td>
            </tr>
            <tr height="100px">
                <td width="264px" style="text-align: left;">Department Chair</td>
                <td width="10px"></td>
                <td width="264px" style="text-align: right;">Registrar</td>
            </tr>
        </table>
        ';
    
    
    /*end of upper part */
    
    
    // start of lower part
    
    
    
//}
                           

//$html = utf8_encode($html);
$pdf->writeHTML($html, true, false, true, false, '');
// $pdf->writeHTML("<hr />", true, false, true, false, '');
// $pdf->writeHTML($html, true, false, true, false, '');
// Print text using writeHTMLCell()
//$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

// ---------------------------------------------------------

// Close and output PDF document
// This method has several options, check the source code documentation for more information.
$pdf->Output($student['strLastname'] . ", " . $student['strFirstname'] . ', ' . substr($student['strMiddlename'], 0,1). ".-". $student['strProgramCode'] . ".pdf", 'I');

?>