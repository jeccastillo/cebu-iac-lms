<?php 
    error_reporting(0);
?>
<aside class="right-side">
<section class="content-header">
            <h1>
                        My Accounting Summary
                        <small>view your accounting information</small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="<?php echo base_url() ?>portal/dashboard"><i class="fa fa-home"></i> Home</a></li>
                        <li class="active">Accounting</li>
                    </ol>
                    <div class="box-tools pull-right">
                        <form action="#" method="get" class="sidebar-form">
                      
                            <select id="select-sem" class="form-control" >
                                <?php foreach($sy as $s): ?>
                                    <option rel='<?php echo $page ?>' <?php echo ($selected_ay == $s['intID'])?'selected':''; ?> value="<?php echo $s['intID']; ?>"><?php echo $s['enumSem']." ".$term_type." ".$s['strYearStart']."-".$s['strYearEnd']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                     </div>
                     <div style="clear:both"></div>
</section>
<div class="content">
    <input type="hidden" value="<?php echo $student['intID'] ?>" id="student-id" />
    <input type="hidden" value="<?php echo $active_sem['intID']; ?>" id="active-sem-id" />

    <?php if(!empty($transactions)): ?>
        <div class="box box-solid box-success">
            <div class="box-header">
                <h4 class="box-title">Transactions</h4>
            </div>
            <div class="box-body">
                <table class="table table-bordered">
                    <tr>
                        <th>OR Number</th>
                        <th>Amount Paid</th>
                        <th>Date Paid</th>
                    </tr>
                <?php foreach($transactions as $transaction): 
                                    $paid += $transaction['totalAmountPaid'];
                                ?>

                        <tr>
                          <td><?php echo $transaction['intORNumber']; ?></td>
                          <td><?php echo $transaction['totalAmountPaid']; ?></td>
                          <td><?php echo date("M j, Y",strtotime($transaction['dtePaid'])); ?></td>
                        </tr>

                        <!-- /.box-body -->

                <?php endforeach;
                ?>
                </table>
            </div>
        </div>
    
    <?php
    else:?>
        <div class="info-box">
            <span class="info-box-icon bg-green"><i class="ion ion-cash"></i></span>

                <div class="info-box-content">
                    <span class="info-box-text">No Transactions</span>
                </div>  
        </div>
    <?php
    endif;
    $remaining_balance = $tuition['total'] - $paid;
    ?>

    <input type="hidden" value="<?php echo $paid; ?>" id="totalPaid" />
    <input type="hidden" value="<?php echo $tuition['total']; ?>" id="tuitionTotal" />
    
