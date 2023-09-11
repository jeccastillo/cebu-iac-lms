<?php

    tcpdf();
    // create new PDF document
    //$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    //$pdf = new TCPDF("P", PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetTitle("Enrollment Summary");
    
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
                    <font style="font-family:Calibri Light; font-size: 11;font-weight: bold;">Student Grade Slip '.$active_sem['enumSem'].' Term SY'.$active_sem['strYearStart'].'-'.$active_sem['strYearEnd'].'</font>
                </td>
            </tr>        
            </table>
           ';
    $html .= '<table border="1" cellspacing="0" cellpadding="0" style="color:#333; font-size:9;">
                <tr>                            
                    <td style="border-bottom:1px solid #333">             
                        <font style="font-size: 8;">Student Number:</font>
                    </td>
                    <td style="border-bottom:1px solid #333">             
                        <font style="font-size: 8;">Student Name:</font>
                    </td>
                    <td style="border-bottom:1px solid #333">             
                        <font style="font-size: 8;">Course:</font>
                    </td>
                </tr>
                <tr>                            
                    <td style="border-bottom:1px solid #333">             
                        <font style="font-size: 8;">'.$student['strStudentNumber'].'</font>
                    </td>
                    <td style="border-bottom:1px solid #333">             
                        <font style="font-size: 8;">'.$student['strLastname'].' '.$student['strFirstname'].' '.$student['strMiddlename'].'</font>
                    </td>
                    <td style="border-bottom:1px solid #333">             
                        <font style="font-size: 8;">'.$student['strProgramDescription'].'</font>
                    </td>
                </tr>        
           </table>
          ';
    



$html .= '       
     <br />
     <table v-if="enrolled" class="table table-bordered table-striped">
     <tr>
         <th style="width:15%;font-size:9px;">Course Code</th>
         <th style="width:40%;font-size:9px;">Descriptive Title</th>
         <th style="width:15%;font-size:9px;">Units</th>         
         <th style="width:15%;font-size:9px;">Final Grade</th>
         <th style="width:15%;font-size:9px;">Units Earned</th>
     </tr>     
     ';
     
    
    foreach($records as $item){                
        
        $grade = ($item['intFinalized'] >= 2)?$item['v3']:'NGS';
        $units_earned = ($item['strRemarks'] == "Passed")?$item['strUnits']:0;
        $html .= '            
            <tr>
                <td style="font-size:8px;">'.$item['strCode'].'</td>
                <td style="font-size:8px;">'.$item['strDescription'].'</td>
                <td style="font-size:8px;text-align:center;">'.number_format($item['strUnits'],1).'</td>
                <td style="font-size:8px;text-align:center;">'.$grade.'</td>
                <td style="font-size:8px;text-align:center;">'.number_format($item['strUnits'],1).'</td>
            </tr>            
            ';
    }
  
            
    $html .='<tr style="line-height:5px;">
                <th colspan="6">GWA:'.$other_data['gwa'].'</th>
            </tr>
            </table>';

$pdf->writeHTML($html, true, false, true, false, '');

//$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

// ---------------------------------------------------------

// Close and output PDF document
// This method has several options, check the source code documentation for more information.
$pdf->Output("grade_slip".date("Ymdhis").".pdf", 'I');


?>