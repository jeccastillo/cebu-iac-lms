<?php $paid = 0; ?>
<aside class="right-side">
<section class="content-header">
    <h1>
        <small>
            <a class="btn btn-app" href="<?php echo base_url() ?>student/view_all_students" ><i class="ion ion-arrow-left-a"></i>All Students</a> 
                            <a class="btn btn-app trash-student-record2" rel="<?php echo $student['intID']; ?>" href="#"><i class="ion ion-android-close"></i> Delete</a>   
                            <a class="btn btn-app" href="<?php echo base_url()."student/edit_student/".$student['intID']; ?>"><i class="ion ion-edit"></i> Edit</a> 
                            
                            
        </small>
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
                <?php if($active_registration && in_array($user['intUserLevel'],array(2,3,4,6))): ?>
              <li><a href="<?php echo base_url(); ?>unity/student_viewer/<?php echo $student['intID']; ?>/<?php echo $selected_ay; ?>/tab_5">Schedule</a></li>
              <li><a href="<?php echo base_url()."unity/registration_viewer/".$student['intID']."/".$selected_ay; ?>">Statement of Account</a></li>
            <?php endif; ?>
              <li class="active"><a href="#tab_1" data-toggle="tab">Accounting Summary</a></li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane active" id="tab_1">
        <?php  if(!empty($transactions)): 
        for($i=0;$i<count($transactions);$i++):          
        ?>
        <div class="box box-solid box-success">
            <div class="box-header">
                <?php
                if($sy[$i]['intID'] != $selected_ay)
                    $sm = $sy[$i]['enumSem']." ".$term_type." ".$sy[$i]['strYearStart']."-".$sy[$i]['strYearEnd'];
                else
                    $sm = "current semester";
                ?>
                <h4 class="box-title">Transactions for <?php echo $sm; ?></h4>
            </div>
            <div class="box-body">
                <table class="table table-bordered">
                    <tr>
                        <th>OR Number</th>
                        <th>Amount Paid</th>
                        <th>Date Paid</th>
                    </tr>
                <?php foreach($transactions[$i] as $transaction): 
                                    $paid += $transaction['totalAmountPaid'];
                                ?>

                        <tr>
                          <td><?php echo $transaction['intORNumber']; ?></td>
                          <td><?php echo $transaction['totalAmountPaid']; ?>php</td>
                          <td><?php echo date("M j, Y",strtotime($transaction['dtePaid'])); ?></td>
                          
                        </tr>

                        <!-- /.box-body -->

                <?php endforeach;
                    $remaining_balance = $tuition[$i]['total'] - $paid;
                ?>
                    <tr>
                        <td colspan="3">
                           Total: <?php echo $tuition[$i]['total']; ?>php
                        </td>
                    </tr>
                    <tr>
                        <td style="<?php echo ($remaining_balance!=0)?'background:#c55;color:#fff;':''; ?>" colspan="3">
                           remaining balance: <?php echo $remaining_balance; ?>php
                        </td>
                    </tr>
                </table>

                <hr />
                <div class="row">
                    <div class="col-sm-4">Repeated Subjects:</div>
                    <div class="col-sm-6"></div>
                </div>
                <?php foreach($tuition[$i]['repeated'] as $repeated): ?>
                <div class="row">
                    <div class="col-sm-4" style="text-align:right;"><?php echo $repeated['subjectCode']; ?>:</div>
                    <div class="col-sm-6">
                            <?php echo $repeated['amount']; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <div class="row">
                    <div class="col-sm-4" style="text-align:right;">Total:</div>
                    <div class="col-sm-6 text-green"><?php echo $tuition[$i]['total_for_repeated']; ?></div>                                
                </div>
            </div>
        </div>
    
    <?php
     $paid = 0;
   endfor;
   ?>
    <?php
   
     endif;
     ?>
    <?php /*
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
                                <div class="col-sm-6 text-green"><?php echo $total_misc; ?>
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
                                <div class="col-sm-6 text-green"><?php echo $tuition['id_fee']; ?>
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
                                <div class="col-sm-6 text-green"><?php echo $tuition['athletic']; ?>
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
                                <div class="col-sm-6 text-green"><?php echo $tuition['srf']; ?>
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
                                <div class="col-sm-6 text-green"><?php echo $tuition['sfdf']; ?>
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
                                <div class="col-sm-6 text-green"><?php echo $tuition['csg']['student_handbook']+$tuition['csg']['student_publication']; ?>
                                <?php if(isset($payment['csg'])): ?>
                                    (-<?php echo $payment['csg']; ?>)
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
        */ ?>
    </div>
    
    
        
</div>
