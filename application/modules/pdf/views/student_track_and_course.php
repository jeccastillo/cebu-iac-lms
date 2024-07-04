<table border="0" cellspacing="0" cellpadding="0" style="color:#333; font-size:9;">   
    <tr style="font-weight:bold;">        
        <td style="font-size:14px; text-align:center">Student List with Track and College Course - <?php echo $year_level;?></td>
    </tr>
</table>
<br><br>
<table>
    <tr style="font-size:12px; font-weight:bold; text-align:center;">
        <td style="border: 1px solid #000000" width="5%">NO.</td>
        <td style="border: 1px solid #000000" width="15%">STUDENT NO.</td>
        <td style="border: 1px solid #000000" width="20%">LAST NAME</td>
        <td style="border: 1px solid #000000" width="20%">FIRST NAME</td>
        <td style="border: 1px solid #000000" width="20%">MIDDLE NAME</td>
        <td style="border: 1px solid #000000" width="20%">ENROLLED COURSE</td>
    </tr>

    <?php foreach($students as $index => $student):?>
        <tr style="font-size 9px; text-align:center;">
            <td style="border: 1px solid #000000" width="5%"><?php echo $index + 1?></td>
            <td style="border: 1px solid #000000" width="15%"><?php echo str_replace("-", "", $student['strStudentNumber'])?></td>
            <td style="border: 1px solid #000000" width="20%"><?php echo ucfirst($student['strLastname'])?></td>
            <td style="border: 1px solid #000000" width="20%"><?php echo ucfirst($student['strFirstname'])?></td>
            <td style="border: 1px solid #000000" width="20%"><?php echo ucfirst($student['strMiddlename'])?></td>
            <td style="border: 1px solid #000000" width="20%"><?php echo $student['strProgramCode']?></td>
        </tr>
    <?php endforeach; ?>
</table>