<div class="row">
    
    <div class="col-md-6">
    <div class="box box-solid <?php echo ($remaining_balance>0 && ($registration['enumScholarship'] == "paying" || $registration['enumScholarship'] == "7th district scholar"))?'box-danger':'box-success' ?>">
    <?php if ($registration['intROG'] <= 0):  { ?>
                <div class="box-header">
                    <h4 class="box-title">NO ASSESSMENT OF FEES</h4>
                </div>
                <div class="box-body">
                <div class="row">
                    <div class="col-sm-4">Tuition:</div>
                    <div class="col-sm-5 text-green">
                   
                    </div>
                    <div class="col-sm-1 text-green">
                      
                    </div>
                </div>
                <hr />
                
                <div class="row">
                    <div class="col-sm-4">Miscellaneous:</div>
                    <div class="col-sm-6 text-green"></div>
                </div>
                 
             
                <div class="row">
                    <div class="col-sm-4" style="text-align:right;"></div>
                    <div class="col-sm-6"></div>
                </div>
              
                <div class="row">
                    <div class="col-sm-4" style="text-align:right;">Total:</div>
                    <div class="col-sm-5 text-green">
                
                    </div>
                    <div class="col-sm-1 text-green">
                        
                    </div>
                    
                </div>
                <hr />
                <div class="row">
                    <div class="col-sm-4">ID Fee:</div>
                    <div class="col-sm-5 text-green">
                    
                    </div>
                    <div class="col-sm-1 text-green">
                        
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-4">Athletic Fee:</div>
                    <div class="col-sm-5 text-green">
                    
                    </div>
                    <div class="col-sm-1 text-green">
                        
                    </div>
                </div>
                <hr />
                <div class="row">
                    <div class="col-sm-4">SRF:</div>
                    <div class="col-sm-5 text-green">
                  
                    </div>
                    <div class="col-sm-1 text-green">
                      
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-4">SFDF:</div>
                    <div class="col-sm-5 text-green">
                   
                    </div>
                    <div class="col-sm-1 text-green">
                      
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-4">Lab Fee:</div>
                    <div class="col-sm-6"></div>
                </div>
               
                <div class="row">
                    <div class="col-sm-4" style="text-align:right;">Total:</div>
                    <div class="col-sm-5 text-green">
                    </div>
                    <div class="col-sm-1 text-green">
                        
                    </div>
                </div>
                <hr />
                <div class="row">
                    <div class="col-sm-4">CSG:</div>
                    <div class="col-sm-6"></div>
                </div>
                <div class="row">
                    <div class="col-sm-4" style="text-align:right;">Student Handbook:</div>
                    <div class="col-sm-6"></div>
                </div>
                <div class="row">
                    <div class="col-sm-4" style="text-align:right;">Student Publication:</div>
                    <div class="col-sm-6"></div>
                </div>
                <div class="row">
                    <div class="col-sm-4" style="text-align:right;">Total:</div>
                    <div class="col-sm-5 text-green">
                    </div>
                    <div class="col-sm-1 text-green">
                        
                    </div>
                </div>
                <hr />
                <div class="row">
                    <div class="col-sm-4">Total:</div>
                    <div class="col-sm-5 text-green"></div>
                    <div class="col-sm-1 text-green">
                        
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-4">Total Amount Paid:</div>
                    <div class="col-sm-6"></div>
                </div>
                <hr />
                <div class="row">
                    <div class="col-sm-4">Remaining Balance:</div>
                    <div class="col-sm-6"></div>
                </div>
            </div>
    <?php } ?>
    <?php else: ?>

            <div class="box-header">
                <h4 class="box-title">ASSESSMENT OF FEES</h4>
            </div>
            <input type="hidden" id="intAYID" value="<?php echo $selected_ay; ?>">
            <div class="box-body">
                <div class="row">
                    <div class="col-sm-4">Tuition:</div>
                    <div class="col-sm-5 text-green"><?php echo $tuition['tuition']; ?>
                    <?php if(isset($payment['tuition'])): ?>
                        (-<?php echo $payment['tuition']; ?>)
                    <?php endif; ?>
                    </div>
                    <div class="col-sm-1 text-green">
                        <?php if((isset($payment['tuition'])) && ($payment['tuition'] >= $tuition['tuition'])): ?>
                            <i style="margin-left:5rem;" class="ion-checkmark-circled ion"></i>
                    
                        <?php endif; ?>
                    </div>
                </div>
                <hr />
                
                <div class="row">
                    <div class="col-sm-4">Miscellaneous:</div>
                    <div class="col-sm-6 text-green"></div>
                </div>
                <?php 
                $total_misc = 0;
                foreach($tuition['misc_fee'] as $key=>$val): ?>
                <div class="row">
                    <div class="col-sm-4" style="text-align:right;"><?php echo $key; ?>:</div>
                    <div class="col-sm-6"><?php echo $val; ?></div>
                </div>
                <?php 
                $total_misc += $val;
                endforeach; ?>
                <div class="row">
                    <div class="col-sm-4" style="text-align:right;">Total:</div>
                    <div class="col-sm-5 text-green"><?php echo $total_misc; ?>
                    <?php if(isset($payment['misc'])): ?>
                        (-<?php echo $payment['misc']; ?>)
                    <?php endif; ?>
                    </div>
                    <div class="col-sm-1 text-green">
                        <?php if(isset($payment['misc']) && ($payment['misc'] >= $total_misc)): ?>
                            <i style="margin-left:5rem;" class="ion-checkmark-circled ion"></i>
                        <?php endif; ?>
                    </div>
                    
                </div>
                <hr />
                <div class="row">
                    <div class="col-sm-4">ID Fee:</div>
                    <div class="col-sm-5 text-green"><?php echo $tuition['id_fee']; ?>
                    <?php if(isset($payment['id fee'])): ?>
                        (-<?php echo $payment['id fee']; ?>)
                    <?php endif; ?>
                    </div>
                    <div class="col-sm-1 text-green">
                        <?php if((isset($payment['id fee'])) && ($payment['id fee'] >= $tuition['id_fee'])): ?>
                            <i style="margin-left:5rem;" class="ion-checkmark-circled ion"></i>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-4">Athletic Fee:</div>
                    <div class="col-sm-5 text-green"><?php echo $tuition['athletic']; ?>
                    <?php if(isset($payment['athletic fee'])): ?>
                        (-<?php echo $payment['athletic fee']; ?>)
                    <?php endif; ?>
                    </div>
                    <div class="col-sm-1 text-green">
                        <?php if((isset($payment['athletic fee'])) && ($payment['athletic fee'] >= $tuition['athletic'])): ?>
                            <i style="margin-left:5rem;" class="ion-checkmark-circled ion"></i>
                        <?php endif; ?>
                    </div>
                </div>
                <hr />
                <div class="row">
                    <div class="col-sm-4">SRF:</div>
                    <div class="col-sm-5 text-green"><?php echo $tuition['srf']; ?>
                    <?php if(isset($payment['srf'])): ?>
                        (-<?php echo $payment['srf']; ?>)
                    <?php endif; ?>
                    </div>
                    <div class="col-sm-1 text-green">
                        <?php if((isset($payment['srf'])) && ($payment['srf'] >= $tuition['srf'])): ?>
                            <i style="margin-left:5rem;" class="ion-checkmark-circled ion"></i>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-4">SFDF:</div>
                    <div class="col-sm-5 text-green"><?php echo $tuition['sfdf']; ?>
                    <?php if(isset($payment['sfdf'])): ?>
                        (-<?php echo $payment['sfdf']; ?>)
                    <?php endif; ?>
                    </div>
                    <div class="col-sm-1 text-green">
                        <?php if((isset($payment['sfdf'])) && ($payment['sfdf'] >= $tuition['sfdf'])): ?>
                            <i style="margin-left:5rem;" class="ion-checkmark-circled ion"></i>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-4">Lab Fee:</div>
                    <div class="col-sm-6"></div>
                </div>
                <?php foreach($tuition['lab_list'] as $key=>$val): ?>
                <div class="row">
                    <div class="col-sm-4" style="text-align:right;"><?php echo $key; ?>:</div>
                    <div class="col-sm-6"><?php echo $val; ?></div>
                </div>
                <?php endforeach; ?>
                <div class="row">
                    <div class="col-sm-4" style="text-align:right;">Total:</div>
                    <div class="col-sm-5 text-green"><?php echo $tuition['lab']; ?>
                           <?php if(isset($payment['lab fee'])): ?>
                        (-<?php echo $payment['lab fee']; ?>)
                    <?php endif; ?>
                    </div>
                    <div class="col-sm-1 text-green">
                        <?php if((isset($payment['lab fee'])) && ($payment['lab fee'] >= $tuition['lab'])): ?>
                            <i style="margin-left:5rem;" class="ion-checkmark-circled ion"></i>
                        <?php endif; ?>
                    </div>
                </div>
                <hr />
                <div class="row">
                    <div class="col-sm-4">CSG:</div>
                    <div class="col-sm-6"></div>
                </div>
                <div class="row">
                    <div class="col-sm-4" style="text-align:right;">Student Handbook:</div>
                    <div class="col-sm-6"><?php echo $tuition['csg']['student_handbook']; ?></div>
                </div>
                <div class="row">
                    <div class="col-sm-4" style="text-align:right;">Student Publication:</div>
                    <div class="col-sm-6"><?php echo $tuition['csg']['student_publication']; ?></div>
                </div>
                <div class="row">
                    <div class="col-sm-4" style="text-align:right;">Total:</div>
                    <div class="col-sm-5 text-green"><?php echo $tuition['csg']['student_handbook']+$tuition['csg']['student_publication']; ?>
                    <?php if(isset($payment['csg'])): ?>
                        (-<?php $payment['csg']; ?>)
                    <?php endif; ?>
                    </div>
                    <div class="col-sm-1 text-green">
                        <?php if((isset($payment['csg'])) && ($payment['csg'] >= $tuition['csg']['student_handbook']+$tuition['csg']['student_publication'])): ?>
                            <i style="margin-left:5rem;" class="ion-checkmark-circled ion"></i>
                        <?php endif; ?>
                    </div>
                </div>
                <hr />
                <div class="row">
                    <div class="col-sm-4">Total:</div>
                    <div class="col-sm-5 text-green"><?php echo $tuition['total']; ?></div>
                    <div class="col-sm-1 text-green">
                        <?php if($paid >= $tuition['total']): ?>
                            <i style="margin-left:5rem;" class="ion-checkmark-circled ion"></i>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-4">Total Amount Paid:</div>
                    <div class="col-sm-6"><?php echo $paid; ?></div>
                </div>
                <hr />
                <div class="row">
                    <div class="col-sm-4">Remaining Balance:</div>
                    <div class="col-sm-6"><?php echo $remaining_balance; ?></div>
                </div>
            </div>
    </div>
    </div>
    <?php endif; ?>
    <div class="col-md-6">
        <div>
        <div class="info-box">
                <span class="info-box-icon bg-red"><i class="fa fa-star"></i></span>

                <div class="info-box-content">
                  <span class="info-box-text">VIEWING ACADEMIC YEAR</span>
                  <span class="info-box-number"><?php echo $active_sem['enumSem']." ".$term_type." ".$active_sem['strYearStart']."-".$active_sem['strYearEnd']; ?></span>


                </div>
                <!-- /.info-box-content -->
        </div>
        </div>
        <div>
        <div class="info-box">
                <span class="info-box-icon bg-green"><i class="ion ion-person"></i></span>

                <div class="info-box-content">
                  <span class="info-box-text">ENROLMENT STATUS and DATE</span>
                  <span class="info-box-number"><?php echo ($registration['intROG'])?'<span style="color:#009000;">Enrolled</span>':'<span style="color:#900000;">Not Enrolled</span>'; ?></span>
                    <span class="info-box-text"><?php echo ($registration['intROG'])?'<span style="color:#009000;">'.$registration['dteRegistered'].'</span>':'<span style="color:#900000;">N/A</span>'; ?></span>


                </div>
                <!-- /.info-box-content -->
        </div>
        </div>
        <div>
        <div class="info-box">
                <span class="info-box-icon bg-green"><i class="ion ion-university"></i></span>

                <div class="info-box-content">
                  <span class="info-box-text">SCHOLARSHIP</span>
                  <span class="info-box-number"><?php echo $registration['enumScholarship']; ?></span>
                    


                </div>
                <!-- /.info-box-content -->
        </div>
        </div>
    </div>
              </div>
            </div>
        </div>
        </div>
    </div>
</div>

