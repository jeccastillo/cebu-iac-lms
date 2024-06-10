<table border="0" cellspacing="0" cellpadding="0" style="color:#333; font-size:9;">   
    <tr style="font-weight:bold;">        
        <td style="font-size:14px; text-align:center">LIST OF ENHANCED</td>
    </tr>
</table>
<br><br>
<!-- <table style="page-break-inside:avoid"> -->
<table>
    <tr style="font-size:12px; font-weight:bold; text-align:center;">
        <td style="border: 1px solid #000000">STUDENT NUMBER</td>
        <td style="border: 1px solid #000000">STUDENT NAME</td>
        <td style="border: 1px solid #000000">COURSE</td>
    </tr>

    <?php foreach($students as $index => $student):?>
        <tr style="font-size 9px; text-align:center;">
            <td style="border: 1px solid #000000"><?php echo str_replace("-", "", $student['strStudentNumber'])?></td>
            <td style="border: 1px solid #000000"><?php echo ucfirst($student['strLastname'] . ', ' . ucfirst($student['strFirstname']) . ' ' . ucfirst($student['strMiddlename']))?></td>
            <td style="border: 1px solid #000000"><?php echo ucfirst($student['course']['strProgramCode'])?></td>
        </tr>
    <?php endforeach; ?>
</table>