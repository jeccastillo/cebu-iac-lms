<aside class="right-side">
<section class="content-header">
                    <h1>
                        Student
                        <small></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Student</a></li>
                        <li class="active">Edit Student</li>
                    </ol>
                </section>
<div class="content">
    <div class="span10 box box-primary">
        <div class="box-header">
                <h3 class="box-title">Edit Student</h3>
            <?php //echo print_r($student); ?>
        </div>
       
            
            <form id="validate-student" action="<?php echo base_url(); ?>student/edit_submit_student" method="post" role="form" enctype="multipart/form-data">
                <input type="hidden" name="intID" class="form-control" id="intID" value="<?php echo $student['intID']; ?>">
                 <div class="box-body">
                    <div class="row">
                        <div class="form-group col-xs-4">
                            <label for="strLastname">Last Name*</label>
                            <input type="text" value="<?php echo $student['strLastname']; ?>" name="strLastname" class="form-control" id="strLastname" placeholder="Enter Last Name">
                        </div>
                        <div class="form-group col-xs-4">
                            <label for="strFirstname">First Name*</label>
                            <input type="text" value="<?php echo $student['strFirstname']; ?>" name="strFirstname" class="form-control" id="strFirstname" placeholder="Enter First Name">
                        </div>
                        <div class="form-group col-xs-4">
                            <label for="strMiddlename">Middle Name</label>
                            <input type="text" value="<?php echo $student['strMiddlename']; ?>" name="strMiddlename" class="form-control" id="strMiddlename" placeholder="Enter Middle Name">
                        </div>
                        <div class="form-group col-xs-4">
                            <label for="enumGender">Gender: </label>
                            <select class="form-control" name="enumGender" >
                        <!-- <option>---Select Gender---</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
        -->
                            <option <?php echo ($student['enumGender'] == "male")?'selected':''; ?> value="male">Male</option>
                            <option <?php echo ($student['enumGender'] == "female")?'selected':''; ?> value="female">Female</option>

                        </select>
                        </div>
                        <div class="form-group col-xs-4">
                            <label for="strStudentNumber">Student Number<a rel="locked" href="#" id="studentnumber-lock"><i class="ion ion-locked"></i></a></label>
                            <input type="text" disabled value="<?php echo $student['strStudentNumber']; ?>" name="strStudentNumber" class="form-control" id="strStudentNumber" placeholder="Enter Student Number">
                        </div>
                        <div class="form-group col-xs-4">
                            <label for="strLRN">Learner Reference Number (LRN)</label>
                            <input type="text" value="<?php echo $student['strLRN']; ?>" name="strLRN" class="form-control" id="strLRN" placeholder="Enter Learner Reference Number">    
                    </div>
                    
                        
                        <div class="form-group col-xs-6">                     
                            <label for="strStudentNumber">Portal Password (<?php echo ($student['strPass']!="")?'has password':'no password'; ?>)</label>
                            <div class="input-group">
                                <input type="text" value="" name="strPass" class="form-control" id="strPass" placeholder="Password">
                                <span class="input-group-btn">
                                    <button type="button" id="generate-password" class="btn btn-danger btn-flat">Generate</button>
                                </span>
                            </div>
                        </div>
                    <div class="form-group col-xs-6">
                            <label for="strEmail">Email</label>
                            <input type="email" value="<?php echo $student['strEmail']; ?>" name="strEmail" class="form-control" id="strEmail" placeholder="Enter Email Address">
                        </div>
                    <div class="form-group col-xs-6">
                            <label for="dteBirthDate">Birthday</label>
                            <div class="input-group date">  
                            <input type="text" name="dteBirthDate" value="<?php echo date("m/d/Y",strtotime($student['dteBirthDate'])); ?>"  class="form-control validate" id="dteBirthDate" placeholder="Enter Birthday">
                            <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                            </div>
                        </div> 
                    <div class="form-group col-xs-6">
                            <label for="strMobileNumber">Contact Number</label>
                            <input type="number" value="<?php echo $student['strMobileNumber']; ?>" name="strMobileNumber" class="form-control" id="strMobileNumber" placeholder="Enter Contact Number">
                        </div>
                    <div class="form-group col-xs-6">
                            <label>Address</label>
                            <textarea class="form-control" name="strAddress" rows="3" placeholder="Enter Address"><?php echo $student['strAddress']; ?></textarea>
                    </div>
                    
                    <!-- <div class="form-group col-xs-6">
                            <label for="strZipCode">Zip Code</label>
                            <input type="number" name="strZipCode" class="form-control" id="strZipCode" placeholder="Enter Zip Code" value="<?php echo $student['strZipCode']; ?>">
                    </div>
                    <div class="form-group col-xs-6">
                            <label for="strGSuiteEmail">GSuite Email</label>
                            <input type="email" name="strGSuiteEmail" class="form-control" id="strGSuiteEmail" placeholder="Enter GSuite Email" value="<?php echo $student['strGSuiteEmail']; ?>">
                    </div>      -->
                    <div class="form-group col-xs-6">
                        <label for="intProgramID">Course</label>
                        <select class="form-control" name="intProgramID" >
                            <?php foreach ($programs as $prog): ?>
                            <option <?php echo ($student['intProgramID'] == $prog['intProgramID'])?'selected':''; ?> value="<?php echo $prog['intProgramID']; ?>"><?php echo $prog['strProgramCode']; ?></option>
                            <?php endforeach; ?>
                        </select>
                        
                    </div>
                    <div class="form-group col-xs-6">
                        <label for="">Curriculum</label>
                        <select class="form-control select2" name="intCurriculumID" id="intCurriculumID" >
                            <?php foreach ($curriculum as $curr): ?>
                            <option <?php echo ($student['intCurriculumID'] == $curr['intID'])?'selected':''; ?> value="<?php echo $curr['intID']; ?>"><?php echo $curr['strName']; ?></option>
                            <?php endforeach; ?>
                        </select>
                        
                    </div>
                    <!--div class="form-group col-xs-6">
                        <label for="enumEnrolledStatus">Student Status</label>
                        <select class="form-control" name="enumEnrolledStatus" >
                            <option <?php echo ($student['enumEnrolledStatus'] == "enrolled")?'selected':''; ?> value="enrolled">Enrolled</option>
                            <option <?php echo ($student['enumEnrolledStatus'] == "dismissed")?'selected':''; ?> value="dismissed">Dismissed</option>
                            <option <?php echo ($student['enumEnrolledStatus'] == "inactive")?'selected':''; ?> value="inactive">Inactive</option>
                        </select>
                        
                    </div-->
                    <div class="form-group col-xs-6">
                            <label for="srtPicture">Upload Picture</label>
                            <?php if($student['strPicture'] != "" ): ?>
                                <img class="img-responsive" src="<?php echo $student_pics.$student['strPicture']; ?>" width="30%" height="30%" />
                        <input type="file" name="strPicture" />
                            <?php else: ?>
                                <i class="icon ion-android-social-user"></i>
                                <input type="file" name="strPicture" />
                            <?php endif; ?>
                        
                        
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-4">
                            <label>Mother's Maiden Name</label>
                            <input type="text" value="<?php echo $student['mother']; ?>" name="mother" class="form-control" id="mother" placeholder="Enter Name">
                    </div>
                    <div class="form-group col-md-4">
                            <label>Mother's Contact Number</label>
                            <input type="text" value="<?php echo $student['mother_contact']; ?>" name="mother_contact" class="form-control" id="mother_contact" placeholder="Enter Contact">
                    </div>
                    <div class="form-group col-md-4">
                            <label>Mother's Email Address</label>
                            <input type="email" value="<?php echo $student['mother_email']; ?>" name="mother_email" class="form-control" id="mother_email" placeholder="Enter Email">
                    </div>

                    <div class="form-group col-md-4">
                            <label>Father's Name</label>
                            <input type="text" value="<?php echo $student['father']; ?>" name="father" class="form-control" id="father" placeholder="Enter Name">
                    </div>
                    <div class="form-group col-md-4">
                            <label>Father's Contact Number</label>
                            <input type="text" value="<?php echo $student['father_contact']; ?>" name="father_contact" class="form-control" id="father_contact" placeholder="Enter Contact">
                    </div>
                    <div class="form-group col-md-4">
                            <label>Father's Email Address</label>
                            <input type="email" value="<?php echo $student['father_email']; ?>" name="father_email" class="form-control" id="father_email" placeholder="Enter Email">
                    </div>


                    <div class="form-group col-md-4">
                            <label>Guardian's Name</label>
                            <input type="text" value="<?php echo $student['guardian']; ?>" name="guardian" class="form-control" id="guardian" placeholder="Enter Name">
                    </div>
                    <div class="form-group col-md-4">
                            <label>Guardian's Contact Number</label>
                            <input type="text" value="<?php echo $student['guardian_contact']; ?>" name="guardian_contact" class="form-control" id="guardian_contact" placeholder="Enter Contact">
                    </div>
                    <div class="form-group col-md-4">
                            <label>Guardian's Email Address</label>
                            <input type="email" value="<?php echo $student['guardian_email']; ?>" name="guardian_email" class="form-control" id="guardian_email" placeholder="Enter Email">
                    </div>

                    <div class="form-group col-md-4">
                            <label>High School</label>
                            <input type="text" value="<?php echo $student['high_school']; ?>" name="high_school" class="form-control" id="high_school" placeholder="Enter Name">
                    </div>
                    <div class="form-group col-md-4">
                            <label>High School Address</label>
                            <textarea name="high_school_address" class="form-control" id="high_school_address" placeholder="Enter Address"><?php echo $student['high_school_address'] ?></textarea>
                    </div>
                    <div class="form-group col-md-4">
                            <label>Attended</label>
                            <input type="text" value="<?php echo $student['high_school_attended']; ?>" name="high_school_attended" class="form-control" id="high_school_attended" placeholder="Enter Attended Date">
                    </div>

                    <div class="form-group col-md-4">
                            <label>Senior High School</label>
                            <input type="text" value="<?php echo $student['senior_high']; ?>" name="senior_high" class="form-control" id="senior_high" placeholder="Enter Name">
                    </div>
                    <div class="form-group col-md-4">
                            <label>Senior High School Address</label>
                            <textarea name="senior_high_address" class="form-control" id="senior_high_address" placeholder="Enter Address"><?php echo $student['senior_high_address'] ?></textarea>
                    </div>
                    <div class="form-group col-md-4">
                            <label>Attended</label>
                            <input type="text" value="<?php echo $student['senior_high_attended']; ?>" name="senior_high_attended" class="form-control" id="senior_high_attended" placeholder="Enter Attended Date">
                    </div>

                    <div class="form-group col-md-4">
                            <label>College</label>
                            <input type="text" value="<?php echo $student['college']; ?>" name="college" class="form-control" id="college" placeholder="Enter Name">
                    </div>
                    <div class="form-group col-md-4">
                            <label>College Address</label>
                            <textarea name="college_address" class="form-control" id="college_address" placeholder="Enter Address"><?php echo $student['college_address'] ?></textarea>
                    </div>
                    <div class="form-group col-md-4">
                            <label>Attended From</label>
                            <input type="text" value="<?php echo $student['college_attended_from']; ?>" name="college_attended_from" class="form-control" id="college_attended_from" placeholder="Enter Attended Date">
                    </div>
                    <div class="form-group col-md-4">
                            <label>Attended To</label>
                            <input type="text" value="<?php echo $student['college_attended_to']; ?>" name="college_attended_to" class="form-control" id="college_attended_to" placeholder="Enter Attended Date">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-xs-12">
                        <input type="submit" value="update" class="btn btn-default  btn-flat">
                    </div>
                </div>                
            </form>
        </div>
</aside>