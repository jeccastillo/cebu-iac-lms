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
        <td style="font-size:9px;text-align:center;"><b>CHED ENROLLMENT LIST REPORT</b></td>
    </tr>
    
    <tr style="line-height:10px;">
        <td style="font-size:9px;text-align:center"><?php echo $sy['enumSem'] . " Term, School Year ".$sy['strYearStart'] . "-" . $sy['strYearEnd']; ?></td>
    </tr>
    <tr style="line-height:10px;">
        <td style="font-size:9px;text-align:center"></td>
    </tr>
</table>
<table border="0">        
    <tr style="line-height:25px;">
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>  
</table>
<table>
    <tr style="line-height:12px;text-align:center;">        
        
        <th style="font-size:8px;" width="2%"><b>#</b></th>
        <th style="font-size:8px;" width="13%"><b>Student No.</b></th>
        <th style="font-size:8px;" width="22%"><b>Student Name</b></th>
        <th style="font-size:8px;" width="5%"><b>Yr</b></th>
        <th style="font-size:8px;" width="10%"><b>Gender</b></th>
        <th style="font-size:8px;" width="13%"><b>Subject Code</b></th>
        <th style="font-size:8px;" width="30%"><b>Descriptive Title</b></th>
        <th style="font-size:8px;" width="5%"><b>Units</b></th>
    
    </tr>
    <tr style="line-height:10px;">
        <td colspan="7"></td>        
    </tr>  
    <?php 
    $i = 1;
    foreach($student['classes'] as $class):
    $name = $student['strLastname'].", ".$student['strFirstname']; 
    $name .= isset($st['strMiddlename'])?", ".$student['strMiddlename']:'';
    
    ?>
    
    <tr style="line-height:12px;text-align:center;">
        <td style="font-size:8px;"><?php echo $i == 1?"1":""; ?></td>
        <td style="font-size:8px;"><?php echo $i == 1?$student['strStudentNumber']:''; ?></td>
        <td style="font-size:8px;text-align:left;"> <?php echo $i == 1?strtoupper($name):''; ?></td>        
        <td style="font-size:8px;"><?php echo $i == 1?$student['intYearLevel']:''; ?></td>
        <td style="font-size:8px;"><?php echo $i == 1?$student['enumGender']:''; ?></td>
        <td style="font-size:8px;text-align:left;"><?php echo $class['strCode']; ?></td>
        <td style="font-size:8px;text-align:left;"><?php echo $class['strDescription']; ?></td>        
        <td style="font-size:8px;"><?php echo $class['strUnits']; ?></td>        
    
    </tr>
   <?php 
    $i++;
    endforeach; ?>
</table>