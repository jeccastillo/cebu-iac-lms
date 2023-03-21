<table border="0" cellspacing="0" cellpadding="0" style="color:#333; font-size:9;">            
            <tr>            
                <td colspan = "3" width="100%" style="text-align: center;">             
                    
                </td>
            </tr>        
            <tr>            
                <td colspan = "3" width="100%" style="text-align: center;line-height:1">             
                    <font style="font-family:Calibri Light; font-size: 14;font-weight: bold;">iACADEMY CEBU</font><br /><br />
                    <font style="font-family:Calibri Light; font-size: 10;">Filinvest Cebu Cyberzone Tower 2 Salinas Drive corner W. Geonzon St., Brgy. Apas, Lahug, Cebu City</font><br />             
                </td>           
            </tr>
<table border="0">
    <tr style="line-height:12px;">
        <td style="font-size:12px;text-align:center;"><b>OFFICIAL CLASSLIST</b></td>
    </tr>
    
    <tr style="line-height:10px;">
        <td style="font-size:10px;text-align:center">A.Y. <?php echo $sy['strYearStart'] . "-" . $sy['strYearEnd'] . "-" . $sy['enumSem'] . " Semester" ?></td>
    </tr>
    <tr style="line-height:10px;">
        <td style="font-size:9px;text-align:center"></td>
    </tr>
</table>
<table border="0">    
    <tr style="line-height:12px;">
        <td style="font-size:10px;width:15%"><b>Course:</b></td>
        <td style="font-size:10px;width:35%"><?php echo $classlist['strClassName']; ?></td>
        <td style="font-size:10px;width:15%"><b>Year:</b></td>
        <td style="font-size:10px;width:15%"><?php echo $classlist['year']; ?></td>
        <td style="font-size:10px;width:10%"><b>Section:</b></td>
        <td style="font-size:10px;width:10%"><?php echo $classlist['strClassName'].$classlist['year'].$classlist['strSection']." ".$classlist['sub_section']; ?></td>
    </tr>
    <tr style="line-height:12px;">
        <td style="font-size:10px;"><b>Subject:</b></td>
        <td style="font-size:10px;"><?php echo $subject['strCode']; ?></td>
        <td style="font-size:10px;"><b>Descriptive Title:</b></td>    
        <td colspan="3" style="font-size:10px;"><?php echo $subject['strDescription']; ?></td>
        
    </tr>
    <tr style="line-height:12px;">
        <td style="font-size:10px;"><b>Time/Day/Room:</b></td>    
        <td style="font-size:10px;"><?php echo $schedule; ?></td>
        <td style="font-size:10px;"><b>Instructor:</b></td>        
        <td colspan="3" style="font-size:10px;"><?php echo $faculty['strFirstname']." ".$faculty['strLastname']; ?></td>
        <!--td style="font-size:10px;border:1px solid #444;"> Semester: <?php echo $sy['enumSem']; ?> Sem</td-->            
    </tr>  
    <tr style="line-height:12px;">
    </tr>  
</table>
<table>
    <tr style="line-height:20px;text-align:center;">
        <td style="font-size:10px;" width="5%">No.</td>
        <td style="font-size:10px;" width="35%">Name of Student <br /> Last Name, First Name, M.I.</td>
        <td style="font-size:10px;" width="20%">Student Number </td>
        <td style="font-size:10px;" width="10%">Grade</td>
        <td style="font-size:10px;" width="10%">Credit</td>
        <td style="font-size:10px;" width="20%">Remarks</td>
    
    </tr>
    <?php 
    $cs = count($students);
    $i = $snum;
    foreach($students as $st): ?>
    <tr style="line-height:18px;text-align:center;">
        <td style="font-size:10px;" width="5%"><?php echo $i; ?></td>
        <td style="font-size:9px;text-align:left;" width="35%"> <?php echo $st['strLastname'].", ".$st['strFirstname']; echo isset($st['strMiddlename'][0])?", ".$st['strMiddlename'][0].".":''; ?></td>
        <td style="font-size:10px;" width="20%"><?php echo $st['strStudentNumber']; ?></td>
        <td style="font-size:10px;"><?php echo ($st['floatFinalGrade']>=3.5)?'color:#a00':''; ?>" width="10%"><?php echo ($st['strRemarks']=="lack of reqts.")?'inc':number_format($st['floatFinalGrade'], 2); ?></td>
        <td style="font-size:10px;"><?php echo ($st['floatFinalGrade']>=3.5)?'color:#a00':''; ?>" width="10%"><?php echo ($st['floatFinalGrade']>=3.5) || $st['strRemarks']=="lack of reqts." ?'0':$subject['strUnits']; ?></td>
        <td style="font-size:10px;"><?php echo ($st['floatFinalGrade']>=3.5)?'color:#a00':''; ?>; " width="20%"><?php echo $st['strRemarks']; ?></td>
    
    </tr>
    <?php   
    $i++;
    endforeach; ?>
</table>
<?php if($nothing_follows): ?>
<table>
    <tr style="line-height:20px;">
        <td style="font-size:10px;text-align:center;color:#555;">----------------------------------------------NOTHING FOLLOWS----------------------------------------------</td>
    </tr>
</table>
<?php else: ?>
<table>
    <tr style="line-height:20px;">
        <td style="font-size:10px;text-align:center;color:#555;">----------------------------------------------NEXT PAGE----------------------------------------------</td>
    </tr>
</table>


<?php endif; ?>
