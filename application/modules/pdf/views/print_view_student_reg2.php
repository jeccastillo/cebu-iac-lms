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
    $pdf->SetMargins(PDF_MARGIN_LEFT, 10 , PDF_MARGIN_RIGHT);
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
$html = '<table border="0" cellpadding="0" style="color:#333; font-size:10;">
        <tr>
            <td width="100%" align="center" style="text-align:center;vertical-align: middle;"><img src= "https://i.ibb.co/XW1DRVT/iacademy-logo.png"  width="150" height="44"/></td>
        </tr>
        <tr>            
            <td colspan = "3" width="100%" style="text-align: center; vertical-align: middle;">             
             <font style="font-family:Calibri Light; font-size: 16;font-weight: bold;">Information & Communications Technology Academy </font><br />
             </td>
        </tr>
        <tr>
            <td colspan="3" style="font-size:10;"></td>
        </tr>
        <tr>            
            <td colspan = "3" width="100%" style="text-align: center; vertical-align: middle; line-height:100%">             
			 <font style="font-family:Calibri Light; font-size: 10;">Filinvest Cebu Cyberzone Tower 2 Salinas Drive corner W. Geonzon St., Brgy. Apas, Lahug, Cebu City</font><br />
             <font style="font-family:Calibri Light; font-size: 10;">Telephone No: (046) 483-0470 / (046) 483-0672</font><br />
            </td>           
        </tr>
        <tr>
            <td colspan = "3" style="font-weight: bold;text-align:center; font-size:12;">ASSESSMENT/REGISTRATION FORM</td>
        </tr>
        <tr>
            <td colspan = "3" style="text-align:center; color:black; font-size: 10;"></td>
        </tr>
        <tr>
            <td colspan="3" style="font-size:10;">
        </td>
        </tr>
    </table>
     <br />
    <table border="0" cellpadding="0" style="color:#333; font-size:11;" width="528px">     
     <tr>
      <td width="80px" >&nbsp;</td>
      <td width="250px">&nbsp;</td>
      <td width="113px"></td>
      <td width="85px" ></td>
      
     </tr>
     <tr>
      <td width="80px">&nbsp;NAME</td>
      <td width="200px" >:&nbsp;' . strtoupper($student['strLastname']) . ", " . strtoupper($student['strFirstname']) . " " . substr($student['strMiddlename'], 0,1) . ".".'</td>
      <td width="80px">&nbsp;DATE</td>
      <td width="200px" >:&nbsp;'. $registration['dteRegistered']. '</td>      
     </tr>
     <tr>
      <td width="80px" >&nbsp;PROGRAM</td>
      <td width="200px" >:&nbsp;'.$student['strProgramCode'] . '</td>      
      <td width="80px" >&nbsp;STUD NO</td>
      <td width="200px" >:&nbsp;' . $student['strStudentNumber']. '</td>
     </tr>
     <tr>
      <td width="80px" >&nbsp;MAJOR</td>
      <td width="200px" >:&nbsp;' .$student['strMajor'] . '</td>
      <td width="80px" >&nbsp;SY/TERM</td>
      <td width="200px" style="text-transform:capitalize;">:&nbsp;' .$active_sem['strYearStart']."-".$active_sem['strYearEnd'] . ", " . $active_sem['enumSem'].' Term' . '</td>
     </tr>
     <tr>
        <td >&nbsp;</td>
        <td>&nbsp;</td>
        <td >&nbsp;</td>
        <td>&nbsp;</td>
     </tr>
    </table> '; 
