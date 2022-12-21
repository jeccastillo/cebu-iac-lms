    <div class="grid grid-cols-2 grid-flow-col gap-4">
        <div>
            <table class="table-fixed border-collapse w-full border border-slate-400 dark:border-slate-500 bg-white dark:bg-slate-800 text-sm shadow-sm">
                <tbody>
                    <tr>
                    <td class="border border-slate-300 dark:border-slate-600 font-semibold p-4 text-slate-900 dark:text-slate-200 text-right w-1/4">Tuition:</td>
                    <td class="w-3/4 border border-slate-300 dark:border-slate-700 p-4 text-slate-500 dark:text-slate-400"><?php echo $tuition['tuition']; ?></td>      
                    </tr>
                    <tr>
                        <td colspan="2" class="w-1/2 border border-slate-200 dark:border-slate-500 font-semibold p-4 text-slate-600 dark:text-slate-400 text-center">Miscellaneous</td>                        
                    </tr>
                <?php foreach($tuition['misc_list'] as $key=>$val): ?>
                    <tr>
                        <td class="border border-slate-300 dark:border-slate-600 font-semibold p-4 text-slate-900 dark:text-slate-200 text-right w-1/2"><?php echo $key; ?></td>
                        <td class="w-1/2 border border-slate-300 dark:border-slate-700 p-4 text-slate-500 dark:text-slate-400"><?php echo $val; ?></td>      
                    </tr>
                <?php endforeach; ?>
                    <tr>
                        <td class="border border-slate-300 dark:border-slate-600 font-semibold p-4 text-slate-900 dark:text-slate-200 text-right w-1/2">Total:</td>
                        <td class="w-1/2 border border-slate-300 dark:border-slate-700 p-4 text-slate-500 dark:text-slate-400"><?php echo $tuition['misc']; ?></td>      
                    </tr>    
                    <tr>
                        <td class="border border-slate-300 dark:border-slate-600 font-semibold p-4 text-slate-900 dark:text-slate-200 text-right w-1/2">Laboratory Fee:</td>
                        <td class="text-green-400"></td>
                    </tr>
                    <hr />                
                    <?php foreach($tuition['lab_list'] as $key=>$val): ?>
                        <tr>
                            <td class="border border-slate-300 dark:border-slate-600 font-semibold p-4 text-slate-900 dark:text-slate-200 text-right w-1/2"><?php echo $key; ?></td>
                            <td class="w-1/2 border border-slate-300 dark:border-slate-700 p-4 text-slate-500 dark:text-slate-400"><?php echo $val; ?></td>
                        </tr>
                    <?php endforeach; ?>        
                    <tr>
                        <td class="border border-slate-300 dark:border-slate-600 font-semibold p-4 text-slate-900 dark:text-slate-200 text-right w-1/2">Total:</td>
                        <td class="text-green-400"><?php echo $tuition['lab']; ?></td>
                    </tr>
                    <hr />
                    <?php if($tuition['thesis_fee']!= 0): ?>                    
                        <tr>
                            <td class="border border-slate-300 dark:border-slate-600 font-semibold p-4 text-slate-900 dark:text-slate-200 text-right w-1/2">THESIS FEE: </td>
                            <td class="text-green-400"><?php echo $tuition['thesis_fee']; ?></td>
                        </tr>
                        <hr />                    
                    <?php endif; ?>
                    <?php if($tuition['internship_fee']!= 0): ?>
                        
                        <tr>
                            <td class="border border-slate-300 dark:border-slate-600 font-semibold p-4 text-slate-900 dark:text-slate-200 text-right w-1/2">Internship Fees:</td>
                            <td class="text-green-400"></td>
                        </tr>
                        <hr />
                        
                        <?php foreach($tuition['internship_fee_list'] as $key=>$val): ?>
                            <tr>
                                <td class="border border-slate-300 dark:border-slate-600 font-semibold p-4 text-slate-900 dark:text-slate-200 text-right w-1/2"><?php echo $key; ?></td>
                                <td class="w-1/2 border border-slate-300 dark:border-slate-700 p-4 text-slate-500 dark:text-slate-400"><?php echo $val; ?></td>
                            </tr>
                        <?php endforeach; ?>

                        
                        <tr>
                            <td class="border border-slate-300 dark:border-slate-600 font-semibold p-4 text-slate-900 dark:text-slate-200 text-right w-1/2">Total:</td>
                            <td class="text-green-400"><?php echo $tuition['internship_fee']; ?></td>
                        </tr>
                        <hr />
                    <?php endif; ?>                    
                </tbody>
            </table>
        </div>
        <div>
            <table class="table-fixed border-collapse w-full border border-slate-400 dark:border-slate-500 bg-white dark:bg-slate-800 text-sm shadow-sm">
                <tbody>
                <?php if($tuition['new_student']!= 0): ?>
                    <tr>
                        <td class="border border-slate-300 dark:border-slate-600 font-semibold p-4 text-slate-900 dark:text-slate-200 text-right w-1/2">New Student Fees:</td>
                        <td class="text-green-400"></td>
                    </tr>
                    
                    <?php foreach($tuition['new_student_list'] as $key=>$val): ?>                
                        <tr>
                            <td class="border border-slate-300 dark:border-slate-600 font-semibold p-4 text-slate-900 dark:text-slate-200 text-right w-1/2"><?php echo $key; ?></td>
                            <td class="w-1/2 border border-slate-300 dark:border-slate-700 p-4 text-slate-500 dark:text-slate-400"><?php echo $val; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    
                    <tr>
                        <td class="border border-slate-300 dark:border-slate-600 font-semibold p-4 text-slate-900 dark:text-slate-200 text-right w-1/2">Total:</td>
                        <td class="text-green-400"><?php echo $tuition['new_student']; ?></td>
                    </tr>
                    <hr />
                    <?php endif; ?>                        
                    <tr>
                        <td class="border border-slate-300 dark:border-slate-600 font-semibold p-4 text-slate-900 dark:text-slate-200 text-right w-1/2">Total Amount Due:</td>
                        <td class="text-green-400"><?php echo number_format($tuition['total'], 2, '.' ,','); ?></td>
                    </tr>                    
                    <tr>
                        <td class="border border-slate-300 dark:border-slate-600 font-semibold p-4 text-slate-900 dark:text-slate-200 text-right w-1/2">Down Payment</td>
                        <td class="w-1/2 border border-slate-300 dark:border-slate-700 p-4 text-slate-500 dark:text-slate-400"><?php echo number_format($tuition['down_payment'], 2, '.' ,','); ?></td>
                    </tr>
                    <br />
                    <?php for($i=0;$i<5;$i++): ?>
                    <tr>        
                        <td class="border border-slate-300 dark:border-slate-600 font-semibold p-4 text-slate-900 dark:text-slate-200 text-right w-1/2"><?php echo switch_num($i + 1) ?> INSTALLMENT</td>
                        <td class="w-1/2 border border-slate-300 dark:border-slate-700 p-4 text-slate-500 dark:text-slate-400"><?php echo number_format($tuition['installment_fee'], 2, '.' ,','); ?></td>            
                    </tr>
                    <?php endfor; ?>
                    <hr />
                    <tr>
                        <td class="border border-slate-300 dark:border-slate-600 font-semibold p-4 text-slate-900 dark:text-slate-200 text-right w-1/2">Total for installment</td>
                        <td class="text-green-400"><?php echo number_format($tuition['total_installment'], 2, '.' ,','); ?></td>
                    </tr>   
                </tbody>
            </table>
        </div>
    </div>
