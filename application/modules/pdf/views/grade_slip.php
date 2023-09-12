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
    $pdf->SetMargins(10, 10 , 10);
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
    
    // Set some content to print
    $html = '<table border="0" cellspacing="0" cellpadding="1" style="color:#333; font-size:9;">
                <tr>             
                    <td width="20%"></td>
                    <td width="80%">             
                        <font style="font-family:Calibri Light; font-size: 9;font-weight: bold;">Information & Communications Technology, Inc. '.$cm.'</font>
                    </td>
                </tr>
                <tr>             
                    <td></td>               
                    <td>             
                        <font style="font-family:Calibri Light; font-size: 9;font-weight: bold;">Final Grade SY '.$active_sem['strYearStart'].'-'.$active_sem['strYearEnd'].' '.$term_type.' '.switch_num_rev($active_sem['enumSem']).'</font>
                    </td>
                </tr> 
                <tr>
                    <td style="line-height:10px;" colspan=5></td>         
                </tr>        
            </table>
           ';
    $html .= '<table border="0" cellspacing="0" cellpadding="0" style="color:#333; font-size:9;">
                <tr>                            
                    <td width="20%">             
                        <font style="font-size: 8;">Student Number:</font>
                    </td>
                    <td width="40%">             
                        <font style="font-size: 8;">Student Name:</font>
                    </td>
                    <td width="40%">             
                        <font style="font-size: 8;">Course:</font>
                    </td>
                </tr>
                <tr>                            
                    <td>             
                        <font style="font-size: 8;">'.$student['strStudentNumber'].'</font>
                    </td>
                    <td>             
                        <font style="font-size: 8;">'.$student['strLastname'].' '.$student['strFirstname'].' '.$student['strMiddlename'].'</font>
                    </td>
                    <td>             
                        <font style="font-size: 8;">'.$student['strProgramDescription'].'</font>
                    </td>
                </tr>        
           </table>
          ';
    



$html .= '       
     <br />
     <table v-if="enrolled" class="table table-bordered table-striped">
     <tr>
         <td style="line-height:10px;" colspan=5></td>         
     </tr> 
     <tr>
         <th style="width:15%;font-size:9px;border-bottom:1px solid #333;"><b>Course Code</b></th>
         <th style="width:40%;font-size:9px;border-bottom:1px solid #333;"><b>Descriptive Title</b></th>
         <th style="width:15%;font-size:9px;border-bottom:1px solid #333;text-align:center;"><b>Units</b></th>         
         <th style="width:15%;font-size:9px;border-bottom:1px solid #333;text-align:center;"><b>Final Grade</b></th>
         <th style="width:15%;font-size:9px;border-bottom:1px solid #333;text-align:center;"><b>Units Earned</b></th>
     </tr>
     <tr>
         <td style="line-height:5px;" colspan=5></td>         
     </tr>     
     ';
         
    foreach($records as $item){                
        
        $grade = ($item['intFinalized'] >= 2)?$item['v3']:'NGS';
        $units_earned = ($item['strRemarks'] == "Passed")?number_format($item['strUnits'],1):0;
        
        $html .= '            
            <tr>
                <td style="font-size:8px;">'.$item['strCode'].'</td>
                <td style="font-size:8px;">'.$item['strDescription'].'</td>
                <td style="font-size:8px;text-align:center;">'.number_format($item['strUnits'],1).'</td>
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
                <td style="line-height:10px;border-bottom:1px solid #333"></td>         
                <td style="line-height:10px;border-bottom:1px solid #333"></td>         
                <td style="line-height:10px;border-bottom:1px solid #333"></td>         
                <td style="line-height:10px;border-bottom:1px solid #333"></td>         
                <td style="line-height:10px;border-bottom:1px solid #333"></td>         
            </tr>
            </table>';

$pdf->writeHTML($html, true, false, true, false, '');

//$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

// ---------------------------------------------------------

// Close and output PDF document
// This method has several options, check the source code documentation for more information.
$pdf->Output("grade_slip".date("Ymdhis").".pdf", 'I');


?>