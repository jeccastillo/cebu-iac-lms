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
    $pdf->SetMargins(10, 5 , 10);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    $pdf->SetFont('helvetica','',10);
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
     <table border="1" cell-padding="0" class="table table-bordered table-striped">
     <tr>
         <td style="line-height:10px;" colspan=5></td>         
     </tr> 
     <tr>
         <th style="width:15%;font-size:9px;text-align:center;"><b>Course Code</b></th>
         <th style="width:40%;font-size:9px;text-align:center;"><b>Descriptive Title</b></th>
         <th style="width:15%;font-size:9px;text-align:center;"><b>Units</b></th>         
         <th style="width:15%;font-size:9px;text-align:center;">
            <table style="margin:0;" border="1">
                <tr><td colspan="2">Period</td></tr>
                <tr><td>Midterm</td><td>Final</td></tr>
            </table>
         </th>
         <th style="width:15%;font-size:9px;text-align:center;"><b>Units Earned</b></th>
     </tr>
     <tr>
         <td style="line-height:5px;" colspan=5></td>         
     </tr>     
     ';
         
    foreach($records as $item){                
        
        if($period == "final")
            $grade = ($item['intFinalized'] >= 2)?$item['v3']:'NGS';
        else
            $grade = ($item['intFinalized'] >= 1)?$item['v2']:'NGS';
        
        $units_earned = ($item['strRemarks'] == "Passed" && $item['intFinalized'] >= 2 && $period == "final")?number_format($item['strUnits'],1):0;
        if($item['include_gwa'])
            $units = number_format($item['strUnits'],1);
        else{
            $units = "(".number_format($item['strUnits'],1).")";
            $units_earned = "(".$units_earned.")";
        }
        
        $html .= '            
            <tr>
                <td style="font-size:8px;">'.$item['strCode'].'</td>
                <td style="font-size:8px;">'.$item['strDescription'].'</td>
                <td style="font-size:8px;text-align:center;">'.$units.'</td>
                <td style="font-size:8px;text-align:center;">'.$grade.'</td>
                <td style="font-size:8px;text-align:center;">'.$units_earned.'</td>
            </tr>            
            ';
    }
  
            
    $html .='
            <tr>
                <td style="line-height:15px;" colspan=5></td>         
            </tr>
            
            <tr style="font-size:9px;">
                <th colspan="3" style="text-align:right;"><b>General Weighted Average(GWA)</b></th> 
                <th style="text-align:center;"><b>'.$other_data['gwa'].'</b></th>
            </tr>
            <tr style="font-size:9px;">
                <th colspan="3" style="text-align:right;"><b>Total Units Earned</b></th>
                <th style="text-align:center;"><b>'.number_format($other_data['total_units'],1).'</b></th>                
            </tr>
            <tr>
                <td style="line-height:2px;border-top:1px solid #333"></td>         
                <td style="line-height:2px;border-top:1px solid #333"></td>         
                <td style="line-height:2px;border-top:1px solid #333"></td>         
                <td style="line-height:2px;border-top:1px solid #333"></td>         
                <td style="line-height:2px;border-top:1px solid #333"></td>         
            </tr>
            </table>
            <font style="font-weight:bold;">Grading System</font>      
            <p style="font-size:8px;">1.00 (98-100) Excelent; 1.25 (95-97); 1.50 (92-94) Very Good; 1.75 (89-91); 2.00 (86-88); 2.25 (83-85);2.50 (80-82) Satisfactory; 2.75 (77-79) Fair; 3.00 (75-76); 5.00 (Below 75) Failed; OD (Officially Dropped); UD (Unofficially Dropped); FA (Failure due to Absences); IP (In Progress) for internship only; P (Passed); F (Failed); OW (Officially Withdrawn); UW (Unofficially Withdrawn); NGS (No Grade Submitted)			
            </p>      
            <div style="text-align:center;font-weight:bold;margin-top:10px;font-size:9px;">
                ___________________________________________________<br />Registrar
            </div>
            <p style="font-size:8px;margin-top:10px;">GENERATED BY:'.$user['strFirstname']." ".$user['strLastname'].'<br />                 
                RUNDATE&TIME:'.date('Y-m-d h:i a').'
            </p>             
            ';

$pdf->writeHTML($html, true, false, true, false, '');

//$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

// ---------------------------------------------------------

// Close and output PDF document
// This method has several options, check the source code documentation for more information.
$pdf->Output("grade_slip".date("Ymdhis").".pdf", 'I');


?>