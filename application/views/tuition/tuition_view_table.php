    <table class="table-fixed w-full">
    <thead>
        <tr>
        <th class="text-right w-1/2">Tuition:</th>
        <td class="w-1/2"><?php echo $tuition['tuition']; ?></td>      
        </tr>
    </thead>
    <tbody>
        <tr>
            <th class="text-right w-1/2">Miscellaneous:</th>
            <td class="w-1/2"></td>      
        </tr>
    <?php foreach($tuition['misc_list'] as $key=>$val): ?>
        <tr>
            <th class="text-right w-1/2"><?php echo $key; ?></th>
            <td class="w-1/2"><?php echo $val; ?></td>      
        </tr>
    <?php endforeach; ?>
        <tr>
            <th class="text-right w-1/2">Total:</th>
            <td class="w-1/2"><?php echo $tuition['misc']; ?></td>      
        </tr>    
        <tr>
            <th class="text-right w-1/2">Laboratory Fee:</th>
            <td class="text-green-400"></td>
        </tr>
        <hr />                
        <?php foreach($tuition['lab_list'] as $key=>$val): ?>
            <tr>
                <th class="text-right w-1/2"><?php echo $key; ?></th>
                <td class="w-1/2"><?php echo $val; ?></td>
            </tr>
        <?php endforeach; ?>        
        <tr>
            <th class="text-right w-1/2">Total:</th>
            <td class="text-green-400"><?php echo $tuition['lab']; ?></td>
        </tr>
        <hr />
        <?php if($tuition['thesis_fee']!= 0): ?>                    
            <tr>
                <th class="text-right w-1/2">THESIS FEE: </th>
                <td class="text-green-400"><?php echo $tuition['thesis_fee']; ?></td>
            </tr>
            <hr />                    
        <?php endif; ?>
        <?php if($tuition['internship_fee']!= 0): ?>
            
            <tr>
                <th class="text-right w-1/2">Internship Fees:</th>
                <td class="text-green-400"></td>
            </tr>
            <hr />
            
            <?php foreach($tuition['internship_fee_list'] as $key=>$val): ?>
                <tr>
                    <th class="text-right w-1/2"><?php echo $key; ?></th>
                    <td class="w-1/2"><?php echo $val; ?></td>
                </tr>
            <?php endforeach; ?>

            
            <tr>
                <th class="text-right w-1/2">Total:</th>
                <td class="text-green-400"><?php echo $tuition['internship_fee']; ?></td>
            </tr>
            <hr />
        <?php endif; ?>
        <?php if($tuition['new_student']!= 0): ?>
            <tr>
                <th class="text-right w-1/2">New Student Fees:</th>
                <td class="text-green-400"></td>
            </tr>
            <hr />
            
            <?php foreach($tuition['new_student_list'] as $key=>$val): ?>                
                <tr>
                    <th class="text-right w-1/2"><?php echo $key; ?></th>
                    <td class="w-1/2"><?php echo $val; ?></td>
                </tr>
            <?php endforeach; ?>

            
            <tr>
                <th class="text-right w-1/2">Total:</th>
                <td class="text-green-400"><?php echo $tuition['new_student']; ?></td>
            </tr>
            <hr />
        <?php endif; ?>
            
        <tr>
            <th class="text-right w-1/2">Total:</th>
            <td class="text-green-400"><?php echo $tuition['total'] ?></td>
        </tr>
        <hr />
        <h4 class="box-title">FOR INSTALLMENT</h4>
        <tr>
            <th class="text-right w-1/2">Down Payment</th>
            <td class="w-1/2"><?php echo number_format($tuition['down_payment'], 2, '.' ,','); ?></td>
        </tr>
        <br />
        <?php for($i=0;$i<5;$i++): ?>
        <tr>        
            <th class="text-right w-1/2"><?php echo switch_num($i + 1) ?> INSTALLMENT</th>
            <td class="w-1/2"><?php echo number_format($tuition['installment_fee'], 2, '.' ,','); ?></td>            
        </tr>
        <?php endfor; ?>
        <hr />
        <tr>
            <th class="text-right w-1/2">Total for installment</th>
            <td class="text-green-400"><?php echo number_format($tuition['total_installment'], 2, '.' ,','); ?></td>
        </tr>   
    </tbody>
    </table>
