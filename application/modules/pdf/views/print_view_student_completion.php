<?php

    tcpdf();
    // create new PDF document
    //$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    //$pdf = new TCPDF("P", PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf = new TCPDF("P", PDF_UNIT, array(8.5, 11), true, 'UTF-8', false);

    // set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetTitle($cs['strLastname'] . ", " . $cs['strFirstname'] . ', ' . substr($cs['strMiddlename'], 0,1). "-". $cs['strProgramCode']);
    
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
$html = '<table border="0" cellpadding="0" style="color:black; font-size:10;">
        <tr>
        <td>CCT-AA Form No. 4, Rev. 1</td>
        </tr>
        <tr>
            <td width="64" align="right"></td>
            <td width="400" style="text-align: center; line-height:100%">
             <font style="font-family:Calibri Light; font-size: 10;">Republic of the Philippines</font><br />
             <font style="font-family:Calibri Light; font-size: 10;font-weight: bold;">iACADEMY Inc.</font><br />
			 <font style="font-family:Calibri Light; font-size: 10;">Filinvest Cebu Cyberzone Tower 2 Salinas Drive corner W. Geonzon St., Brgy. Apas, Lahug, Cebu City</font><br />
            </td>
            <td width="64" align="left" valign="middle"></td>
        </tr>
        <tr>
            <td colspan = "3" style="font-weight: bold;text-align:center; font-size:10;"> REPORT OF COMPLETION / REMOVAL OF GRADE</td>
        </tr>
        <tr>
            <td></td>
        </tr>
        <tr>
        <td colspan="3" style="font-size:10;">
        </td>
        </tr>
    </table>
     <br />
    <table border="0" cellpadding="0" style="color:black; font-size:9;" width="528px">
     <tr>
            <td colspan ="3" style="text-align:left; font-size:10;">
                For: The College Registrar
            </td>
     </tr>
     <tr>
      <td colspan ="3" style="text-align:left; font-size:10;">
        
      </td>
   
     </tr>
     <tr>
      <td width="120px" style="text-align: right;">&nbsp;This is to certify that</td>
      <td width="253px" style="border-bottom: 0px solid black;color: black;text-align: center;font-weight: bold;">&nbsp;' . strtoupper($cs['strLastname']) . ", " . strtoupper($cs['strFirstname']) . " " . substr($cs['strMiddlename'], 0,1) . ".".'</td>
      <td width="5px">,</td>
      <td width="100px" style="border-bottom: 0px solid black;text-align: center;font-weight: bold;">'. $cs['strStudentNumber'].'</td>
      <td width="50px" style="color: black;">&nbsp;a student in</td>
     </tr>
     <tr>
      <td width="125px" style="">&nbsp;</td>
      <td width="253px" style="color: black;text-align:center;font-style:italic;">&nbsp;(student name)</td>
      <td width="100px" style="text-align:center;font-style:italic;">&nbsp;(student number)</td>
      <td width="50px" style="color: black;">&nbsp;</td>
     </tr>
     <tr>
      <td width="20px" style="">&nbsp;the</td>
      <td width="200px" style="color: black;text-align:center;border-bottom: 0px solid black;">'.$cs['strProgramCode'] . '</td>
      <td colspan="2" width="308px" style="color: black;">&nbsp;program, has completed the grade of "INC" / removed the grade of "4.0" on 
       </td>
     </tr>
     <tr>
     <td colspan ="4" style="text-align:left;"></td>
    </tr>
     <tr>
      <td colspan ="4" style="text-align:left;">&nbsp;the course(s) listed below:</td>
     </tr>
     <tr>
     <td colspan ="4" style="text-align:left;"></td>
    </tr>
    </table> '; 
