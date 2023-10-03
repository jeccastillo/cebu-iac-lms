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
    $cm = ($campus == "Cebu")?"iACADEMY Cebu":"iACADEMY";
    $seal = ($campus == "Cebu")?"https://i.ibb.co/9hgbYNB/seal.png":"https://i.ibb.co/kcYVsS7/i-ACADEMY-Seal-Makati.png";
    
    // Set some content to print
    $html = '<table border="0" cellspacing="0" cellpadding="1" style="color:#333; font-size:9;">                
                <tr>
                    <td style="line-height:150px;" colspan=6></td>         
                </tr>        
            </table>
           ';
    $header_first_page = '<table border="0" cellspacing="0" cellpadding="0" style="color:#333; font-size:9;">
                <tr>                    
                    <td width="70%">
                        <table border="0" cellspacing="0" cellpadding="0" style="color:#333; font-size:9;">                                
                            <tr>                            
                                <td width="20%">             
                                    <font style="font-size: 8;">Name</font>
                                </td>
                                <td colspan="3">             
                                    <font style="font-size: 8;">: '.$student['strLastname'].' '.$student['strFirstname'].' '.$student['strMiddlename'].'</font>
                                </td>                    
                                
                            </tr>
                            <tr>                            
                                <td>             
                                    <font style="font-size: 8;">Program Persued</font>
                                </td>
                                <td colspan="3">             
                                    <font style="font-size: 8;">: '.trim($student['strProgramDescription']).'</font>
                                </td>                      
                            </tr>
                            <tr>                            
                                <td>             
                                    <font style="font-size: 8;">Date of Birth</font>
                                </td>
                                <td width="40%">             
                                    <font style="font-size: 8;">: '.$student['dteBirthDate'].'</font>
                                </td>    
                                <td width="20%"><font style="font-size: 8;">Place of Birth</font></td>                
                                <td width="20%">
                                    <font style="font-size: 8;">: '.$student['place_of_birth'].'</font>
                                </td>                
                            </tr>
                            <tr>                            
                                <td>             
                                    <font style="font-size: 8;">Citizenship</font>
                                </td>
                                <td>             
                                    <font style="font-size: 8;">: '.$student['strCitizenship'].'</font>
                                </td>    
                                <td><font style="font-size: 8;">Gender</font></td>                
                                <td>
                                    <font style="font-size: 8;">: '.$student['enumGender'].'</font>
                                </td>                
                            </tr>
                            <tr>                            
                                <td>             
                                    <font style="font-size: 8;">Secondary School</font>
                                </td>
                                <td>             
                                    <font style="font-size: 8;">: '.$student['high_school'].'</font>
                                </td>    
                                <td><font style="font-size: 8;">ID No.</font></td>                
                                <td>
                                    <font style="font-size: 8;">: '.preg_replace("/[^a-zA-Z0-9]+/", "", $student['strStudentNumber']).'</font>
                                </td>                
                            </tr>
                            <tr>                            
                                <td>             
                                    <font style="font-size: 8;">Tertiary School</font>
                                </td>
                                <td>             
                                    <font style="font-size: 8;">: '.$student['college'].'</font>
                                </td>                     
                                <td><font style="font-size: 8;">Date of Admission</font></td>                
                                <td>
                                    <font style="font-size: 8;">: '.$other_details['admission_date'].'</font>
                                </td>                
                            </tr>
                            <tr>                            
                                <td colspan="2">             
                                    <font style="font-size: 8;"></font>
                                </td>
                                <td><font style="font-size: 8;">Date of Graduation</font></td>                
                                <td>
                                    <font style="font-size: 8;">: '.$student['date_of_graduation'].'</font>
                                </td>                
                            </tr>
                            <tr>                            
                                <td colspan="2">             
                                    <font style="font-size: 8;"></font>
                                </td>
                                <td><font style="font-size: 8;">NSTP Serial No.</font></td>
                                <td>
                                    <font style="font-size: 8;">: '.$student['nstp_serial'].'</font>
                                </td>                
                            </tr>
                        </table>
                    </td>
                    <td width="30%">                    
                        <img src="'.$other_details['picture'].'" width="200px" />                    
                    </td>
                </tr>     
           </table>
          ';

