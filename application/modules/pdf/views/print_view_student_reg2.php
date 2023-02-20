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
    $pdf->SetMargins(PDF_MARGIN_LEFT, 6 , PDF_MARGIN_RIGHT);
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
$html = '<table border="0" cellspacing="0" cellpadding="0" style="color:#333; font-size:9;">
        <tr>
            <td width="100%" align="center" style="text-align:center;vertical-align: middle;"><img src= "https://i.ibb.co/XW1DRVT/iacademy-logo.png"  width="100" height="29"/></td>
        </tr>
        <tr>            
            <td colspan = "3" width="100%" style="text-align: center;">             
                
             </td>
        </tr>        
        <tr>            
            <td colspan = "3" width="100%" style="text-align: center;line-height:1">             
                <font style="font-family:Calibri Light; font-size: 14;font-weight: bold;">Information & Communications Technology Academy </font><br /><br />
			    <font style="font-family:Calibri Light; font-size: 10;">Filinvest Cebu Cyberzone Tower 2 Salinas Drive corner W. Geonzon St., Brgy. Apas, Lahug, Cebu City</font><br />             
            </td>           
        </tr>
        <tr>
            <td colspan = "3" style="font-weight: bold;text-align:center; font-size:11; border-bottom:1px solid #333;">ASSESSMENT/REGISTRATION FORM</td>
        </tr>    
    </table>
     <br />
    <table border="0" cellpadding="0" style="color:#333; font-size:8;" width="528px">     
     <tr>
      <td width="80px" >&nbsp;</td>
      <td width="250px">&nbsp;</td>
      <td width="113px"></td>
      <td width="85px" ></td>
      
     </tr>
     <tr>
      <td width="80px">&nbsp;NAME</td>
      <td width="200px" >:&nbsp;' . strtoupper($student['strLastname']) . ", " . strtoupper($student['strFirstname']) . " " . strtoupper($student['strMiddlename']) .'</td>
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
$html.= '<table border="0" cellpadding="0" cellspacing="0" style="color:#333; font-size:9;" width="528" >
   
        <tr>
            <td width="60px" style="text-align:left; font-weight:bold;">SECTION</td>            
            <td width="198px" style="text-align:left; font-weight:bold;  border-bottom: 1px solid #333;">SUBJECT NAME</td>
            <td width="40px" style="text-align:left; font-weight:bold;  border-bottom: 1px solid #333;">LAB</td>
            <td width="40px" style="text-align:left; font-weight:bold;  border-bottom: 1px solid #333;">UNITS</td>
            <td width="45px" style="text-align:left; font-weight:bold;  border-bottom: 1px solid #333;">DAY</td>
            <td width="100px" style="text-align:left; font-weight:bold;  border-bottom: 1px solid #333;">TIME</td>
            <td width="45px" style="text-align:left; font-weight:bold;  border-bottom: 1px solid #333;">ROOM</td>
        </tr> ';        
                
                        $totalUnits = 0;
                        if (empty($records)){
                            $html.='<tr style="color: black; border-bottom: 0px solid gray;">
                                                    <td colspan="7" style="text-align:left;font-size: 10px;">No Data Available</td>
                                                </tr>';
                        }
                        else {
                                foreach($records as $record) {
                                    $units = ($record['strUnits'] == 0)?'('.$record['intLectHours'].')':$record['strUnits'];
                                    $html.='<tr style="color: #333;">
                                            <td width="60px"> ' . $record['strSection'].'</td>                                            
                                            <td width="198px" align ="left"> '. $record['strDescription']. '</td>
                                            <td width="40px" align = "left"> '. $record['intLab'] . '</td> 
                                            <td width="40px" align = "left"> '. $units . '</td> ';
                                            $html.= '<td width="45px">';

                                            foreach($record['schedule'] as $sched) {
                                                if(isset($sched['strDay']))
                                                    $html.= $sched['strDayAbvr'];                    
                                                    //$html.= date('g:ia',strtotime($sched['dteStart'])).'  '.date('g:ia',strtotime($sched['dteEnd']))." ".$sched['strDay']." ".$sched['strRoomCode'] . " ";                    
                                            }
                                            $html.= '</td>
                                            <td width="100px">';                                            
                                                if(isset($record['schedule'][0]['strDay']))                                                
                                                    $html.= date('g:ia',strtotime($record['schedule'][0]['dteStart'])).'  '.date('g:ia',strtotime($record['schedule'][0]['dteEnd']));                                                            
                                            $html.= '</td>                                            
                                            ';
                                            $html.= '<td width="45px">';                                            
                                                if(isset($record['schedule'][0]['strDay']))
                                                    $html.= $record['schedule'][0]['strRoomCode'];
                                            $html.= '</td>
                                            </tr>';                                        
                                }
                        }

            
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

                         
        $html.='
        <table border="0" cellpadding="0" style="color:#333; font-size:9; " width="528px">        
            <tr>
                <td colspan="2" style= "font-size:9; line-height:1.5; border-top:1px solid #333;"></td>                
            </tr>
        </table>
        
        <table border="0" cellpadding="0" style="color:#333; font-size:9; " width="528px">        
            <tr>
                <td width="264px" style= "font-size:9; font-weight:bold;">ASSESSMENT SUMMARY</td>
                <td width="264px" style= "font-size:9; font-weight:bold;">MISCELANEOUS DETAIL</td>            
            </tr>
        </table>
        ';
        $html .='
            <table cellpadding="0" style="color:#333; text-align:left; font-size:9;" width="528px">                                
                <tr>
                    <td>
                        <table cellspacing="2px" cellpadding="0"  width="258px" style="color:#333; font-size:9;">
                            <tr>
                                <td width="78px"></td>
                                <td width="78px" style="text-decoration:underline;">FULL PAYMENT</td>
                                <td width="78px" style="text-decoration:underline;">INSTALLMENT</td>
                            </tr>
                            <tr>
                                <td style="text-align:left;">Tuition Fee</td>
                                <td style="text-align:center;">'.number_format($tuition['tuition'], 2, '.' ,',') .'</td>
                                <td style="text-align:center;">'.number_format($tuition['tuition_installment'], 2, '.' ,',') .'</td>
                            </tr>
                            <tr>
                                <td>Laboratory</td>
                                <td style="text-align:center;">'.number_format($tuition['lab'], 2, '.' ,',') .'</td>
                                <td style="text-align:center;">'.number_format($tuition['lab_installment'], 2, '.' ,',') .'</td>
                            </tr>
                            <tr>
                                <td>Miscellaneous</td>
                                <td style="text-align:center;">'.number_format($tuition['misc'], 2, '.' ,',') .'</td>
                                <td style="text-align:center;">'.number_format($tuition['misc'], 2, '.' ,',') .'</td>
                            </tr>
                            <tr>
                                <td>New Student</td>
                                <td style="text-align:center;">'.number_format($tuition['new_student'], 2, '.' ,',') .'</td>
                                <td style="text-align:center;">'.number_format($tuition['new_student'], 2, '.' ,',') .'</td>
                            </tr>
                            <tr>
                                <td></td>
                                <td style="font-weight:bold; border-bottom: 1px solid #555; text-align:center;">'.number_format($tuition['total'], 2, '.' ,',').'</td>
                                <td style="font-weight:bold; border-bottom: 1px solid #555; text-align:center;">'.number_format($tuition['total_installment'], 2, '.' ,',').'</td>
                            </tr>
                        </table>
                        <table cellpadding="0"  width="258px" style="color:#333; font-size:8;">
                            <tr>
                                <td colspan="2" style="font-size:8; line-height:1; color:#fff;">Space</td>
                            </tr>
                            <tr>
                                <td width="140px">DOWN PAYMENT</td>
                                <td width="80px" style="text-align:right;">'.number_format($tuition['down_payment'], 2, '.' ,',').'</td>
                            </tr>';
                            for($i=0;$i<5;$i++){
                                $html .= '
                                <tr>
                                    <td width="140px">'.switch_num($i + 1).' INSTALLMENT</td>
                                    <td width="80px" style="text-align:right;">'.number_format($tuition['installment_fee'], 2, '.' ,',').'</td>
                                </tr>';                    
                            }

                    $html .= 
                        '<tr>
                            <td width="140px"></td>
                            <td width="80px" style="text-align:right; font-weight:bold; border-bottom:1px solid #333;">'.number_format($tuition['total_installment'], 2, '.' ,',').'</td>
                        </tr>
                        </table>
                    </td>
                    <td>                                
                        <table  width="258px"  style="color:#333; font-size:8; ">';
                        if($tuition['misc'] != 0){
                        foreach($tuition['misc_list'] as $key=>$val){
        
                            $html .= '<tr>
                                        <td width="159px">'.$key.'</td>
                                        <td width="99px" style="text-align:right;">'.number_format($val, 2, '.' ,',').'</td>
                                    </tr>';                
                        }
                        $html.=' 
                            <tr>
                                <td>Total</td>
                                <td style="border-bottom: 1px solid #555; text-align:right;">'.number_format($tuition['misc'], 2, '.' ,',').'</td>                
                            </tr>';
                    }
                    $html.='                        
                    </table>
                    </td>
                </tr>
            </table>
            <table border="0" cellpadding="0" style="color:#333; font-size:9; " width="528px">
                <tr>
                    <td colspan="2" style="font-size:9; line-height:2; color:#fff;">Space</td>
                </tr>
            </table>
            <table border="0" cellpadding="0" style="color:#333; font-size:9; " width="528px">        
                <tr>
                    <td width="264px" style= "font-size:9;">Official Receipt Number/date______________________</td>
                    <td width="264px" style= "font-size:9;"></td>            
                </tr>
            </table>
            <table border="0" cellpadding="0" style="color:#333; font-size:9; " width="528px">        
                <tr>
                    <td width="264px" style= "font-size:9;">Enrollment Confirmed by:</td>
                    <td width="264px" style= "font-size:9;"></td>            
                </tr>
            </table>
            <table border="0" cellpadding="0" style="color:#333; font-size:9; " width="528px">
                <tr>
                    <td colspan="2" style="font-size:9; line-height:2; color:#fff;">Space</td>
                </tr>
            </table>
            <table border="0" cellspacing="5px" cellpadding="0" style="color:#333; font-size:9; " width="500px">        
                <tr>
                    <td width="230px" style= "font-size:9; text-align:center; border-bottom:1px solid #333;">&nbsp;</td>
                    <td width="30px" style= "font-size:9; text-align:center;">&nbsp;</td>
                    <td width="230px" style= "font-size:9; text-align:center; border-bottom:1px solid #333;">&nbsp;</td>            
                </tr>                   
                <tr>
                    <td style= "font-size:9; text-align:center">Authorized Signatory</td>
                    <td style= "font-size:9; text-align:center;">&nbsp;</td>
                    <td style= "font-size:9; text-align:center">Registrar</td>            
                </tr>
            </table>
            <table border="0" cellpadding="0" style="color:#333; font-size:9; " width="528px">
                <tr>
                    <td colspan="2" style="font-size:9; line-height:1; color:#fff;">Space</td>
                </tr>
            </table>
            <table border="0" cellpadding="0" style="color:#333; font-size:9; " width="528px">        
                <tr>
                    <td width="264px" style= "font-size:9;">Note: Class schedule is subject to change</td>
                    <td width="264px" style= "font-size:9;">Generated: Datetime and Employee Here</td>            
                </tr>
            </table>
            <table border="0" cellpadding="0" style="color:#333; font-size:9; " width="528px">
                <tr>
                    <td colspan="2" style="font-size:9; line-height:1; color:#fff;">Space</td>
                </tr>
            </table>
            <table border="0" cellpadding="0" style="color:#333; font-size:9; " width="500px">
                <tr>
                    <td colspan="2" style="font-size:8; color:#555;">I shall abide by all existing rules and regulations of the School and those that may be promulgated from time to time.
                    I understand that the school has to collect my personal data and I allow the school to process all my information and all
                    purposes related to this.</td>
                </tr>
            </table> 
            <table border="0" cellpadding="0" style="color:#333; font-size:9; " width="528px">
                <tr>
                    <td colspan="2" style="font-size:9; line-height:1; color:#fff;">Space</td>
                </tr>
            </table>
            <table border="0" cellspacing="5px" cellpadding="0" style="color:#333; font-size:9; " width="500px">        
                <tr>
                    <td width="230px" style= "font-size:9; text-align:center;">&nbsp;</td>
                    <td width="30px" style= "font-size:9; text-align:center;">&nbsp;</td>
                    <td width="230px" style= "font-size:9; text-align:center; border-bottom:1px solid #333;">&nbsp;</td>            
                </tr>                   
                <tr>
                    <td style= "font-size:9; text-align:center">&nbsp;</td>
                    <td style= "font-size:9; text-align:center;">&nbsp;</td>
                    <td style= "font-size:9; text-align:center">Student Signature/Date</td>            
                </tr>
            </table>       
            <table border="0" cellpadding="0" style="color:#333; font-size:9; " width="528px">
                <tr>
                    <td colspan="2" style="font-size:9; line-height:1; color:#fff;">Space</td>
                </tr>
            </table>               
                          
        ';

    //     $html .=' <table border="0" cellspacing="5px" cellpadding="0" style="color:#333; font-size:7; " width="528px">        
    //     <tr>
    //         <td>Policy on School Charges and Refund of Fees<br /><br />
    //             Officially Enrolled Students who withdraw their enrollment before the official start of classes shall be charged a Withdrawal Fee of two thousand
    //             five hundred pesos (PhP 2,500.00).<br /><br />
    //             Officially Enrolled Students who withdraw their enrollment after the official start of classes, and have already paid the pertinent tuition and other
    //             school fees in full or for any length longer than one month (regardless of whether or not he has actually attended classes) shall be charged the
    //             appropriate retention fee as stipulated in CHED Manual of Regulations for Private Higher Education (MORPHE) of 2009, as follows:  
    //             <ul>
    //                 <li>Within the first week of classes - twenty-five percent (25%) of the total school fees.</li>
    //                 <li>Within the second week of classes - fifty percent (50%) of the total school fees.</li>
    //                 <li>Beyond the second week of classes - one hundred percent (100%) of the total school fees.</li>
    //             </ul><br />
    //             One-time penalty for the late enrollment (PhP 500.00) shall be charged after the first day of official start of classes per term.
    //         </td>
    //     </tr>                   
        
    // </table> ';

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