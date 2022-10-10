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
                    <h3 class="box-title">General Directions</h3>
                        <div class="gen-instructions">
                            <ul>
                                <li>This online application form is suggested be filled-up using a laptop or desktop computer with access to the internet.</li>
                                <li>Please provide the <b>correct information</b> in this form. Your application will be considered null and void if you input false information/picture regarding your application.</li>
                                <li>Type <b>NA</b> for fields that are not applicable.</li>
                            </ul>
                        </div>
                    <hr />
                    <h3 class="box-title">Mandatory Requirements</h3>
                        <div class="gen-instructions">
                            <ul>
                                <li>An <b><u>active google mail (gmail)</u></b> address must be provided. Your google mail address will be used to send the links pertaining to your application.</li>
                                <li>To avoid delay and disapproval, soft copy / scanned copy of <u><b>2x2 picture in plain white background wearing decent attire</b></u> with exact dimensions of <u><b>300 pixels by 300 pixels</b></u> will be used for the profile picture upload. These are strictly required to be able to process your application.</li>
                                <li>Kindly fill up the application form by visiting the <a href="https://tinyurl.com/y7fzce8m">link</a</li>
                            </ul>
                        </div>
             </div>
            <div class="box-body">
                <div class="row">
                    
                    
                </div>
            </div>
        </div>