$footer ='
        <tr>
            <td style="line-height:15px;" colspan=6></td>         
        </tr>
        <tr>
            <td style="font-size:9px;">Remarks</td>
            <td colspan="5"><p style="font-size:8px;">'.$other_details['remarks'].'</p></td>                    
        </tr> 
        <tr>
            <td style="line-height:5px;" colspan=6></td>         
        </tr>                                            
        <tr>
            <td style="font-size:9px;">Grading System</td>
            <td colspan="5"><p style="font-size:8px;">1.00 (98-100) Excelent; 1.25 (95-97); 1.50 (92-94) Very Good; 1.75 (89-91); 2.00 (86-88); 2.25 (83-85);2.50 (80-82) Satisfactory; 2.75 (77-79) Fair; 3.00 (75-76); 5.00 (Below 75) Failed; OD (Officially Dropped); UD (Unofficially Dropped); FA (Failure due to Absences); IP (In Progress) for internship only; P (Passed); F (Failed); OW (Officially Withdrawn); UW (Unofficially Withdrawn); NGS (No Grade Submitted)</p>      
            </td>                    
        </tr>
        <tr>
            <td style="line-height:5px;" colspan=6></td>         
        </tr>                       
        <tr>
            <td style="font-size:9px;">Note</td>
            <td colspan="5"><p style="font-size:8px;">This document is valid only when it bears the seal of the College and affixed with the original signature in ink. Any erasure or alteration made on this copy renders the whole document invalid.</p>      
            </td>                    
        </tr>
        <tr>
            <td style="line-height:10px;" colspan=6></td>         
        </tr> 
        <tr>
            <td style="font-size:9px;">Prepared By</td>
            <td colspan="2"><b>'.$other_details['prepared_by'].'</b></td>                     
        </tr>
        <tr>
            <td style="line-height:10px;" colspan=6></td>         
        </tr> 
        <tr>
            <td style="font-size:9px;">Verified By</td>
            <td colspan="2"><b>'.$other_details['verified_by'].'</b></td>         
            <td colspan="3" style="text-align:center"><b>'.$other_details['registrar'].'</b></td>                       
        </tr>
        <tr>
            <td style="line-height:10px;" colspan=6></td>         
        </tr> 
        <tr>
            <td style="font-size:9px;">Date Issued</td>
            <td colspan="2"><b>'.$other_details['date_generated'].'</b></td>         
            <td colspan="3" style="text-align:center">Registrar</td>                       
        </tr>
          ';          
$generated_line = '<p style="font-size:8px;margin-top:10px;">GENERATED BY:'.$user['strFirstname']." ".$user['strLastname'].'<br />                 
              RUNDATE&TIME:'.date('Y-m-d h:i a').'
          </p>';
$html .= $header_first_page;

$table_header_page = '<tr>
                        <th style="width:15%;font-size:9px;border-bottom:1px solid #333;"><b>Course Code</b></th>
                        <th style="width:40%;font-size:9px;border-bottom:1px solid #333;"><b>Descriptive Title</b></th>
                        <th style="width:10%;font-size:9px;border-bottom:1px solid #333;text-align:center;"><b>Units</b></th>         
                        <th style="width:10%;font-size:9px;border-bottom:1px solid #333;text-align:center;"><b>Grade</b></th>
                        <th style="width:15%;font-size:9px;border-bottom:1px solid #333;text-align:center;"><b>Completion</b></th>            
                        <th style="width:10%;font-size:9px;border-bottom:1px solid #333;text-align:center;"><b>Units Earned</b></th>
                        </tr>
                        <tr>
                        <td style="line-height:10px;" colspan="6"></td>         
                        </tr>';

$page = 1;
$ctr = 0;
$page_ctr = 0;
$firstpage = true;
$page_footer_margin = 500;
$html .='<table>';
$html.=$table_header_page;

foreach($records as $record){

    $active_sem = $record['other_data']['term'];
    $term_type = ($active_sem['term_label'] == "Sem")?"Semester":"Trimester";
    $html .= '
        <tr>                                           
            <td style="font-size:9px;" colspan="6"><b>iACADEMY</b></td>
        </tr>';
        $page_footer_margin -= 15;
    $html .= '
        <tr>                                           
            <td style="font-size:9px;" colspan="6"><b>SY '.$active_sem['strYearStart'].'-'.$active_sem['strYearEnd'].' '.$term_type.' '.switch_num_rev($active_sem['enumSem']).'</b></td>
        </tr>                    
        ';
        $page_footer_margin -= 15;
         
        foreach($record['records'] as $item){                
        
       
            if($item['intFinalized'] >= 2){
                $ctr++;
                $page_ctr++;
                $page_footer_margin -= 15;
                $units_earned = ($item['strRemarks'] == "Passed")?number_format($item['strUnits'],1):0;
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
                        <td style="font-size:8px;text-align:center;">'.$item['v3'].'</td>
                        <td style="font-size:8px;text-align:center;"></td>                        
                        <td style="font-size:8px;text-align:center;">'.$units_earned.'</td>
                    </tr>            
                    ';
            }
        }        
        
}
$html .= '<tr>
            <td style="text-align:center;" colspan="6">------------------------------------------------------------ Nothing Follows ------------------------------------------------------------</td>
        </tr>';
$html .= '<tr>
            <td style="line-height:'.$page_footer_margin.'px;" colspan="6"></td>         
        </tr>';

$html .= $footer;
$html .="</table>";    
            
    


$pdf->writeHTML($html, true, false, true, false, '');

//$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

// ---------------------------------------------------------

// Close and output PDF document
// This method has several options, check the source code documentation for more information.
$pdf->Output("grade_slip".date("Ymdhis").".pdf", 'I');


?>