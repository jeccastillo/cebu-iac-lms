<table border="0" cellspacing="0" cellpadding="0" style="color:#333; font-size:9;">            
            <tr>            
                <td colspan = "3" width="100%" style="text-align: center;">             
                    
                </td>
            </tr>        
            <tr>            
                <td colspan = "3" width="100%" style="text-align: center;line-height:1">             
                    <font style="font-family:Calibri Light; font-size: 11;font-weight: bold;">iACADEMY CEBU</font><br />
                    <font style="font-family:Calibri Light; font-size: 10;">Filinvest Cebu Cyberzone Tower 2 Salinas Drive corner W. Geonzon St., Brgy. Apas, Lahug, Cebu City</font><br />             
                    <font style="font-family:Calibri Light; font-size: 10;">+63 32 520 4888</font><br />                                 
                </td>           
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
<table>    
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
$num = $count_start;
foreach($classlists as $classlist): ?>

    <tr style="line-height:12px;">        
        <td style="font-size:8px;"><?php echo $classlist['subjectDescription']; ?></td>
        <td style="font-size:8px;"> <?php echo $classlist['strCode']; ?></td>        
        <td style="font-size:8px;"><?php echo $classlist['strClassName'].$classlist['year'].$classlist['strSection']." ".$classlist['sub_section']; ?></td>
        <td style="font-size:8px;"><?php echo $classlist['sched_day']; ?></td>
        <td style="font-size:8px;"><?php echo $classlist['sched_time']; ?></td>
        <td style="font-size:8px;"><?php echo $classlist['sched_room']; ?></td>        
        <td style="font-size:8px;"><?php echo $classlist['strUnits']; ?></td>            
        <td style="font-size:8px;"><?php echo $classlist['slots_taken_enrolled']; ?></td>
    </tr>
   <?php 
    $i++;    
    endforeach; ?>
</table>
