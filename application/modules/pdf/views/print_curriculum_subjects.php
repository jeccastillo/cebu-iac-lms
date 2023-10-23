<?php 
    $prev_year_sem = '0_0';
    $i = 0;
    $unitsPerSem = 0;
    $totalUnits = 0;
    foreach($curriculum_subjects as $s): 
        $totalUnits += $s['strUnits'];
        $unitsPerSem += $s['strUnits'];
    //echo $prev_year_sem."<br />";
    ?>
    <?php if($prev_year_sem != $s['intYearLevel'].'_'.$s['intSem']): ?>
    
    <table>
        <thead>
            <tr>
                <th colspan="2"><?php echo switch_num($s['intYearLevel'])." Year | ".switch_num($s['intSem'])." Term"; ?>                    
                </th>
            </tr>
            <tr style="line-height:10px">
                <th colspan="2"></th>
            </tr>
            <tr style="font-size:11px;line-height:12px;">
                <th style="width:20%">Course Code</th>
                <th style="width:50%">Course Description</th>
                <th style="width:10%">Lecture Units</th>
                <th style="width:10%">Lab Units</th>
                <th style="width:10%">Total Units</th>                
            </tr>
        </thead>
        <tbody>
            
    <?php 
            $prev_year_sem = $s['intYearLevel'].'_'.$s['intSem'];
            endif; ?>
            
    <tr style="font-size:10px;line-height:12px;">
        <td><?php echo $s['strCode']; ?></td>
        <td><?php echo $s['strDescription']; ?></td>
        <td><?php echo $s['intLectHours']; ?></td>
        <td><?php echo $s['intLab']; ?></td>
        <td><?php echo $s['strUnits']; ?></td>      
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