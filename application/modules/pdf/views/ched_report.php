<table border="0" cellspacing="0" cellpadding="0" style="color:#333; font-size:9;">            
    <!-- <tr>            
        <td colspan = "3" width="100%" style="text-align: center;">             
            
        </td>
    </tr>         -->
    <tr>            
        <td colspan = "3" width="100%" style="text-align: center; line-height:1; font-weight: bold;">             
            <font style="font-family:Calibri Light; font-size: 13px;">INFORMATION AND COMMUNICATIONS TECHNOLOGY ACADEMY INC., (iACADEMY <?php echo ucfirst(strtolower($campus)); ?>)</font><br />
            <?php if($campus == 'Cebu'):?>
                <font style="font-family:Calibri Light; font-size: 11px;">5th Floor Filinvest Cyberzone Tower 2 Salinas Drive Cor. W. Geonzon St., Cebu IT Park, Apas, Cebu City</font><br />
            <?php else: ?>
                <font style="font-family:Calibri Light; font-size: 11px;">7434 Yakal Street Brgy. San Antonio, Makati City</font><br />
            <?php endif; ?>                                 
        </td>           
    </tr>
<table border="0">
    <tr style="line-height:12px; font-weight: bold;">
        <td style="font-size:10px;text-align:center;">CHED FORM XIX FOR </td>
        <td><u><?php echo $sy->enumSem . ' ' . $this->data["term_type"]; ?></u></td>
        <td style="font-size:10px;text-align:center;">SCHOOL YEAR</td>
        <td><u><?php echo $sy->strYearStart . '-' . $sy->strYearEnd; ?></u></td>
    </tr>
</table>
<br><br>
<table>
    <tr style="line-height:16px;text-align:center; font-weight: bold;">
        <th style="font-size:8px;border:1px solid #333;" width="8%">STUDENT NO.</th>
        <th style="font-size:8px;border:1px solid #333;" width="10%">LAST NAME</th>
        <th style="font-size:8px;border:1px solid #333;" width="14%">FIRST NAME</th>
        <th style="font-size:8px;border:1px solid #333;" width="10%">MIDDLE NAME</th>
        <th style="font-size:8px;border:1px solid #333;" width="6%">GENDER</th>
        <th style="font-size:8px;border:1px solid #333;" width="5%">COURSE</th>
        <th style="font-size:8px;border:1px solid #333;" width="3%">YEAR</th>
        <th style="font-size:8px;border:1px solid #333;" width="8%">SUBJECTS</th>
        <th style="font-size:8px;border:1px solid #333;" width="25%">SUBJECT DESCRIPTIONS</th>
        <th style="font-size:8px;border:1px solid #333;" width="3%">UNIT</th>
        <th style="font-size:8px;border:1px solid #333;" width="4%">MG</th>
        <th style="font-size:8px;border:1px solid #333;" width="4%">FG</th>
    </tr>
    <?php foreach($students as $student):?>
        <tr style="line-height:10px;">
            <td style="border-top:1px solid #333;"><?php echo str_replace("-", "", $student['student']['strStudentNumber']) ?> </td>
            <td style="border-top:1px solid #333;"><?php echo strtoupper($student['student']['strLastname']) ?> </td>
            <td style="border-top:1px solid #333;"><?php echo strtoupper($student['student']['strFirstname']) ?> </td>
            <td style="border-top:1px solid #333;"><?php echo strtoupper($student['student']['strMiddlename']) ?> </td>
            <td style="border-top:1px solid #333;"><?php echo strtoupper($student['student']['enumGender']) ?> </td>
            <td style="border-top:1px solid #333;"><?php echo $student['course'] ?> </td>
            <td style="border-top:1px solid #333; text-align:center"><?php echo strtoupper($student['student']['intStudentYear']) ?> </td>

            <td style="border-top:1px solid #333;"><?php echo strtoupper($student['subjects'][0]['strCode']) ?> </td>
            <td style="border-top:1px solid #333;"><?php echo strtoupper($student['subjects'][0]['strDescription']) ?> </td>
            <td style="border-top:1px solid #333; text-align:center"><?php echo strtoupper($student['subjects'][0]['strUnits']) ?> </td>
            <td style="border-top:1px solid #333; text-align:center"><?php echo strtoupper($student['subjects'][0]['floatMidtermGrade']) ?> </td>
            <td style="border-top:1px solid #333; text-align:center"><?php echo strtoupper($student['subjects'][0]['floatFinalGrade']) ?> </td>
            
        </tr>

        <?php if(count($student['subjects']) > 1): ?>
            <?php for($index = 1; $index < count($student['subjects']); $index++): ?>
                <tr>
                    <td colspan="7"></td>
                    <td><?php echo strtoupper($student['subjects'][$index]['strCode']) ?> </td>
                    <td><?php echo strtoupper($student['subjects'][$index]['strDescription']) ?> </td>
                    <td style="text-align:center"><?php echo strtoupper($student['subjects'][$index]['strUnits']) ?> </td>
                    <td style="text-align:center"><?php echo strtoupper($student['subjects'][$index]['floatMidtermGrade']) ?> </td>
                    <td style="text-align:center"><?php echo strtoupper($student['subjects'][$index]['floatFinalGrade']) ?> </td>
                </tr>
            <?php  endfor;
                endif; 
            ?>
    <?php endforeach; ?>
</table>