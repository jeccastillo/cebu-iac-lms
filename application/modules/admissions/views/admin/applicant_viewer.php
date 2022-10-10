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
                            <a class="btn btn-app" href="<?php echo base_url() ?>admissions/view_all_applicants" ><i class="ion ion-arrow-left-a"></i>All Applicants</a> 
                            <a class="btn btn-app trash-student-record2" rel="<?php echo $applicant['intApplicationID']; ?>" href="#"><i class="ion ion-android-close"></i> Delete</a>   
                            <a class="btn btn-app" href="<?php echo base_url()."admissions/edit_applicant/".$applicant['intApplicationID']; ?>"><i class="ion ion-edit"></i> Edit</a> 
                            
                              <a target="_blank" class="btn btn-app" href="<?php echo base_url()."pdf/print_admission_form/".$student['intID'] ?>"><i class="fa fa-print"></i>Print Admission Form</a> 
                        </small>
                        
                        <div class="box-tools pull-right">
                        <select id="select-sem-student" class="form-control" >
                            <?php foreach($sy as $s): ?>
                                <option <?php echo ($selected_ay == $s['intID'])?'selected':''; ?> value="<?php echo $s['intID']; ?>"><?php echo $s['enumSem']." ".$term_type." ".$s['strYearStart']."-".$s['strYearEnd']; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if($registration): ?>
                         <label style="font-size:.6em;"> Admission Status</label>
                            
                            <select class="form-control" rel="<?php echo $registration['intRegistrationID']; ?>" id="ROGStatusChange">
                                <option <?php echo ($registration['intROG'] == 0)?'selected':'' ?>  value="0">Registered</option>
                                <option <?php echo ($registration['intROG'] == 1)?'selected':'' ?> value="1">Enrolled</option>
                                <option <?php echo ($registration['intROG'] == 2)?'selected':'' ?> value="2">Cleared</option>
                            </select>
                            <?php endif; ?>
                    </div>
                        <div style="clear:both"></div>
                    </h1>
                    
                  
                </section>
    <hr />
<div class="content">
    <input type="hidden" value="<?php echo $applicant['intApplicationID'] ?>" id="appId" />
    <input type="hidden" value="<?php echo $active_sem['intID']; ?>" id="active-sem-id" />
    <input type="hidden" value="<?php echo switch_num_rev($active_sem['enumSem']); ?>" id="active-sem" >
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-widget widget-user-2">
            <!-- Add the bg color to the header using any of the bg-* classes -->
            <div class="widget-user-header bg-green">
              <!-- /.widget-user-image -->
              <h3 class="widget-user-username" style="text-transform:capitalize;margin-left:0;font-size:1.3em;"><?php echo strtolower($applicant['strLastname'].", ". $applicant['strFirstname']); ?><?php echo ($applicant['strMiddlename'] != "")?' '.strtolower($applicant['strMiddlename']):''; ?></h3>
              <h5 class="widget-user-appnumber" style="margin-left:0;">Application Number: <?php echo $applicant['strAppNumber']; ?></h5>
             </div>

<!--
            <div class="box-footer no-padding">
              <ul class="nav nav-stacked">
                <li><a style="font-size:13px;" href="#">Learner's Reference Number: <span class="pull-right"><?php echo $applicant['strAppLRN']; ?></span></a> </li>
                <li><a style="font-size:13px;" href="#">Email Address: <span class="pull-right"><?php echo $applicant['strAppEmail']; ?></span></a> </li>
                <li><a style="font-size:13px;" href="#">Phone Number: <span class="pull-right"><?php echo $applicant['strAppPhoneNumber']; ?></span></a></li>
                                  
              </ul>
            </div>
