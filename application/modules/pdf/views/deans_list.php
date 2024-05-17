<table style="page-break-inside:avoid">
    <tr>
        <td><b>1st HONORS</b></td>
    </tr>
    <tr style="text-align:center; line-height:15px; font-weight: bold;">
        <td style="font-size:13px;border:1px solid #000000;" width="15%">Student Number</td>
        <td style="font-size:13px;border:1px solid #000000;" width="20%">Last Name</td>
        <td style="font-size:13px;border:1px solid #000000;" width="20%">First Name</td>
        <td style="font-size:13px;border:1px solid #000000;" width="20%">Middle Name</td>
        <td style="font-size:13px;border:1px solid #000000;" width="15%">Course</td>
        <td style="font-size:13px;border:1px solid #000000;" width="10%">GWA</td>
    </tr>

    <?php foreach($list_1st_honor as $student):?>
        <tr style="font-size:12px; text-align:center;">
            <td style="border: 1px solid #000000"><?php echo str_replace("-", "", $student['strStudentNumber'])?></td>
            <td style="border: 1px solid #000000"><?php echo ucfirst($student['strLastname'])?></td>
            <td style="border: 1px solid #000000"><?php echo ucfirst($student['strFirstname'])?></td>
            <td style="border: 1px solid #000000"><?php echo ucfirst($student['strMiddlename'])?></td>
            <td style="border: 1px solid #000000"><?php echo $student['strProgramCode'] ?></td>
            <td style="border: 1px solid #000000"><?php echo $student['gwa'] ?></td>
        </tr>
    <?php endforeach; ?>
</table>
<br><br>
<table style="page-break-inside:avoid">
    <tr>
        <td><b>2nd HONORS</b></td>
    </tr>
    <tr style="text-align:center; line-height:15px; font-weight: bold;">
        <td style="font-size:13px;border:1px solid #000000;" width="15%">Student Number</td>
        <td style="font-size:13px;border:1px solid #000000;" width="20%">Last Name</td>
        <td style="font-size:13px;border:1px solid #000000;" width="20%">First Name</td>
        <td style="font-size:13px;border:1px solid #000000;" width="20%">Middle Name</td>
        <td style="font-size:13px;border:1px solid #000000;" width="15%">Course</td>
        <td style="font-size:13px;border:1px solid #000000;" width="10%">GWA</td>
    </tr>

    <?php foreach($list_2nd_honor as $student):?>
        <tr style="font-size:12px; text-align:center;">
            <td style="border: 1px solid #000000"><?php echo str_replace("-", "", $student['strStudentNumber'])?></td>
            <td style="border: 1px solid #000000"><?php echo ucfirst($student['strLastname'])?></td>
            <td style="border: 1px solid #000000"><?php echo ucfirst($student['strFirstname'])?></td>
            <td style="border: 1px solid #000000"><?php echo ucfirst($student['strMiddlename'])?></td>
            <td style="border: 1px solid #000000"><?php echo $student['strProgramCode'] ?></td>
            <td style="border: 1px solid #000000"><?php echo $student['gwa'] ?></td>
        </tr>
    <?php endforeach; ?>
</table>