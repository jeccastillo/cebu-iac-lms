<?php $paid = 0; ?>
<aside class="right-side">
<section class="content-header">
    <h1>
        <small>
            <a class="btn btn-app" href="<?php echo base_url() ?>student/view_all_students" ><i class="ion ion-arrow-left-a"></i>All Students</a> 
                            <a class="btn btn-app trash-student-record2" rel="<?php echo $student['intID']; ?>" href="#"><i class="ion ion-android-close"></i> Delete</a>   
                            <a class="btn btn-app" href="<?php echo base_url()."student/edit_student/".$student['intID']; ?>"><i class="ion ion-edit"></i> Edit</a> 
                            <a class="btn btn-app" href="<?php echo base_url()."pdf/student_viewer_registration_print/".$student['intID'] ."/". $active_sem['intID']; ?>">
                                <i class="ion ion-printer"></i>Reg Form Print Preview</a> 
                            
                            
        </small>
        <div class="pull-right">
            <?php if($registration): ?>
                         <label style="font-size:.6em;"> Registration Status</label>
                            
                            <select class="form-control" rel="<?php echo $registration['intRegistrationID']; ?>" id="ROGStatusChange">
                                <option <?php echo ($registration['intROG'] == 0)?'selected':'' ?>  value="0">Registered</option>
                                <option <?php echo ($registration['intROG'] == 1)?'selected':'' ?> value="1">Enrolled</option>
                                <option <?php echo ($registration['intROG'] == 2)?'selected':'' ?> value="2">Cleared</option>
                            </select>
                            <?php endif; ?>
    </div>
    </h1>


</section>
    <hr />