-->

          </div>
            
        </div>
        
    
        <div class="col-sm-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#tab_1" data-toggle="tab">Basic Information</a></li>
                    <li><a href="#tab_2" data-toggle="tab">Course Preference</a></li>
                    <li><a href="#tab_3" data-toggle="tab">Personal Information</a></li>
                    <li><a href="#tab_4" data-toggle="tab">Exam Information</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="tab_1">
                        <div class="box box-primary">
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-sm-3 size-96">
                                      <?php if($applicant['strAppPicture'] == "" ): ?>
                                       <img src="<?php echo $img_dir?>default_image2.png" class="img-responsive"/>
                                      <?php else: ?>
                                            <img class="img-responsive" src="<?php echo $student_pics.$applicant['strAppPicture']; ?>" />
                                      <?php endif; ?>
                                    </div>
                                    <div class="col-sm-8 bordered-info">
                                        <div class="row">
                                            <div class="col-sm-4 text-right"><strong>Applicant Number: </strong></div>
                                            <div class="col-sm-8"><?php echo $applicant['strAppNumber']; ?></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4 text-right"><strong>Learner Reference Number(LRN): </strong></div>
                                            <div class="col-sm-8"><?php echo $applicant['strAppLRN']; ?>&nbsp;</div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4 text-right"><strong>Address: </strong></div>
                                            <div class="col-sm-8"><?php echo ucwords(strtolower($applicant['strAppAdress'])) . ", ". $applicant['brgyDesc'] . ", " . ucwords(strtolower($applicant['citymunDesc'])) . ", " . ucwords(strtolower($applicant['provDesc'])) ; ?></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4 text-right"><strong>Contact: </strong></div>
                                            <div class="col-sm-8"><?php echo $applicant['strAppPhoneNumber']; ?></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4 text-right"><strong>Email: </strong></div>
                                            <div class="col-sm-8"><?php echo $applicant['strAppEmail']; ?></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4 text-right"><strong>Birthdate: </strong></div>
                                            <div class="col-sm-8"><?php echo date("M j, Y",strtotime($applicant['dteAppBirthdate'])); ?></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4 text-right"><strong>Date Created: </strong></div>
                                            <div class="col-sm-8"><?php echo date("M j, Y",strtotime($applicant['strAppDate'])); ?></div>
                                        </div>
                                      
                                    </div>
                                </div>    
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="tab_2">
                        <div class="box box-primary">
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-sm-3 size-96">
                                      <?php if($applicant['strAppPicture'] == "" ): ?>
                                       <img src="<?php echo $img_dir?>default_image2.png" class="img-responsive"/>
                                      <?php else: ?>
                                            <img class="img-responsive" src="<?php echo $student_pics.$applicant['strAppPicture']; ?>" />
                                      <?php endif; ?>
                                    </div>
                                    <div class="col-sm-8 bordered-info">
                                        <div class="row">
                                            <div class="col-sm-4 text-right"><strong>1st Choice: </strong></div>
                                            <div class="col-sm-8"><?php echo $applicant['enumCourse1']; ?></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4 text-right"><strong>2nd Choice: </strong></div>
                                            <div class="col-sm-8"><?php echo $applicant['enumCourse2']; ?></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4 text-right"><strong>3rd Choice: </strong></div>
                                            <div class="col-sm-8"><?php echo $applicant['enumCourse3']; ?></div>
                                        </div>                                       
                                    </div>
                                </div>
                            </div>    
                        </div>
                    </div>                    
                    <div class="tab-pane" id="tab_3">
                        <div class="box box-primary">
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-sm-3 size-96">
                                      <?php if($applicant['strAppPicture'] == "" ): ?>
                                       <img src="<?php echo $img_dir?>default_image2.png" class="img-responsive"/>
                                      <?php else: ?>
                                            <img class="img-responsive" src="<?php echo $student_pics.$applicant['strAppPicture']; ?>" />
                                      <?php endif; ?>
                                    </div>
                                    <div class="col-sm-9 bordered-info">
                                        <div class="row">
                                            <div class="col-sm-4 text-right"><strong>Last School Attended: </strong></div>
                                            <div class="col-sm-8"><?php echo $applicant['strAppLastSchool']; ?></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4 text-right"><strong>Birthdate: </strong></div>
                                            <div class="col-sm-8"><?php echo date('M j, Y',strtotime($applicant['dteAppBirthdate'])); ?></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4 text-right"><strong>Gender: </strong></div>
                                            <div class="col-sm-8"><?php echo ucfirst($applicant['strAppGender']); ?></div>
                                        </div>                                      
                                        <div class="row">
                                            <div class="col-sm-4 text-right"><strong>Civil Status: </strong></div>
                                            <div class="col-sm-8"><?php echo ucfirst($applicant['strAppCivilStatus']); ?></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4 text-right"><strong>Father: </strong></div>
                                            <div class="col-sm-8"><?php echo $applicant['strAppFather']; ?></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4 text-right"><strong>Mother: </strong></div>
                                            <div class="col-sm-8"><?php echo $applicant['strAppMother']; ?></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4 text-right"><strong>Spouse: </strong></div>
                                            <div class="col-sm-8"><?php echo $applicant['strAppSpouse']; ?>&nbsp;</div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4 text-right"><strong>Religion: </strong></div>
                                            <div class="col-sm-8"><?php echo $applicant['strAppReligion']; ?>&nbsp;</div>
                                        </div>
                                    </div>
                                </div>    
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="tab_4">
                        <div class="box box-primary">
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-sm-3 size-96">
                                      <?php if($applicant['strAppPicture'] == "" ): ?>
                                       <img src="<?php echo $img_dir?>default_image2.png" class="img-responsive"/>
                                      <?php else: ?>
                                            <img class="img-responsive" src="<?php echo $student_pics.$applicant['strAppPicture']; ?>" />
                                      <?php endif; ?>
                                    </div>
                                    <div class="col-sm-8 bordered-info">
                                        <div class="row">
                                            <div class="col-sm-4 text-right"><strong>Exam Date: </strong></div>
                                           <div class="col-sm-8"><?php echo date('M j, Y',strtotime($applicant['dteScheduleExam'])); ?></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4 text-right"><strong>Confirmation Code: </strong></div>
                                            <div class="col-sm-8"><?php echo $applicant['strConfirmationCode']; ?></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4 text-right"><strong>Is Confirmed: </strong></div>
                                            <div class="col-sm-8"><?php echo $exam['isConfirmed']; ?></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4 text-right"><strong>Score: </strong></div>
                                            <div class="col-sm-8">
                                                <input type="number" class="form-control" value="<?php echo $exam['intExamScore']; ?>" id="intExamScore" />
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4 text-right"><strong>Remarks: </strong></div>
                                            <div class="col-sm-8">
                                                <textarea class="form-control" id="strExamRemarks" ><?php echo $exam['strExamRemarks']; ?></textarea>
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

