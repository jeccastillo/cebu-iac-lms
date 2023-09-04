<?php

    tcpdf();
    // create new PDF document
    //$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    //$pdf = new TCPDF("P", PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetTitle("Reservation Summary");
    
    // set margins
    $pdf->SetMargins(10, 20 , 10);
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

    
    
    // Set some content to print
    $html = '<table border="0" cellspacing="0" cellpadding="1" style="color:#333; font-size:9;">
            <tr>                            
                <td width="100%" style="text-align: center; border-bottom:1px solid #333">             
                    <font style="font-family:Calibri Light; font-size: 11;font-weight: bold;">Reservation Summary for '.$sem['enumSem'].' Term SY'.$sem['strYearStart'].'-'.$sem['strYearEnd'].'</font>
                </td>
            </tr>        
            </table>
           ';
    



$html .= '       
     <br />
     <table v-if="enrolled" class="table table-bordered table-striped">
     <tr>
         <th style="width:50%;font-size:9px;">Program</th>
         <th style="width:10%;font-size:9px;">Freshman</th>
         <th style="width:10%;font-size:9px;">Transferee</th>
         <th style="width:10%;font-size:9px;">Foreign</th>
         <th style="width:10%;font-size:9px;">SD</th>
         <th style="width:10%;font-size:9px;">Total</th>
     </tr>
     <tr style="line-height:10px;">
        <th colspan="6"></th>
     </tr>
     ';
     
     
    foreach($reserved['reserved'] as $item){                    
        $html .= '            
            <tr>
                <td style="font-size:8px;">'.trim($item[0]->program).'</td>';
                foreach($item as $type){     
                    if($type->student_type == "freshman"){            
                        
                        $html .= '                                
                        <td style="font-size:8px;">
                            '.$type->reserved_count.'
                        </td>';                                                                                
                    }
                    if(!$reserved['r_fresh'][$item[0]->type_id]){
                        $html .= '                                
                            <td style="font-size:8px;">
                                0
                            </td>';
                    }
                    if($type->student_type == "transferee"){            
                        
                        $html .= '                                
                        <td style="font-size:8px;">
                            '.$type->reserved_count.'
                        </td>';                                                                                
                    }
                    if(!$reserved['r_trans'][$item[0]->type_id]){
                        $html .= '                                
                            <td style="font-size:8px;">
                                0
                            </td>';
                    }
                    if($type->student_type == "foreign"){            
                        
                        $html .= '                                
                        <td style="font-size:8px;">
                            '.$type->reserved_count.'
                        </td>';                                                                                
                    }
                    if(!$reserved['r_foreign'][$item[0]->type_id]){
                        $html .= '                                
                            <td style="font-size:8px;">
                                0
                            </td>';
                    }
                    if($type->student_type == "second degree"){            
                        
                        $html .= '                                
                        <td style="font-size:8px;">
                            '.$type->reserved_count.'
                        </td>';                                                                                
                    }
                    if(!$reserved['r_sd'][$item[0]->type_id]){
                        $html .= '                                
                            <td style="font-size:8px;">
                                0
                            </td>';
                    }

                    $html .= '                                
                    <td style="font-size:8px;">
                        '.$reserved['totals'][$item[0]->type_id].'
                    </td>';
                    
                }
        $html .= '                
            </tr>
            <tr style="line-height:5px;">
                <th colspan="6"></th>
            </tr>
            ';
    }
$html .= ' 
    <tr style="line-height:10px;">
        <th style="border-top:1px solid #333;" colspan="6"></th>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td><strong>&nbsp;'.$reserved['all_reserved'].'</strong></td>
    </tr>
    <tr style="line-height:30px;">
        <th colspan="6"></th>
    </tr>
    <tr style="text-align:center;">
        <td>GENERATED BY: '.$user['strFirstname']." ".$user['strLastname'].'</td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    <tr style="text-align:center;">
        <td>RUNDATE&TIME:'.date('Y-m-d h:i a').'</td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
 </table>'; 
  
            
$pdf->writeHTML($html, true, false, true, false, '');

//$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

// ---------------------------------------------------------

// Close and output PDF document
// This method has several options, check the source code documentation for more information.
$pdf->Output("reservationSummary".date("Ymdhis").".pdf", 'I');


?>