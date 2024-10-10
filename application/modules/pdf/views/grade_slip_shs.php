<?php

    tcpdf();
    // create new PDF document
    //$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    //$pdf = new TCPDF("P", PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf = new TCPDF("L", "mm", array(210,146), true, 'UTF-8', false);

    // set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetTitle("Enrollment Summary");
    
    // set margins
    $pdf->SetMargins(15, 35 , 15);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    $pdf->SetFont('dejavusans','',10);
    //$pdf->SetAutoPageBreak(TRUE, 6);
    
   //font setting
    //$pdf->SetFont('calibril_0', '', 10, '', 'false');
    
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    // Add a page
    // This method has several options, check the source code documentation for more information.
    $pdf->AddPage('P', 'LEGAL');            

    $term_type = ($active_sem['term_label'] == "Sem")?"Semester":"Trimester";
    $cm = ($campus == "Cebu")?"iACADEMY Cebu":"iACADEMY";
    $seal = ($campus == "Cebu")?"https://i.ibb.co/9hgbYNB/seal.png":"https://i.ibb.co/kcYVsS7/i-ACADEMY-Seal-Makati.png";
    
    // Set some content to print
    // $html = '<table border="0" cellspacing="0" cellpadding="1" style="color:#333; font-size:9;">
    //             <tr>                             
    //                 <td rowspan="3" width="20%">
    //                     <img width="60px;" src="'.$seal.'" alt="seal" border="0">
    //                 </td>
    //                 <td width="80%">             
    //                     <font style="font-family:Calibri Light; font-size: 9;font-weight: bold;">Information & Communications Technology, Inc.</font>
    //                 </td>
    //             </tr>
    //             <tr>                                                 
    //                 <td width="80%">             
    //                     <font style="font-family:Calibri Light; font-size: 9;font-weight: bold;">'.$cm.'</font>
    //                 </td>
    //             </tr>
    //             <tr>                                           
    //                 <td>             
    //                     <font style="font-family:Calibri Light; font-size: 9;font-weight: bold;">'.ucfirst($period).' Grade SY '.$active_sem['strYearStart'].'-'.$active_sem['strYearEnd'].' '.$term_type.' '.switch_num_rev($active_sem['enumSem']).'</font>
    //                 </td>
    //             </tr> 
    //             <tr>
    //                 <td style="line-height:10px;" colspan=5></td>         
    //             </tr>        
    //         </table>
    //        ';
    $html = '<table border="1" cellspacing="0" cellpadding="1" style="color:#333; font-size:10;">
                <tr>                            
                    <td width="15%">             
                        <font style="font-size: 9;font-weight:bold">Name:</font>
                    </td>
                    <td width="35%">             
                        <font style="font-size: 9;">'.strtoupper($student['strLastname'].' '.$student['strFirstname'].' '.$student['strMiddlename']).'</font>
                    </td>
                    <td width="15%">             
                        <font style="font-size: 9;font-weight:bold">ID No:</font>
                    </td>
                    <td width="35%">             
                        <font style="font-size: 9;">'.preg_replace("/[^a-zA-Z0-9]+/", "", $student['strStudentNumber']).'</font>
                    </td>
                </tr>
                <tr>                            
                    <td width="15%">             
                        <font style="font-size: 9;font-weight:bold">Grade & Sec:</font>
                    </td>
                    <td width="35%">             
                        <font style="font-size: 9;">'.$grade_level.' '.$registration['block_name'].'</font>
                    </td>
                    <td width="15%">             
                        <font style="font-size: 9;font-weight:bold">LRN:</font>
                    </td>
                    <td width="35%">             
                        <font style="font-size: 9;">'.$student['strLRN'].'</font>
                    </td>
                </tr>
                <tr>                            
                    <td width="15%">             
                        <font style="font-size: 9;font-weight:bold">Adviser:</font>
                    </td>
                    <td width="35%">             
                        <font style="font-size: 9;">'.$adviser_name.'</font>
                    </td>
                    <td width="15%">             
                        <font style="font-size: 9;font-weight:bold">SY & Sem:</font>
                    </td>
                    <td width="35%">             
                        <font style="font-size: 9;">'.$active_sem['strYearStart']."-".$active_sem['strYearEnd']." ".$active_sem['enumSem']." ".$active_sem['term_label'].'</font>
                    </td>
                </tr>
                <tr>                            
                    <td width="15%">             
                        <font style="font-size: 9;font-weight:bold">Track/Strand:</font>
                    </td>
                    <td width="35%">             
                        <font style="font-size: 9;">'.trim($student['strProgramDescription']).'</font>
                    </td>
                    <td width="15%">             
                        <font style="font-size: 9;font-weight:bold">Grading Period:</font>
                    </td>
                    <td width="35%">             
                        <font style="font-size: 9;">'.$period.'</font>
                    </td>
                </tr>                   
           </table>
          ';
    



