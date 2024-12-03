<table border="0">
    <tr style="line-height:40px;">
        <td style="font-size:9px;text-align:center">CURRICULUM OUTLINE</td>
    </tr>
</table>
<table border="0" cellpadding="0" style="color:#333; font-size:10;">
        <tr style="line-height:25px;">
            <td width="64" align="right"></td>
            <td width="400" style="text-align: center; line-height:100%">
                
             <font style="font-family:Calibri Light; font-size: 10;">Republic of the Philippines<br />City of Makati</font><br /><br />
             <font style="font-family:Calibri Light; font-size: 10;font-weight: bold;">iACADEMY, Inc.</font><br />
			 <font style="font-family:Calibri Light; font-size: 10;">Filinvest Cebu Cyberzone Tower 2 Salinas Drive corner W. Geonzon St., Brgy. Apas, Lahug, Cebu City</font><br />             
            </td>
            <td width="64" align="left" valign="middle"></td>
        </tr>
</table>
<table border="0">
    <tr style="line-height:20px;">
        <td style="font-size:12px;text-align:center"><b><?php echo $student['strStudentNumber'] . " - " . $student['strLastname'].", ".$student['strFirstname']; ?></b></td>
    </tr>
    <tr style="line-height:20px;">
        <td style="font-size:12px;text-align:center"><b><?php echo $curriculum['strName']; ?></b></td>
    </tr>
    <tr style="line-height:10px;">
        <td style="font-size:9px;text-align:center"></td>
    </tr>
