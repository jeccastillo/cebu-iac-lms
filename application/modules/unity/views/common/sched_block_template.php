<?php foreach($schedule as $sched):
        if(isset($sched['dteStart'])):                                
            $hourdiff = round((strtotime($sched['dteEnd']) - strtotime($sched['dteStart']))/3600, 1);
?>
        <input type="hidden" class="<?php echo $sched['strDay']; ?>"
            value="<?php echo date('gia',strtotime($sched['dteStart'])); ?>"
            href="<?php echo $hourdiff*2; ?>"
            rel="<?php echo $class['strCode']; ?> <?php echo $sched['strRoomCode']; ?>"
            data-section="<?php echo $class['strSection']; ?>">

<?php 
        endif;
    endforeach; 
?>