<div id="container">
            <b><p style="font-size:12px; text-align:center">Office of the Registrar</p></b>
    <br><br>
    <div style="text-align:center; font-size:16px; font-weight:bold">C E R T I F I C A T I O N</div>
    <br><br>
    <div style="font-size:11px;">This is to certify that
        <b><?php 
            if($student_data['student']['enumGender'] == 'male'){
                echo 'MR. ';
            }else{
                echo 'MS. ';
            }
            echo strtoupper($student_data['student']['strFirstname']) . ' ' . strtoupper($student_data['student']['strLastname']); 
        ?></b>
        with Student No. <b><?php echo str_replace("-", "", $student_data['student']['strStudentNumber'])?></b> is a bonafide student of iACADEMY. He is currently enrolled this 
        <?php echo $sem['enumSem'] . ' ' . $sem['term_label'] . ', Academic Year ' . $sem['strYearStart'] . '-' . $sem['strYearEnd']; ?> under the program
        <?php echo $student_data['student']['strProgramDescription']; ?>.<br>

        <div>This certification is being issued to
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