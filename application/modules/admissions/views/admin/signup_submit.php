<div class="container">
    
    <div class="content">
        <div class="header">
            <div class="box box-primary">   
              <img class="img-responsive" src="<?php echo $img_dir; ?>admission_header5.jpg" />
            </div>
            <h3 class="main-title">APPLICATION FOR ADMISSION</h3>
        </div>
          <div class="box box-primary">
            <div class="box-header">
                    <h3 class="box-title">CONFIRMATION</h3>
                        <div class="gen-instructions">
                            <ul>
                                <li>Please check all the information printed on this form.</li>
                                <li>If you have corrections, you may locate and click EDIT BUTTON at the bottom part of the screen to update your information.</li>
                                <li>If you have already validated your information, you may locate and click CONFIRM BUTTON at the bottom part of the screen to finalize your application.</li>
                                
                            </ul>
                        </div>
             </div>
            <div class="box-body">
                <div class="row">
                    
                    
                </div>
            </div>
        </div>
        <form id="appForm" action="<?php echo base_url()."admissions/confirm_form" ?>" method="post">
            <input type="hidden" name="strLastname" value="<?php echo $info['strLastname']; ?>" class="form-control" id="strLastname">
            <input type="hidden" name="strFirstname" value="<?php echo $info['strFirstname']; ?>" class="form-control app" id="strFirstname">
            <input type="hidden" name="strMiddlename" value="<?php echo $info['strMiddlename']; ?>" class="form-control app" id="strMiddlename">
            <input type="hidden" name="strAppLRN" value="<?php echo $info['strAppLRN']; ?>" class="form-control app" id="strAppLRN"> 
            <input type="hidden" value="<?php echo $info['strAppEmail']; ?>" name="strAppEmail" class="form-control app" id="strAppEmail">
            <input type="hidden" name="strAppPhoneNumber" value="<?php echo $info['strAppPhoneNumber']; ?>" class="form-control app" id="strAppPhoneNumber">
            <input type="hidden" value="<?php echo $info['enumCourse1']; ?>" class="form-control app" name="enumCourse1" id="enumCourse1">
            <input type="hidden" value="<?php echo $info['enumCourse2']; ?>" class="form-control app" name="enumCourse2" id="enumCourse2">
            <input type="hidden" value="<?php echo $info['enumCourse3']; ?>" class="form-control app" name="enumCourse3" id="enumCourse3">
            <input type="hidden" value="<?php echo $info['strAppProvince']; ?>" class="form-control app" name="strAppProvince" id="strAppProvince">
            <input type="hidden" value="<?php echo $info['strAppCity']; ?>" class="form-control app" name="strAppCity" id="strAppCity">
            <input type="hidden" value="<?php echo $info['strAppBrgy']; ?>" class="form-control app" name="strAppBrgy" id="strAppBrgy">
            <input type="hidden" value="<?php echo $info['strAppAdress']; ?>" class="form-control app" name="strAppAdress" id="strAppAdress">
            <input type="hidden" value="<?php echo $info['strAppLastSchool']; ?>" class="form-control app" name="strAppLastSchool" id="strAppLastSchool">
            <input type="hidden" value="<?php echo $info['dteAppBirthdate']; ?>" class="form-control app" name="dteAppBirthdate" id="dteAppBirthdate">
            <input type="hidden" value="<?php echo $info['strAppGender']; ?>" class="form-control app" name="strAppGender" id="strAppGender">
            <input type="hidden" value="<?php echo $info['strAppCivilStatus']; ?>" class="form-control app" name="strAppCivilStatus" id="strAppCivilStatus">
            <input type="hidden" value="<?php echo $info['strAppFather']; ?>" class="form-control app" name="strAppFather" id="strAppFather">
            <input type="hidden" value="<?php echo $info['strAppMother']; ?>" class="form-control app" name="strAppMother" id="strAppMother">
            <input type="hidden" value="<?php echo $info['strAppSpouse']; ?>" class="form-control app" name="strAppSpouse" id="strAppSpouse">
            <input type="hidden" value="<?php echo $info['strAppReligion']; ?>" class="form-control app" name="strAppReligion" id="strAppReligion">
            <input type="hidden" value="<?php echo $info['strAppPicture']; ?>" class="form-control app" name="strAppPicture" id="strAppPicture">
            <input type="hidden" value="<?php echo $info['dteScheduleExam']; ?>" class="form-control app" name="dteScheduleExam" id="dteScheduleExam">
        </form>
        
        <form id="appFormBack" action="<?php echo base_url()."admissions/signup_form_back" ?>" method="post">
            <input type="hidden" name="strLastname" value="<?php echo $info['strLastname']; ?>" class="form-control" id="strLastname">
            <input type="hidden" name="strFirstname" value="<?php echo $info['strFirstname']; ?>" class="form-control app" id="strFirstname">
            <input type="hidden" name="strMiddlename" value="<?php echo $info['strMiddlename']; ?>" class="form-control app" id="strMiddlename">
            <input type="hidden" name="strAppLRN" value="<?php echo $info['strAppLRN']; ?>" class="form-control app" id="strAppLRN"> 
            <input type="hidden" value="<?php echo $info['strAppEmail']; ?>" name="strAppEmail" class="form-control app" id="strAppEmail">
            <input type="hidden" name="strAppPhoneNumber" value="<?php echo $info['strAppPhoneNumber']; ?>" class="form-control app" id="strAppPhoneNumber">
            <input type="hidden" value="<?php echo $info['enumCourse1']; ?>" class="form-control app" name="enumCourse1" id="enumCourse1">
            <input type="hidden" value="<?php echo $info['enumCourse2']; ?>" class="form-control app" name="enumCourse2" id="enumCourse2">
            <input type="hidden" value="<?php echo $info['enumCourse3']; ?>" class="form-control app" name="enumCourse3" id="enumCourse3">
            <input type="hidden" value="<?php echo $info['strAppProvince']; ?>" class="form-control app" name="strAppProvince" id="strAppProvince">
            <input type="hidden" value="<?php echo $info['strAppCity']; ?>" class="form-control app" name="strAppCity" id="strAppCity">
            <input type="hidden" value="<?php echo $info['strAppBrgy']; ?>" class="form-control app" name="strAppBrgy" id="strAppBrgy">
            <input type="hidden" value="<?php echo $info['strAppAdress']; ?>" class="form-control app" name="strAppAdress" id="strAppAdress">
            <input type="hidden" value="<?php echo $info['strAppLastSchool']; ?>" class="form-control app" name="strAppLastSchool" id="strAppLastSchool">
            <input type="hidden" value="<?php echo $info['dteAppBirthdate']; ?>" class="form-control app" name="dteAppBirthdate" id="dteAppBirthdate">
            <input type="hidden" value="<?php echo $info['strAppGender']; ?>" class="form-control app" name="strAppGender" id="strAppGender">
            <input type="hidden" value="<?php echo $info['strAppCivilStatus']; ?>" class="form-control app" name="strAppCivilStatus" id="strAppCivilStatus">
            <input type="hidden" value="<?php echo $info['strAppFather']; ?>" class="form-control app" name="strAppFather" id="strAppFather">
            <input type="hidden" value="<?php echo $info['strAppMother']; ?>" class="form-control app" name="strAppMother" id="strAppMother">
            <input type="hidden" value="<?php echo $info['strAppSpouse']; ?>" class="form-control app" name="strAppSpouse" id="strAppSpouse">
            <input type="hidden" value="<?php echo $info['strAppReligion']; ?>" class="form-control app" name="strAppReligion" id="strAppReligion">
            <input type="hidden" value="<?php echo $info['strAppPicture']; ?>" class="form-control app" name="strAppPicture" id="strAppPicture">
            <input type="hidden" value="<?php echo $info['dteScheduleExam']; ?>" class="form-control app" name="dteScheduleExam" id="dteScheduleExam">
        </form>
            
            <div class="box box-primary">
                <div class="box-header">
                        <h3 class="box-title">Basic Information</h3>
                </div>

                <div class="box-body">
                    <div class="row">
                        <div class="form-group col-sm-9">
                            <div class="row">
                                <div class="col-sm-6">
                                    <label for="strLastname">Full Name:</label>
                                </div>
                                <div class="col-sm-6">
                                    <p><?php echo $info['strLastname'].", ".$info['strFirstname']." ".$info['strMiddlename']; ?></p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <label for="strAppLRN">Learner Reference Number (LRN):</label>
                                </div>
                                <div class="col-sm-6">
                                    <p><?php echo $info['strAppLRN']; ?></p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <label for="strAppEmail">Email Address:</label>
                                </div>
                                <div class="col-sm-6">
                                    <p><?php echo $info['strAppEmail']; ?></p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <label for="strAppPhoneNumber">Contact Number:</label>
                                </div>
                                <div class="col-sm-6">
                                    <p><?php echo $info['strAppPhoneNumber']; ?></p>
                                </div>
                            </div>                            
                        </div>
                        <div class="col-sm-3">
                            <label for="strAppPicture">Upload 2x2 Picture (width:300px height:300px)</label>
                            <?php if($info['strAppPicture'] == ""): ?>
                            <div class='text-error'>
                                <?php echo $file_error; ?> Click Edit Information to fix.
                            </div>
                            <?php else: 
                            ?>
                            <img style="width:100%" src="<?php echo $temp_pics.$info['strAppPicture']; ?>" />
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            </div>
        
        <div class="box box-primary">
            <div class="box-header">
                    <h3 class="box-title">Course/Program Preference</h3>
            </div>

            <div class="box-body">
                <div class="row">
                    <div class="form-group col-sm-4">
                        <label for="enumCourse1">1st Choice</label>
                        <p><?php echo $info['enumCourse1']; ?></p>
                        
                    </div>
                     <div class="form-group col-sm-4">
                        <label for="enumCourse2">2nd Choice</label>
                        <p><?php echo $info['enumCourse2']; ?></p>
                    </div>
                    <div class="form-group col-sm-4">
                        <label for="enumCourse3">3rd Choice</label>
                        <p><?php echo $info['enumCourse3']; ?></p>
                    </div>
                    
                </div>
            </div>
        </div>
    
       <div class="box box-primary">
            <div class="box-header">
                    <h3 class="box-title">Personal Information</h3>
            </div>

            <div class="box-body">
                <div class="row">
                    
                    <div class="col-sm-6">
                         <div class="row">
                                <div class="col-sm-4">
                                    <label for="provinceDesc">Province:</label>
                                </div>
                                <div class="col-sm-8">
                                    <p><?php echo $info['provinceDesc']; ?></p>
                                </div>
                            </div>      
                        
                    </div>
                    <div class="col-sm-6">
                         <div class="row">
                                <div class="col-sm-4">
                                    <label for="cityDesc">City/Municipality:</label>
                                </div>
                                <div class="col-sm-8">
                                    <p><?php echo $info['cityDesc']; ?></p>
                                </div>
                            </div>      
                        
                    </div>
                    <div class="col-sm-6">
                         <div class="row">
                                <div class="col-sm-4">
                                    <label for="brgyDesc">Barangay:</label>
                                </div>
                                <div class="col-sm-8">
                                    <p><?php echo $info['brgyDesc']; ?></p>
                                </div>
                            </div>      
                        
                    </div>
                    <div class="col-sm-6">
                         <div class="row">
                                <div class="col-sm-4">
                                    <label for="strAppAdress">House/Unit/Flr #, Bldg Name, Blk or Lot #/Street Address*</label>
                                </div>
                                <div class="col-sm-8">
                                    <p><?php echo $info['strAppAdress']; ?></p>
                                </div>
                            </div>      
                        
                    </div>                    
                </div>
                <div class="row">
                    <div class="col-sm-6">
                         <div class="row">
                                <div class="col-sm-4">
                                    <label for="strAppAdress">Last School Attended:</label>
                                </div>
                                <div class="col-sm-8">
                                    <p><?php echo $info['strAppLastSchool']; ?></p>
                                </div>
                            </div>      
                        
                    </div> 
                    <div class="col-sm-6">
                         <div class="row">
                                <div class="col-sm-4">
                                    <label for="strAppAdress">Birthdate:</label>
                                </div>
                                <div class="col-sm-8">
                                    <p><?php echo $info['dteAppBirthdate']; ?></p>
                                </div>
                            </div>      
                        
                    </div> 
                </div>
                <div class="row">
                    <div class="col-sm-6">
                         <div class="row">
                                <div class="col-sm-4">
                                    <label for="strAppGender">Gender:</label>
                                </div>
                                <div class="col-sm-8">
                                    <p><?php echo $info['strAppGender']; ?></p>
                                </div>
                            </div>      
                        
                    </div> 
                    <div class="col-sm-6">
                         <div class="row">
                                <div class="col-sm-4">
                                    <label for="strAppGender">Civil Status:</label>
                                </div>
                                <div class="col-sm-8">
                                    <p><?php echo $info['strAppCivilStatus']; ?></p>
                                </div>
                            </div>      
                        
                    </div>                     
                </div>
                <div class="row">
                    <div class="col-sm-6">
                         <div class="row">
                                <div class="col-sm-4">
                                    <label for="strAppGender">Father's Name:</label>
                                </div>
                                <div class="col-sm-8">
                                    <p><?php echo $info['strAppFather']; ?></p>
                                </div>
                            </div>      
                    </div>
                    <div class="col-sm-6">
                         <div class="row">
                                <div class="col-sm-4">
                                    <label for="strAppGender">Mother's Name:</label>
                                </div>
                                <div class="col-sm-8">
                                    <p><?php echo $info['strAppMother']; ?></p>
                                </div>
                            </div>      
                    </div>
                    <div class="col-sm-6">
                         <div class="row">
                                <div class="col-sm-4">
                                    <label for="strAppSpouse">Spouse's Name:</label>
                                </div>
                                <div class="col-sm-8">
                                    <p><?php echo $info['strAppSpouse']; ?></p>
                                </div>
                            </div>      
                    </div>
                    <div class="col-sm-6">
                         <div class="row">
                                <div class="col-sm-4">
                                    <label for="strAppReligion">Religion:</label>
                                </div>
                                <div class="col-sm-8">
                                    <p><?php echo $info['strAppReligion']; ?></p>
                                </div>
                            </div>      
                    </div>                                                 
                </div>
                <hr />
<!--
                <div class="row">
                    <div class="col-sm-6">
                         <div class="row">
                                <div class="col-sm-4">
                                    <label for="strAppReligion">Exam Schedule:</label>
                                </div>
                                <div class="col-sm-8">
                                    <p><?php echo $info['dteScheduleExam']; ?></p>
                                </div>
                            </div>      
                    </div>
                
                </div>
-->
                <hr />
                <div class="row">
                    <div class="form-group col-sm-12 text-center">
                        <input type="button" id="backAndEdit" value="Edit Information" class="btn btn-lg btn-warning btn-flat">
                        <input type="button" id="confirmSubmission" value="Confirm Application" class="btn btn-lg btn-default btn-flat">
                    </div>
                </div>
                    
            </div>
         </div>
    </div>
</div>