<!--
        
        <form id="appForm" action="{formAction}" method="post" enctype="multipart/form-data">
            <div class="box box-primary">
                <div class="box-header">
                        <h3 class="box-title">Basic Information</h3>
                </div>

                <div class="box-body">
                    <div class="row">
                        <div class="form-group col-sm-8">
                            <label for="strLastname">Last Name*</label>
                            <input type="text" value="<?php echo (isset($info['strLastname']))?$info['strLastname']:''; ?>" name="strLastname" class="form-control app" id="strLastname" placeholder="Enter Last Name">

                            <label for="strFirstname">First Name*</label>
                            <input type="text" value="<?php echo (isset($info['strFirstname']))?$info['strFirstname']:''; ?>" name="strFirstname" class="form-control app" id="strFirstname" placeholder="Enter First Name">

                            <label for="strMiddlename">Middle Name</label>
                            <input type="text" value="<?php echo (isset($info['strMiddlename']))?$info['strMiddlename']:''; ?>" name="strMiddlename" class="form-control app" id="strMiddlename" placeholder="Enter Middle Name">

                            <label for="strAppLRN">Learner Reference Number (LRN)</label>
                            <input type="number" value="<?php echo (isset($info['strAppLRN']))?$info['strAppLRN']:''; ?>"  name="strAppLRN" class="form-control app" id="strAppLRN" placeholder="Enter Learner Reference Number">                 
                            <label for="strAppEmail">Email Address* (Must be a gmail address)</label>
                            <input type="email" value="<?php echo (isset($info['strAppEmail']))?$info['strAppEmail']:''; ?>" name="strAppEmail" class="form-control app" id="strAppEmail" placeholder="Enter Email Address">
                            <label for="strAppPhoneNumber">Contact Number</label>
                            <input type="number" value="<?php echo (isset($info['strAppPhoneNumber']))?$info['strAppPhoneNumber']:''; ?>" name="strAppPhoneNumber" class="form-control app" id="strAppPhoneNumber" placeholder="Enter Contact Number">
                        </div>
                        <div class="col-sm-4">
                            <label for="strAppPicture">Upload 2x2 Picture (width:300px height:300px)*</label>
                            <input type="file" name="strAppPicture" id="strAppPicture" class="form-control app"></input>
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
                        <label for="enumCourse1">1st Choice*</label>
                        <select class="form-control app" name="enumCourse1" id="enumCourse1">
                            
                        </select>
                    </div>
                     <div class="form-group col-sm-4">
                        <label for="enumCourse2">2nd Choice*</label>
                        <select class="form-control app" name="enumCourse2" id="enumCourse2">
                            
                        </select>
                    </div>
                    <div class="form-group col-sm-4">
                        <label for="enumCourse3">3rd Choice</label>
                        <select class="form-control app" name="enumCourse3" id="enumCourse3">
                            
                        </select>
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
                    
                    <?php echo cms_dropdown_app('strAppProvince','Province*',$provinces,'col-sm-6','','app'); ?>
                    
                    <div class="form-group col-sm-6">
                        <label for="strAppCity">City/Municipality*</label>
                        <select class="form-control app" name="strAppCity" id="strAppCity" >
                            
                        </select>
                    </div>
                    <div class="form-group col-sm-6">
                        <label for="strAppBrgy">Barangay*</label>
                        <select class="form-control app" name="strAppBrgy" id="strAppBrgy" >
                            
                        </select>
                    </div>
                    <div class="form-group col-sm-6">
                        <label for="strAppAdress">House/Unit/Flr #, Bldg Name, Blk or Lot #/Street Address*</label>
                        <textarea name="strAppAdress" class="form-control app" id="strAppAdress" placeholder="Enter Home/Street Address"><?php echo (isset($info['strAppAdress']))?$info['strAppAdress']:''; ?></textarea>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-sm-6">
                        <label for="strAppLastSchool">Name of Last School Attended*</label>
                        <input type="text" value="<?php echo (isset($info['strAppLastSchool']))?$info['strAppLastSchool']:''; ?>" name="strAppLastSchool" class="form-control app" id="strAppLastSchool" placeholder="Enter Last School Attended">
                    </div>
                    <div class="form-group col-sm-4">
                        <label for="dteAppBirthdate">Birthdate (MM/DD/YYYY)*</label>
                        <div class="input-group date">                      
                         <input value="<?php echo (isset($info['dteAppBirthdate']))?$info['dteAppBirthdate']:''; ?>" type="text" name="dteAppBirthdate" value=""  class="form-control validate datepicker app" id="dteAppBirthdate"  placeholder="Enter Birthdate">
                         <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                        </div>
                    </div>
                </div>
                <div class="row">
                   <?php 
                    $options = 
                        [
                            'male'=>'Male',
                            'female'=>'Female',
                        ];
                    $selected = (isset($info['strAppGender']))?$info['strAppGender']:'';
                    echo cms_dropdown_app('strAppGender','Gender',$options,'col-sm-4',$selected,'app'); 
                    
                     $options = 
                        [
                            'single'=>'Single',
                            'married'=>'Married',
                            'widowed'=>'Widowed',
                        ];
                    $selected = (isset($info['strAppCivilStatus']))?$info['strAppCivilStatus']:'';
                    echo cms_dropdown_app('strAppCivilStatus','Civil Status',$options,'col-sm-4',$selected,'app'); 
                    ?>
                                       
                </div>
                <div class="row">
                     <div class="form-group col-sm-8">
                        <label for="strAppFather">Father's Name*</label>
                        <input type="text" value="<?php echo (isset($info['strAppFather']))?$info['strAppFather']:''; ?>" name="strAppFather" class="form-control app" id="strAppFather" placeholder="Enter Father's Name">
                        <label for="strAppMother">Mother's Name*</label>
                        <input type="text" value="<?php echo (isset($info['strAppMother']))?$info['strAppMother']:''; ?>" name="strAppMother" class="form-control app" id="strAppMother" placeholder="Enter Mother's Name">
                        <label for="strAppSpouse">Spouse's Name</label>
                        <input type="text" value="<?php echo (isset($info['strAppSpouse']))?$info['strAppSpouse']:''; ?>" name="strAppSpouse" class="form-control app" id="strAppSpouse" placeholder="Enter Spouse's Name">
                        <label for="strAppReligion">Religion</label>
                        <input type="text" value="<?php echo (isset($info['strAppReligion']))?$info['strAppReligion']:''; ?>" name="strAppReligion" class="form-control app" id="strAppReligion" placeholder="Enter Religion">
                    </div>  
                </div>
                div class="row">
                    <div class="form-group col-sm-6">
                        <label for="dteScheduleExam">Select Schedule for Exam*</label>
                        <div class="input-group date">                      
                         <input style="cursor:pointer" readonly value="<?php echo (isset($info['dteScheduleExam']))?$info['dteScheduleExam']:''; ?>" type="text" name="dteScheduleExamR" value=""  class="form-control validate datepickerExam" id="dteScheduleExamR"  placeholder="Enter Schedule Date for Exam">
                            
                         <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                        </div>
                        <input type="hidden" id="dteScheduleExam" class="app" name="dteScheduleExam" value="<?php echo (isset($info['dteScheduleExam']))?$info['dteScheduleExam']:''; ?>">
                    </div>
                </div
                <input type="hidden" id="dteScheduleExam" class="app" name="dteScheduleExam" value="<?php echo (isset($info['dteScheduleExam']))?$info['dteScheduleExam']:date("Y-m-d"); ?>">
                <hr />
                <div class="row">
                    <div class="form-group col-sm-12 text-center">
                        <input type="button" id="submitForm" value="Submit Application" class="btn btn-lg btn-default btn-flat">
                    </div>
                </div>
                    
            </div>
         </div>
        </form>
-->
    </div>
</div>
