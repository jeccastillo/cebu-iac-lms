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
            <b><p style="font-size:12px; text-align:center">Office of the Registrar</p></b>
    <br><br>
    <div style="text-align:center; font-size:16px; font-weight:bold">C E R T I F I C A T I O N</div>
    <br><br>
    <div style="font-size:11px;">This is to certify that
        <b><?php 
            if($student_data['student']['enumGender'] == 'male'){
                echo 'Mr. ';
            }else{
                echo 'Ms. ';
            }
            echo strtoupper($student_data['student']['strFirstname']) . ' ' . strtoupper($student_data['student']['strLastname']); 
        ?></b>
        with Student No. <b><?php echo str_replace("-", "", $student_data['student']['strStudentNumber'])?></b> is a bonafide student of iACADEMY under the program
        <?php $student_data['student']['strProgramDescription']; ?>.
        <?php 
            if($student_data['student']['enumGender'] == 'male'){
                echo 'He ';
            }else{
                echo 'She ';
            }
        ?> already earned a total of <?php 
        $asOfTerm = '';
        foreach($student_data['data'] as $index => $term){

            if($selected_term == 'All'){
                if($term['units_earned'] > 0){
                    $asOfTerm = $term['reg']['enumSem'] . ' ' . $term['reg']['term_label'] . ', Academic Year ' . $term['reg']['strYearStart'] . '-' . $term['reg']['strYearEnd'];
                }
            }else{
                if($term['reg']['intAYID'] == $selected_term){
                    echo $term['units_earned'];
                    $asOfTerm = $term['reg']['enumSem'] . ' ' . $term['reg']['term_label'] . ', Academic Year ' . $term['reg']['strYearStart'] . '-' . $term['reg']['strYearEnd'];
                }
            }
        }
        ?> units as of <?php echo $asOfTerm; ?>


        <br><br>

        <?php
            foreach($student_data['data'] as $index => $term):
                $isRegistered = false;

                //check if the term is selected
                if($selected_term == 'All'){
                    $isRegistered = true;
                }else{
                    if($term['reg']['intAYID'] == $selected_term){
                        $isRegistered = true;
                    }
                }

                if($isRegistered):
                    $withUnitPassed = false;

                    foreach ($term['records'] as $index => $checkItem){  
                        if(isset($checkItem))
                            if($checkItem['strRemarks'] == 'Passed' || $checkItem['strRemarks'] == 'Taken')
                                $withUnitPassed = true;
                    }

                if($withUnitPassed):
        ?>
        <br>
        <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="font-size:9px; font-weight:bold; border: none"><?php echo $term['reg']['enumSem'][0] . '<sup>' . substr($term['reg']['enumSem'], 1) . '</sup> ' . $term['reg']['term_label'] . ', AY ' . $term['reg']['strYearStart'] . '-' . $term['reg']['strYearEnd'] ?></th>
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
                        if(isset($item)):
                            if($item['strRemarks'] == 'Passed' || $checkItem['strRemarks'] == 'Taken'):
                ?>                                
                <tr>                                                
                    <td width="22%"><?php echo $item['strCode'] ?></td>
                    <td width="45%"><?php echo $item['strDescription'] ?></td>   
                    <td width="15%" style="text-align:center;">
                        <?php
                            if($item['strUnits']) 
                                if($student_data['student']['level'] == 'college')    
                                    echo $item['v3'];
                                else
                                    echo $item['semFinalGrade']
                        ?>
                    </td>
                    <td width="18%" style="text-align:center;">
                        <?php
                            if($item['strUnits']) 
                                echo $item['strUnits'];
                        ?>
                    </td>                                                                                                                                                                            
                </tr>       
                <?php endif; endif; endforeach; ?>                                                                             
            </tbody>
        </table></div>
        <?php 
            endif;
            endif;
            endforeach;
            //endforeach; 
        ?><br><br><br>

        <div>This certification is issued upon request of
            <b><?php 
            if($student_data['student']['enumGender'] == 'male'){
                echo 'Mr. ';
            }else{
                echo 'Ms. ';
            }
            echo strtoupper($student_data['student']['strFirstname']) . ' ' . strtoupper($student_data['student']['strLastname']); 
            ?></b> for whatever legal purpose it may serve
            <?php 
            if($student_data['student']['enumGender'] == 'male'){
                echo 'him. ';
            }else{
                echo 'her. ';
            }?>
        </div>
        <div>Issued this <?php echo date('jS'); ?> day of 
        <?php 
            echo date('F Y'); 
            if($campus == 'Makati'){
                echo ', Makati City, Philippines.';
            }else if($campus == 'Cebu'){
                echo ', Cebu City, Philippines.';
            }
        ?>
        <br><br><br><br><br><br>

        <div> 
            <b><?php 
                if($signature_by){
                    echo strtoupper($signature_by);
                }
                else{
                    if($campus == 'Makati'){
                        echo 'MS. JOCELYN R. BANIAGO';
                    }
                }
            ?></b>
            <br>
            <?php 
                if($position){
                    echo ucfirst($position);
                }else{
                    echo 'Head Registrar';
                }
            ?>
        </div>
        <br>
        <div style="font-size:7px; font-style:italic">
            Prepared By: <?php echo $this->data['user']['strFirstname'] . ' ' . $this->data['user']['strLastname'] ?>
            <br><br><br>
            Not valid without School Seal
        </div>
        </div>
    </div>
</div>