</table>
<?php 
            $prev_year_sem = '0';
            for($i = 0;$i<count($curriculum_subjects); $i++): 
            $key = array_search($curriculum_subjects[$i]['strCode'], array_column($grades, 'strCode'));
            //echo $prev_year_sem."<br />";

            ?>
            <?php if($prev_year_sem != $curriculum_subjects[$i]['intYearLevel']."_".$curriculum_subjects[$i]['intSem']): ?>
            <table class="table table-bordered">
                <thead>
                    <tr style="line-height:20px;font-size:9px;">
                        <th style="border:1px solid #333;" colspan="4">
                            <?php echo switch_num($curriculum_subjects[$i]['intYearLevel'])." Year ".switch_num($curriculum_subjects[$i]['intSem'])." Term"; ?>
                        </th>
                    </tr>
                    <tr style="line-height:15px;font-size:9px;text-align:center">
                        <th width="20%" style="border:1px solid #333;">Course Code</th>
                        <th width="60%" style="border:1px solid #333;">Course Title</th>
                        <th width="10%" style="border:1px solid #333;">Units</th>
                        <th width="10%" style="border:1px solid #333;">Grade</th>
                    </tr>
                </thead>
                <tbody>

            <?php 

                    endif; 
                    $prev_year_sem = $curriculum_subjects[$i]['intYearLevel']."_".$curriculum_subjects[$i]['intSem'];
                    ?>
            <!-- <tr style="<?php echo ($key && $grades[$key]['floatFinalGrade'] <= "3" && $grades[$key]['floatFinalGrade'] != "0")?'background-color:#669966':''; ?>;font-size:9px;line-height:15px;"> -->
            <tr style="<?php echo ($key && $grades[$key]['floatFinalGrade'] <= "3" && $grades[$key]['floatFinalGrade'] != "0")?'':''; ?>;font-size:9px;line-height:15px;">
                <td width="20%" style="border:1px solid #d2d2d2;"><?php echo $curriculum_subjects[$i]['strCode']; ?></td>
                <td width="60%" style="border:1px solid #d2d2d2;">
                    <?php echo $curriculum_subjects[$i]['strDescription']; ?>
                </td>
                <td width="10%" style="text-align:center;border:1px solid #d2d2d2;">
                    <?php echo $curriculum_subjects[$i]['strUnits']; ?> 
                </td>
                <td width="10%" style="text-align:center;border:1px solid #d2d2d2;">
                    <?php echo ($key)?(number_format($grades[$key]['floatFinalGrade'], 2, '.' ,',')):'NR'; ?>
                </td>
            </tr>
        <?php if((isset($curriculum_subjects[$i+1]) && $prev_year_sem != $curriculum_subjects[$i+1]['intYearLevel']."_".$curriculum_subjects[$i+1]['intSem']) || count($curriculum_subjects) == $i+1): ?>   

            </tbody>
        </table>
        <table>
            <tr style="line-height:20px;">
                <td style="font-size:9px;text-align:center"></td>
            </tr>    
        </table>
        <?php endif; ?>
        <?php endfor; ?>
        <hr />
        <small>
        <!-- <div class="legend"><span class="text-bold">Legend: </span>
        <span class="holder" style="padding-right: 15px;"><span class="legend normal" style="border: solid 1px; background-color:gray;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> - not yet taken</span>
        
        <span class="holder" style="padding-right: 15px;"><span class="legend passed" style="border: solid 1px; background-color:green;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> - passed subject</span>
        
        <span class="holder" style="padding-right: 15px;"><span class="legend currently-enrolled" style="border: solid 1px; background-color:blue;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> - currently enrolled</span>
        
        <span class="holder" style="padding-right: 15px;"><span class="legend incomplete" style="border: solid 1px; background-color:#995c00;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> - incomplete</span></div></small>
        <hr /> -->
        <br pagebreak="true"/>
        <h3>Equivalent Courses</h3>
        <p style="font-style:italic;"><small>Note: The following are courses with different course code from the old curriculum but with same course title with your curriculum.</small></p>
        <hr />
        <?php 
            $prev_year_sem = '0';
            for($i = 0;$i<count($equivalent_subjects); $i++): 
            $key = array_search($equivalent_subjects[$i]['strCode'], array_column($grades, 'strCode'));            
            $key2 = array_search($equivalent_subjects[$i]['mainSubjectID'], array_column($curriculum_subjects,'intSubjectID'));            
            //echo $prev_year_sem."<br />";

            ?>
            <?php if($prev_year_sem != $equivalent_subjects[$i]['intYearLevel']."_".$equivalent_subjects[$i]['intSem']): ?>

                
            <table class="table table-bordered">
                <thead>
                    <tr style="line-height:20px;font-size:9px;">
                        <th style="border:1px solid #333;" colspan="5">
                            <?php echo switch_num($equivalent_subjects[$i]['intYearLevel'])." Year ".switch_num($equivalent_subjects[$i]['intSem'])." Term"; ?>
                        </th>
                    </tr>
                    <tr style="line-height:15px;font-size:9px;text-align:center">
                        <th width="15%" style="border:1px solid #333;">Course Code</th>
                        <th width="55%" style="border:1px solid #333;">Course Title</th>
                        <th width="10%" style="border:1px solid #333;">Units</th>
                        <th width="10%" style="border:1px solid #333;">Main</th>
                        <th  width="10%" style="border:1px solid #333;">Grade</th>
                    </tr>
                </thead>
                <tbody>

            <?php 

                    endif; 
                    $prev_year_sem = $equivalent_subjects[$i]['intYearLevel']."_".$equivalent_subjects[$i]['intSem'];
                    ?>
            <!-- <tr style="<?php echo ($key && $grades[$key]['floatFinalGrade'] <= "3" && $grades[$key]['floatFinalGrade'] != "0")?'background-color:#669966':''; ?>;font-size:9px;line-height:15px;"> -->
            <tr style="<?php echo ($key && $grades[$key]['floatFinalGrade'] <= "3" && $grades[$key]['floatFinalGrade'] != "0")?'':''; ?>;font-size:9px;line-height:15px;">
                <td width="15%" style="border:1px solid #d2d2d2;"><?php echo $equivalent_subjects[$i]['strCode']; ?></td>
                <td width="55%" style="border:1px solid #d2d2d2;">
                    <?php echo $equivalent_subjects[$i]['strDescription']; ?>
                </td>
                <td width="10%" style="text-align:center;border:1px solid #d2d2d2;">
                    <?php echo $equivalent_subjects[$i]['strUnits']; ?> 
                </td>
                <td width="10%" style="border:1px solid #d2d2d2;">
                <?php echo ($key2)?$curriculum_subjects[$key2]['strCode']:'None'; ?>
                </td>
                <td width="10%" style="text-align:center;border:1px solid #d2d2d2;">
                     <?php echo ($key)?$grades[$key]['floatFinalGrade']:'NR'; ?>
                </td>
            </tr>
        <?php if((isset($equivalent_subjects[$i+1]) && $prev_year_sem != $equivalent_subjects[$i+1]['intYearLevel']."_".$equivalent_subjects[$i+1]['intSem']) || count($equivalent_subjects) == $i+1): ?>   
            </tbody>
        </table>
        <table>
            <tr style="line-height:20px;">
                <td style="font-size:9px;text-align:center"></td>
            </tr>    
        </table>
        <?php endif; ?>
        <?php endfor; ?>