<table border="0" cellspacing="0" cellpadding="0" style="color:#333; font-size:9;">   
    <tr style="font-weight:bold;">        
        <td style="font-size:14px; text-align:center">iACADEMY</td>
    </tr>
    <tr>        
        <td style="font-size:10; text-align:center"><?php echo $campus_address;?></td>
    </tr>
    <tr><td></td></tr>
    <tr style="font-weight:bold;">        
        <td style="font-size:11; text-align:center">NUMBER OF STUDENTS ENROLLED (By Grade Level)</td>
    </tr>
    <tr>
        <td style="font-size:9; text-align:center"><?php echo $sem['enumSem'] . " Term SY " . $sem['strYearStart'] . "-" . $sem['strYearEnd']; ?></td>
    </tr>
    <tr>
        <td style="font-size:9; text-align:right"><?php echo date('F d, Y h:i A'); ?></td>
    </tr>
</table>
<br><br>
<table>
    <tr style="font-size:12px; font-weight:bold; text-align:center;">
        <td style="text-align:left" width="55%">Course</td>
        <td width="15%">Grade 11</td>
        <td width="15%">Grade 12</td>
        <td width="15%">Total</td>
    </tr>

    <tr style="line-height:10px;">
        <th style="border-top:1px solid #333;" colspan="8"></th>
    </tr>
    <?php 
        foreach($enrollment as $item):    
        $major = ($item['strMajor'] != "None" && $item['strMajor'] != "")?'Major in '.$item['strMajor']:'';
    ?>
        <tr style="font-size:10px">
            <td><?php echo $item['strProgramDescription'] . ' ' . $major; ?></td>
            <td style="text-align:center"><?php echo $item['grade11'] ?></td>
            <td style="text-align:center"><?php echo $item['grade12'] ?></td>
            <td style="text-align:center"><?php echo ($item['grade11'] + $item['grade12']); ?></td>
        </tr>
    <?php endforeach; ?>
    <tr style="font-size:12px; font-weight:bold; text-align:center;">
        <td style="text-align:right">Total:</td>
        <td><?php echo $grade11_total; ?></td>
        <td><?php echo $grade12_total; ?></td>
        <td><?php echo $total_enrolled; ?></td>
    </tr>
    <tr style="font-size:12px; font-weight:bold;">
        <td style="text-align:right">No Grade Level:</td>
        <td></td>
        <td></td>
        <td style="text-align:center"><?php echo $no_grade_level; ?></td>
    </tr>
    <tr style="font-size:3px">
        <th></th>
        <th style="border-bottom:1px solid #333;" colspan="3"></th>
    </tr>
    <tr style="font-size:12px; font-weight:bold;">
        <td style="text-align:right">Grand Total:</td>
        <td></td>
        <td></td>
        <td style="text-align:center"><?php echo ($total_enrolled - $no_grade_level); ?></td>
    </tr>
</table>