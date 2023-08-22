<table border="0" cellspacing="0" cellpadding="0" style="color:#333; font-size:9;">            
            <tr>            
                <td colspan = "3" width="100%" style="text-align: center;">             
                    
                </td>
            </tr>            
            <tr>
                <td colspan="3" align="center" style="text-align:center;vertical-align: bottom"><img src= "https://i.ibb.co/xL1WcSm/iac-cebu.png"  width="150" /></td>        
            </tr>            
<table border="0">    
    <tr style="line-height:12px;">
        <td style="font-size:9px;text-align:center;letter-spacing:5px;"><b>FACULTY LOAD <FORM:post></FORM:post></b></td>
    </tr>
    
    <tr style="line-height:10px;">
        <td style="font-size:9px;text-align:center"><?php echo  $sem['enumSem']." Term, ".$sem['strYearStart'] . "-" . $sem['strYearEnd']; ?></td>
    </tr>
    <tr style="line-height:10px;">
        <td style="font-size:9px;text-align:center"></td>
    </tr>
</table>
<table>
    
    <tr style="line-height:10px;">
        <td colspan="7"></td>        
    </tr>  
</table>
<table border="1">    
    <tr style="line-height:16px;text-align:center;">                        
        <th style="font-size:8px;border-top:1px dashed #333;border-bottom:1px dashed #333;" width="30%"><b>Subject(s)</b></th>
        <th style="font-size:8px;border-top:1px dashed #333;border-bottom:1px dashed #333;" width="10%"><b>Subject Code</b></th>
        <th style="font-size:8px;border-top:1px dashed #333;border-bottom:1px dashed #333;" width="10%"><b>Section</b></th>
        <th style="font-size:8px;border-top:1px dashed #333;border-bottom:1px dashed #333;" width="5%"><b>Days</b></th>
        <th style="font-size:8px;border-top:1px dashed #333;border-bottom:1px dashed #333;" width="10%"><b>Time</b></th>
        <th style="font-size:8px;border-top:1px dashed #333;border-bottom:1px dashed #333;" width="10%"><b>Room</b></th>
        <th style="font-size:8px;border-top:1px dashed #333;border-bottom:1px dashed #333;" width="5%"><b>Units</b></th>
        <th style="font-size:8px;border-top:1px dashed #333;border-bottom:1px dashed #333;" width="20%"><b>No. of Students</b></th>
    </tr>
<?php 
foreach($classlists as $classlist): ?>

    <tr style="line-height:12px;">        
        <td cellpadding="2px" style="font-size:8px;"><?php echo $classlist['subjectDescription']; ?></td>
        <td cellpadding="2px" style="font-size:8px;"> <?php echo $classlist['strCode']; ?></td>        
        <td cellpadding="2px" style="font-size:8px;text-align:center;"><?php echo $classlist['strClassName'].$classlist['year'].$classlist['strSection']." ".$classlist['sub_section']; ?></td>
        <td cellpadding="2px" style="font-size:8px;text-align:center;"><?php echo $classlist['sched_day']; ?></td>
        <td cellpadding="2px" style="font-size:8px;text-align:center;"><?php echo $classlist['sched_time']; ?></td>
        <td cellpadding="2px" style="font-size:8px;text-align:center;"><?php echo $classlist['sched_room']; ?></td>        
        <td cellpadding="2px" style="font-size:8px;text-align:center;"><?php echo $classlist['strUnits']; ?></td>            
        <td cellpadding="2px" style="font-size:8px;text-align:center;"><?php echo $classlist['slots_taken_enrolled']; ?></td>
    </tr>
   <?php        
    endforeach; ?>
</table>
