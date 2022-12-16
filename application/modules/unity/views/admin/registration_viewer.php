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
        <div class="box box-solid">
            <div class="box-header">
                <h4 class="box-title">ASSESSMENT OF FEES</h4>
            </div>
            <input type="hidden" id="intAYID" value="<?php echo $selected_ay; ?>">
            <div class="box-body">
                <div class="row">
                    <div class="col-sm-6">
                        <?php echo $tuition; ?>
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

