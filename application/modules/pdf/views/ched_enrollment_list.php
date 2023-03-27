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
        <td style="font-size:9px;text-align:center;">OFFICE OF THE REGISTRAR</td>
    </tr>
    <tr style="line-height:12px;">
        <td style="font-size:9px;text-align:center;"><b>CHED ENROLLMENT LIST REPORT</b></td>
    </tr>
    
    <tr style="line-height:10px;">
        <td style="font-size:9px;text-align:center"><?php echo  "ACADEMIC ".$sy['strYearStart'] . "-" . $sy['strYearEnd'].", ".strtoupper(switch_num_word($sy['enumSem'])) . " TERM"; ?></td>
    </tr>
    <tr style="line-height:10px;">
        <td style="font-size:9px;text-align:center"></td>
    </tr>
</table>
<table border="0">     
    <tr style="line-height:12px;">
        <td style="font-size:8px;width:15%"><b>Institutional Identifier:</b></td>
        <td style="font-size:8px;width:35%">11315</td>
        <td style="font-size:8px;width:15%"><b>Semester/ Trimester:</b></td>
        <td style="font-size:8px;width:35%"><?php echo switch_num_word($sy['enumSem'])." Term" ?></td>        
    </tr>
    <tr style="line-height:12px;">
        <td style="font-size:8px;"><b>Name of Institution:</b></td>
        <td style="font-size:8px;">iACADEMY Cebu</td>
        <td style="font-size:8px;"><b>Course/Program:</b></td>    
        <td colspan="3" style="font-size:8px;"><?php echo !empty($students)?$students[0]['strProgramDescription']:''; ?></td>                
    </tr>  
    <tr>
        <td style="font-size:8px;"><b>Address:</b></td>
        <td style="font-size:8px;">Filinvest Cebu Cyberzone Tower 2 Salinas Drive corner W. Geonzon St., Brgy. Apas, Lahug, Cebu City</td>
        <td style="font-size:8px;"><b>Year Level:</b></td>    
        <td colspan="3" style="font-size:8px;"><?php echo !empty($students)?$students[0]['intYearLevel']:''; ?></td>
    </tr>
    <tr>
        <td style="font-size:8px;"><b>Tel No.:</b></td>
        <td style="font-size:8px;">+63 32 520 4888</td>
        <td style="font-size:8px;"></td>    
        <td colspan="3" style="font-size:9px;"></td>
    </tr>
    <tr style="line-height:25px;">
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>  
</table>
<?php 
$num = 1;
foreach($students as $student): ?>
<table>
    <tr style="line-height:16px;text-align:center;">        
        
        <th style="font-size:8px;border-top:1px dashed #333;border-bottom:1px dashed #333;" width="2%"><b>#</b></th>
        <th style="font-size:8px;border-top:1px dashed #333;border-bottom:1px dashed #333;" width="13%"><b>Student No.</b></th>
        <th style="font-size:8px;border-top:1px dashed #333;border-bottom:1px dashed #333;" width="22%"><b>Student Name</b></th>
        <th style="font-size:8px;border-top:1px dashed #333;border-bottom:1px dashed #333;" width="5%"><b>Yr</b></th>
        <th style="font-size:8px;border-top:1px dashed #333;border-bottom:1px dashed #333;" width="10%"><b>Gender</b></th>
        <th style="font-size:8px;border-top:1px dashed #333;border-bottom:1px dashed #333;" width="13%"><b>Subject Code</b></th>
        <th style="font-size:8px;border-top:1px dashed #333;border-bottom:1px dashed #333;" width="30%"><b>Descriptive Title</b></th>
        <th style="font-size:8px;border-top:1px dashed #333;border-bottom:1px dashed #333;" width="5%"><b>Units</b></th>
    
    </tr>
    <tr style="line-height:10px;">
        <td colspan="7"></td>        
    </tr>  
    <?php 
    $i = 1;
    $total_units = 0;    
    foreach($student['classes'] as $class):
    $name = $student['strLastname'].", ".$student['strFirstname']; 
    $name .= isset($st['strMiddlename'])?", ".$student['strMiddlename']:'';
    
    ?>
    
    <tr style="line-height:12px;text-align:center;">
        <td style="font-size:8px;"><?php echo $i == 1?$num:""; ?></td>
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
    $total_units += $class['strUnits'];
    endforeach; ?>
    <tr style="line-height:6px;text-align:center;">
        <td style="font-size:8px;"></td>
        <td style="font-size:8px;"></td>
        <td style="font-size:8px;text-align:left;"></td>        
        <td style="font-size:8px;"></td>
        <td style="font-size:8px;"></td>
        <td style="font-size:8px;text-align:left;"></td>
        <td style="font-size:8px;">&nbsp;</td>        
        <td style="font-size:8px;">&nbsp;</td>            
    </tr>
    <tr style="line-height:8px;text-align:center;">
        <td style="font-size:8px;"></td>
        <td style="font-size:8px;"></td>
        <td style="font-size:8px;text-align:left;"></td>        
        <td style="font-size:8px;"></td>
        <td style="font-size:8px;"></td>
        <td style="font-size:8px;text-align:left;"></td>
        <td style="font-size:8px;border-top:1px dashed #333;border-bottom:1px dashed #333;">&nbsp;</td>        
        <td style="font-size:8px;border-top:1px dashed #333;border-bottom:1px dashed #333;">&nbsp;</td>            
    </tr>
    <tr style="line-height:4px;text-align:center;">
        <td style="font-size:8px;"></td>
        <td style="font-size:8px;"></td>
        <td style="font-size:8px;text-align:left;"></td>        
        <td style="font-size:8px;"></td>
        <td style="font-size:8px;"></td>
        <td style="font-size:8px;text-align:left;"></td>
        <td style="font-size:8px;">&nbsp;</td>        
        <td style="font-size:8px;">&nbsp;</td>            
    </tr>
    <tr style="line-height:12px;text-align:center;">
        <td style="font-size:8px;"></td>
        <td style="font-size:8px;"></td>
        <td style="font-size:8px;text-align:left;"></td>        
        <td style="font-size:8px;"></td>
        <td style="font-size:8px;"></td>
        <td style="font-size:8px;text-align:left;"></td>
        <td style="font-size:8px;text-align:left;"><b>Total Units:</b></td>        
        <td style="font-size:8px;"><b><?php echo $total_units; ?></b></td>            
    </tr>
</table>
<?php 
$num++;
endforeach; ?>