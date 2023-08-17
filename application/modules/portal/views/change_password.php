<?php 
    error_reporting(0);
?>
<aside class="right-side">
<section class="content-header">
                    <h1>
                        <!--small>
                            <a class="btn btn-app" href="<?php echo base_url() ?>unity/view_all_students" ><i class="ion ion-arrow-left-a"></i>Back</a> 
                            <a class="btn btn-app trash-student-record2" rel="<?php echo $student['intID']; ?>" href="#"><i class="ion ion-android-close"></i> Delete</a>   
                            <a class="btn btn-app" href="<?php echo base_url()."unity/edit_student/".$student['intID']; ?>"><i class="ion ion-edit"></i> Edit</a> 
                            <a class="btn btn-app" href="<?php echo base_url()."unity/student_viewer_print/".$student['intID'] ."/". $active_sem['intID']; ?>">
                                <i class="ion ion-printer"></i>Print Preview</a> 
                            <?php if($registration): ?>
                            <a class="btn btn-app" href="<?php echo base_url()."unity/student_viewer_registration_print/".$student['intID'] ."/". $active_sem['intID']; ?>">
                                <i class="ion ion-printer"></i>Reg Form Print Preview</a> 
                            <?php endif; ?>
                        </small-->
                        
                    </h1>
                    
                  
                </section>
<div class="content">
    <form method="post" id="cp-form" action="<?php echo base_url().'portal/change_password_submit'; ?>">
    <input type="hidden" value="<?php echo $student['intID'] ?>" id="student-id" />
    <input type="hidden" value="<?php echo $active_sem['intID']; ?>" id="active-sem-id" />
    <?php if(isset($first_login)): ?>
        <div class="alert alert-info">
            <i class="fa fa-info"></i>
            <span id="alert-text"><?php echo $firstlog; ?></span>
        </div>
    <?php endif; ?>
    <?php if(isset($error_message) && $error_message=="Password Updated"): ?>    
        <div class="alert alert-info">
            <i class="fa fa-info"></i>
            <span id="alert-text"><?php echo $error_message; ?></span>
        </div>
    <?php elseif($error_message!=""): ?>
        <div class="alert alert-danger">
            <i class="fa fa-ban"></i>
            <span id="alert-text"><?php echo $error_message; ?></span>
        </div>
    <?php endif; ?>
    <div class="box box-solid box-primary">
         <div class="box-header">
            <h4 class="box-title">Change Password</h4>
        </div>
        
        <div class="box-body">
            <div class="row">
                <div class="form-group col-md-6">
                    <label for="current_password">Current Password</label>
                    <input type="password" class="form-control" name="current_password" />
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-6">
                    <label for="password">Enter Password</label>
                    <input type="password" class="form-control" name="password" />
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-6">
                    <label for="repeat_password">Repeat Password</label>
                    <input type="password" class="form-control" name="repeat_password" />
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-6">
                    <input type="submit" class="btn btn-primary" value="submit" />
                </div>
            </div>
            <hr />
            
        </div>
    </div>
    </form>
</div>

