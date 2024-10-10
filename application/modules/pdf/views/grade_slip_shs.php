<style>
body {
    margin: 0;
    font-family: Arial, Sans-Serif;
}

.sheet-outer {
    margin: 0;
}

.sheet {
    margin: 0;
    overflow: hidden;
    position: relative;
    box-sizing: border-box;
    page-break-after: always;
}

section {
    font-size: 12px
}

table {
    width: 100%;
}

table tr td {
    vertical-align: top;
}

@media screen {
    body {
        background: #e0e0e0
    }

    .sheet {
        background: white;
        box-shadow: 0 .5mm 2mm rgba(0, 0, 0, .3);
        margin: 5mm auto;
    }
}

.sheet-outer.A4 .sheet {
    width: 210mm;
    height: 296mm
}

.sheet.padding-5mm {
    padding-top: 10mm;
    padding-left: 8mm;
    padding-right: 10mm;
}

@page {
    size: A4;
    margin: 0
}

@media print {

    .sheet-outer.A4,
    .sheet-outer.A5.landscape {
        width: 210mm
    }
}
</style>

<body>
    <div class="sheet-outer A4">
        <section class="sheet padding-5mm">
<?php

    $html = '<table border="1" cellspacing="0" cellpadding="1" style="color:#333; font-size:10;">
                <tr>                            
                    <td width="15%">             
                        <font style="font-weight:bold">Name:</font>
                    </td>
                    <td width="35%">             
                        <font style="">'.strtoupper($student['strLastname'].' '.$student['strFirstname'].' '.$student['strMiddlename']).'</font>
                    </td>
                    <td width="15%">             
                        <font style="font-weight:bold">ID No:</font>
                    </td>
                    <td width="35%">             
                        <font style="">'.preg_replace("/[^a-zA-Z0-9]+/", "", $student['strStudentNumber']).'</font>
                    </td>
                </tr>
                <tr>                            
                    <td width="15%">             
                        <font style="font-weight:bold">Grade & Sec:</font>
                    </td>
                    <td width="35%">             
                        <font style="">'.$grade_level.' '.$registration['block_name'].'</font>
                    </td>
                    <td width="15%">             
                        <font style="font-weight:bold">LRN:</font>
                    </td>
                    <td width="35%">             
                        <font style="">'.$student['strLRN'].'</font>
                    </td>
                </tr>
                <tr>                            
                    <td width="15%">             
                        <font style="font-weight:bold">Adviser:</font>
                    </td>
                    <td width="35%">             
                        <font style="">'.$adviser_name.'</font>
                    </td>
                    <td width="15%">             
                        <font style="font-weight:bold">SY & Sem:</font>
                    </td>
                    <td width="35%">             
                        <font style="">'.$active_sem['strYearStart']."-".$active_sem['strYearEnd']." ".$active_sem['enumSem']." ".$active_sem['term_label'].'</font>
                    </td>
                </tr>
                <tr>                            
                    <td width="15%">             
                        <font style="font-weight:bold">Track/Strand:</font>
                    </td>
                    <td width="35%">             
                        <font style="">'.trim($student['strProgramDescription']).'</font>
                    </td>
                    <td width="15%">             
                        <font style="font-weight:bold">Grading Period:</font>
                    </td>
                    <td width="35%">             
                        <font style="">'.$period.'</font>
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

            $html .= "<table><tr><td></td>";
            foreach($term_months as $month){
                $html .="<td>".$month['month']."</td>";
            }
            $html .="</tr></table>";


            echo $html;
?>
        </section>
    </div>
</body>