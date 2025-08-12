<?php

$html .='
    <table cellpadding="0" style="color:#333; text-align:left; font-size:8;" width="570px">                                
        <tr>
            <td width="320px">
                <table cellpadding="0"  style="color:#333; font-size:7;">
                    <tr>
                        <td colspan="3" style= "font-size:8; font-weight:bold;">ASSESSMENT SUMMARY</td>                                
                    </tr>
                    <tr>
                        <td width="80px"></td>
                        <td width="60px" style="text-decoration:underline; text-align:right;">FULL PAYMENT</td>
                        <td width="80px" style="text-decoration:underline; text-align:right;">50% DOWN PAYMENT</td>
                        <td width="80px" style="text-decoration:underline; text-align:right;">30% DOWN PAYMENT</td>
                    </tr>
                    <tr>
                        <td style="text-align:left;">Tuition Fee</td>
                        <td style="text-align:right;">'.number_format($tuition['tuition_before_discount'], 2, '.' ,',') .'</td>
                        <td style="text-align:right;">'.number_format($tuition['tuition_installment_before_discount50'], 2, '.' ,',') .'</td>
                        <td style="text-align:right;">'.number_format($tuition['tuition_installment_before_discount30'], 2, '.' ,',') .'</td>
                    </tr>
                    <tr>
                        <td>Laboratory</td>
                        <td style="text-align:right;">'.number_format($tuition['lab_before_discount'], 2, '.' ,',') .'</td>
                        <td style="text-align:right;">'.number_format($tuition['lab_installment_before_discount50'], 2, '.' ,',') .'</td>
                        <td style="text-align:right;">'.number_format($tuition['lab_installment_before_discount30'], 2, '.' ,',') .'</td>
                    </tr>
                    <tr>
                        <td>Miscellaneous</td>
                        <td style="text-align:right;">'.number_format($tuition['misc_before_discount'], 2, '.' ,',') .'</td>
                        <td style="text-align:right;">'.number_format($tuition['misc_before_discount'], 2, '.' ,',') .'</td>
                        <td style="text-align:right;">'.number_format($tuition['misc_before_discount'], 2, '.' ,',') .'</td>
                    </tr>
                    <tr>
                        <td>Other Fees</td>
                        <td style="text-align:right;">'.number_format($tuition['new_student'] + $tuition['total_foreign'], 2, '.' ,',') .'</td>
                        <td style="text-align:right;">'.number_format($tuition['new_student'] + $tuition['total_foreign'], 2, '.' ,',') .'</td>
                        <td style="text-align:right;">'.number_format($tuition['new_student'] + $tuition['total_foreign'], 2, '.' ,',') .'</td>
                    </tr>';
                    if($tuition['late_enrollment_fee'] > 0):
                        $html .='<tr>
                                <td>Late Enrollment</td>
                                <td style="text-align:right;">'.number_format($tuition['late_enrollment_fee'], 2, '.' ,',') .'</td>
                                <td style="text-align:right;">'.number_format($tuition['late_enrollment_fee'], 2, '.' ,',') .'</td>
                                <td style="text-align:right;">'.number_format($tuition['late_enrollment_fee'], 2, '.' ,',') .'</td>
                            </tr>';
                    endif;
                    

        if($tuition['scholarship_deductions'] > 0 || $tuition['discount_deductions'] > 0):              
            $html .='   <tr>
                            <td style="font-weight:bold;"></td>
                            <td style="font-weight:bold;border-top: 1px solid #555; text-align:right;">'.number_format($tuition['total_before_deductions'], 2, '.' ,',').'</td>
                            <td style="font-weight:bold;border-top: 1px solid #555; text-align:right;">'.number_format($tuition['ti_before_deductions50'], 2, '.' ,',').'</td>
                            <td style="font-weight:bold;border-top: 1px solid #555; text-align:right;">'.number_format($tuition['ti_before_deductions30'], 2, '.' ,',').'</td>
                        </tr>
                        <tr>
                            <td colspan="3" style= "font-size:7; line-height:1.0;"></td>                
                        </tr>';
        endif;
            if(!empty($tuition['scholarship'])):
                $ctr = 0;
                foreach($tuition['scholarship'] as $sch):                                      
                $html .='   <tr>
                                <td style="font-size:7px">'.$sch->name.'</td>
                                <td style="text-align:right;">-'.number_format($tuition['scholarship_deductions_array'][$ctr], 2, '.' ,',').'</td>
                                <td style="text-align:right;">-'.number_format($tuition['scholarship_deductions_installment_array50'][$ctr], 2, '.' ,',').'</td>
                                <td style="text-align:right;">-'.number_format($tuition['scholarship_deductions_installment_array30'][$ctr], 2, '.' ,',').'</td>
                            </tr>';
                $ctr++;
                endforeach;
            endif;
            if(!empty($tuition['discount'])):
                $ctr = 0;
                foreach($tuition['discount'] as $sch):                                      
                $html .='   <tr>
                                <td style="font-size:7px">'.$sch->name.'</td>
                                <td style="text-align:right;">-'.number_format($tuition['scholarship_deductions_dc_array'][$ctr], 2, '.' ,',').'</td>                                            
                                <td style="text-align:right;">-'.number_format($tuition['scholarship_deductions_installment_dc_array50'][$ctr], 2, '.' ,',').'</td>
                                <td style="text-align:right;">-'.number_format($tuition['scholarship_deductions_installment_dc_array30'][$ctr], 2, '.' ,',').'</td>
                            </tr>';
                $ctr++;
                endforeach;
            endif;                                        
            $html .='
                    <tr>
                        <td style="font-weight:bold;">Total</td>
                        <td style="font-weight:bold;text-decoration: underline; text-align:right;">'.number_format($tuition['total'], 2, '.' ,',').'</td>
                        <td style="font-weight:bold;text-decoration: underline; text-align:right;">'.number_format($tuition['total_installment50'], 2, '.' ,',').'</td>
                        <td style="font-weight:bold;text-decoration: underline; text-align:right;">'.number_format($tuition['total_installment30'], 2, '.' ,',').'</td>
                    </tr>                        
                    <tr>
                        <td style="font-size:8; line-height:1; color:#fff;">Space</td>
                        <td style="font-size:8; line-height:1; color:#fff;">Space</td>
                        <td style="font-size:8; line-height:1; color:#fff;">Space</td>
                    </tr>                                                           
                    <tr>
                        <td width="80px">DOWN PAYMENT</td>                                
                        <td width="60px"></td>
                        <td width="80px" style="text-align:right;">'.number_format($tuition['down_payment50'], 2, '.' ,',').'</td>
                        <td width="80px" style="text-align:right;">'.number_format($tuition['down_payment30'], 2, '.' ,',').'</td>
                        
                    </tr>
                    <tr>
                            <td>1st INSTALLMENT</td>                                    
                            <td style="text-align:right;">'.date('m/d/Y',strtotime($active_sem['installment1'])).' ('.switch_day(date('N',strtotime($active_sem['installment1']))).')</td>
                            <td style="text-align:right;">'.number_format($tuition['installment_fee50'], 2, '.' ,',').'</td>
                            <td style="text-align:right;">'.number_format($tuition['installment_fee30'], 2, '.' ,',').'</td>
                        </tr>
                        <tr>
                            <td>2nd INSTALLMENT</td>                                    
                            <td style="text-align:right;">'.date('m/d/Y',strtotime($active_sem['installment2'])).' ('.switch_day(date('N',strtotime($active_sem['installment2']))).')</td>
                            <td style="text-align:right;">'.number_format($tuition['installment_fee50'], 2, '.' ,',').'</td>
                            <td style="text-align:right;">'.number_format($tuition['installment_fee30'], 2, '.' ,',').'</td>
                        </tr>
                        <tr>
                            <td>3rd INSTALLMENT</td>                                    
                            <td style="text-align:right;">'.date('m/d/Y',strtotime($active_sem['installment3'])).' ('.switch_day(date('N',strtotime($active_sem['installment3']))).')</td>
                            <td style="text-align:right;">'.number_format($tuition['installment_fee50'], 2, '.' ,',').'</td>
                            <td style="text-align:right;">'.number_format($tuition['installment_fee30'], 2, '.' ,',').'</td>
                        </tr>
                        <tr>
                            <td>4th INSTALLMENT</td>                                    
                            <td style="text-align:right;">'.date('m/d/Y',strtotime($active_sem['installment4'])).' ('.switch_day(date('N',strtotime($active_sem['installment4']))).')</td>
                            <td style="text-align:right;">'.number_format($tuition['installment_fee50'], 2, '.' ,',').'</td>
                            <td style="text-align:right;">'.number_format($tuition['installment_fee30'], 2, '.' ,',').'</td>
                        </tr>
                        <tr>
                            <td>5th INSTALLMENT</td>                                    
                            <td style="text-align:right;">'.date('m/d/Y',strtotime($active_sem['installment5'])).' ('.switch_day(date('N',strtotime($active_sem['installment5']))).')</td>
                            <td style="text-align:right;">'.number_format($tuition['installment_fee50'], 2, '.' ,',').'</td>
                            <td style="text-align:right;">'.number_format($tuition['installment_fee30'], 2, '.' ,',').'</td>
                        </tr>
                        <tr>
                            <td style="font-weight:bold;"></td>                                                        
                            <td></td>
                            <td style="text-align:right; font-weight:bold; text-decoration: underline;">'.number_format($tuition['total_installment50'], 2, '.' ,',').'</td>
                            <td style="text-align:right; font-weight:bold; text-decoration: underline;">'.number_format($tuition['total_installment30'], 2, '.' ,',').'</td>                                                                      
                        </tr>                            
                </table>
            </td>';                    
                                        
            $html .= '                        
            <td width="145px">                                
                <table style="color:#333; font-size:7; ">
                    <tr>
                        <td colspan="2" style= "font-size:8; font-weight:bold;">MISCELLANEOUS DETAIL</td>            
                    </tr>
                ';
                
                if($tuition['misc'] != 0){
                    
                foreach($tuition['misc_list'] as $key=>$val){

                    $html .= '<tr>
                                <td width="100px">'.$key.'</td>
                                <td width="40px" style="text-align:right;">'.number_format($val, 2, '.' ,',').'</td>
                            </tr>';                
                }
                $html.=' 
                    <tr>
                        <td style="font-weight:bold;">Total</td>
                        <td style="border-top: 1px solid #555; font-weight:bold; text-align:right;">'.number_format($tuition['misc_before_discount'], 2, '.' ,',').'</td>                
                    </tr>';
            }
            $html .= 
                '</table>  
            </td>
            <td width="100px">
                <table style="color:#333; font-size:7;">';
                if(($tuition['new_student'] + $tuition['total_foreign'])  != 0){
                    $html .='
                        <tr>
                            <td colspan="2" style= "font-size:9; font-weight:bold;">OTHER FEES DETAIL</td>
                            
                        </tr>';
                }

                if($tuition['new_student'] != 0){

                    $html.='<tr>
                    <td colspan="2" style= "font-size:8;">NEW STUDENT FEES</td></tr>';
                
                    foreach($tuition['new_student_list'] as $key=>$val){
    
                        $html .= '<tr>
                                    <td width="70px">'.$key.'</td>
                                    <td width="40px" style="text-align:right;">'.number_format($val, 2, '.' ,',').'</td>
                                </tr>';                
                    }                       
                }

                
                
                    

                if($tuition['total_foreign'] != 0){
                    $html .= '<tr>
                        <td width="80px"></td>
                        <td width="60px" style="text-align:right;"></td>
                    </tr>
                    <tr>
                    <td colspan="2" style= "font-size:8;">FOREIGN STUDENT FEES</td></tr>';
                
                    foreach($tuition['foreign_fee_list'] as $key=>$val){
    
                        $html .= '<tr>
                                    <td width="80px">'.$key.'</td>
                                    <td width="60px" style="text-align:right;">'.number_format($val, 2, '.' ,',').'</td>
                                </tr>';                
                    }
                    
                }        
                if(($tuition['new_student'] + $tuition['total_foreign'])  != 0){
                    $html.=' 
                    <tr>
                        <td style="font-weight:bold;">Total</td>
                        <td style="border-top: 1px solid #555; font-weight:bold; text-align:right;">'.number_format($tuition['total_foreign'] + $tuition['new_student'], 2, '.' ,',').'</td>                
                    </tr>';
                }
                
            $html.='                        
            </table>
            </td>
        </tr>
    </table>';
  





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
// $html = utf8_encode($html);
echo $html;


?>