$html .= '       
     <br />
     <div style="text-align:center;font-weight:bold;">REPORT ON LEARNING PROGRESS AND ACHIEVEMENT</div>
     <table cellpadding="1">     
     <tr>         
         <th rowspan="2" style="width:55%;font-size:9px;text-align:center;border:1px solid #333;"><b>Subject</b></th>         
         <th colspan="2" style="width:20%;font-size:9px;text-align:center;border:1px solid #333;"><b>Period</b></th>
         <th rowspan="2" style="width:10%;font-size:9px;text-align:center;border:1px solid #333;"><b>Semester<br />Final Grade</b></th>
         <th rowspan="2" style="width:15%;font-size:9px;text-align:center;border:1px solid #333;"><b>Remarks</b></th>
     </tr>
     <tr style="text-align:center;">
        <th style="border-right:1px solid #333;border-bottom:1px solid #333;"><b>Midterm</b></th>
        <th style="border-bottom:1px solid #333;"><b>Finals</b></th>
     </tr>  
     ';
         
    foreach($records as $item){                
        
        
        $grade_final = ($item['intFinalized'] >= 2)?$item['v3']:'NGS';        
        $grade_midterm = ($item['intFinalized'] >= 1)?$item['v2']:'NGS';
        
        $units_earned = ($item['strRemarks'] == "Passed" && $item['intFinalized'] >= 2 && $period == "final")?number_format($item['strUnits'],1):0;
        if($item['include_gwa'])
            $units = number_format($item['strUnits'],1);
        else{
            $units = "(".number_format($item['strUnits'],1).")";
            $units_earned = "(".$units_earned.")";
        }
        
        $html .= '            
            <tr>                
                <td style="font-size:9px;border-left:1px solid #333;">'.$item['strDescription'].'</td>                
                <td style="font-size:9px;border-left:1px solid #333;text-align:center;">'.$grade_midterm.'</td>
                <td style="font-size:9px;border-left:1px solid #333;text-align:center;">'.$grade_final.'</td>                
                <td style="font-size:9px;border-left:1px solid #333;text-align:center;"></td>
                <td style="font-size:9px;border-right:1px solid #333;border-left:1px solid #333;text-align:center;">'.$item['strRemarks'].'</td>
            </tr>                       
            ';
    }
  
            
    $html .='
            <tr>
                <td colspan="3" style="border-top:1px solid #333;border-left:1px solid #333;">General Average for the Semester</td>
                <td style="text-align:center;border-top:1px solid #333;border-left:1px solid #333;">'.$other_data['gwa'].'</td>
                <td style="border-top:1px solid #333;border-left:1px solid #333;border-right:1px solid #333;"></td>
            </tr> 
            <tr>
                <td style="line-height:2px;border-top:1px solid #333"></td>         
                <td style="line-height:2px;border-top:1px solid #333"></td>         
                <td style="line-height:2px;border-top:1px solid #333"></td>         
                <td style="line-height:2px;border-top:1px solid #333"></td>         
                <td style="line-height:2px;border-top:1px solid #333"></td>         
            </tr>
            </table>            
            <div style="line-height:20px"></div>
            <table>
                <tr style="font-size:10;">
                    <td style="width:25%;"></td>
                    <td style="width:50%;text-align:center;font-size:9px;border-bottom:1px solid #333;">
                                '.$adviser_name.' 
                    </td>
                    <td style="width:25%;"></td>
                </tr>
            </table>
            <div style="text-align:center;">
                Class Adviser
            </div>               
            <div style="line-height:20px"></div>         
            <div style="text-align:center;font-weight:bold;">REPORT ON LEARNING PROGRESS AND ACHIEVEMENT</div>
            ';

$pdf->writeHTML($html, true, false, true, false, '');

$rotated = "<table><tr><td></td>";
    foreach($term_months as $month){
        $rotated.="<td>".$month['month']."</td>";
    }
$rotated .="</tr></table>";
$pdf->writeHTML($rotated, true, false, true, false, '');
// $pdf->StartTransform();
// $pdf->Rotate(90);
// $pdf->writeHTML($rotated, false);
// $pdf->StopTransform();


//$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

// ---------------------------------------------------------

// Close and output PDF document
// This method has several options, check the source code documentation for more information.
$pdf->Output("grade_slip".date("Ymdhis").".pdf", 'I');

/*
<p style="font-size:8px;margin-top:10px;">GENERATED BY:'.$user['strFirstname']." ".$user['strLastname'].'<br />                 
    RUNDATE&TIME:'.date('Y-m-d h:i a').'
</p> 
*/
?>