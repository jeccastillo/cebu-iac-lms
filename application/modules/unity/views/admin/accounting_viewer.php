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
                
            </div>
        </div>
    
    <?php
     $paid = 0;
   endfor;
   ?>
    <?php
   
     endif;
     ?>
    </div>
    
    
        
</div>
