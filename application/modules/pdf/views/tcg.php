<?php

    tcpdf();

    class MYPDF extends TCPDF {

    
        // Page footer
        public function Footer() {
            // Position at 15 mm from bottom
            $this->SetY(-15);
            // Set font
            $this->SetFont('helvetica', 'I', 8);
            // Page number
            $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().' of '.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
        }
    }

    // create new PDF document
    //$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    //$pdf = new TCPDF("P", PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf = new MYPDF("L", "mm", array(210,146), true, 'UTF-8', false);

    // set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetTitle("Copy of Grades");
    
    // set margins
    $pdf->SetMargins(10, 10 , 10);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    $pdf->setFooterData();
    $pdf->SetFont('helvetica','',10);
    //$pdf->SetAutoPageBreak(TRUE, 6);
    
   //font setting
    //$pdf->SetFont('calibril_0', '', 10, '', 'false');
    
    $pdf->setPrintHeader(false);
    //$pdf->setPrintFooter(true);
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
    $header_page = '
                <div style="text-align:center;font-size: 10;">OFFICE OF THE REGISTRAR<br /><strong>COPY OF GRADES</strong></div>                    
                <table>
                    <tr>
                        <td style="line-height:15px;" colspan=6></td>         
                    </tr>
                </table>
                <table border="0" cellspacing="0" cellpadding="0" style="color:#333; font-size:9;">                                
                    <tr>                            
                        <td width="14%"><font style="font-size: 9;">Name</font></td>
                        <td width="1%" style="text-align:center">:</td>
                        <td width="60%"><font style="font-size: 9;">'.$student['strLastname'].' '.$student['strFirstname'].' '.$student['strMiddlename'].'</font></td>                    
                        <td width="9%"><font style="font-size: 9;">ID No</font></td>
                        <td width="1%" style="text-align:center">:</td>
                        <td width="15%"><font style="font-size: 9;">'.preg_replace("/[^a-zA-Z0-9]+/", "", $student['strStudentNumber']).'</font></td>                                                    
                    </tr>
                    <tr>
                        <td style="line-height:1px;" colspan=2></td>         
                    </tr>
                    <tr>                            
                        <td><font style="font-size: 9;">Program Persued</font></td>
                        <td style="text-align:center">:</td>
                        <td><font style="font-size: 9;">'.trim($student['strProgramDescription']).'</font></td>                     
                        <td></td> 
                        <td></td> 
                        <td></td> 
                    </tr>
                                                
                </table>
                <br />
          ';

       
    $signatory_label = isset($other_details['signatory_label'])?$other_details['signatory_label']:'Registrar';

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
                    <td style="line-height:50px;" colspan=6></td>         
                </tr> 
                <tr>
                    <td style="font-size:9px;"></td>
                    <td colspan="2" style="font-size:9px;"></td>         
                    <td colspan="3" style="text-align:center;font-size:9px;"><b>'.$other_details['registrar'].'</b></td>                       
                </tr>
                <tr>
                    <td style="line-height:10px;" colspan=6></td>         
                </tr> 
                <tr style="font-size:9px">
                    <td colspan="3">GENERATED BY:'.$user['strFirstname']." ".$user['strLastname'].'<br />                 
                    RUNDATE&TIME:'.date("Y-m-d H:i:s",strtotime($other_details['date_generated'])).'</td>                     
                    <td colspan="3" style="text-align:center;font-size:9px;">'.$signatory_label.'</td>                       
                </tr>
                ';         
            $html .= $header_page;

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
$page_footer_margin = 450;
$html .='<table>';
$html.=$table_header_page;
$prev_school = "";
$cred_ctr = 0;
$sch_ctr = 0;
foreach($credited_subjects as $record_credited){    
    if($prev_school != $record_credited['other_data']['school']){       
        $html .= '
            <tr>                                           
                <td style="font-size:9px;" colspan="6"><b>'.$record_credited['other_data']['school'].'</b></td>
            </tr>';
            $page_footer_margin -= 15;
    }
    $html .= '
        <tr>                                           
            <td style="font-size:9px;" colspan="6"><b>'.$record_credited['other_data']['school_year'].', '.$record_credited['other_data']['term'].'</b></td>
        </tr>                    
        ';
        $page_footer_margin -= 15;
    foreach($record_credited['records'] as $item){ 
        $ctr++;
        $page_ctr++;
        $page_footer_margin -= 15;
        $html .= '            
        <tr>
            <td style="font-size:9px;">'.$item['course_code'].'</td>
            <td style="font-size:9px;">'.$item['descriptive_title'].'</td>
            <td style="font-size:9px;text-align:center;">('.number_format($item['units'], 1, '.', '').')</td>
            <td style="font-size:9px;text-align:center;">'.$item['grade'].'</td>
            <td style="font-size:9px;text-align:center;"></td>                        
            <td style="font-size:9px;text-align:center;">('.number_format($item['units'], 1, '.', '').')</td>
        </tr>            
        ';

        if($page_ctr == 35){
                
            $page++;
            $page_ctr = 0;
            $html .= '<tr>
                        <td style="text-align:center;" colspan="6">------------------------------------------------------------ Continued on Page '.$page.' ------------------------------------------------------------</td>
                    </tr>';
            $html .= '<tr>
                        <td style="line-height:'.$page_footer_margin.'px;" colspan="6"></td>         
                    </tr>';
            $html .= $footer;
            $html .= '</table>';
            $pdf->writeHTML($html, true, false, true, false, '');                
            $pdf->AddPage();
            $page_footer_margin = 520;
            $html = '<table border="0" cellspacing="0" cellpadding="1" style="color:#333; font-size:9;">                
                    <tr>
                        <td style="line-height:150px;" colspan=6></td>         
                    </tr>        
                </table>
            ';
            $html .= $header_page;
            $html .='<table>';
            $html.=$table_header_page;

        }        
    }
    $html .= '
    <tr>
        <td style="font-size:8px;"></td>
        <td colspan=2 style="font-size:8px;text-align:right">Term GWA</td>
        <td style="font-size:8px;text-align:center;">0.000</td>
        <td style="font-size:8px;text-align:center;"></td>                        
        <td style="font-size:8px;text-align:center;"></td>
    </tr>
    <tr>
        <td style="font-size:8px;"></td>
        <td colspan=2 style="font-size:8px;text-align:right">Cumulative GWA</td>        
        <td style="font-size:8px;text-align:center;">0.000</td>
        <td style="font-size:8px;text-align:center;"></td>                        
        <td style="font-size:8px;text-align:center;"></td>
    </tr>';
    $ctr+=2;
    $page_ctr+=2;
    $page_footer_margin -= 30;
    $prev_school = $record_credited['other_data']['school'];  
    
}

