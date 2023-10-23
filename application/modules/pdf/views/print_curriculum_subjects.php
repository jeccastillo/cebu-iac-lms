<?php 
    $prev_year_sem = '0_0';
    $i = 0;
    $unitsPerSem = 0;
    $totalUnits = 0;
    ?>
    <h3 style="text-align:center;"><?php echo $item['strName']; ?></h3>
    <hr />
    <?php
    foreach($curriculum_subjects as $s): 
        $totalUnits += $s['strUnits'];
        $unitsPerSem += $s['strUnits'];
    //echo $prev_year_sem."<br />";
    ?>
    <?php if($prev_year_sem != $s['intYearLevel'].'_'.$s['intSem']): ?>
    
    <table>
        <thead>
            <tr style="line-height:15px">
                <th colspan="2"></th>
            </tr>
            <tr>
                <th colspan="2"><?php echo switch_num($s['intYearLevel'])." Year | ".switch_num($s['intSem'])." Term"; ?>                    
                </th>
            </tr>
            <tr style="line-height:10px">
                <th colspan="2"></th>
            </tr>
            <tr style="font-size:11px;line-height:12px;">
                <th style="width:15%">Course Code</th>
                <th style="width:50%">Course Description</th>
                <th style="width:10%">Lect Units</th>
                <th style="width:10%">Lab Units</th>
                <th style="width:15%">Total Units</th>                
            </tr>
            <tr style="line-height:10px">
                <th colspan="2"></th>
            </tr>
        </thead>
        <tbody>
            
    <?php 
            $prev_year_sem = $s['intYearLevel'].'_'.$s['intSem'];
            endif; ?>
            
    <tr style="font-size:10px;line-height:12px;">
        <td style="width:15%"><?php echo $s['strCode']; ?></td>
        <td style="width:50%;"><?php echo $s['strDescription']; ?></td>
        <td style="width:10%;text-align:center;"><?php echo $s['intLectHours']; ?></td>
        <td style="width:10%;text-align:center;"><?php echo $s['intLab']; ?></td>
        <td style="width:15%;text-align:center;"><?php echo $s['strUnits']; ?></td>      
            </tr>
            <?php if($prev_year_sem != $s['intYearLevel'].'_'.$s['intSem']):            
            $unitsPerSem = 0;
            endif;
            if($prev_year_sem != $s['intYearLevel'].'_'.$s['intSem'] || count($curriculum_subjects) == $i+1): ?>       
    <tr>
        <th><?php echo "TOTAL UNITS : " .  $totalUnits; ?></th>
    </tr>
    </tbody>
</table>
    <?php endif; ?>
<?php 
$i++;
endforeach; ?>