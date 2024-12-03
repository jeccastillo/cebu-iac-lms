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
    $pdf->SetTitle("Blank ROG Form");
    
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
            <td colspan = "3" style="font-weight: bold;text-align:center; font-size:10; letter-spacing: 10px;">REPORT OF GRADES</td>
        </tr>
        <tr>
            <td colspan = "3" style="text-align:center; color:black; font-size: 10;"> </td>
        </tr>
        <tr>
        <td colspan="3" style="font-size:10;">
        </td>
        </tr>
    </table>
     <br />
    <table border="0" cellpadding="0" style="color:#014fb3; font-size:9; border: 0px solid #014fb3;" width="528px">
     <tr>
            <td colspan ="3" style="text-align:center; color:black; font-size:10; font-weight:bold;">
                STUDENT INFORMATION
            </td>
     </tr>
     <tr>
      <td width="80px">&nbsp;</td>
      <td width="250px">&nbsp;</td>
      <td width="113px">&nbsp;</td>
      <td width="85px" style="color: black;"></td>
     </tr>
     <tr>
      <td width="80px" >&nbsp;NAME:</td>
      <td width="250px" style="color: black;">&nbsp;</td>
      <td width="113px">&nbsp;STUDENT NUMBER:</td>
      <td width="85px" style="color: black;">&nbsp;</td>
     </tr>
     <tr>
      <td width="80px">&nbsp;PROGRAM:</td>
      <td width="250px" style="color: black;">&nbsp;</td>
      <td width="113px">&nbsp;YEAR LEVEL:</td>
      <td width="85px" style="color: black;">&nbsp;</td>
     </tr>
     <tr>
      <td width="80px">&nbsp;MAJOR:</td>
      <td width="250px" style="color:black;">&nbsp;</td>
      <td width="113px">&nbsp;REGISTRATION STATUS:</td>
      <td width="85px" style="color: black;text-transform:capitalize;">&nbsp;</td>
     </tr>
     <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
     </tr>
    </table> '; 
