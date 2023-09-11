<table border="0" cellspacing="0" cellpadding="0" style="color:#333; font-size:9;">            
            <tr>            
                <td colspan = "3" width="100%" style="text-align: center;">             
                    
                </td>
            </tr>             
<table border="0">    
    <tr style="line-height:12px;">
        <td style="text-align:center;vertical-align: bottom"><img src= "https://i.ibb.co/xL1WcSm/iac-cebu.png"  width="120" /></td>        
        <td style="font-size:9px;text-align:center;"><b>Information and Communications Technology Academy, Inc.</b></td>
    </tr>
    <tr style="line-height:12px;">
        <td colspan="2" style="font-size:9px;text-align:center;"><b>(iACADEMY <?php echo $campus; ?>)</b></td>
    </tr>
    <tr style="line-height:12px;">
        <td colspan="2" style="font-size:9px;text-align:center;"><b>Grading Sheet</b></td>
    </tr>
        
    <tr style="line-height:10px;">
        <td colspan="2" style="font-size:9px;text-align:center"></td>
    </tr>
</table>
<table>    
    <tr style="line-height:10px;">
        <td colspan="7"></td>        
    </tr>  
</table>
<table>    
    <tr style="line-height:10px;">
        <td colspan="7"></td>        
    </tr>  
</table>
<table border="1" cellpadding="2px">
    <tr>
        <td>Course Code</td>
        <td colspan="2"><b><?php echo $classlist['strCode']; ?></b></td>
        <td>SY</td>
        <td colspan="2"><b><?php echo $sem['strYearStart'] . "-" . $sem['strYearEnd']; ?></b></td>
    </tr>    
    <tr>
        <td>Course Description</td>
        <td colspan="5"><b><?php echo $classlist['subjectDescription']; ?></b></td>                
    </tr>        
    <tr>
        <td>Section</td>
        <td colspan="2"><b><?php echo $classlist['strClassName'].$classlist['year'].$classlist['strSection']." ".$classlist['sub_section']; ?></b></td>
        <td><?php echo $sem['term_label']; ?></td>
        <td colspan="2"><b><?php echo $sem['enumSem']; ?></b></td>
    </tr>
    <tr>
        <td>Schedule</td>
        <td colspan="5"><b><?php echo $classlist['sched_day']." ".$classlist['sched_time']." ".$classlist['sched_room']; ?></b></td>                
    </tr>
    <tr>
        <td>Faculty Name</td>
        <td colspan="5"><b><?php echo $classlist['strLastname'].", ".$classlist['strFirstname']." ".$classlist['strMiddlename']; ?></b></td>                
    </tr>
    <tr style="line-height:16px;text-align:center;">                        
        <th rowspan="2" style="font-size:8px;" width="15%"><b>Student No.</b></th>
        <th rowspan="2" style="font-size:8px;" width="35%"><b>Name</b></th>
        <th rowspan="2" style="font-size:8px;" width="15%"><b>Specialization</b></th>
        <th colspan="2" style="font-size:8px;" width="20%"><b>Grade</b></th>
        <th rowspan="2" style="font-size:8px;" width="15%"><b>Remarks</b></th>        
    </tr>
    <tr style="line-height:16px;text-align:center;">
        <th style="font-size:8px;"><b>Midterm</b></th>
        <th style="font-size:8px;"><b>Final</b></th>
    </tr>
<?php 
$hgt = 395; 
foreach($students as $student): 
    $hgt -= 15;
?>
    
    <tr style="line-height:12px;">        
        <td  style="font-size:8px;"><?php echo preg_replace("/[^a-zA-Z0-9]+/", "", $student['strStudentNumber']); ?></td>
        <td  style="font-size:8px;"> <?php echo $student['strLastname']." ".$student['strFirstname']." ".$student['strMiddlename']; ?></td>        
        <td  style="font-size:8px;"><?php echo $student['strProgramCode']; ?></td>
        <?php if($classlist['intFinalized'] >= 1): ?>
            <td style="font-size:8px;"><?php echo $student['floatMidtermGrade']; ?></td>
        <?php else: ?>
            <td style="font-size:8px;">NGS</td>
        <?php endif; ?>
        <?php if($classlist['intFinalized'] >= 2): ?>
            <td style="font-size:8px;"><?php echo $student['floatFinalGrade']; ?></td>
        <?php else: ?>
            <td style="font-size:8px;">NGS</td>
        <?php endif; ?>        
        <td style="font-size:8px;"><?php echo $student['strRemarks']; ?></td>                
    </tr>
   <?php        
    endforeach; ?>    
</table>
<table>    
    <tr style="line-height:<?php echo $hgt; ?>px">
        <td colspan="7"></td>        
    </tr>  
</table>
<table border="0" cellpadding="2px" style="font-size:8px;border-spacing: 15px 0;">
    <tr>
        <td width="45%">Submitted by:</td>
        <td width="45%">Noted by:</td>      
    </tr>
    <tr style="line-height:20px;">
        <td colspan="4"></td>        
    </tr>  
    <tr style="text-align:center;">
        <td style="border-top:1px solid #333"><strong>Faculty Name</strong></td>
        <td style="border-top:1px solid #333"><strong>Chairperson</strong></td>
    </tr>
    <tr style="line-height:20px;">
        <td colspan="4"></td>        
    </tr>  
    <tr style="text-align:center;">
        <td></td>
        <td style="border-top:1px solid #333">Date</td>
    </tr>
    <tr style="line-height:10px;">
        <td colspan="4"></td>        
    </tr> 
    <tr style="text-align:center;">
        <td>GENERATED BY: <?php echo $user['strFirstname']." ".$user['strLastname']; ?></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    <tr style="text-align:center;">
        <td>RUNDATE&TIME: <?php echo date('Y-m-d h:i a'); ?></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
</table>