$html.= '<table border="0" cellpadding="0" style="font-size:9;" width="528">    
                <tr style="line-height: 20px;">
                    <td colspan="6" rowspan="24" height="210px">';

                        $html.= '<table border="0" cellpadding="0" style="color:black; font-size:9;" width="528">';
                        $html.= '<tr style="line-height: 15px;border: 1px solid black;">
                        <th width="80px" style="text-align:center;font-weight: bold;border: 0px solid black;">Course Code</th>
                        <th width="60px" style="text-align:center;font-weight: bold;border: 0px solid black;">Previous<br />Grade</th>
                        <th width="120px" style="text-align:center;font-weight: bold;border: 0px solid black;">Sem./Summer/AY Taken</th>
                        <th width="95px" style="text-align:center;font-weight: bold;border: 0px solid black;">Date of Completion/Removal</th>
                        <th width="86px" style="text-align:center;font-weight: bold;border: 0px solid black;">Final Term Grade<br /> '. '<span style="font-size: 7px;">(P = ' .$cs['floatPrelimGrade']  . ', M = ' . $cs['floatMidtermGrade'] . ')</span></th>
                        <th width="87px" style="text-align:center;font-weight: bold;border: 0px solid black;">Sem.Grade & Numerical Rating</th>
                        </tr> ';

                                    $html.='<tr style="color: black;line-height: 20px;text-align:center;">
                                            <td width="80px" style="border: 0px solid black;">'. $cs['strCode']. '</td>
                                            <td width="60px" style="border: 0px solid black;">' .$cs['enumStatus']. '</td>
                                            <td width="120px" style="border: 0px solid black;">'. $cs['enumSem'] . " sem " . $cs['strYearStart'] . "-" . $cs['strYearEnd']. '</td>
                                            <td width="95px" align = "center" style="border: 0px solid black;">' .$st['dteDateOfCompletion'] . '</td> ';
                                            $html.= '<td width="86px" style="border: 0px solid black;">'. $st['floatNewFinalTermGrade'];


                                            $html.= '</td><td width="87px" style="border: 0px solid black;">'. $ave . " = " . $eq . '</td>';
                                        $html.='</tr>';
                                        
                                    $html.='<tr style="color: black;line-height: 20px;">
                                    <td width="80px" style="border: 0px solid black;"> </td>
                                    <td width="60px" style="border: 0px solid black;"> </td>
                                    <td width="120px" style="border: 0px solid black;"> </td>
                                    <td width="95px" style="border: 0px solid black;"> </td> ';
                                    $html.= '<td width="86px" style="border: 0px solid black;">';

                                
                                    $html.= '</td><td width="87px" style="border: 0px solid black;"></td>';
                                $html.='</tr>';
                                
                                $html.='<tr style="color: black;line-height: 20px;">
                                <td width="80px" style="border: 0px solid black;"> </td>
                                <td width="60px" style="border: 0px solid black;"> </td>
                                <td width="120px" style="border: 0px solid black;"> </td>
                                <td width="95px" style="border: 0px solid black;"> </td> ';
                                $html.= '<td width="86px" style="border: 0px solid black;">';

                                $html.= '</td><td width="87px" style="border: 0px solid black;"></td>';
                            $html.='</tr>';

                            $html.='<tr style="color: black;line-height: 50px;">
                            <td width="80px"> </td>
                            <td width="60px"> </td>
                            <td width="120px"> </td>
                            <td width="95px"> </td> ';
                            $html.= '<td width="86px">';

                            $html.= '</td><td width="87px"></td>';
                        $html.='</tr>';


                        $html.='<tr style="color: black;line-height: 10px;">
                                <td width="80px"> </td>
                                <td width="60px"> </td>
                                <td width="120px"></td>
                                <td width="95px" colspan="2"> Instructor/Professor:</td> ';
                                $html.= '<td width="173px" style="border-bottom: 0px solid black;text-align:center;font-size: 9px;font-weight:bold;" colspan="2">' . ucwords(strtolower($cs['facFname'])) . " " . ucwords(strtolower($cs['facLname']));

                                $html.= '</td>';
                            $html.='</tr>';
                            $html.='<tr style="color: black;line-height: 15px;">
                                <td width="80px"> </td>
                                <td width="60px"> </td>
                                <td width="120px"></td>
                                <td width="95px" colspan="2"></td> ';
                                $html.= '<td width="173px" style="text-align: center;" colspan="2">(Signature over Printed Name)';

                                $html.= '</td>';
                            $html.='</tr>';
                            
                            $html.='<tr style="color: black;line-height: 15px;">
                            <td width="80px"> </td>
                            <td width="60px"> </td>
                            <td width="120px"></td>
                            <td width="95px" colspan="2"></td> ';
                            $html.= '<td width="173px" style="border-bottom: 0px solid black;text-align:center;" colspan="2">'.$st['dteDateOfCompletion'] ;

                            $html.= '</td>';
                        $html.='</tr>';


                        $html.='<tr style="color: black;line-height: 15px;">
                        <td width="80px"> </td>
                        <td width="60px"> </td>
                        <td width="120px"></td>
                        <td width="95px" colspan="2"></td> ';
                        $html.= '<td width="173px" style="text-align: center;" colspan="2">Date';

                        $html.= '</td>';
                    $html.='</tr>';
                        $html.= '</table>';

            $html.= '</td> 
            </tr>';
             

                         
        $html.='</table>
                
        
        <table border="0" cellpadding="0" style="color:black; font-size:9;" width="528px">
           <tr>
            <td colspan ="3" style="">
            </td>
            </tr>
        
        <tr style="">
            <td width="235"> Recommending Approval:</td>
            <td width="293"></td>
        </tr>
        
        <tr style="">
            <td width="235" style="text-align:center; color:black;" > </td>
            <td> &nbsp;</td>
           
        </tr>
        <tr>
            <td width="235" style="text-align:center; color:black;border-bottom: solid 0px black; font-weight: bold;" >'. $cs['strSignatory1Name']	.' </td>
            <td> &nbsp;</td>
           
        </tr>
        <tr>
            <td width="235" style="text-align:center;"> Chairperson</td>
            <td width="145"> &nbsp; </td>
            <td width="148" style="text-align:center; color:black;"></td>
        </tr>
        <tr>
            <td width="80" style="">&nbsp;</td>
            <td width="155" style="color:black; text-align:right;">  &nbsp; </td>
            <td width="145"> &nbsp; </td>
            <td width="148" style="text-align:center; color:black;"></td>
        </tr>
                    <tr>
                        <td width="80" style="">&nbsp;Department of:</td>';
                           
                        
                        $html.= '<td width="155"style="color:black; text-align:center;border-bottom: 0px solid black;font-weight: bold;">&nbsp;' . $cs['strProgramCode'].'</td>
                        <td width="145"> &nbsp; </td>
                        <td width="148" style="text-align:center; color:black;"></td>
                    </tr>
                    <tr>
                        <td width="80" style="">&nbsp;</td>
                        <td width="155" style="color:black; text-align:right;"> &nbsp; </td>
                        <td width="145"> &nbsp;</td>
                        <td width="148" style="text-align:center; color:black;"></td>                    
                    </tr>
                    <tr>
                        <td width="80" style="">&nbsp;Date:</td>
                            
                        <td width="155" style="color:black; text-align:center;border-bottom: 0px solid black;"> &nbsp;'.$st['dteDateOfCompletion'] . ' </td>
                        <td></td>
                        <td width="148"></td>
                    </tr>
                    <tr>
                        <td width="80" style="">&nbsp;</td>
                        <td width="155" style="color:black; text-align:right;"> &nbsp; </td>
                        <td></td>
                        <td width="148"></td>
                    </tr>
                    <tr>
                        <td width="80" style="">&nbsp;</td>
                        <td width="155" style="color:black; text-align:right;">&nbsp; </td>    <td></td>
                        <td width="148"></td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td width="155" style="color:black; text-align:right;">&nbsp; </td>
                        <td></td>
                        <td width="148"></td>
                    </tr>
                   
                      <tr>
                        <td width="80">&nbsp;</td>
                        <td width="155" style="color:black; text-align:right;"></td>
                        <td colspan="2" rowspan = "9" align="">
                          <table border="0" width="285" style="font-size: 9px;">
                                <tr style="line-height: 20px;">
                                    <td></td>
                                </tr>
                                <tr style="">
                                    <td>Approved:</td>
                                </tr>
                                <tr>
                                    <td></td>
                                </tr>
                            
                                <tr>
                                    <td style="border-bottom: solid 0px black; text-align:center; font-weight: bold;">'.$cs[
                                        'strSignatory2Name'] .'</td>
                                   
                                </tr>
                                <tr style="">
                                    <td style="text-align: center;">Dean, School of '. $school . '</td>
                                </tr>
                                <tr style="line-height: 15px;">
                                    <td></td>
                                </tr>
                                <tr style="line-height: 15px;">
                                    <td></td>
                                </tr>
                                <tr style="line-height: 15px;">
                                    <td></td>
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
                        <td width="80">&nbsp;</td>
                        <td width="155" style="color:black; text-align:right;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </td>
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
                        <td></td>
                        <td style="text-align:center;"></td>
                    </tr>
                   <tr>
                        <td>&nbsp;</td>
                        <td  width="155" style="vertical-align: middle; text-align:center;"></td>
                   </tr>
                    
         
        </table>
        <br />
        <br />
        
        <table border="0" cellpadding="0" width="528px" style="font-size:9;">
            <tr style="line-height: 15px;">
                <td></td>
            </tr>
                 
            <tr>
                <td style="text-align: center;"> (To be accomplished in quadruplicate, one (1) copy each for the School Dean, Registrar, Department Chair, and Student.) </td>
            </tr>
          
        </table>
    ';

$html = utf8_encode($html);
$pdf->writeHTML($html);

//$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

// ---------------------------------------------------------

// Close and output PDF document
// This method has several options, check the source code documentation for more information.
$pdf->Output($cs['strLastname'] . ", " . $cs['strFirstname'] . ', ' . substr($cs['strMiddlename'], 0,1). ".-". $cs['strProgramCode'] . ".pdf", 'I');


?>