<table border="0" cellspacing="0" cellpadding="0" style="color:#333; font-size:9;">   
    <tr style="font-weight:bold;">        
        <td colspan="13" style="font-size:10px; text-align:center">iACADEMY</td>
    </tr>
    <tr style="font-weight:bold;">
        <td colspan="13" style="font-size:10px; text-align:center"><?php if($campus == 'Cebu'):?>5th Floor Filinvest Cyberzone Tower 2 Salinas Drive Cor. W. Geonzon St., Cebu IT Park, Apas, Cebu City
            <?php else: ?>7434 Yakal Street Brgy. San Antonio, Makati City Contact No. 889-5555
            <?php endif; ?>                                 
        </td>           
    </tr>
    <tr style="font-weight:bold;">        
        <td colspan="13" style="font-size:10px; text-align:center">List of NSTP CWTS/LTS Enrollees</td>
    </tr>
    <tr style="font-weight:bold;">
        <td colspan="13" style="font-size:10px; text-align:center">Acad Year : <?php echo $sy->enumSem . ' ' . $this->data["term_type"] . ' ' . $sy->strYearStart . '-' . $sy->strYearEnd; ?></td>
    </tr>
</table>
<br><br>

<!-- <table style="page-break-inside:avoid"> -->
<table>
    <tr style="text-align:center; line-height:12px; font-size: 7px; font-weight: bold;">
        <td style="font-size:8px;border:1px solid #000000;" width="3%">No.</td>
        <td style="font-size:8px;border:1px solid #000000;" width="6%">Student No.</td>
        <td style="font-size:8px;border:1px solid #000000;" width="10%">Surname</td>
        <td style="font-size:8px;border:1px solid #000000;" width="10%">First Name</td>
        <td style="font-size:8px;border:1px solid #000000;" width="10%">Middle Name</td>
        <td style="font-size:8px;border:1px solid #000000;" width="10%">Course/Program (Write in Full)</td>
        <td style="font-size:8px;border:1px solid #000000;" width="5%">Gender</td>
        <td style="font-size:8px;border:1px solid #000000;" width="7%">Birthdate (ex. 11/25/1992)</td>
        <td style="font-size:8px;border:1px solid #000000;" width="8%">Street/Barangay Address</td>
        <td style="font-size:8px;border:1px solid #000000;" width="8%">Town/City Address</td>
        <td style="font-size:8px;border:1px solid #000000;" width="7%">Provincial Address</td>
        <td style="font-size:8px;border:1px solid #000000;" width="8%">Contact Number Telephone/Mobile</td>
        <td style="font-size:8px;border:1px solid #000000;" width="8%">Email address</td>
    </tr>

    <?php foreach($students as $index => $student):?>
        <tr style="font-size:7px; text-align:center;">
            <td style="border: 1px solid #000000"><?php echo $index + 1?></td>
            <td style="border: 1px solid #000000"><?php echo str_replace("-", "", $student['strStudentNumber'])?></td>
            <td style="border: 1px solid #000000"><?php echo ucfirst($student['strLastname'])?></td>
            <td style="border: 1px solid #000000"><?php echo ucfirst($student['strFirstname'])?></td>
            <td style="border: 1px solid #000000"><?php echo ucfirst($student['strMiddlename'])?></td>
            <td style="border: 1px solid #000000"><?php echo $student['course']['strProgramDescription']?></td>
            <td style="border: 1px solid #000000"><?php echo ucfirst($student['enumGender'])?></td>
            <td style="border: 1px solid #000000"><?php echo date("m/d/Y", strtotime($student['dteBirthDate']))?></td>
            <td style="border: 1px solid #000000"><?php echo $student['address'][0]?></td>
            <td style="border: 1px solid #000000"><?php echo $student['city'] ?></td>
            <td style="border: 1px solid #000000"><?php echo $student['province'] ?></td>
            <td style="border: 1px solid #000000"><?php echo $student['strMobileNumber']?></td>
            <td style="border: 1px solid #000000"><?php echo $student['strEmail']?></td>
        </tr>
    <?php endforeach; ?>
</table>