$html.= '<table border="0" cellpadding="0" style="color:#014fb3; font-size:8; border: 0px solid #014fb3;" width="528">
        
        <tr style="background-color:#ffffcc;">
            <th width="60px" style="text-align:left;border-bottom: 0px solid gray;font-weight:bold;">SECTION</th>
            <th width="65px" style="text-align:center;border-bottom: 0px solid gray;font-weight:bold;">COURSE CODE</th>
            <th width="170px" style="text-align:center;border-bottom: 0px solid gray;font-weight:bold;">COURSE DESCRIPTION</th>
            <th width="30px" style="text-align:center;border-bottom: 0px solid gray;font-weight:bold;">UNITS</th>
            <th width="40px" style="text-align:center;border-bottom: 0px solid gray;font-weight:bold;">GRADE</th>
            <th width="70px" style="text-align:center;border-bottom: 0px solid gray;font-weight:bold;">REMARKS</th>
            <th width="93px" style="text-align:center;border-bottom: 0px solid gray;font-weight:bold;">FACULTY</th>
        </tr> ';
        $html.= '<tr>
                    <td height="200px" style="border: 0px solid #014fb3;">
                    </td>
                    <td height="200px" style="border: 0px solid #014fb3;">
                    </td> 
                    <td height="200px" style="border: 0px solid #014fb3;">
                    </td> 
                    <td height="200px" style="border: 0px solid #014fb3;">
                    </td> 
                    <td height="200px" style="border: 0px solid #014fb3;">
                    </td> 
                    <td height="200px" style="border: 0px solid #014fb3;">
                    </td> 
                    <td height="200px" style="border: 0px solid #014fb3;">
                    </td> 
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
                                        if(getEquivalent($record['v3']) != "inc" && getEquivalent($record['v3']) != "0" && $record['intFinalized'] == 1 )                                         {
                                        //$productArray = array();
                                        $product = $record['strUnits'] * getEquivalent($record['v3']); 
                                        $products[] = $product;

                                        //echo $product

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
                                
        
        
                         
        $html.='</table>
                <table border="0" cellpadding="0" style="color:#014fb3; font-size:10; border: 0px solid #014fb3;" width="528px">
        
                    <tr style="background-color:#ffffcc;font-weight:bold;">
                    <td width="80px" height="10px">&nbsp;SUBJECTS: </td>
                    <td width="50px" height="10px" style="color: black;text-align:left;"></td>
                    <td width="150px" height="10px" colspan="2">&nbsp;TOTAL UNITS CREDITED: </td>
                    <td width="65px" height="10px" style="color: black;text-align:center;">&nbsp;&nbsp;</td>
                    <td width="65px" height="10px" style="color: black;text-align:center;"></td>
                    <td width="40px" height="10px">&nbsp;GPA: </td>
                    <td width="78px" height="10px"style="color: black;text-align:center;">&nbsp; </td>
                    </tr>

        </table>
        
        <table border="0" cellpadding="0" style="color:#014fb3; font-size:8; border: 0px solid #014fb3;" width="528px">
           <tr>
            <td colspan ="3" style="text-align:center;color:black; font-size:10;font-weight:bold;">
                GRADING INFORMATION
            </td>
        </tr>
        
        <tr style="border: 0px solid #014fb3;">
            <td width="235" style="border: 0px solid #014fb3;font-weight:bold;"> SCHOLARSHIP GRANT:</td>
            <td width="293" style="border: 0px solid #014fb3;font-weight:bold;">
                GRADE POINT AVERAGE DETAILS
            </td>
        </tr>
        <tr style="border: 0px solid #014fb3;">
            <td width="235" style="text-align:center; color:black; border: 0px solid #014fb3;" > </td>
            <td> &nbsp;</td>
           
        </tr>
        <tr>
            <td width="235" style="border: 0px solid #014fb3;font-weight:bold;"> GRADING SYSTEM:</td>
            <td width="145" style="font-weight:bold;"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;CLASSIFICATION </td>
            <td width="148" style="text-align:center; color:black;font-weight:bold;">GPA RANGE</td>
        </tr>
        <tr>
            <td width="117" style="border-right: 0px solid #014fb3;text-align:center;">1.00</td>
            <td width="118" style="color:black; text-align:center; border-right: 0px solid #014fb3;">&nbsp;&nbsp;98 - 100</td>
            <td width="145"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Dean\'s Lister/Academic Scholars: </td>
            <td width="148" style="text-align:left; color:black;">1.60 above & no grade lower than 2.25 </td>
        </tr>
                    <tr>
                        <td width="117" style="border-right: 0px solid #014fb3;text-align:center;">1.25</td>';
                        
                        
                        $html.= '<td width="118"style="color:black; text-align:center;border-right: 0px solid #014fb3;">95 - 97</td>
                        <td width="145"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Scholars: </td>
                        <td width="148" style="text-align:left; color:black;">not lower than 3.00</td>
                    </tr>
                    <tr>
                        <td width="117" style="border-right: 0px solid #014fb3;text-align:center;">1.50</td>
                        <td width="118" style="color:black; text-align:center; border-right: 0px solid #014fb3;">92 - 94</td>
                        <td width="145"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </td>
                        <td width="148" style="text-align:center; color:black;"></td>                    
                    </tr>
                    <tr>
                        <td width="117" style="border-right: 0px solid #014fb3;text-align:center;">1.75</td>
                            
                        <td width="118" style="color:black; text-align:center;border-right: 0px solid #014fb3;">89 - 91</td>
                        <td></td>
                        <td width="148"></td>
                    </tr>
                    <tr>
                        <td width="117" style="border-right: 0px solid #014fb3;text-align:center;">2.00</td>
                        <td width="118" style="color:black; text-align:center;border-right: 0px solid #014fb3;">86 - 88 </td>
                        <td></td>
                        <td width="148"></td>
                    </tr>
                    <tr>
                        <td width="117" style="border-right: 0px solid #014fb3;text-align:center;">2.25</td>
                        <td width="118" style="color:black; text-align:center; border-right: 0px solid #014fb3;">83 - 85</td>    <td></td>
                        <td width="148"></td>
                    </tr>
                    <tr>
                        <td width="117" style="border-right: 0px solid #014fb3;text-align:center;">2.50</td>
                        <td width="118" style="color:black; text-align:center; border-right: 0px solid #014fb3;">80 - 82</td>
                        <td></td>
                        <td width="148"></td>
                    </tr>
                    <tr>
                        <td width="117" style="border-right: 0px solid #014fb3;text-align:center;">2.75 </td>
                        <td width="118" style="color:black; text-align:center; border-right: 0px solid #014fb3;">77 - 79</td>
                        <td style="border-bottom: 0px solid #014fb3;"></td>
                        <td width="148" style="border-bottom: 0px solid #014fb3;"></td>
                        
                    </tr>
                      <tr>
                        <td width="117" style="border-right: 0px solid #014fb3;text-align:center;">3.00 </td>
                        <td width="118" style="color:black; text-align:center; border-right: 0px solid #014fb3;">75 - 76</td>
                        <td colspan="2" rowspan = "9" align="center" style="border-right: 0px solid #014fb3;">
                          <table border="0px" width="285" style="font-size: 8px;">
                          <tr>
                                    <td style="font-weight:bold;"></td>
                                </tr>
                                <tr>
                                    <td style="font-weight:bold;">POLICY ON GRADES</td>
                                </tr>
                               
                                <tr>
                                    <td style="text-align: left;"></td>
                                    
                                </tr>
                                <tr>
                                    <td align="justify" style="color:black;" width="270">
                                        <ol>
                                            <li align="justify">In case of erroneous input on grades, course code and description, exclusion  of student\'s name, the subject teacher must secure a <b>Notarized Affidavit</b> to be submitted to the Office of the Registrar, Office of the VPAA, and MIS Officer. Alterations or inclusions on the printed copies of ROG will not be allowed and honored.</li>
                                                
                                            <li align="justify">Incomplete Grades must be complied within 2 semesters except for graduating students. Incomplete Grades of Graduating Students must be complied before Midterm Examination of the Second Semester, otherwise they will not be included on the list of candidates for graduation.</li>
                                 
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
                        <td width="117" style="border-right: 0px solid #014fb3;text-align:center;">5.00</td>
                        <td width="118" style="color:black; text-align:center; border-right: 0px solid #014fb3;">Below 75 - Failed</td>
                        
                    </tr>
                      <tr>
                        <td width="117" style="border-right: 0px solid #014fb3;text-align:center;">4.00</td>
                        <td width="118" style="color:black; text-align:center; border-right: 0px solid #014fb3;">Conditional Grade</td>
                        
                    </tr>
                      <tr>
                        <td width="117" style="border-right: 0px solid #014fb3;text-align:center;">0.00</td>
                        <td width="118" style="color:black; text-align:center; border-right: 0px solid #014fb3;">Not Yet Encoded</td>
                        
                    </tr>
                    
                     <tr>
                        <td width="117" style="border-right: 0px solid #014fb3;text-align:center;">INC</td>
                        <td width="118" style="color:black; text-align:center;border-right: 0px solid #014fb3;">Incomplete</td>
                    </tr>
                    <tr>
                        <td width="117" style="border-right: 0px solid #014fb3;text-align:center;">-</td>
                        <td width="118" style="color:black; text-align:center; border-right: 0px solid #014fb3;">Not Yet Submitted</td>
                        
                    </tr>
                      <tr>
                        <td width="117" style="border-right: 0px solid #014fb3;">&nbsp;</td>
                        <td width="118" style="color:black; text-align:right; border-right: 0px solid #014fb3;"></td>
                        
                    </tr>
                    <tr>
                        <td width="117" style="border-right: 0px solid #014fb3;"></td>
                        <td width="118" style="border-right: 0px solid #014fb3;"></td>
                    </tr>
                   <tr>
                        <td width="117" style="border-right: 0px solid #014fb3;">&nbsp;</td>
                        <td width="118" style="color:#014fb3; text-align:center; border-right: 0px solid #014fb3;">
                        </td>
                   </tr>
                    
         
        </table>
        <br />
        <br />
          <table border="0" cellpadding="0" width="528px" style="color:#014fb3; font-size:8;">
           <tr>
                <td width="80"></td>';
                
                
                
                    $html.='<td width="155" tyle="color:black;text-align:center;vertical-align: bottom;" rowspan="2">';
                    //$html.= '<u>' . $user['strFirstname']. '&nbsp; '. $user['strLastname'] . '</u>';
                
                $html.= '</td>
                <td width="80" style="text-align:center;"></td>
                <td width="213" style="text-align:left; font-weight:bold; text-decoration:underline;">  </td>
            </tr>
            
            <tr>
                
                <td width="80" style="text-align:left;font-size:6pt;"> Printed by: </td>';
                
                
                $html.= '<td width="80" style="text-align:center;"> Checked by:</td>
                <td width="213" style="text-align:center; font-weight:bold; text-decoration:underline;"> _______________________ </td>
            </tr>
             <tr>
                <td width="80" style="text-align:left;font-size:6pt;"> Date/Time Printed:</td>
                <td width="155" style="text-align:left;font-size:6pt;color:black;"></td>
                <td width="80"></td>
                <td width="213" style="text-align:center;"> Department Chair</td>
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
$pdf->Output("Blank_ROG_Form". ".pdf", 'I');

?>