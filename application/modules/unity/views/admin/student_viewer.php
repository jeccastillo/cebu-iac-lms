
<aside class="right-side">
    <div id="student-viewer-container">
        <section class="content-header">
            <h1>
                <small>
                    <a class="btn btn-app" href="<?php echo base_url() ?>student/view_all_students" ><i class="ion ion-arrow-left-a"></i>All Students</a> 
                    <a class="btn btn-app trash-student-record2" rel="<?php echo $student['intID']; ?>" href="#"><i class="ion ion-android-close"></i> Delete</a>   
                    <a class="btn btn-app" href="<?php echo base_url()."student/edit_student/".$student['intID']; ?>"><i class="ion ion-edit"></i> Edit</a> 
                    <!-- <a class="btn btn-app" href="<?php echo base_url() ?>excel/download_repeated_subject_per_student/<?php echo $student['intID']; ?>"><i class="ion ion-android-download"></i> Download Repeated Subjects</a> -->
                    <a class="btn btn-app" target="_blank" href="<?php echo base_url()."pdf/print_curriculum/".$student['intCurriculumID']."/".$student['intID'] ?>"><i class="fa fa-print"></i>Curriculum Outline</a> 
                    <?php if($registration && in_array($user['intUserLevel'],array(2,3,6))): ?>
                        <a target="_blank" class="btn btn-app" href="<?php echo base_url()."pdf/student_viewer_registration_print/".$student['intID'] ."/". $active_sem['intID']; ?>">
                        <i class="ion ion-printer"></i>Reg Form Print Preview</a> 
                    
                        <!-- <a target="_blank" class="btn btn-app" href="<?php echo base_url()."pdf/student_viewer_registration_data_print/".$student['intID'] ."/". $active_sem['intID']; ?>">
                        <i class="ion ion-printer"></i>Registration Data Only</a>  -->
                        <!-- <a target="_blank" class="btn btn-app" href="<?php echo base_url()."pdf/student_viewer_registration_data_print_legacy/".$student['intID'] ."/". $active_sem['intID']; ?>">
                        <i class="ion ion-printer"></i>Registration Data Only (Legacy)</a>  -->
                    <?php endif; ?>
                    <?php if($reg_status!="For Advising"): ?>
                    <a target="_blank" class="btn btn-app" href="<?php echo base_url()."pdf/student_viewer_advising_print/".$student['intID'] ."/". $active_sem['intID']; ?>">
                        <i class="ion ion-printer"></i>Print Advising Form</a> 
                    <?php endif; ?>
                    <?php if($reg_status == "For Advising"): ?>
                        <a class="btn btn-app" href="<?php echo base_url()."/department/advising/".$student['intID']; ?>">
                        <i class="fa fa-book"></i>Advising/Subject Loading</a> 
                        
                    <?php endif; ?>                            
                    <?php if(!$registration && $reg_status!="For Advising"): ?>
                        <a class="btn btn-app" href="<?php echo base_url()."unity/edit_sections/".$student['intID'] ."/". $active_sem['intID']; ?>">
                        <i class="fa fa-book"></i> Update Sections</a> 
                        
                        <a class="btn btn-app" href="<?php echo base_url()."/registrar/register_old_student2/".$student['intID']; ?>">
                        <i class="fa fa-book"></i>Register Student</a> 
                    <?php endif; ?>                    
                </small>
                
                <div class="box-tools pull-right">
                    <select id="select-sem-student" class="form-control" >
                    <?php foreach($sy as $s): ?>
                        <option <?php echo ($selected_ay == $s['intID'])?'selected':''; ?> value="<?php echo $s['intID']; ?>"><?php echo $s['enumSem']." ".$term_type." ".$s['strYearStart']."-".$s['strYearEnd']; ?></option>
                    <?php endforeach; ?>
                    </select>
                    <?php if($registration): ?>
                    <label style="font-size:.6em;"> Registration Status</label>
                    
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
                    
                </div>
                
            
                <div class="col-sm-12">
                    <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                    <li class="<?php echo ($tab == "tab_1")?'active':'' ?>"><a href="#tab_1" data-toggle="tab">Personal Information</a></li>
                    <?php if(in_array($user['intUserLevel'],array(2,4,3)) ): ?>
                    <li class="<?php echo ($tab == "tab_2")?'active':'' ?>"><a href="#tab_2" data-toggle="tab">Report of Grades</a></li>
                    <li class="<?php echo ($tab == "tab_3")?'active':'' ?>"><a href="#tab_3" data-toggle="tab">Assessment</a></li>
                        <?php endif; ?>
                    <?php if($registration && in_array($user['intUserLevel'],array(2,3,4,6))): ?>
                    <li class="<?php echo ($tab == "tab_5")?'active':'' ?>"><a href="#tab_5" data-toggle="tab">Schedule</a></li>
                    <li><a href="<?php echo base_url()."unity/registration_viewer/".$student['intID']."/".$selected_ay; ?>">Statement of Account</a></li>
                        
                        <li><a href="<?php echo base_url()."unity/edit_registration/".$student['intID']."/".$selected_ay; ?>">Edit Registration</a></li>
                        <?php endif; ?>
                        <li><a href="<?php echo base_url()."unity/accounting/".$student['intID']; ?>">Accounting Summary</a></li>
                        
                    
                    </ul>
                    <div class="tab-content">
                    <div class="tab-pane <?php echo ($tab == "tab_1")?'active':'' ?>" id="tab_1">
                        <div class="box box-primary">
                            
                            <div class="box-body">
                                <div class="row">
                                    <div class="alert alert-danger" style="<?php echo ($upload_errors=="")?'display:none':'' ?>;">
                                        <i class="fa fa-ban"></i>
                                        <b>Alert!</b>
                                        <?php echo $upload_errors; ?>
                                    </div>
                                    <div class="col-sm-3 size-96">
                                    <?php if($student['strPicture'] == "" ): ?>
                                    <img src="<?php echo $img_dir?>default_image2.png" class="img-responsive"/>
                                    <?php else: ?>
                                            <img class="img-responsive" src="<?php echo $photo_dir.$student['strPicture']; ?>" />
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-sm-9">
                                    <p><strong>Student Number: </strong><?php echo $student['strStudentNumber']; ?></p>
                                    <p><strong>Learner Reference Number(LRN): </strong><?php echo $student['strLRN']; ?></p>
                                    <p><strong>Address: </strong><?php echo $student['strAddress']; ?></p>
                                    <p><strong>Contact: </strong><?php echo $student['strMobileNumber']; ?></p>
                                    <p><strong>Institutional Email: </strong><?php echo $student['strGSuiteEmail']; ?></p>  
                                    <p><strong>Personal Email: </strong><?php echo $student['strEmail']; ?></p>  
                                    <p><strong>Birthdate: </strong><?php echo date("M j, Y",strtotime($student['dteBirthDate'])); ?></p>  
                                    <p><strong>Date Created: </strong><?php echo date("M j, Y",strtotime($student['dteCreated'])); ?></p>
                                        
                                    
                                    <strong>Is Graduate:</strong>
                                    <?php if(in_array($user['intUserLevel'],array(2,3)) ): ?>
                                    <select class="form-control" rel="<?php echo $student['intID']; ?>" id="GraduateStatus">
                                        <option <?php echo ($student['isGraduate'] == 0)?'selected':'' ?>  value="0">No</option>
                                        <option <?php echo ($student['isGraduate'] == 1)?'selected':'' ?> value="1">Yes</option>
                                    </select>
                                        <hr />
                                        <a href="<?php echo base_url()."pdf/portal_login_data/".$student['intID']; ?>" class="btn btn-info" target="_blank">Portal Login Data</a>
                                    <?php else: 
                                        switch($student['isGraduate'])
                                        {
                                            case 0:
                                                echo 'No';
                                                break;
                                            case 1:
                                                echo 'yes';
                                                break;
                                        }
                                        ?>
                                        
                                    <?php endif; ?>
                                        
                                    </div>
                                    
                                    
                                </div>    
                            </div>
                        </div>
                        </div>
                    <!-- /.tab-pane -->
                    <?php if(in_array($user['intUserLevel'],array(2,4,3)) ): ?>
                    <div class="tab-pane <?php echo ($tab == "tab_2")?'active':'' ?>" id="tab_2">
                        <div class="box box-primary">
                                <div class="box-body">
                                    <?php if($active_sem['enumFinalized'] == "no" && $registration): ?>
                                    <div class="row">
                                                    <div class="col-sm-6">
                                                        <div class="input-group">
                            
                                                        <select class="select2" id="subjectSv" name="subjectSv">
                                                                <?php foreach($curriculum_subjects as $s): ?>
                                                                    <option value="<?php echo $s['intSubjectID'] ?>"><?php echo $s['strCode']; ?> <?php echo $s['strDescription']; ?></option> 
                                                                <?php endforeach; ?>
                                                            </select>
                                                            <a href="<?php echo base_url(); ?>subject/subject_viewer/<?php echo $curriculum_subjects[0]['intSubjectID']; ?>" id="viewSchedules" target="_blank" class='btn btn-default input-group-addon  btn-flat'>View Schedules</a>

                                                    </div>
                                                        
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <select class="form-control" id="sections-to-add">
                                                        <?php foreach($sections as $sc): ?>
                                                            <option value="<?php echo $sc['intID'] ?>"><?php echo $sc['strSection']; ?></option> 
                                                        <?php endforeach; ?>
                                                    </select>
                                                    </div>
                                                    <div class="col-sm-2">
                                                        <a href="#" id="submitSubject" class='btn btn-default  btn-flat'>Add Subject <i class='fa fa-plus'></i></a>
                                                    </div>
                                                </div>
                                    <hr />
                                    <?php endif; ?> 
                                    <table class="table table-condensed table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Section Code</th>
                                                <th>Course Code</th>
                                                <th>Units</th>
                                                <th>Grade</th>
                                                <th>Remarks</th>
                                                <th>Faculty</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $units = 0;
                                            $totalUnits = 0;
                                            $totalLab = 0;
                                            foreach($records as $record): ?>
                                            <tr style="font-size: 13px;">
                                                <td><?php echo $record['strSection']; ?></td>
                                                <td><?php echo $record['strCode']; ?></td>
                                                <td><?php echo $record['strUnits']; ?>
                                                <?php
                                                    $units += $record['strUnits'];
                                                    if($record['intLab'] == 1)
                                                    {
                                                        $totalLab++;
                                                    }  
                                                ?>
                                                <?php if($record['intFinalized'] > 2): 
                                                        if($record['v3'] == 5.00):    
                                                ?>
                                                    <td><span class="text-red"><?php echo ($record['v3']==3.50)?'inc':$record['v3']; ?></span></td>
                                                <?php else: ?>
                                                <td></span><span><?php echo ($record['v3']==3.50)?'inc':number_format($record['v3'], 2, '.' ,','); ?></span></td>
                                                <?php endif; ?>
                                                <?php else: ?>
                                                    <td><span><?php echo "-" ?></span></td>
                                                    
                                                <?php endif; ?>
                                                    
                                                    <?php
                                                        if($record['v3'] != 3.50 && $record['v3'] != "0")
                                                        {
                                                            //$productArray = array();
                                                        
                                                            //echo $product
                                                            if ($record['intBridging'] == 1){
                                                                //$num_of_bridging = count($record['intBridging']);
                                                                $totalUnits += $record['strUnits'];
                                                                $totalUnits-=3;
                                                            }
                                                            else{
                                                                $product = $record['strUnits'] * $record['v3']; 
                                                                $products[] = $product;
                                                                $totalUnits += $record['strUnits'];
                                                            }     
                                                        }

                                                    ?>
                                        <?php if($record['intFinalized']  > 2):
                                                    if($record['v3'] == 5.00): 
                                                ?>  
                                                    <td><span class="text-red"><?php echo $record['strRemarks']; ?></span></td>
                                                <?php else: ?>
                                                <td><?php echo $record['strRemarks']; ?></td>
                                                <?php endif; ?>
                                            
                                    <?php else: ?>
                                                    <td><span><?php echo "-" ?></span></td>
                                                    
                                                <?php endif; ?>
                                                <td>  
                                                <?php 
                                                        if($record['strFirstname']!="unassigned"){
                                                        $firstNameInitial = substr($record['strFirstname'], 0,1);
                                                        echo $firstNameInitial.". ".$record['strLastname'];  
                                                        }
                                                        else
                                                        echo "unassigned";
                                                    ?>
                                                </td>
                                    <td><?php 
                                                if ($record['strFirstname'] == "unassigned") {
                                                    echo "<span class='label label-warning'>No Assigned Faculty Yet</span>";
                                                }
                                                else {
                                                    if ($record['intFinalized'] > 2) {
                                                        echo "<span class='label label-success'>Submitted</span>";
                                                    }
                                                    else {
                                                        echo "<span class='label label-danger'>Not Yet Submitted</span>";
                                                    }
                                                }
                                        ?> 
                                    </td>
                                                <td>
                                                    <?php if($record['intFinalized'] < 2): ?>
                                                        <a href="#" rel="<?php echo $record['intCSID']; ?>" class="remove-from-classlist">Remove</a><br />
                                                        <a href="<?php echo base_url()."unity/classlist_viewer/".$record['classlistID'] ?>" rel="<?php echo $record['intCSID']; ?>">View Classlist</a>
                                                    <?php else: ?>
                                                    <a href="<?php echo base_url()."unity/classlist_viewer/".$record['classlistID'] ?>" rel="<?php echo $record['intCSID']; ?>">View Classlist</a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                                <tr style="font-size: 13px;">
                                                    <td></td>
                                                    <td align="right"><strong>TOTAL UNITS CREDITED:</strong></td>
                                                    <td><?php 
                                                            echo $totalUnits;
                                                            ?>
                                                    </td>
                                                    <td colspan="3"></td>

                                                </tr>

                                                <tr style="font-size: 11px;">
                                                    <td></td>
                                                    <td align="right"><strong>GPA:</strong></td>
                                                    <td>
                                                        <?php
                                                        $gpa = round(array_sum($products) / $totalUnits, 2)  ;
                                                        echo $gpa;
                                                        //echo $num_of_bridging;
                                                        ?>


                                                    </td>
                                                    <td colspan="3"></td>
                                                </tr>


                                        </tbody>
                                    </table>
                                    <hr />
                                    <a target="_blank" class="btn btn-default  btn-flat" href="<?php echo base_url()."pdf/student_viewer_rog_print/".$student['intID'] ."/". $active_sem['intID']; ?>">
                                        <i class="ion ion-printer"></i> Print Preview</a> 
                                    <a target="_blank" class="btn btn-default  btn-flat" href="<?php echo base_url()."pdf/student_viewer_rog_data_print/".$student['intID'] ."/". $active_sem['intID']; ?>">
                                        <i class="ion ion-printer"></i> Print Data Preview</a> 
                                        
                                    
                                </div>
                            </div>
                    </div>
                    <!-- /.tab-pane -->
                    <div class="tab-pane <?php echo ($tab == "tab_3")?'active':'' ?>" id="tab_3">
                        <div class="row">
                            <div class="col-md-10 col-md-offset-1">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Academic Standing</th>
                                            <th>CGPA</th>
                                            <th>Units Earned</th>
                                            <th>Total Units</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><?php echo switch_num($academic_standing['year']); ?> Year / <?php echo $academic_standing['status']; ?></td>
                                            <td><?php echo $gpa_curriculum; ?></td>
                                            <td><?php echo $totalUnitsEarned; ?></td>
                                            <td><?php echo $units_in_curriculum; ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <?php 
                                    $prev_year_sem = '0';
                                    $sgpa = 0;
                                    $scount = 0;
                                    $countBridg = 0;
                                    for($i = 0;$i<count($grades); $i++): 
                                    //echo $prev_year_sem."<br />";
                                
                                    if($grades[$i]['floatFinalGrade']!="0" && $grades[$i]['floatFinalGrade']!="3.5")
                                    {    
                                        //print_r($grades[$i]['intBridging']);
                                        
                                        if ($grades[$i]['intBridging'] == 1) { 
                                            $countBridg  = $countBridg + $grades[$i]['intBridging'];
                                            $scount += $grades[$i]['strUnits'];
                                            $scount-=3;
                                        }
                                        else {
                                            
                                            $sgpa += $grades[$i]['floatFinalGrade']*$grades[$i]['strUnits'];
                                            $scount+=$grades[$i]['strUnits'];
                                        
                                        }
                                    //print_r($grades[$i]['intBridging']);
                                    //echo "<br />" . $countBridg;
                                    }

                                    ?>
                                    <?php if($prev_year_sem != $grades[$i]['syID']): 
                                        $countBridg = 0;
                                    ?>
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th colspan="4">
                                                    <?php echo ($grades[$i]['syID'] != 0)?$grades[$i]['enumSem']." Sem A.Y. ".$grades[$i]['strYearStart']." - ".$grades[$i]['strYearEnd']:'Credited Units'; ?>
                                                </th>
                                            </tr>
                                            <tr>
                                                <th>Course Code</th>
                                                <th>Course Description</th>
                                                <th>P</th>
                                                <th>M</th>
                                                <th>F</th>
                                                <th>FG</th>
                                                <th>Num. Rating</th>
                                                <th>Units</th>
                                                <th>Remarks</th>
                                                <th>Faculty</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                    <?php 
                                            
                                            endif; 
                                            $prev_year_sem = $grades[$i]['syID'];
                                        
                                            ?>
                                    <tr class="<?php echo (strtoupper($grades[$i]['strRemarks'])=='PASSED')?'green-bg':''; ?> <?php echo ($grades[$i]['strRemarks']=='Failed' || $grades[$i]['strRemarks']=='Failed(U.D.)')?'red-bg':''; ?>">
                                        <td><a href="<?php echo base_url()."unity/classlist_viewer/".$grades[$i]['classListID'] ?>"><?php echo $grades[$i]['strCode']; ?></a></td>
                                        <td><?php echo $grades[$i]['strDescription']; ?></td>
                                        <td><?php echo $grades[$i]['floatPrelimGrade']; ?></td>
                                        <td><?php echo $grades[$i]['floatMidtermGrade']; ?></td>
                                        <td><?php echo $grades[$i]['floatFinalsGrade']; ?></td>
                                        
                                        <td>
                                        <?php  echo number_format(getAve($grades[$i]['floatPrelimGrade'],$grades[$i]['floatMidtermGrade'],$grades[$i]['floatFinalsGrade']), 2); ?>
                                        </td>
                                        <td>
                                        <?php echo number_format($grades[$i]['floatFinalGrade'], 2, '.' ,','); ?>
                                        </td>
                                        <td>
                                            <?php echo $grades[$i]['strUnits']; ?> 
                                        </td>
                                        <td>
                                            <?php echo $grades[$i]['strRemarks']; ?>
                                        </td>
                                        
                                        <td>
                                        <?php
                                            if($grades[$i]['strFirstname']!="unassigned"){
                                                        $firstNameInitial = substr($grades[$i]['strFirstname'], 0,1);
                                                        echo $firstNameInitial. ". " . $grades[$i]['strLastname'];  
                                                        }
                                                        else
                                                        echo "unassigned";
                                            ?>
                                        </td>
                                    </tr>
                                <?php if($prev_year_sem != $grades[$i+1]['syID'] || count($grades) == $i+1): 
                                    $sgpa_computed = $sgpa/$scount;
                                    $scount_counted = $scount;
                                    $sgpa = 0;
                                    $scount = 0;
                                
                                ?>   
                                    <tr>
                                        <th colspan="4">GPA: <?php echo round($sgpa_computed,2); ?></th>
                                        <th colspan="6">Units: <?php echo $scount_counted; ?></th>
                                    </tr>
                                    <tr>
                                    <?php if($countBridg > 0): ?>
                                    <td colspan="10" style="font-style:italic;font-size:13px;"><small>Note: (<?php echo $countBridg; ?>) Bridging course/s - not computed in units & GPA.</small></td>
                                    <?php endif; ?> 
                                    </tr>
                                    </tbody>
                                </table>
                                <?php 
                                endif; ?>
                                <?php 
                                endfor; ?>

                            </div> 
                        </div>
                            
                    </div>
                    <?php endif; ?>
                    <?php if($registration): ?>
                        <div class="tab-pane <?php echo ($tab == "tab_5")?'active':'' ?>" id="tab_5">
                                <div class="box box-primary">
                                    <div class="box-body">
                                        <table class="table table-condensed table-bordered">
                                            <thead>
                                                <tr style="font-size: 13px;">
                                                    <th>Section</th>
                                                    <th>Course Code</th>
                                                    <th>Course Description</th>
                                                    <th>Units</th>
                                                    <th>Schedule</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $totalUnits = 0;
                                                foreach($records as $record): ?>
                                                <tr style="font-size: 13px;">
                                                    <td><?php echo $record['strSection']; ?></td>
                                                    <td><?php echo $record['strCode']; ?></td>
                                                    <td><?php echo $record['strDescription'] ?></td>
                                                    <td><?php echo ($record['strUnits'] == 0)?'('.$record['intLectHours'].')':$record['strUnits']; ?></td>     
                                                    <?php if(!empty($record['schedule'])): ?>

                                                    <td>
                                                        <?php foreach($record['schedule'] as $sched): ?>

                                                        <?php echo date('g:ia',strtotime($sched['dteStart'])).' - '.date('g:ia',strtotime($sched['dteEnd'])); ?> <?php echo $sched['strDay']; ?> <?php echo $sched['strRoomCode']; ?>
                                                        
                                                        <?php
                                                            $hourdiff = round((strtotime($sched['dteEnd']) - strtotime($sched['dteStart']))/3600, 1);
                                                            
                                                        ?>
                                                        <input type="hidden" class="<?php echo $sched['strDay']; ?>" value="<?php echo date('gia',strtotime($sched['dteStart'])); ?>" href="<?php echo $hourdiff*2; ?>" rel="<?php echo $record['strCode']; ?> <?php echo $sched['strRoomCode']; ?>" data-section="<?php echo $sched['strSection']; ?>">
                                                        <br />
                                                        <?php endforeach; ?>
                                                    </td>
                                                    <?php else: ?>
                                                    <td></td>

                                                    <?php endif; ?>

                                                </tr>

                                                <?php endforeach; ?>




                                            </tbody>
                                        </table>
                                    </div>
                            </div>
                            <div class="box box-primary">
                                    <div class="box-body">
                                    <form method="post" action="<?php echo base_url() ?>pdf/print_sched">   
                                        <input type="hidden" name="sched-table" id="sched-table" />
                                        <input type="hidden" value="<?php echo $student['strLastname']."-".$student['strFirstname']."-".$student['strStudentNumber']; ?>" name="studentInfo" id="studentInfo" />
                                        
                                        <div id="sched-table-container">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th style="text-align:center;border:1px solid #555;"></th>
                                                    <th style="text-align:center;border:1px solid #555;">Mon</th>
                                                    <th style="text-align:center;border:1px solid #555;">Tue</th>
                                                    <th style="text-align:center;border:1px solid #555;">Wed</th>
                                                    <th style="text-align:center;border:1px solid #555;">Thu</th>
                                                    <th style="text-align:center;border:1px solid #555;">Fri</th>
                                                    <th style="text-align:center;border:1px solid #555;">Sat</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tbody-sched">
                                                <tr id="700am">
                                                <td style="border:1px solid #555;">7:00-7:30</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                                <tr id="730am">
                                                    <td style="border:1px solid #555;">7:30-8:00</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                                <tr id="800am">
                                                    <td style="border:1px solid #555;">8:00-8:30</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                                <tr id="830am">
                                                    <td style="border:1px solid #555;">8:30-9:00</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                                <tr id="900am">
                                                    <td style="border:1px solid #555;">9:00-9:30</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                                <tr id="930am">
                                                    <td style="border:1px solid #555;">9:30-10:00</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                                <tr id="1000am">
                                                    <td style="border:1px solid #555;">10:00-10:30</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                                <tr id="1030am">
                                                    <td style="border:1px solid #555;">10:30-11:00</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                                <tr id="1100am">
                                                    <td style="border:1px solid #555;">11:00-11:30</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                            <tr id="1130am">
                                                    <td style="border:1px solid #555;">11:30-12:00</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                                <tr id="1200pm">
                                                    <td style="border:1px solid #555;">12:00-12:30</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                                <tr id="1230pm">
                                                    <td style="border:1px solid #555;">12:30-1:00</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                                <tr id="100pm">
                                                    <td style="border:1px solid #555;">1:00-1:30</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                                <tr id="130pm">
                                                    <td style="border:1px solid #555;">1:30-2:00</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                                <tr id="200pm">
                                                    <td style="border:1px solid #555;">2:00-2:30</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                                <tr id="230pm">
                                                    <td style="border:1px solid #555;">2:30-3:00</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                                <tr id="300pm">
                                                    <td style="border:1px solid #555;">3:00-3:30</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                                <tr id="330pm">
                                                    <td style="border:1px solid #555;">3:30-4:00</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                                <tr id="400pm">
                                                    <td style="border:1px solid #555;">4:00-4:30</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                                <tr id="430pm">
                                                    <td style="border:1px solid #555;">4:30-5:00</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                                <tr id="500pm">
                                                    <td style="border:1px solid #555;">5:00-5:30</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                                <tr id="530pm">
                                                    <td style="border:1px solid #555;">5:30-6:00</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                                <tr id="600pm">
                                                    <td style="border:1px solid #555;">6:00-6:30</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                                <tr id="630pm">
                                                    <td style="border:1px solid #555;">6:30-7:00</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        </div>
                                        <input class="btn btn-flat btn-default" type="submit" value="print preview" />
                                        </form> 
                                    </div>
                                </div>
                        </div>
                    <?php endif; ?>
                    
                    </div>
                    <!-- /.tab-content -->
                </div>
                </div>
            </div>
    
    
    
    
        </div>
</div>
</aside>

<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@2.6.12"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
    integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>

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

<script>
new Vue({
    el: '#student-viewer-container',
    data: {
        id: '<?php echo $id; ?>',    
        sem: '<?php echo $sem; ?>',
        tab: '<?php echo $tab; ?>',
        base_url: '<?php echo base_url(); ?>',                      
        loader_spinner: true,                        
    },

    mounted() {

        let url_string = window.location.href;        
        if(this.id != 0){            
            //this.loader_spinner = true;
            axios.get(this.base_url + 'unity/student_viewer_data/' + this.id + '/' + this.sem + '/' this.tab)
                .then((data) => {  
                    if(data.data.success){                                                                                           
                        console.log(data.data);
                    }
                    else{
                        document.location = this.base_url + 'users/login';
                    }

                    this.loader_spinner = false;                    
                })
                .catch((error) => {
                    console.log(error);
                })
        }

    },

    methods: {        
        
    }

})
</script>