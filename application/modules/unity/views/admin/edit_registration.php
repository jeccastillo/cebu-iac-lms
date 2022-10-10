<?php 
    error_reporting(0);
?>
<style>
    .green-bg
    {
        background-color:#77cc77;
    }
    .red-bg
    {
        background-color:#cc7777;
    }
    .select2-container
    {
        display: block !important;
    }
</style>
<aside class="right-side">


    <section class="content-header">
                    <h1>
                        <small>
                            <a class="btn btn-app" href="<?php echo base_url().'unity/student_viewer/'.$student['intID']; ?>" ><i class="ion ion-arrow-left-a"></i>Back</a> 
                        </small>
                        
                        <div style="clear:both"></div>
                    </h1>
                    
                  
                </section>
    <hr />
<div class="content">
    <input type="hidden" value="<?php echo $student['intID'] ?>" id="student-id" />
    <input type="hidden" value="<?php echo $student['intCurriculumID'] ?>" id="curriculum-id" />
    <input type="hidden" value="<?php echo $active_sem['intID']; ?>" id="active-sem-id" />
    <input type="hidden" value="<?php echo $academic_standing['year']; ?>" id="academic-standing">
    <input type="hidden" value="<?php echo $academic_standing['status']; ?>" id="academic-standing-stat">
    <input type="hidden" value="<?php echo switch_num_rev($active_sem['enumSem']); ?>" id="active-sem" >
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
                   <li><a href="#" style="font-size:13px;">Curriculum <span class="pull-right text-blue"><?php echo $student['strName']; ?></span></a></li>
                  <li><a style="font-size:13px;" href="#">Registration Status <span class="pull-right"><?php echo $reg_status; ?></span></a> </li>
                    <li><a href="<?php echo base_url()."unity/delete_registration/".$student['intID']."/".$active_sem['intID']; ?>"><i class="ion ion-android-close"></i> Reset Status</a> </li>
                <li><a style="font-size:13px;" href="#">Date Registered <span class="pull-right"><?php echo ($registration)?'<span style="color:#009000;">'.$registration['dteRegistered'].'</span>':'<span style="color:#900000;">N/A</span>'; ?></span></a></li>
                  <li><a style="font-size:13px;" href="#">Scholarship Type <span class="pull-right"><?php echo $registration['enumScholarship']; ?></span></a></li>
                  
              </ul>
            </div>
          </div>
            
        <div class="span10 box box-primary">
        <div class="box-header">
                <h3 class="box-title">Edit Registration Data</h3>
        </div>
       
            
            <form id="validate-curriculum" action="<?php echo base_url(); ?>unity/submit_edit_registration" method="post" role="form">
                <div class="box-body">
                    
                        <input type="hidden" value="<?php echo $registration['intRegistrationID']; ?>" name="intRegistrationID" >
                    <input type="hidden" value="<?php echo $registration['intStudentID']; ?>" name="intStudentID" >
                         <div class="form-group col-xs-6">
                            <label for="enumScholarship">Scholarship Grant</label>
                            <select class="form-control" id="enumScholarship" name="enumScholarship">
                                <option <?php echo ($registration['enumScholarship'] == "paying")?'selected':''; ?> value="paying">Paying</option>
                                <option <?php echo ($registration['enumScholarship'] == "7th district")?'selected':''; ?> value="7th district">7th District</option>
                                <option <?php echo ($registration['enumScholarship'] == "resident scholar")?'selected':''; ?> value="resident scholar">Resident Scholar</option>
                                <option <?php echo ($registration['enumScholarship'] == "tagaytay resident")?'selected':''; ?> value="tagaytay resident">Tagaytay Resident</option>
                                <option <?php echo ($registration['enumScholarship'] == "DILG scholar")?'selected':''; ?> value="DILG scholar">DILG Scholar</option>
                                <option <?php echo ($registration['enumScholarship'] == "FREE HIGHER EDUCATION PROGRAM (R.A. 10931)")?'selected':''; ?> value="FREE HIGHER EDUCATION PROGRAM (R.A. 10931)">FREE HIGHER EDUCATION PROGRAM (R.A. 10931)</option>
                            </select>
                        </div>

                        <div class="form-group col-xs-6">
                            <label for="enumStudentType">Student Type</label>
                            <select class="form-control" id="enumStudentType" name="enumStudentType">
                                <option <?php echo ($registration['enumStudentType'] == "old")?'selected':''; ?> value="old">Old/Returning</option>
                                <option <?php echo ($registration['enumStudentType'] == "new")?'selected':''; ?> value="new">New</option>
                            </select>
                        </div>

                         
                        <div class="form-group col-xs-12">
                            <input type="submit" value="update" class="btn btn-default  btn-flat">
                        </div>
                    <div style="clear:both"></div>
                </div>
            </form>
    </div>
            
        </div>
        
    
        
        </div>
    </div>
    
    
    
    
</div>

