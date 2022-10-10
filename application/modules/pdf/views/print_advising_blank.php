<?php

    tcpdf();
    // create new PDF document
    //$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    //$pdf = new TCPDF("P", PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
     $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, array(8.5,13), true, 'UTF-8', false, true);

    // set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetTitle("Blank Advising Form");
    
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
$html = '<table border="0" cellpadding="0" style="color:maroon; font-size:10;">
        <tr>
            <td width="64" align="right"><img src= "'.$img_dir .'tagaytayseal.png"  width="50" height="50"/></td>
            <td width="400" style="text-align: center; line-height:100%">
             <font style="font-family:Calibri Light; font-size: 10;">City of Tagaytay</font><br />
             <font style="font-family:Calibri Light; font-size: 10;font-weight: bold;">CITY COLLEGE OF TAGAYTAY</font><br />
			 <font style="font-family:Calibri Light; font-size: 10;">Akle St., Kaybagal South, Tagaytay City</font><br />
             <font style="font-family:Calibri Light; font-size: 10;">Telephone No: (046) 483-0470 / (046) 483-0672</font><br />
            </td>
            <td width="64" align="left" valign="middle"><img src= "'.$img_dir .'cctlogo.png"  width="50" height="50"/></td>
        </tr>
        <tr>
            <td colspan = "3" style="font-weight: bold;text-align:center; font-size:10; letter-spacing: 0px;">OFFICE OF THE COLLEGE REGISTRAR</td>
        </tr>
        <tr>
            <td colspan = "3" style="text-align:center; color:black; font-size: 10;">Student\'s Advising Form</td>
            
        </tr>
          <tr>
            <td colspan = "3" style="text-align:center; color:black; font-size: 10;"> </td>
            
        </tr>
        <tr>
        <td colspan="3" style="font-size:10;">
        </td>
        </tr>
        </table>
    ';
$html.= '<table border="0" cellpadding="0" style="color:maroon; font-size:8;" width="528px">
     
     <tr>
      <td width="80px" >&nbsp;NAME:</td>
      <td width="250px" style="color: black;"></td>
      <td width="113px">&nbsp;STUDENT NUMBER:</td>
      <td width="85px" style="color: black;"></td>
     </tr>
     <tr>
      <td width="80px">&nbsp;PROGRAM:</td>
      <td width="250px" style="color: black;"></td>
      <td width="113px">&nbsp;YEAR LEVEL:</td>
      <td width="85px" style="color: black;"></td>
     </tr>
     <tr>
      <td width="80px">&nbsp;MAJOR:</td>
      <td width="250px" style="color:black;"></td>
      <td width="113px">&nbsp;REGISTRATION STATUS:</td>
      <td width="85px" style="color: black;text-transform:capitalize;">&nbsp;</td>
     </tr>
     </table><br /><br />';
    
//if($prev_records!=null){
$html.= '<table border="0" cellpadding="0" style="color:maroon; font-size:8;" width="528px">

            <tr height="200px">
                <td width="259px" height="180px"><table border="0" cellpadding="0" width="259px" style="border: solid 0px maroon;">
                <tr><th colspan="3" align="center" style="border-bottom: 0px solid maroon;">Course TAKEN from the previous Semester</th></tr>
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
                                    $s3 = isset($prev_records[$i]['v3'])?getEquivalent($prev_records[$i]['v3']):'';
                                    $html.='<tr style="color: black;;">
                                                <td width="60px" style="color: black; border: 0px solid maroon"> '.$s. '</td>
                                                <td width="164px" align ="left" style="color: black; border: 0px solid maroon;"> '. $s2. '</td>
                                                <td width="35px" align = "center" style="color: black; border: 0px solid maroon"> '. $s3. '</td>';
                                    $html.='</tr>';
                                }                           
                        $html.= '</table>';

                    $html.= '</td> 
                    </tr>
                </table>
                </td>
                
                
                <td width="10px"></td>
                
                
                            
                <td width="259px"><table border="0" cellpadding="0" width="259px" style="border: solid 0px maroon;">
                <tr><th colspan="3" align="center" style="border-bottom: 0px solid maroon;">Course to ENROL this Semester</th></tr>
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
                                      if(isset($advised[$i]['strUnits']))
                                            $totalUnits += $advised[$i]['strUnits'];
                                    $html.='<tr style="color: black;">
                                            <td width="60px" style="color: black;border: 0px solid maroon;"> '.$s. '</td>
                                            <td width="164px" align ="left" style="color: black;border: 0px solid maroon;"> '.$s2. '</td>
                                            <td width="35px" align = "center" style="color: black; border: 0px solid maroon"> '.$s3. '</td>';
                                        $html.='</tr>';
                                }
                            
                        $html.= '<tr style="color: black;">
                                    <td width="60px"></td>
                                    <td width="164px" style="text-align: right;border-right: 0px solid maroon;font-weight:bold;">Total Units:</td>
                                    <td width="35px" style="text-align: center;font-weight:bold;"></td>
                                        
                        </tr>';
                        $html.= '</table>';
                    $html.= '</td> 
                    </tr>
                </table>
                
                
                </td>
            </tr> 
            
            </table>';
  
    
$html.= '<br /><table border="0" cellpadding="0" style="color:maroon; font-size:8;" width="528px">

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
                <td width="264px" style="font-weight:bold; text-decoration:underline;text-align: right;">Heizel M. Garcia</td>
            </tr>
            <tr height="100px">
                <td width="264px" style="text-align: left;">Department Chair</td>
                <td width="10px"></td>
                <td width="264px" style="text-align: right;">College Registrar</td>
            </tr>
        </table>
        ';
    
    
    /*end of upper part */
    
    
    // start of lower part
    
    
    
//}
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
                                        if(getEquivalent($record['v3']) != "inc" && getEquivalent($record['v3']) != "0"){
                                            $product = $record['strUnits'] * getEquivalent($record['v3']); 
                                            $products[] = $product;
                                            $totalUnits += $record['strUnits'];

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
                                
        

//$html = utf8_encode($html);
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->writeHTML("<hr />", true, false, true, false, '');
$pdf->writeHTML($html, true, false, true, false, '');
// Print text using writeHTMLCell()
//$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

// ---------------------------------------------------------

// Close and output PDF document
// This method has several options, check the source code documentation for more information.
$pdf->Output("Blank_Advising_Form" . ".pdf", 'I');

?>