$html.= '<table border="0" cellpadding="0" style="color:#333; font-size:10;" width="528" >
   
        <tr>
            <th width="80px" style="text-align:left; font-weight:bold;  border-bottom: 1px solid #333;">SECTION</th>            
            <th width="218px" style="text-align:left; font-weight:bold;  border-bottom: 1px solid #333;">SUBJECT NAME</th>
            <th width="40px" style="text-align:left; font-weight:bold;  border-bottom: 1px solid #333;">UNITS</th>
            <th width="45px" style="text-align:left; font-weight:bold;  border-bottom: 1px solid #333;">DAY</th>
            <th width="100px" style="text-align:left; font-weight:bold;  border-bottom: 1px solid #333;">TIME</th>
            <th width="45px" style="text-align:left; font-weight:bold;  border-bottom: 1px solid #333;">ROOM</th>
        </tr> ';
        $html.= '
                <tr><td colspan="5"> </td> </tr>
                <tr>
                    <td colspan="5" rowspan="24">';

                        $html.= '<table border="0" cellpadding="0" style="color:#014fb3; font-size:10;" font-weight:400; width="528">';
                        $totalUnits = 0;
                        if (empty($records)){
                            $html.='<tr style="color: black; border-bottom: 0px solid gray;">
                                                    <td colspan="7" style="text-align:left;font-size: 10px;">No Data Available</td>
                                                </tr>';
                        }
                        else {
                                foreach($records as $record) {
                                    $units = ($record['strUnits'] == 0)?'('.$record['intLectHours'].')':$record['strUnits'];
                                    $html.='<tr style="color: black;">
                                            <td width="80px"> ' . $record['strSection'].'</td>                                            
                                            <td width="218px" align ="left"> '. $record['strDescription']. '</td>
                                            <td width="40px" align = "left"> '. $units . '</td> ';
                                            $html.= '<td width="45px">';

                                        foreach($record['schedule'] as $sched) {
                                            if(!empty($record['schedule']))
                                                $html.= $sched['strDay'];                    
                                                //$html.= date('g:ia',strtotime($sched['dteStart'])).'  '.date('g:ia',strtotime($sched['dteEnd']))." ".$sched['strDay']." ".$sched['strRoomCode'] . " ";                    
                                        }
                                            $html.= '</td>
                                            <td width="100px">';                                            
                                            if(!empty($record['schedule']))                                                
                                                $html.= date('g:ia',strtotime($record['schedule'][0]['dteStart'])).'  '.date('g:ia',strtotime($record['schedule'][0]['dteEnd']));                                                            
                                            $html.= '</td>                                            
                                            ';
                                            $html.= '<td width="45px">';                                            
                                                if(!empty($record['schedule']))                                                
                                                    $html.= $record['schedule'][0]['strRoomCode'];
                                            $html.= '</td>
                                            </tr>';                                        
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
                <table border="0" cellpadding="0" style="color:#014fb3; font-size:8;" width="528px">
                    <tr>
                        <td colspan="3" style="font-size:10;"></td>
                    </tr>
                    <tr style="background-color:#ffff99; font-weight:bold;">
                        <td width="80px">&nbsp;SUBJECTS: </td>
                        <td width="75px" style="color: black;text-align:left;">' . $noOfSubjs . '</td>
                        <td width="80px">&nbsp;LEC. UNITS: </td>
                        <td width="45px" style="color: black;text-align:center;">'. $totalLec . '</td>
                        <td width="55px">&nbsp;LAB UNITS: </td>
                        <td width="45px" style="color: black;text-align:center;">' . $totalLab . '</td>
                        <td width="70px">&nbsp;TOTAL CREDITS: </td>
                        <td width="78px" style="color: black;text-align:center;">' . $units . '</td>
                    </tr>
                    <tr>
                        <td colspan="3" style="font-size:10;"></td>
                    </tr>

        </table>
        
        <table border="0" cellpadding="0" style="color:#333; font-size:10; " width="528px">        
        <tr>
            <td width="264px" style= "font-size:10; font-weight:bold;">ASSESSMENT SUMMARY</td>
            <td width="264px" style= "font-size:10; font-weight:bold;">MISCELANEOUS DETAIL</td>            
        </tr>
        </table>
        ';
        $html .='
            <table>
                <tr>
                      <td width="88px"></td>
                      <td width="88px" style="border-bottom:1px solid #333;">FULL PAYMENT</td>
                      <td width="88px" style="border-bottom:1px solid #333;">INSTALLMENT</td>
                </tr>
                <tr>
                      <td width="88px">Tuition</td>
                      <td width="88px"></td>
                      <td width="88px"></td>
                </tr>
                <tr>
                      <td width="88px">Laboratory</td>
                      <td width="88px"></td>
                      <td width="88px"></td>
                </tr>
                <tr>
                      <td width="88px">Miscellaneous</td>
                      <td width="88px"></td>
                      <td width="88px"></td>
                </tr>
                <tr>
                      <td width="88px">New Student</td>
                      <td width="88px"></td>
                      <td width="88px"></td>
                </tr>
            </table>
        ';


    //     $html .='<tr>        
    //     <div class="box-body">
    //         <div class="row">
    //             <div class="col-sm-6">Tuition:</div>
    //             <div class="col-sm-6 text-green">'.$tuition['tuition'].'</div>
    //         </div>
    //         <hr />
            
    //         <div class="row">
    //             <div class="col-sm-6">Miscellaneous:</div>
    //             <div class="col-sm-6 text-green"></div>
    //         </div>';
        
    //         foreach($tuition['misc_list'] as $key=>$val){
            
    //             $ret .='<div class="row">
    //                         <div class="col-sm-6" style="text-align:right;">'.$key.'</div>
    //                         <div class="col-sm-6">'.$val.'</div>
    //                     </div>';                
    //         }
            
    //         $ret .= '                
    //         <div class="row">
    //             <div class="col-sm-6" style="text-align:right;">Total:</div>
    //             <div class="col-sm-6 text-green">'.$tuition['misc'].'</div>
    //         </div>';

    //         if($tuition['nsf']!= 0){
    //             $ret .= '                
    //             <div class="row">
    //                 <div class="col-sm-6">MISC - NEW STUDENT: </div>
    //                 <div class="col-sm-6 text-green">'.$tuition['nsf'].'</div>
    //             </div>
    //             <hr />
    //             ';
    //         }
            
    //         $ret .= '                
    //         <div class="row">
    //             <div class="col-sm-6">Laboratory Fee:</div>
    //             <div class="col-sm-6 text-green"></div>
    //         </div>
    //         <hr />
    //         ';
            
            
    //         foreach($tuition['lab_list'] as $key=>$val){                
    //             $ret .='<div class="row">
    //                         <div class="col-sm-6" style="text-align:right;">'.$key.'</div>
    //                         <div class="col-sm-6">'.$val.'</div>
    //                     </div>';                
    //         }

    //         $ret .= '
    //         <div class="row">
    //             <div class="col-sm-6" style="text-align:right;">Total:</div>
    //             <div class="col-sm-6 text-green">'.$tuition['lab'].'</div>
    //         </div>
    //         <hr />';
    //         if($tuition['thesis_fee']!= 0){
    //             $ret .= '                
    //                 <div class="row">
    //                     <div class="col-sm-6">THESIS FEE: </div>
    //                     <div class="col-sm-6 text-green">'.$tuition['thesis_fee'].'</div>
    //                 </div>
    //                 <hr />
    //                 ';
    //         }    
    //         if($tuition['internship_fee']!= 0){
    //             $ret .= '                
    //             <div class="row">
    //                 <div class="col-sm-6">Internship Fees:</div>
    //                 <div class="col-sm-6 text-green"></div>
    //             </div>
    //             <hr />
    //             ';
                
                
    //             foreach($tuition['internship_fee_list'] as $key=>$val){                
    //                 $ret .='<div class="row">
    //                             <div class="col-sm-6" style="text-align:right;">'.$key.'</div>
    //                             <div class="col-sm-6">'.$val.'</div>
    //                         </div>';                
    //             }

    //             $ret .= '
    //             <div class="row">
    //                 <div class="col-sm-6" style="text-align:right;">Total:</div>
    //                 <div class="col-sm-6 text-green">'.$tuition['internship_fee'].'</div>
    //             </div>
    //             <hr />';
    //         }
    //         if($tuition['new_student']!= 0){
    //             $ret .= '                
    //             <div class="row">
    //                 <div class="col-sm-6">New Student Fees:</div>
    //                 <div class="col-sm-6 text-green"></div>
    //             </div>
    //             <hr />
    //             ';
                
                
    //             foreach($tuition['new_student_list'] as $key=>$val){                
    //                 $ret .='<div class="row">
    //                             <div class="col-sm-6" style="text-align:right;">'.$key.'</div>
    //                             <div class="col-sm-6">'.$val.'</div>
    //                         </div>';                
    //             }

    //             $ret .= '
    //             <div class="row">
    //                 <div class="col-sm-6" style="text-align:right;">Total:</div>
    //                 <div class="col-sm-6 text-green">'.$tuition['new_student'].'</div>
    //             </div>
    //             <hr />';
    //         }

    //         $ret .= '           
    //         <div class="row">
    //             <div class="col-sm-6">Total:</div>
    //             <div class="col-sm-6 text-green">'.$tuition['total'].'</div>
    //         </div>
    //     </div>
    // </div>';

     
    // <tr><td width="148" style="text-align:center; color:black;">' . number_format($payment_division, 2, '.' ,',') . ' </td></tr>
$html = utf8_encode($html);
$pdf->writeHTML($html);

//$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

// ---------------------------------------------------------

// Close and output PDF document
// This method has several options, check the source code documentation for more information.
$pdf->Output($student['strLastname'] . ", " . $student['strFirstname'] . ', ' . substr($student['strMiddlename'], 0,1). ".-". $student['strProgramCode'] . ".pdf", 'I');


?>