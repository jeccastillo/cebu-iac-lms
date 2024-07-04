<table border="0" cellspacing="0" cellpadding="0" style="color:#333; font-size:9;">   
    <tr style="font-weight:bold;">        
        <td style="font-size:14px; text-align:center">SHS GWA Rank - <?php echo $year_level;?></td>
    </tr>
</table>
<br><br>
<table>
    <tr style="font-size:12px; font-weight:bold; text-align:center;">
        <td style="border: 1px solid #000000" width="5%">RANK</td>
        <td style="border: 1px solid #000000" width="12%">STUDENT NO.</td>
        <td style="border: 1px solid #000000" width="20%">LAST NAME</td>
        <td style="border: 1px solid #000000" width="20%">FIRST NAME</td>
        <td style="border: 1px solid #000000" width="20%">MIDDLE NAME</td>
        <td style="border: 1px solid #000000" width="13%">TRACK/STRAND</td>
        <td style="border: 1px solid #000000" width="6%">GWA</td>
        <td style="border: 1px solid #000000" width="4%">GL</td>
    </tr>

    <?php foreach($students as $index => $student):?>
        <tr style="font-size 9px; text-align:center;">
            <td style="border: 1px solid #000000" width="5%"><?php echo $index + 1?></td>
            <td style="border: 1px solid #000000" width="12%"><?php echo str_replace("-", "", $student['student_number'])?></td>
            <td style="border: 1px solid #000000" width="20%"><?php echo ucfirst($student['last_name'])?></td>
            <td style="border: 1px solid #000000" width="20%"><?php echo ucfirst($student['first_name'])?></td>
            <td style="border: 1px solid #000000" width="20%"><?php echo ucfirst($student['middle_name'])?></td>
            <td style="border: 1px solid #000000" width="13%"><?php echo $student['track']?></td>
            <td style="border: 1px solid #000000" width="6%"><?php echo number_format(round($student['gwa'],2),2)?></td>
            <td style="border: 1px solid #000000" width="4%"><?php echo $student['year_level']?></td>
        </tr>
    <?php endforeach; ?> 
</table>