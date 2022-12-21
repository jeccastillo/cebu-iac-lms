<div class="box box-solid">
    <div class="box-header">
        <h4 class="box-title">ASSESSMENT OF FEES</h4>
    </div>
    <div class="box-body">
        <div class="grid grid-rows-2 grid-flow-col gap-4">
            <div class="col-sm-6">Tuition:</div>
            <div class="col-sm-6 text-green"><?php echo $tuition['tuition']; ?></div>
        </div>
        <hr />                
        <div class="grid grid-rows-2 grid-flow-col gap-4">
            <div class="col-sm-6">Miscellaneous:</div>
            <div class="col-sm-6 text-green"></div>
        </div>
    
        <?php foreach($tuition['misc_list'] as $key=>$val): ?>        
        <div class="grid grid-rows-2 grid-flow-col gap-4">
            <div class="col-sm-6" style="text-align:right;"><?php echo $key; ?></div>
            <div class="col-sm-6"><?php echo $val; ?></div>
        </div>
        <?php endforeach; ?>

        <div class="grid grid-rows-2 grid-flow-col gap-4">
            <div class="col-sm-6" style="text-align:right;">Total:</div>
            <div class="col-sm-6 text-green"><?php echo $tuition['misc']; ?></div>
        </div>

        <?php if($tuition['nsf']!= 0): ?>                             
            <div class="grid grid-rows-2 grid-flow-col gap-4">
                <div class="col-sm-6">MISC - NEW STUDENT: </div>
                <div class="col-sm-6 text-green"><?php echo $tuition['nsf']; ?></div>
            </div>
            <hr />                    
        <?php endif; ?>                  

        <div class="grid grid-rows-2 grid-flow-col gap-4">
            <div class="col-sm-6">Laboratory Fee:</div>
            <div class="col-sm-6 text-green"></div>
        </div>
        <hr />                
        <?php foreach($tuition['lab_list'] as $key=>$val): ?>
            <div class="grid grid-rows-2 grid-flow-col gap-4">
                <div class="col-sm-6" style="text-align:right;"><?php echo $key; ?></div>
                <div class="col-sm-6"><?php echo $val; ?></div>
            </div>
        <?php endforeach; ?>

        
        <div class="grid grid-rows-2 grid-flow-col gap-4">
            <div class="col-sm-6" style="text-align:right;">Total:</div>
            <div class="col-sm-6 text-green"><?php echo $tuition['lab']; ?></div>
        </div>
        <hr />
        <?php if($tuition['thesis_fee']!= 0): ?>                    
            <div class="grid grid-rows-2 grid-flow-col gap-4">
                <div class="col-sm-6">THESIS FEE: </div>
                <div class="col-sm-6 text-green"><?php echo $tuition['thesis_fee']; ?></div>
            </div>
            <hr />                    
        <?php endif; ?>
        <?php if($tuition['internship_fee']!= 0): ?>
            
            <div class="grid grid-rows-2 grid-flow-col gap-4">
                <div class="col-sm-6">Internship Fees:</div>
                <div class="col-sm-6 text-green"></div>
            </div>
            <hr />
            
            <?php foreach($tuition['internship_fee_list'] as $key=>$val): ?>
                <div class="grid grid-rows-2 grid-flow-col gap-4">
                    <div class="col-sm-6" style="text-align:right;"><?php echo $key; ?></div>
                    <div class="col-sm-6"><?php echo $val; ?></div>
                </div>
            <?php endforeach; ?>

            
            <div class="grid grid-rows-2 grid-flow-col gap-4">
                <div class="col-sm-6" style="text-align:right;">Total:</div>
                <div class="col-sm-6 text-green"><?php echo $tuition['internship_fee']; ?></div>
            </div>
            <hr />
        <?php endif; ?>
        <?php if($tuition['new_student']!= 0): ?>
            <div class="grid grid-rows-2 grid-flow-col gap-4">
                <div class="col-sm-6">New Student Fees:</div>
                <div class="col-sm-6 text-green"></div>
            </div>
            <hr />
            
            
            
            <?php foreach($tuition['new_student_list'] as $key=>$val): ?>                
                <div class="grid grid-rows-2 grid-flow-col gap-4">
                    <div class="col-sm-6" style="text-align:right;"><?php echo $key; ?></div>
                    <div class="col-sm-6"><?php echo $val; ?></div>
                </div>
            <?php endforeach; ?>

            
            <div class="grid grid-rows-2 grid-flow-col gap-4">
                <div class="col-sm-6" style="text-align:right;">Total:</div>
                <div class="col-sm-6 text-green"><?php echo $tuition['new_student']; ?></div>
            </div>
            <hr />
        <?php endif; ?>
            
        <div class="grid grid-rows-2 grid-flow-col gap-4">
            <div class="col-sm-6">Total:</div>
            <div class="col-sm-6 text-green"><?php echo $tuition['total'] ?></div>
        </div>
        <hr />
        <h4 class="box-title">FOR INSTALLMENT</h4>
        <div class="grid grid-rows-2 grid-flow-col gap-4">
            <div class="col-sm-6">Down Payment</div>
            <div class="col-sm-6"><?php echo number_format($tuition['down_payment'], 2, '.' ,','); ?></div>
        </div>
        <br />
        <?php for($i=0;$i<5;$i++): ?>
        <div class="grid grid-rows-2 grid-flow-col gap-4">        
            <div class="col-sm-6">
                <td width="140px"><?php echo switch_num($i + 1) ?> INSTALLMENT</td>
            </div>
            <div class="col-sm-6">
                <td width="80px" style="text-align:right;"><?php echo number_format($tuition['installment_fee'], 2, '.' ,','); ?></td>
            </div>                    
        </div>
        <?php endfor; ?>
        <hr />
        <div class="grid grid-rows-2 grid-flow-col gap-4">
            <div class="col-sm-6">Total for installment</div>
            <div class="col-sm-6 text-green"><?php echo number_format($tuition['total_installment'], 2, '.' ,','); ?></div>
        </div>                                                                    
    </div>
</div>