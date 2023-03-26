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
        
        <th style="font-size:9px;" width="2%"><b>#</b></th>
        <th style="font-size:9px;" width="13%"><b>Student No.</b></th>
        <th style="font-size:9px;" width="22%"><b>Student Name</b></th>
        <th style="font-size:9px;" width="10%"><b>Yr</b></th>
        <th style="font-size:9px;" width="10%"><b>Gender</b></th>
        <th style="font-size:9px;" width="19%"><b>Subject Code</b></th>
        <th style="font-size:9px;" width="19%"><b>Descriptive Title</b></th>
        <th style="font-size:9px;" width="5%"><b>Units</b></th>
    
    </tr>
    <tr style="line-height:10px;">
        <td colspan="7"></td>        
    </tr>  
    <?php 

    
    $name = $student['strLastname'].", ".$student['strFirstname']; 
    $name .= isset($st['strMiddlename'])?", ".$student['strMiddlename']:'';
    ?>
    
    <tr style="line-height:12px;text-align:center;">
        <td style="font-size:9px;">1</td>
        <td style="font-size:9px;"><?php echo $student['strStudentNumber']; ?></td>
        <td style="font-size:8px;text-align:left;"> <?php echo strtoupper($name); ?></td>        
        <td style="font-size:9px;"><?php echo $student['intYearLevel']; ?></td>
        <td style="font-size:9px;"></td>
        <td style="font-size:9px;"></td>
        <td style="font-size:9px;"></td>        
    
    </tr>
   
</table>