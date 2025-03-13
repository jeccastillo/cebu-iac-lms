<style>
    th, td{
        border:1px solid #000000;
        padding: 5px;
    }
    
    table {
        border-collapse: collapse;
        /* break-inside: avoid; */
        /* page-break-inside: avoid; */
    }
    
    .table-container {
        break-inside: avoid;
        page-break-inside: avoid;
    }
</style>
<div id="container">
    <!-- <table border="0" style="color:#333; font-size:9; ">
        <tr style="font-weight:bold;">         -->
            <p style="font-size:12px; text-align:center">Office of the Registrar</p>
        <!-- </tr>
    </table> -->
    <br><br>
    <div style="text-align:center; font-size:16px; font-weight:bold">C E R T I F I C A T I O N</div>
    <br><br>
    <div style="font-size:11px;">This is to clarify that
        <b><?php 
            if($student_data['student']['enumGender'] == 'male'){
                echo 'Mr. ';
            }else{
                echo 'Ms. ';
            }
            echo strtoupper($student_data['student']['strFirstname']) . ' ' . strtoupper($student_data['student']['strLastname']); 
        ?></b>
        with Student ID No. <b><?php echo str_replace("-", "", $student_data['student']['strStudentNumber'])?></b> is a bonafide student of iACADEMY under the program
        <?php $student_data['student']['strProgramDescription']; ?>.
        <?php 
            if($student_data['student']['enumGender'] == 'male'){
                echo 'He ';
            }else{
                echo 'She ';
            }
        ?> already earned a total of <?php echo $student_data['total_units_earned']; ?> units as of

        <br><br>

        <?php 
            foreach($student_data['curriculum_subjects'] as $index => $curriculum_subjects):
                foreach ($curriculum_subjects as $index => $term):
                    $withUnitPassed = false;

                    foreach ($term['records'] as $index => $checkItem){
                        if(isset($checkItem['rec']))
                            if($checkItem['rec']['strRemarks'] == 'Passed')
                                $withUnitPassed = true;
                    }

                if($withUnitPassed):
        ?>
        <br>
        <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="font-size:9px; font-weight:bold; border: none"><?php echo $term['stringify_year'] . ' Year ' . $term['stringify_sem'] . ' Term' ?></th>
                </tr>
                <tr style="text-align:center; font-weight:bold;">
                    <th width="22%">Course Code</th>
                    <th width="45%">Descriptive Title</th> 
                    <th width="15%">Final Grade</th>                                  
                    <th width="18%">Units Earned</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($term['records'] as $index => $item): 
                        if(isset($item['rec'])):
                            if($item['rec']['strRemarks'] == 'Passed'):
                ?>                                
                <tr>                                                
                    <td width="22%"><?php echo $item['strCode'] ?></td>
                    <td width="45%"><?php echo $item['strDescription'] ?></td>   
                    <td width="15%" style="text-align:center;">
                        <?php
                            if($item['equivalent']) 
                                echo $item['equivalent']['grade'];
                            else{        
                                    echo $item['rec']['floatFinalGrade'];
                            }
                        ?>
                    </td>
                    <td width="18%" style="text-align:center;">
                        <?php
                            if($item['equivalent']) 
                                echo '(' . parseInt($item['equivalent']['units']).toFixed(1) . ')';
                            else{        
                                echo $item['rec']['strUnits'];
                            }
                        ?>
                    </td>                                                                                                                                                                            
                </tr>       
                <?php endif; endif; endforeach; ?>                                                                             
            </tbody>
        </table></div>
        <?php 
            endif;
            endforeach;
            endforeach; 
        ?><br><br><br>

        <div>This certification is issued upon request of
            <b><?php 
            if($student_data['student']['enumGender'] == 'male'){
                echo 'Mr. ';
            }else{
                echo 'Ms. ';
            }
            echo strtoupper($student_data['student']['strFirstname']) . ' ' . strtoupper($student_data['student']['strLastname']); 
            ?></b>for whatever legal purpose it may serve him.
        </div>
        <div>Issued this
        </div>
    </div>
</div>