$cummulative_gwa = 0.000;
$total_grades_sum = 0;
$total_units = 0;
foreach($records as $record){
    
    $term_gwa = 0.000;
    $grades_sum = 0;
    $term_units = 0;

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
            
       
            
            $ctr++;
            $page_ctr++;
            $page_footer_margin -= 15;
            $units_earned = ($item['strRemarks'] == "Passed")?number_format($item['strUnits'],1):0;
            if($item['include_gwa']){
                $units = number_format($item['strUnits'],1);
                $term_units += $item['strUnits'];
                $total_units += $item['strUnits'];

                switch($item['v3']){
                    case 'FA':
                        $grade = 5;
                    break;
                    case 'UD':
                        $grade = 5;                    
                    break;
                    default:
                        $grade = $item['v3'];                    
                }                             
                        
                $grades_sum += $grade * $item['strUnits'];
                $total_grades_sum += $grade * $item['strUnits'];
            }
            else{
                $units = "(".number_format($item['strUnits'],1).")";
                $units_earned = "(".$units_earned.")";
            }
            
            $html .= '            
                <tr>
                    <td style="font-size:9px;">'.$item['strCode'].'</td>
                    <td style="font-size:9px;">'.$item['strDescription'].'</td>
                    <td style="font-size:9px;text-align:center;">'.$units.'</td>
                    <td style="font-size:9px;text-align:center;">'.$item['v3'].'</td>
                    <td style="font-size:9px;text-align:center;"></td>                        
                    <td style="font-size:9px;text-align:center;">'.$units_earned.'</td>
                </tr>            
                ';
            

            if(($page_ctr == 30 && $page == 1) || $page_ctr == 35){
                
                $page++;
                $page_ctr = 0;
                $html .= '<tr>
                            <td style="text-align:center;" colspan="6">------------------------------------------------------------ Continued on Page '.$page.' ------------------------------------------------------------</td>
                        </tr>';
                $html .= '<tr>
                            <td style="line-height:'.$page_footer_margin.'px;" colspan="6"></td>         
                        </tr>';
                $html .= $footer;
                $html .= '</table>';
                $pdf->writeHTML($html, true, false, true, false, '');                
                $pdf->AddPage();
                $page_footer_margin = 520;
                $html = '<table border="0" cellspacing="0" cellpadding="1" style="color:#333; font-size:9;">                
                        <tr>
                            <td style="line-height:150px;" colspan=6></td>         
                        </tr>        
                    </table>
                ';
                $html .= $header_page;
                $html .='<table>';
                $html.=$table_header_page;

            }
            
        }
        if($term_units > 0)
            $term_gwa = $grades_sum/$term_units;                
        
        $term_gwa = number_format(round($term_gwa,3),3);
        
        if($total_units > 0)
            $cummulative_gwa = $total_grades_sum/$total_units;
                    
        $cummulative_gwa = number_format(round($cummulative_gwa,3),3);
        $html .= '
        <tr>
            <td style="font-size:8px;"></td>
            <td colspan=2 style="font-size:8px;text-align:right">Term GWA</td>
            <td style="font-size:8px;text-align:center;">'.$term_gwa.'</td>
            <td style="font-size:8px;text-align:center;"></td>                        
            <td style="font-size:8px;text-align:center;"></td>
        </tr>
        <tr>
            <td style="font-size:8px;"></td>
            <td colspan=2 style="font-size:8px;text-align:right">Cumulative GWA</td>        
            <td style="font-size:8px;text-align:center;">'.$cummulative_gwa.'</td>
            <td style="font-size:8px;text-align:center;"></td>                        
            <td style="font-size:8px;text-align:center;"></td>
        </tr>';
        $ctr+=2;
        $page_ctr+=2;
        $page_footer_margin -= 30;        
        
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