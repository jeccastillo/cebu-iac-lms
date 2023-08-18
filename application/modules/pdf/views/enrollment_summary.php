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
                    <font style="font-family:Calibri Light; font-size: 11;font-weight: bold;">Enrollment Summary for '.$sem['enumSem'].' Term SY'.$sem['strYearStart'].'-'.$sem['strYearEnd'].'</font>
                </td>
            </tr>        
            </table>
           ';
    



$html .= '       
     <br />
     <table v-if="enrolled" class="table table-bordered table-striped">
     <tr>
         <th style="font-size:8px;width:30%">Program</th>
         <th font-size:8px;width:15%>Freshman</th>
         <th font-size:8px;width:15%>Transferee</th>
         <th font-size:8px;width:15%>Foreign</th>
         <th font-size:8px;width:15%>Second Degree</th>
         <th font-size:8px;width:10%>Total</th>
     </tr>';
     
    foreach($enrollment as $item){
        $major = ($item['strMajor'] != "None" && $item['strMajor'] != "")?'Major in '.$item['strMajor']:''; 
        $html .= '            
            <tr>
                <td>
                    '.$item['strProgramDescription'].' '.$major.'
                </td>
                <td>
                    '.$item['enrolled_freshman'].'
                </td>
                <td>
                    '.$item['enrolled_transferee'].'
                </td>
                <td>
                    '.$item['enrolled_foreign'].'
                </td>
                <td>
                    '.$item['enrolled_second'].'
                </td>
                <td>
                    '.($item['enrolled_freshman'] + $item['enrolled_transferee'] + $item['enrolled_foreign'] + $item['enrolled_second']).'
                </td>
            </tr>';
    }
$html .= ' 
     <tr>
         <td></td>
         <td></td>
         <td></td>
         <td></td>
         <td></td>
         <td><strong>{{ all_enrolled }}</strong></td>
     </tr>
 </table>'; 
  
            
$pdf->writeHTML($html, true, false, true, false, '');

//$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

// ---------------------------------------------------------

// Close and output PDF document
// This method has several options, check the source code documentation for more information.
$pdf->Output("enrollmentSummary".date("Ymdhis").".pdf", 'I');


?>