<div class="content">
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-widget widget-user-2">
            <!-- Add the bg color to the header using any of the bg-* classes -->
            <div class="widget-user-header bg-red">
              <!-- /.widget-user-image -->
              <h3 class="widget-user-username" style="text-transform:capitalize;margin-left:0;font-size:1.3em;"><?php echo strtolower($student['strLastname'].", ". $student['strFirstname']); ?>
                        <?php echo ($student['strMiddlename'] != "")?' '.strtolower($student['strMiddlename']):''; ?></h3>
              <h5 class="widget-user-desc" style="margin-left:0;"><?php echo $student['strProgramCode']." Major in ".$student['strMajor']; ?></h5>
            </div>
            <div class="box-footer no-padding">
              <ul class="nav nav-stacked">
                <li><a href="#" style="font-size:13px;">Student Number <span class="pull-right text-blue"><?php echo $student['strStudentNumber']; ?></span></a></li>
                <li><a style="font-size:13px;" href="#">Registration Status <span class="pull-right"><?php echo $reg_status; ?></span></a></li>
                <li><a style="font-size:13px;" href="#">Date Registered <span class="pull-right"><?php echo ($registration)?'<span style="color:#009000;">'.$registration['dteRegistered'].'</span>':'<span style="color:#900000;">N/A</span>'; ?></span></a></li>
                  <li><a style="font-size:13px;" href="#">Scholarship Type <span class="pull-right"><?php echo $registration['enumScholarship']; ?></span></a></li>
                  
              </ul>
            </div>
          </div>
            
        </div>
        
    
        <div class="col-sm-12">
            <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li><a href="<?php echo base_url(); ?>unity/student_viewer/<?php echo $student['intID']; ?>/<?php echo $selected_ay; ?>/tab_1">Personal Information</a></li>
             <?php if(in_array($user['intUserLevel'],array(2,4)) ): ?>
                <li><a href="<?php echo base_url(); ?>unity/student_viewer/<?php echo $student['intID']; ?>/<?php echo $selected_ay; ?>/tab_2">Report of Grades</a></li>
            <li><a href="<?php echo base_url(); ?>unity/student_viewer/<?php echo $student['intID']; ?>/<?php echo $selected_ay; ?>/tab_3">Assessment</a></li>
            <?php endif; ?>
              <li><a href="<?php echo base_url(); ?>unity/student_viewer/<?php echo $student['intID']; ?>/<?php echo $selected_ay; ?>/tab_5">Schedule</a></li>
              <li class="active"><a href="#tab_1" data-toggle="tab">Statement of Account</a></li>
              <li><a href="<?php echo base_url()."unity/accounting/".$student['intID']; ?>">Accounting Summary</a></li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane active" id="tab_1">
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
                        <th>Actions</th>
                    </tr>
                <?php foreach($transactions as $transaction): 
                                    $paid += $transaction['totalAmountPaid'];
                                ?>

                        <tr>
                          <td><?php echo $transaction['intORNumber']; ?></td>
                          <td><?php echo $transaction['totalAmountPaid']; ?></td>
                          <td><?php echo date("M j, Y",strtotime($transaction['dtePaid'])); ?></td>
                          <td>
                         <button type="button" rel="<?php echo $transaction['intORNumber']; ?>" class="btn btn-box-tool view-or"><i style="font-size:2em;" class="ion ion-eye"></i></button>
                        <button type="button" rel="<?php echo $transaction['intORNumber']; ?>" class="btn btn-box-tool trash-or"><i style="font-size:2em;" class="ion ion-trash-a"></i></button>
                        <a target="_blank" href="<?php echo base_url();?>pdf/registration_viewer_account_data_print/<?php echo $transaction['intORNumber']; ?>/<?php echo $student['intID']; ?>" class="btn btn-box-tool print-transaction"><i style="font-size:2em;" class="ion ion-printer"></i></a> 
                            </td>
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
    <div class="box box-solid <?php echo ($remaining_balance>0 && ($registration['enumScholarship'] == "paying" || $registration['enumScholarship'] == "7th district scholar"))?'box-danger':'box-success' ?>">
            <div class="box-header">
                <h4 class="box-title">ASSESSMENT OF FEES</h4>
            </div>
            <input type="hidden" id="intAYID" value="<?php echo $selected_ay; ?>">
            <div class="box-body">
                <div class="row">
                    <div class="col-sm-6">
                            <div class="row">
                                <div class="col-sm-4">Tuition:</div>
                                <div class="col-sm-6 text-green"><?php echo $tuition['tuition']; ?>
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
                            foreach($tuition['misc_list'] as $key=>$val): ?>
                            <div class="row">
                                <div class="col-sm-4" style="text-align:right;"><?php echo $key; ?>:</div>
                                <div class="col-sm-6"><?php echo $val; ?></div>
                            </div>
                            <?php 
                            endforeach; ?>
                            <div class="row">
                                <div class="col-sm-4" style="text-align:right;">Total:</div>
                                <div class="col-sm-6 text-green"><?php echo $tuition['misc']; ?>
                                <?php if(isset($payment['misc'])): ?>
                                    (-<?php echo $payment['misc']; ?>)
                                <?php endif; ?>
                                </div>
                                <div class="col-sm-1 text-green">
                                    <?php if(isset($payment['misc']) && ($payment['misc'] >= $tuition['misc'])): ?>
                                        <i style="margin-left:5rem;" class="ion-checkmark-circled ion"></i>
                                    <?php endif; ?>
                                </div>

                            </div>
                            <hr />
                            <?php if($tuition['nsf'] != 0): ?>
                            <div class="row">
                                <div class="col-sm-4">MISC - NEW STUDENT:</div>
                                <div class="col-sm-6 text-green"><?php echo $tuition['nsf']; ?>
                                <?php if(isset($payment['nsf'])): ?>
                                    (-<?php echo $payment['nsf']; ?>)
                                <?php endif; ?>
                                </div>
                                <div class="col-sm-1 text-green">
                                    <?php if((isset($payment['nsf'])) && ($payment['nsf'] >= $tuition['nsf'])): ?>
                                        <i style="margin-left:5rem;" class="ion-checkmark-circled ion"></i>

                                    <?php endif; ?>
                                </div>
                            </div>
                            <hr />
                            <?php endif; ?>
                            <div class="row">
                                <div class="col-sm-4">LAB FEES:</div>
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
                                <div class="col-sm-6 text-green"><?php echo $tuition['lab']; ?>
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
                            <?php if($tuition['thesis_fee'] != 0): ?>
                            <div class="row">
                                <div class="col-sm-4">THESIS FEE: </div>
                                <div class="col-sm-6 text-green"><?php echo $tuition['thesis_fee']; ?>
                                <?php if(isset($payment['thesis_fee'])): ?>
                                    (-<?php echo $payment['thesis_fee']; ?>)
                                <?php endif; ?>
                                </div>
                                <div class="col-sm-1 text-green">
                                    <?php if((isset($payment['thesis_fee'])) && ($payment['thesis_fee'] >= $tuition['thesis_fee'])): ?>
                                        <i style="margin-left:5rem;" class="ion-checkmark-circled ion"></i>

                                    <?php endif; ?>
                                </div>
                            </div>
                            <hr />
                            <?php endif; ?>                                                     
                            <div class="row">
                                <div class="col-sm-4">Total:</div>
                                <div class="col-sm-6 text-green"><?php echo $tuition['total']; ?></div>
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
                        <div class="col-sm-6">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h4 class="modal-title">New Transaction</h4>
                              </div>
                              <div class="modal-body">
                                  <div class="alert alert-modal alert-danger" style="display:none;">
                                        <i class="fa fa-ban"></i>
                                      <span id="sched-alert"></span>
                                    </div>
                                    <input type="hidden" value="<?php echo $registration['intRegistrationID']; ?>" id="intRegistrationID" name="intRegistrationID" />
                                  <div class="row">
                                    <div class="form-group col-xs-12 col-lg-12">
                                        <label for="section">OR Number</label>
                                        <div class="input-group">
                                                  <input <?php echo ($remaining_balance==0)?'disabled':''; ?> id="intORNumber" type="number" name="intORNumber" class="form-control">
                                                  <span class="input-group-btn">
                                                    <button <?php echo ($remaining_balance==0)?'disabled':''; ?> type="button" id="generate-or-num" rel="OR" class="btn btn-danger btn-flat">Generate</button>
                                                  </span>
                                                </div>

                                    </div>
                                  </div>
                                    <div id="transaction-wrapper" class="row">
                                        <div class="transaction-group">
                                            <div class="form-group col-xs-12 col-lg-6">
                                                <label for="section">Amount To Pay</label>
                                                <input <?php echo ($remaining_balance==0)?'disabled':''; ?> id="intAmountPaid" type="number" name="intAmountPaid[]" class="form-control">
                                            </div>
                                            <div class="form-group col-xs-12 col-lg-6">
                                                <label for="section">Transaction Type</label>
                                                <select <?php echo ($remaining_balance==0)?'disabled':''; ?> class="form-control" id="strTransactionType" name="strTransactionType[]" >                      
                                                        <option value="tuition">Tuition</option>     
                                                        <option value="misc">Miscellaneous</option>
                                                        <option value="id fee">ID Fee</option>     
                                                        <option value="athletic fee">Athletic Fee</option>
                                                        <option value="srf">SRF</option>     
                                                        <option value="sfdf">SFDF</option>
                                                        <option value="lab fee">Lab Fee</option>     
                                                        <option value="csg">CSG</option>
                                                    </select>
                                            </div>  
                                        </div>
                                    </div>
                                    <a <?php echo ($remaining_balance==0)?'disabled':''; ?> class="btn btn-block btn-default  btn-flat" href="#" id="addTransactionField">Add Field</a>
                                    </div>        
                              <div class="modal-footer">
                                <button <?php echo ($remaining_balance==0)?'disabled':''; ?> type="button" id="addTransactionBtn" class="btn btn-default  btn-flat">Add Transaction</button>
                              </div>
                            </div>
                        </div>
                    </div>
                </div>
    </div>
              </div>
            </div>
        </div>
        </div>
    </div>
    
    
        
</div>
<div class="modal fade" id="transactionsModal" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Transactions</h4>
      </div>
      <div class="modal-body" id="transactionsBody">
        </div>
      </div>
    </div>
</div>

