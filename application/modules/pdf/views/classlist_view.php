<table border="0">
    <tr style="line-height:40px;">
        <td style="font-size:9px;text-align:left">CCT-AA FORM NO. 5</td>
    </tr>
</table>
<table border="0" cellpadding="0" style="color:#333; font-size:10;">
        <tr style="line-height:25px;">
            <td width="64" align="right"><img src= "<?php echo $img_dir."tagaytayseal.png"; ?>"  width="50" height="50" /></td>
            <td width="400" style="text-align: center; line-height:100%">
                
             <font style="font-family:Calibri Light; font-size: 10;">Republic of the Philippines<br />City of Makati</font><br /><br />
             <font style="font-family:Calibri Light; font-size: 10;font-weight: bold;">Information & Communications Technology Academy</font><br />
			 <font style="font-family:Calibri Light; font-size: 10;">Filinvest Cebu Cyberzone Tower 2 Salinas Drive corner W. Geonzon St., Brgy. Apas, Lahug, Cebu City</font><br />
             <font style="font-family:Calibri Light; font-size: 10;">Telephone No: (046) 483-0470</font><br />
            </td>
            <td width="64" align="left" valign="middle"><img src= "<?php echo $img_dir; ?>%20logo.png"  width="50" height="50"/></td>
        </tr>
</table>
<table border="0">
    <tr style="line-height:20px;">
        <td style="font-size:12px;text-align:center;letter-spacing: 7px"><b>GRADING SHEET</b></td>
    </tr>
    
    <tr style="line-height:20px;">
        <td style="font-size:9px;text-align:center">A.Y. <?php echo $sy['strYearStart'] . "-" . $sy['strYearEnd'] . "-" . $sy['enumSem'] . " Semester" ?></td>
    </tr>
    <tr style="line-height:10px;">
        <td style="font-size:9px;text-align:center"></td>
    </tr>
</table>
<table>
    <tr style="line-height:20px;">
        <td style="font-size:10px;border:1px solid #444;"> Course Code: <?php echo $subject['strCode']; ?></td>
        <td  style="font-size:10px;border:1px solid #444;"> Course Title: <?php echo $subject['strDescription']; ?></td>
    
    </tr>
    <tr style="line-height:20px;">
        <td style="font-size:10px;border:1px solid #444;"> Program, Year &amp; Section: <?php echo $classlist['strSection']; ?></td>
        <!--td style="font-size:10px;border:1px solid #444;"> Semester: <?php echo $sy['enumSem']; ?> Sem</td-->
        <td style="font-size:10px;border:1px solid #444;"> Time: <?php echo $time; ?></td>
    
    </tr>
    <tr style="line-height:20px;">
        <td style="font-size:10px;border:1px solid #444;"> Unit/s: <?php echo $subject['strUnits']; ?></td>
        <!--td style="font-size:10px;border:1px solid #444;"> Academic Year: <?php echo $sy['strYearStart']."-".$sy['strYearEnd']; ?></td-->
        <td  style="font-size:10px;border:1px solid #444;"> Day/s: <?php echo $days; ?> </td>
    
    </tr>
</table>
<table>
    <tr style="line-height:20px;text-align:center;">
        <td style="font-size:10px;border:1px solid #444;" width="5%">No.</td>
        <td style="font-size:10px;border:1px solid #444;" width="35%">Name of Student <br /> Last Name, First Name, M.I.</td>
        <td style="font-size:10px;border:1px solid #444;" width="20%">Student Number </td>
        <td style="font-size:10px;border:1px solid #444;" width="10%">Grade</td>
        <td style="font-size:10px;border:1px solid #444;" width="10%">Credit</td>
        <td style="font-size:10px;border:1px solid #444;" width="20%">Remarks</td>
    
    </tr>
    <?php 
    $cs = count($students);
    $i = $snum;
    foreach($students as $st): ?>
    <tr style="line-height:18px;text-align:center;">
        <td style="font-size:10px;border:1px solid #444;" width="5%"><?php echo $i; ?></td>
        <td style="font-size:9px;border:1px solid #444;text-align:left;" width="35%"> <?php echo $st['strLastname'].", ".$st['strFirstname']; echo isset($st['strMiddlename'][0])?", ".$st['strMiddlename'][0].".":''; ?></td>
        <td style="font-size:10px;border:1px solid #444;" width="20%"><?php echo $st['strStudentNumber']; ?></td>
        <td style="font-size:10px;border:1px solid #444;<?php echo ($st['floatFinalGrade']>=3.5)?'color:#a00':''; ?>" width="10%"><?php echo ($st['strRemarks']=="lack of reqts.")?'inc':number_format($st['floatFinalGrade'], 2); ?></td>
        <td style="font-size:10px;border:1px solid #444;<?php echo ($st['floatFinalGrade']>=3.5)?'color:#a00':''; ?>" width="10%"><?php echo ($st['floatFinalGrade']>=3.5) || $st['strRemarks']=="lack of reqts." ?'0':$subject['strUnits']; ?></td>
        <td style="font-size:10px;border:1px solid #444;<?php echo ($st['floatFinalGrade']>=3.5)?'color:#a00':''; ?>; " width="20%"><?php echo $st['strRemarks']; ?></td>
    
    </tr>
    <?php   
    $i++;
    endforeach; ?>
</table>
<?php if($nothing_follows): ?>
<table>
    <tr style="line-height:20px;">
        <td style="font-size:10px;border:1px solid #444;text-align:center;color:#555;">----------------------------------------------NOTHING FOLLOWS----------------------------------------------</td>
    </tr>
</table>
<?php else: ?>
<table>
    <tr style="line-height:20px;">
        <td style="font-size:10px;border:1px solid #444;text-align:center;color:#555;">----------------------------------------------NEXT PAGE----------------------------------------------</td>
    </tr>
</table>


<?php endif; ?>
