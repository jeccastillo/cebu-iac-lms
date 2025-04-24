<table border="0" cellspacing="0" cellpadding="0" style="color:#333; font-size:9;">            
            <tr>            
                <td colspan = "3" width="100%" style="text-align: center;">             
                    
                </td>
            </tr>        
            <tr>            
                <td colspan = "3" width="100%" style="text-align: center;line-height:1">             
                    <font style="font-family:Calibri Light; font-size: 11;font-weight: bold;">iACADEMY <?php echo strtoupper($campus); ?></font><br />
                    <font style="font-family:Calibri Light; font-size: 10;"><?php echo $campus_address;?></font><br />             
                </td>           
            </tr>
<table border="0">
    <tr style="line-height:12px;">
        <td style="font-size:9px;text-align:center;"><b>OFFICIAL CLASSLIST</b></td>
    </tr>
    
    <tr style="line-height:10px;">
        <td style="font-size:9px;text-align:center"><?php echo $sy['enumSem'] . " Term, School Year ".$sy['strYearStart'] . "-" . $sy['strYearEnd']; ?></td>
    </tr>
    <tr style="line-height:10px;">
        <td style="font-size:9px;text-align:center"></td>
    </tr>
</table>
<table border="0">    
    <tr style="line-height:12px;">
        <td style="font-size:9px;width:15%"><b>Course:</b></td>
        <td style="font-size:9px;width:35%"><?php echo $classlist['strClassName']; ?></td>
        <td style="font-size:9px;width:15%"><b>Year:</b></td>
        <td style="font-size:9px;width:15%"><?php echo $classlist['year']; ?></td>
        <td style="font-size:9px;width:10%"><b>Section:</b></td>
        <td style="font-size:9px;width:10%"><?php echo $classlist['strClassName'].$classlist['year'].$classlist['strSection']." ".$classlist['sub_section']; ?></td>
    </tr>
    <tr style="line-height:12px;">
        <td style="font-size:9px;"><b>Subject:</b></td>
        <td style="font-size:9px;"><?php echo $subject['strCode']; ?></td>
        <td style="font-size:9px;"><b>Descriptive Title:</b></td>    
        <td colspan="3" style="font-size:9px;"><?php echo $subject['strDescription']; ?></td>
        
    </tr>
    <tr style="line-height:12px;">
        <td style="font-size:9px;"><b>Time/Day/Room:</b></td>    
        <td style="font-size:9px;"><?php echo $schedule; ?></td>
        <td style="font-size:9px;"><b>Instructor:</b></td>        
        <td colspan="3" style="font-size:9px;"><?php echo $faculty['strFirstname']." ".$faculty['strLastname']; ?></td>                
    </tr>  
    <tr style="line-height:25px;">
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>  
</table>
<table>
    <tr style="line-height:12px;text-align:center;">        
        
        <th style="font-size:9px;" width="2%"><b>#</b></th>
        <th style="font-size:9px;" width="13%"><b>Student No.</b></th>
        <th style="font-size:9px;" width="22%"><b>Student Name</b></th>
        <th style="font-size:9px;" width="10%"><b>Course</b></th>
        <th style="font-size:9px;" width="15%"><b>Enrollment Status</b></th>
        <th style="font-size:9px;" width="19%"><b>Date Enrolled</b></th>
        <th style="font-size:9px;" width="19%"><b>Date Added</b></th>
    
    </tr>
    <tr style="line-height:10px;">
        <td colspan="7"></td>        
    </tr>  
    <?php 
    $cs = count($students);
    $i = $snum;
    foreach($students as $st): 
        $name = $st['strLastname'].", ".$st['strFirstname']; 
        $name .= isset($st['strMiddlename'])?", ".$st['strMiddlename']:'';
    ?>
    
    <tr style="line-height:12px;text-align:center;">
        <td style="font-size:9px;"><?php echo $i; ?></td>
        <td style="font-size:9px;"><?php echo preg_replace("/[^a-zA-Z0-9]+/", "", $st['strStudentNumber']); ?></td>
        <td style="font-size:8px;text-align:left;"> <?php echo strtoupper($name); ?></td>        
        <td style="font-size:9px;"><?php echo $st['strProgramCode']; ?></td>
        <td style="font-size:9px;"><?php echo $st['reg_info']['type_of_class']."-".$st['reg_info']['enumStudentType']; ?></td>
        <td style="font-size:9px;"><?php echo date("Y-m-d h:ia",strtotime($st['reg_info']['dteRegistered']));?></td>
        <td style="font-size:9px;"><?php echo date("Y-m-d h:ia",strtotime($st['date_added'])); ?></td>
        <!-- <td style="font-size:9px;<?php echo ($st['floatFinalGrade']>=3.5)?'color:#a00':''; ?>" width="10%"><?php echo ($st['strRemarks']=="lack of reqts.")?'inc':number_format($st['floatFinalGrade'], 2); ?></td>
        <td style="font-size:9px;<?php echo ($st['floatFinalGrade']>=3.5)?'color:#a00':''; ?>" width="10%"><?php echo ($st['floatFinalGrade']>=3.5) || $st['strRemarks']=="lack of reqts." ?'0':$subject['strUnits']; ?></td>
        <td style="font-size:9px;<?php echo ($st['floatFinalGrade']>=3.5)?'color:#a00':''; ?>; " width="20%"><?php echo $st['strRemarks']; ?></td> -->
    
    </tr>
    <?php   
    $i++;
    endforeach; ?>
</table>
<?php if($nothing_follows): ?>
<table>
    <tr style="line-height:20px;">
        <td style="font-size:9px;text-align:center;color:#555;">----------------------------------------------NOTHING FOLLOWS----------------------------------------------</td>
    </tr>
</table>
<?php else: ?>
<table>
    <tr style="line-height:20px;">
        <td style="font-size:9px;text-align:center;color:#555;">----------------------------------------------NEXT PAGE----------------------------------------------</td>
    </tr>
</table>


<?php endif; ?>
