<table border="0" cellspacing="0" cellpadding="0" style="color:#333; font-size:9;">            
            <tr>            
                <td colspan = "3" width="100%" style="text-align: center;">             
                    
                </td>
            </tr>        
            <tr>            
                <td colspan = "3" width="100%" style="text-align: center;line-height:1">             
                    <font style="font-family:Calibri Light; font-size: 11;font-weight: bold;">iACADEMY CEBU</font><br />
                    <font style="font-family:Calibri Light; font-size: 10;">Filinvest Cebu Cyberzone Tower 2 Salinas Drive corner W. Geonzon St., Brgy. Apas, Lahug, Cebu City</font><br />             
                </td>           
            </tr>
<table border="0">
    <tr style="line-height:12px;">
        <td style="font-size:10px;text-align:center;"><b>ENLISTED STUDENTS</b></td>
    </tr>
    
    <tr style="line-height:10px;">
        <td style="font-size:10px;text-align:center"><?php echo $sy['enumSem'] . " Term, School Year ".$sy['strYearStart'] . "-" . $sy['strYearEnd']; ?></td>
    </tr>
    <tr style="line-height:10px;">
        <td style="font-size:9px;text-align:center"></td>
    </tr>
</table>
<table style="font-size:9px;">
    <tr style="line-height:12px;text-align:center;">
        <th style="font-size:8px;" width="3%"><b>#</b></th>
        <th style="font-size:8px;" width="11%"><b>Student No.</b></th>
        <th style="font-size:8px;" width="35%"><b>Student Name</b></th>
        <th style="font-size:8px;" width="9%"><b>Course</b></th>
        <th style="font-size:8px;" width="14%"><b>Enrollment Status</b></th>
        <th style="font-size:8px;" width="14%"><b>Date Enlisted</b></th>
        <th style="font-size:8px;" width="12%"><b>Enlisted By</b></th>
    
    </tr>    
    <?php 
    $cs = count($students);
    $i = $snum;
    foreach($students as $st): 
        $name = $st['strLastname'].", ".$st['strFirstname']; 
        $name .= isset($st['strMiddlename'])?", ".$st['strMiddlename']:'';
    ?>
    
    <tr style="line-height:10px;text-align:center;">
        <td style="font-size:8px;" ><?php echo $i; ?></td>
        <td style="font-size:8px;" ><?php echo preg_replace("/[^a-zA-Z0-9]+/", "", $st['strStudentNumber']); ?></td>
        <td style="font-size:8px;text-align:left;"> <?php echo strtoupper($name); ?></td>        
        <td style="font-size:8px;" ><?php echo $st['strProgramCode']; ?> </td>
        <td style="font-size:8px;" ><?php echo $st['reg_info']['type_of_class']."-".$st['reg_info']['enumStudentType']; ?></td>
        <td style="font-size:8px;" ><?php echo $st['date_added'];?></td>
        <td style="font-size:8px;" ><?php echo $st['fusername'] ?></td>           
    </tr>
    <?php   
    $i++;
    endforeach; ?>
</table>
<?php if($nothing_follows): ?>
<table>
    <tr style="line-height:20px;">
        <td style="font-size:10px;text-align:center;color:#555;">----------------------------------------------NOTHING FOLLOWS----------------------------------------------</td>
    </tr>
</table>
<?php else: ?>
<table>
    <tr style="line-height:20px;">
        <td style="font-size:10px;text-align:center;color:#555;">----------------------------------------------NEXT PAGE----------------------------------------------</td>
    </tr>
</table>


<?php endif; ?>
