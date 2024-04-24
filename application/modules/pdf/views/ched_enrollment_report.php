<table border="0" cellspacing="0" cellpadding="0" style="color:#333; font-size:9;">   
    <tr style="font-weight:bold;">        
        <td>School Name:</td>
        <td colspan="6" style="font-size:10px; text-align:center">iACADEMY</td>
    </tr>
    <tr style="font-weight:bold;">
        <td>Address:</td>
        <td colspan="6" style="font-size:10px; text-align:center"><?php if($campus == 'Cebu'):?>5th Floor Filinvest Cyberzone Tower 2 Salinas Drive Cor. W. Geonzon St., Cebu IT Park, Apas, Cebu City
            <?php else: ?>7434 Yakal Street Brgy. San Antonio, Makati City
            <?php endif; ?>                                 
        </td>           
    </tr>
    <tr style="font-weight:bold;">
        <td>Term & AY :</td>
        <td colspan="6" style="font-size:10px; text-align:center"><?php echo $sy->enumSem . ' ' . $this->data["term_type"] . ' ' . $sy->strYearStart . '-' . $sy->strYearEnd; ?></td>
    </tr>
    <tr>
        <td colspan="1"><b>Note:</b> </td><td colspan="6">This template is the official template for Enrollment List submission released by CHED NCR Office, You may add rows for student lists but not the columns</td>
    </tr>
</table>
<br><br>
<table style="page-break-inside:avoid">
    <tr style="background-color: #101D6B; color: #FFFFFF; text-align:center; line-height:12px; font-weight: bold;">
        <td style="font-size:8px;border:1px solid #000000;" width="3%" rowspan="3">NO.</td>
        <td style="font-size:8px;border:1px solid #000000;" width="7%">PROGRAM</td>
        <td style="font-size:8px;border:1px solid #000000;" width="7%">MAJOR</td>
        <td style="font-size:8px;border:1px solid #000000;" width="7%" rowspan="2">STUDENT NUMBER</td>
        <td style="font-size:8px;border:1px solid #000000;" width="10%">FIRST NAME</td>
        <td style="font-size:8px;border:1px solid #000000;" width="8%">MIDDLE NAME</td>
        <td style="font-size:8px;border:1px solid #000000;" width="9%">SURNAME</td>
        <td style="font-size:8px;border:1px solid #000000;" width="7%">NAME EXTENSION</td>
        <td style="font-size:8px;border:1px solid #000000;" width="8%">CITIZENSHIP</td>
        <td style="font-size:8px;border:1px solid #000000;" width="6%">GENDER</td>
        <td style="font-size:8px;border:1px solid #000000;" width="5%">YEAR LEVEL</td>
        <td style="font-size:8px;border:1px solid #000000;" width="13%">SUBJECTS ENROLLED FOLLOWED BY UNITS</td>
        <td style="font-size:8px;border:1px solid #000000;" width="4%" rowspan="3">NO. OF UNITS</td>
        <td style="font-size:8px;border:1px solid #000000;" width="6%" rowspan="3">REMARKS (if any)</td>
    </tr>
    <tr style="background-color: #101D6B; color: #FFFFFF; text-align:center; font-style:italic; font-size: 7px;">
        <td style="border:1px solid #000000;">Ex. Bachelor of Science in Business Administration</td>
        <td style="border:1px solid #000000;">Ex. Marketing</td>
        <td style="border:1px solid #000000;">Juan III</td>
        <td style="border:1px solid #000000;">Santos</td>
        <td style="border:1px solid #000000;">Dela Cruz </td>
        <td style="border:1px solid #000000;" rowspan="2">Ex. Jr., II, III Do not insert N/A</td>
        <td style="border:1px solid #000000;" rowspan="2">Ex. Filipino</td>
        <td style="border:1px solid #000000;" rowspan="2">M/F</td>
        <td style="border:1px solid #000000;" rowspan="2">1 / 2 / 3 / 4 / 5</td>
        <td style="border:1px solid #000000;">Ex. On the Job Trainee (3), Communication Arts (3)</td>
    </tr>
    <tr style="background-color: #101D6B; color: #FFFFFF; text-align:center; font-style:italic; font-size: 7px;">
        <td style="border:1px solid #000000;">Please do not abbreviate</td>
        <td style="border:1px solid #000000;">Do not insert N/A</td>
        <td style="border:1px solid #000000;">Do not insert N/A</td>
        <td style="border:1px solid #000000;" colspan="3">Do not insert N/A</td>
        <td style="border:1px solid #000000;">Please do not abbreviate</td>
    </tr>
    <?php foreach($students as $student):?>
        <tr style="font-size:8px; text-align:center;">
            <td style="border: 1px solid #000000; background-color: #101D6B; color: #FFFFFF;"><?php echo $student['index']?></td>
            <td style="border: 1px solid #000000"><?php echo $student['course']['strProgramDescription']?></td>
            <td style="border: 1px solid #000000"><?php if($student['course']['strMajor'] != 'None') echo $student['course']['strMajor'];?></td>
            <td style="border: 1px solid #000000"><?php echo str_replace("-", "", $student['strStudentNumber'])?></td>
            <td style="border: 1px solid #000000"><?php echo ucfirst($student['strFirstname'])?></td>
            <td style="border: 1px solid #000000"><?php echo ucfirst($student['strMiddlename'])?></td>
            <td style="border: 1px solid #000000"><?php echo ucfirst($student['strLastname'])?></td>
            <td style="border: 1px solid #000000"><?php echo ucfirst($student['nameExtension'])?></td>
            <td style="border: 1px solid #000000"><?php echo ucfirst($student['strCitizenship'])?></td>
            <td style="border: 1px solid #000000"><?php echo substr(ucfirst($student['enumGender']), 0, 1)?></td>
            <td style="border: 1px solid #000000"><?php echo strtoupper($student['intStudentYear'])?></td>
            <td style="border: 1px solid #000000"><?php echo $student['subjectsEnrolled']?></td>
            <td style="border: 1px solid #000000"><?php echo $student['totalUnits']?></td>
            <td style="border: 1px solid #000000"><?php ?></td>
        </tr>
    <?php endforeach; ?>
</table>