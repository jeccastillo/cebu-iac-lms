<?php 
    error_reporting(0);
?>
<aside class="right-side">
<section class="content-header">
    <h1>My Profile
        <small>view your profile information</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="<?php echo base_url() ?>portal/dashboard"><i class="fa fa-home"></i> Home</a></li>
        <li class="active">My Profile</li>
    </ol>
</section>
<div class="content">
    <input type="hidden" value="<?php echo $student['intID'] ?>" id="student-id" />
    <input type="hidden" value="<?php echo $active_sem['intID']; ?>" id="active-sem-id" />
    <!-- <div class="box box-solid box-success"> -->
    <div class="box box-warning">
        <div class="box-body">
            <div class="alert alert-danger" style="display:none;">
                <i class="fa fa-ban"></i>
                <b>Alert!</b> Only admins can delete student records.
            </div>
            <div class="col-xs-6 col-md-2 size-96">
              <?php if($student['strPicture'] == "" ): ?>
               <img src="<?php echo $img_dir?>default_image2.png"  width="100%"/>
              <?php else: ?>
                    <img class="img-responsive" src="<?php echo $photo_dir.$student['strPicture']; ?>" />
                  <?php endif; 
                  
                  $major = $student['strMajor']!="None"?'major in '. $student['strMajor']:"";
                  ?>
            </div>            
            <div class="col-xs-6 col-md-6">
              <h3 class="student-name" style="margin-top: 5px;"><?php 
                        $middleInitial = substr($student['strMiddlename'], 0,1);
                        echo $student['strLastname'].", ". $student['strFirstname'] . " " .  $middleInitial . "."; ?></h3>
               <p><?php echo $student['strProgramDescription']; ?></p>
              <p> <?php  echo $major; ?></p>
            </div>
            <div class="col-md-4 col-xs-12">
            
              <p><strong><i class="fa fa-user"></i>&nbsp;Student Number: </strong><?php echo preg_replace("/[^a-zA-Z0-9]+/", "", $student['strStudentNumber']); ?></p>
              <p><strong><i class="fa fa-building-o"></i>&nbsp;School: </strong>
                    <?php 
                        if ($student['strProgramCode'] == 'BSBA-MM' || $student['strProgramCode'] == 'BSBA-HRDM' || $student['strProgramCode'] == 'BSOA') {
                            echo 'School of Business & Management';
                        }
                        else if ($student['strProgramCode'] == 'BSCS' || $student['strProgramCode'] == 'BSIT' ) {
                            echo 'School of Computer Studies';
                        }
                        else if ($student['strProgramCode'] == 'BSE-E' || $student['strProgramCode'] == 'BSE-F' || $student['strProgramCode'] == 'BSE-M' || $student['strProgramCode'] == 'BSE-SS' ) {
                            echo 'School of Education';
                        }
                        else if ($student['strProgramCode'] == 'BSHM' || $student['strProgramCode'] == 'BSTM') {
                            echo 'School of Hospitality & Tourism Management';
                        }  
                    ?>
                </p>
              
              <p><strong><i class="fa fa-envelope"></i>&nbsp;Institutional Email: </strong><?php echo $student['strGSuiteEmail']; ?></p>
             
            </div>
        </div>
       
    </div>
    
    
    <!-- <div class="box box-solid box-warning"> -->
 <div class="col-sm-14">
    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
            <li class="<?php echo ($tab == "tab_1")?'active':'' ?>"><a href="#tab_1" data-toggle="tab">About Me</a></li>
            <!-- <li><a href="#">Family Background</a></li> -->
        </ul>
        <div class="tab-content">
            <div class="tab-pane <?php echo ($tab == "tab_1")?'active':'' ?>" id="tab_1">
                <div class="box box-primary">
                    <div class="box-body">
                        <div class="row text-xs">
                        <legend><i class="fa fa-info-circle"></i> Basic Information</legend>
                                <div class="col-md-6">
                                            
                                            <div class="col-sm-4">Last Name: </div>
                                            <div class="col-sm-8"><label><?php echo $student['strLastname'];?></label></div>
                                            <div class="col-sm-4">First Name: </div>
                                            <div class="col-sm-8"><label><?php echo $student['strFirstname'];?></label></div>
                                            <div class="col-sm-4">Middle Name: </div>
                                            <div class="col-sm-8"><label><?php echo $student['strMiddlename'];?></label></div>
                                            <div class="col-sm-4">Gender: </div>
                                            <div class="col-sm-8"><label class>
                                                <?php echo ucfirst($student['enumGender']); ?></label></div>
                                            <div class="col-sm-4">Date of Birth: </div>
                                            <div class="col-sm-8"><label>
                                                <?php echo date("M j, Y",strtotime($student['dteBirthDate']))?></label></div>
                                 </div>

                                 <div class="col-md-6">
                                            <!-- <div class="col-sm-6">Learner's Reference Number: </div>
                                            <div class="col-sm-6"><label><input type="text" disabled="disabled" class ="form-control"  value="<?php echo $student['strLRN'];?> "></label></div> -->

                                            <div class="col-sm-6">Permanent Address:</div>
                                            <div class="col-sm-6"><label><input type="text" disabled="disabled" class ="form-control" value="<?php echo $student['strAddress'];?>"></label></div>
                                            <div class="col-sm-6">Zip Code:</div>
                                            <div class="col-sm-6"><label><input type="text" disabled="disabled" class ="form-control" value="<?php echo $student['strZipCode'];?>"></label></div>
                                            <div class="col-sm-6">Contact Number: </div>
                                            <div class="col-sm-6"><label>
                                            <input type="text" class ="form-control" disabled="disabled" value="<?php echo $student['strMobileNumber'];?>"></label></div>
                                            <div class="col-sm-6">Email Address:</div>
                                            <div class="col-sm-6"><label><input type="text" disabled="disabled" class ="form-control" value="<?php echo $student['strEmail'];?>"></label></div>
                                            
                                            
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
   
       
