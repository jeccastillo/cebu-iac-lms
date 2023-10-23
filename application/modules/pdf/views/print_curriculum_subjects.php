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
                <th style="" colspan="2">
                    <?php echo switch_num($s['intYearLevel'])." Year | ".switch_num($s['intSem'])." Term"; ?>
                    
                </th>
            </tr>
            <tr>
                <th>Course Code</th>
                <th>Course Description</th>
                <th>Lecture Units</th>
                <th>Lab Units</th>
                <th>Total Units</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            
    <?php 
            $prev_year_sem = $s['intYearLevel'].'_'.$s['intSem'];
            endif; ?>
            
    <tr>
        <td><a target="_blank" href="<?php echo base_url(); ?>subject/subject_viewer/<?php echo $s['intSubjectID']; ?>"><?php echo $s['strCode']; ?></a></td>
        <td><?php echo $s['strDescription']; ?></td>
        <td><?php echo $s['intLectHours']; ?></td>
        <td><?php echo $s['intLab']; ?></td>
        <td><?php echo $s['strUnits']; ?></td>
        <td>
            <a rel="<?php echo $s['intID']; ?>" class="btn btn-danger remove-subject-curriculum" href="#">Remove</a>
        </td>
            </tr>
            <?php if($prev_year_sem != $s['intYearLevel'].'_'.$s['intSem']):
            
            ?>
<!--
            <tr>
                <td colspan="3">Units <?php echo $unitsPerSem; ?></td>
            </tr>
-->
            <?php
            $unitsPerSem = 0;
            endif; ?>
<?php if($prev_year_sem != $s['intYearLevel'].'_'.$s['intSem'] || count($curriculum_subjects) == $i+1): ?>   
    
    <tr>
        <th><?php echo "TOTAL UNITS : " .  $totalUnits; ?></th>
    </tr>
    </tbody>
</table>
    <?php endif; ?>
<?php 
$i++;
endforeach; ?>