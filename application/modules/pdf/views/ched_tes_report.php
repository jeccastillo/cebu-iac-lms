<table border="0" cellspacing="0" cellpadding="0" style="color:#333; font-size:9;">   
    <tr style="font-weight:bold;">        
        <td>HEI NAME :</td>
        <td colspan="6" style="font-size:10px; text-align:center">iACADEMY</td>
    </tr>
    <tr style="font-weight:bold;">
        <td>HEI UII :</td>
        <td colspan="6" style="font-size:10px; text-align:center"><?php if($campus == 'Cebu'):?>5th Floor Filinvest Cyberzone Tower 2 Salinas Drive Cor. W. Geonzon St., Cebu IT Park, Apas, Cebu City
            <?php else: ?>7434 Yakal Street Brgy. San Antonio, Makati City
            <?php endif; ?>                                 
        </td>           
    </tr>
    <tr style="font-weight:bold;">
        <td>Acad Year :</td>
        <td colspan="6" style="font-size:10px; text-align:center"><?php echo $sy->enumSem . ' ' . $this->data["term_type"] . ' ' . $sy->strYearStart . '-' . $sy->strYearEnd; ?></td>
    </tr>
</table>
<br><br>
<!-- <table style="page-break-inside:avoid"> -->
<table>
    <tr style="font-size:8px; font-weight:bold;">
        <td></td>
        <td colspan="9">STUDENT INFORMATION</td>
        <td colspan="8">FAMILY BACKGROUND</td>
    </tr>
    <tr style="color: #FFFFFF; text-align:center; line-height:12px; font-size: 7px; font-weight: bold;">
        <td style="font-size:8px;border:1px solid #000000; background-color: #0D6ED0;" width="3%" rowspan="2">SEQ.</td>
        <td style="font-size:8px;border:1px solid #000000; background-color: #FFAD56;" width="5%" rowspan="2">STUDENT ID</td>
        <td style="font-size:8px;border:1px solid #000000; background-color: #0D6ED0;" width="18%" colspan="4">STUDENT'S NAME</td>
        <td style="font-size:8px;border:1px solid #000000; background-color: #0D6ED0;" width="20%" colspan="4">STUDENT'S PROFILE</td>
        <td style="font-size:8px;border:1px solid #000000; background-color: #0D6ED0;" width="11%" colspan="3">FATHER'S NAME</td>
        <td style="font-size:8px;border:1px solid #000000; background-color: #0D6ED0;" width="11%" colspan="3">MOTHER'S MAIDEN NAME</td>
        <td style="font-size:8px;border:1px solid #000000; background-color: #0D6ED0;" width="10%" colspan="2">PERMANENT'S ADDRESS</td>
        <td style="font-size:8px;border:1px solid #000000; background-color: #0D6ED0;" width="4%" rowspan="2">DISABILITY (leave blank if NOT Applicable)</td>
        <td style="font-size:8px;border:1px solid #000000; background-color: #FFAD56;" width="5%" rowspan="2">CONTACT NUMBER</td>
        <td style="font-size:8px;border:1px solid #000000; background-color: #FFAD56;" width="5%" rowspan="2">EMAIL ADDRESS</td>
        <td style="font-size:8px;border:1px solid #000000; background-color: #0D6ED0;" width="6%" rowspan="2">INDIGENOUS PEOPLE GROUP (leave blank if NOT Applicable)</td>
    </tr>
    <tr style="color: #FFFFFF; text-align:center; font-style:italic; font-size: 7px; font-weight: bold;">
        <td style="border:1px solid #000000; background-color: #FFAD56;" width="5%">LAST NAME</td>
        <td style="border:1px solid #000000; background-color: #FFAD56;" width="5%">GIVEN NAME</td>
        <td style="border:1px solid #000000; background-color: #0D6ED0;" width="3%">EXT. NAME</td>
        <td style="border:1px solid #000000; background-color: #FFAD56;" width="5%">MIDDLE NAME</td>
        <td style="border:1px solid #000000; background-color: #FFAD56;" width="5%">SEX (Male or Female)</td>
        <td style="border:1px solid #000000; background-color: #FFAD56;" width="5%">BIRTHDATE (dd/mm/yyyy)</td>
        <td style="border:1px solid #000000; background-color: #FFAD56;" width="7%">COMPLETE PROGRAM NAME (Should be consistent with your HEI Registry)</td>
        <td style="border:1px solid #000000; background-color: #FFAD56;" width="3%">YEAR LEVEL (1,2,3,4,5)</td>
        <td style="border:1px solid #000000; background-color: #FFAD56;">LAST NAME</td>
        <td style="border:1px solid #000000; background-color: #FFAD56;">GIVEN NAME</td>
        <td style="border:1px solid #000000; background-color: #FFAD56;">MIDDLE NAME</td>
        <td style="border:1px solid #000000; background-color: #FFAD56;">LAST NAME</td>
        <td style="border:1px solid #000000; background-color: #FFAD56;">GIVEN NAME</td>
        <td style="border:1px solid #000000; background-color: #FFAD56;">MIDDLE NAME</td>
        <td style="border:1px solid #000000; background-color: #FFAD56;">STREET & BARANGAY</td>
        <td style="border:1px solid #000000; background-color: #FFAD56;">ZIPCODE (TES Applicant)</td>
    </tr>

    <?php foreach($students as $index => $student):?>
        <tr style="font-size:7px; text-align:center;">
            <td style="border: 1px solid #000000"><?php echo $index + 1?></td>
            <td style="border: 1px solid #000000"><?php echo str_replace("-", "", $student['strStudentNumber'])?></td>
            <td style="border: 1px solid #000000"><?php echo ucfirst($student['strLastname'])?></td>
            <td style="border: 1px solid #000000"><?php echo ucfirst($student['strFirstname'])?></td>
            <td style="border: 1px solid #000000"><?php echo ucfirst($student['nameExtension'])?></td>
            <td style="border: 1px solid #000000"><?php echo ucfirst($student['strMiddlename'])?></td>
            <td style="border: 1px solid #000000"><?php echo ucfirst($student['enumGender'])?></td>
            <td style="border: 1px solid #000000"><?php echo date("d/m/Y", strtotime($student['dteBirthDate']))?></td>
            <td style="border: 1px solid #000000"><?php echo $student['course']['strProgramDescription']?></td>
            <td style="border: 1px solid #000000"><?php echo $student['intStudentYear']?></td>
            <td style="border: 1px solid #000000"><?php echo ucfirst($student['fatherLastName'])?></td>
            <td style="border: 1px solid #000000"><?php echo ucfirst($student['fatherFirstName'])?></td>
            <td style="border: 1px solid #000000"><?php echo ucfirst($student['fatherMiddleName'])?></td>
            <td style="border: 1px solid #000000"><?php echo ucfirst($student['motherLastName'])?></td>
            <td style="border: 1px solid #000000"><?php echo ucfirst($student['motherFirstName'])?></td>
            <td style="border: 1px solid #000000"><?php echo ucfirst($student['motherMiddleName'])?></td>
            <td style="border: 1px solid #000000"><?php echo $student['address'][0]?></td>
            <td style="border: 1px solid #000000"><?php echo is_numeric($student['address'][count($student['address']) - 1]) ? $student['address'][count($student['address']) - 1] : ''?></td>
            <td style="border: 1px solid #000000"><?php echo ''?></td>
            <td style="border: 1px solid #000000"><?php echo $student['strMobileNumber']?></td>
            <td style="border: 1px solid #000000"><?php echo $student['strEmail']?></td>
            <td style="border: 1px solid #000000"><?php echo ''?></td>
        </tr>
    <?php endforeach; ?>
</table>