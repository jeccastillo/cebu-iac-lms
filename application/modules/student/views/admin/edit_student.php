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


            <form id="validate-student" action="<?php echo base_url(); ?>student/edit_submit_student" method="post"
                role="form" enctype="multipart/form-data">
                <input type="hidden" name="intID" class="form-control" id="intID"
                    value="<?php echo $student['intID']; ?>">
                <div class="box-body">
                    <div class="row">
                        <div class="form-group col-xs-4">
                            <label for="strLastname">Last Name*</label>
                            <input type="text" value="<?php echo $student['strLastname']; ?>" name="strLastname"
                                class="form-control" id="strLastname" placeholder="Enter Last Name">
                        </div>
                        <div class="form-group col-xs-4">
                            <label for="strFirstname">First Name*</label>
                            <input type="text" value="<?php echo $student['strFirstname']; ?>" name="strFirstname"
                                class="form-control" id="strFirstname" placeholder="Enter First Name">
                        </div>
                        <div class="form-group col-xs-4">
                            <label for="strMiddlename">Middle Name</label>
                            <input type="text" value="<?php echo $student['strMiddlename']; ?>" name="strMiddlename"
                                class="form-control" id="strMiddlename" placeholder="Enter Middle Name">
                        </div>
                        <div class="form-group col-xs-4">
                            <label for="enumGender">Gender: </label>
                            <select class="form-control" name="enumGender">
                                <option <?php echo ($student['enumGender'] == "male")?'selected':''; ?> value="male">
                                    Male</option>
                                <option <?php echo ($student['enumGender'] == "female")?'selected':''; ?>
                                    value="female">Female</option>
                            </select>
                        </div>
                        <div class="form-group col-xs-4">
                            <label for="strStudentNumber">Student Number<a rel="locked" href="#"
                                    id="studentnumber-lock"><i class="ion ion-locked"></i></a></label>
                            <input type="text" disabled value="<?php echo $student['strStudentNumber']; ?>"
                                name="strStudentNumber" class="form-control" id="strStudentNumber"
                                placeholder="Enter Student Number">
                        </div>
                        <div class="form-group col-xs-4">
                            <label for="strLRN">Learner Reference Number (LRN)</label>
                            <input type="text" value="<?php echo $student['strLRN']; ?>" name="strLRN"
                                class="form-control" id="strLRN" placeholder="Enter Learner Reference Number">
                        </div>


                        <div class="form-group col-xs-6">
                            <label for="strStudentNumber">Portal Password
                                (<?php echo ($student['strPass']!="")?'has password':'no password'; ?>)</label>
                            <div class="input-group">
                                <input type="text" value="" name="strPass" class="form-control" id="strPass"
                                    placeholder="Password">
                                <span class="input-group-btn">
                                    <button type="button" id="generate-password"
                                        class="btn btn-danger btn-flat">Generate</button>
                                </span>
                            </div>
                        </div>
                        <div class="form-group col-xs-6">
                            <label for="strEmail">Email</label>
                            <input type="email" value="<?php echo $student['strEmail']; ?>" name="strEmail"
                                class="form-control" id="strEmail" placeholder="Enter Email Address">
                        </div>
                        <div class="form-group col-xs-6">
                            <label for="dteBirthDate">Birthdate</label>
                            <input type="date" name="dteBirthDate"
                                    value="<?php echo $student['dteBirthDate']; ?>"
                                    class="form-control validate" id="dteBirthDate" />                                                            
                        </div>
                        <div class="form-group col-xs-6">
                            <label for="nstp_serial">Place of Birth</label>
                            <input type="text" value="<?php echo $student['place_of_birth']; ?>" name="place_of_birth"
                                class="form-control" id="place_of_birth" placeholder="Enter Place of Birth">
                        </div>

                        <div class="form-group col-xs-6">
                            <label for="strMobileNumber">Contact Number</label>
                            <input type="text" value="<?php echo $student['strMobileNumber']; ?>" name="strMobileNumber"
                                class="form-control" id="strMobileNumber" placeholder="Enter Contact Number">
                        </div>
                        <div class="form-group col-xs-6">
                            <label>Address</label>
                            <textarea class="form-control" name="strAddress" rows="3"
                                placeholder="Enter Address"><?php echo $student['strAddress']; ?></textarea>
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
                            <label for="">Block Section</label>
                            <select class="form-control select2" name="preferedSection" id="preferedSection">
                                <?php foreach ($block_sections as $sect): ?>
                                <option <?php echo ($student['preferedSection'] == $sect['intID'])?'selected':''; ?>
                                    value="<?php echo $sect['intID']; ?>"><?php echo $sect['name']; ?></option>
                                <?php endforeach; ?>
                            </select>

                        </div>
                        <div class="form-group col-xs-6">
                            <label for="intProgramID">Course</label>
                            <select class="form-control" name="intProgramID">
                                <?php foreach ($programs as $prog): ?>
                                <option <?php echo ($student['intProgramID'] == $prog['intProgramID'])?'selected':''; ?>
                                    value="<?php echo $prog['intProgramID']; ?>"><?php echo $prog['strProgramCode']; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>

                        </div>
                        <div class="form-group col-xs-6">
                            <label for="">Curriculum</label>
                            <select class="form-control select2" name="intCurriculumID" id="intCurriculumID">
                                <?php foreach ($curriculum as $curr): ?>
                                <option <?php echo ($student['intCurriculumID'] == $curr['intID'])?'selected':''; ?>
                                    value="<?php echo $curr['intID']; ?>"><?php echo $curr['strName']; ?></option>
                                <?php endforeach; ?>
                            </select>

                        </div>
                        <div class="form-group col-xs-6">
                            <label for="nstp_serial">NSTP Serial Number</label>
                            <input type="text" value="<?php echo $student['nstp_serial']; ?>" name="nstp_serial"
                                class="form-control" id="nstp_serial" placeholder="Enter NSTP Number">
                        </div>
                        <div class="form-group col-xs-6">
                            <label for="dteBirthDate">Graduation Date</label>
                            <input type="date" name="date_of_graduation"
                                    value="<?php echo $student['date_of_graduation']; ?>"
                                    class="form-control validate" id="date_of_graduation" />  
                        </div>
                        
                        
                        <div class="form-group col-xs-6">
                            <label for="enumScholarship">Tuition Year Selected: </label>
                            <select class="form-control" name="intTuitionYear">
                                <option value="0">None</option>
                                <?php foreach($tuition_years as $ty): ?>                                
                                <option <?php echo ($student['intTuitionYear'] == $ty['intID'])?'selected':''; ?> value="<?php echo $ty['intID']; ?>"><?php echo $ty['year']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group col-xs-6">
                            <label for="student_status">Status</label>
                            <select class="form-control" name="student_status" >
                                <option <?php echo ($student['student_status'] == "active")?'selected':''; ?> value="active">Active</option>
                                <option <?php echo ($student['student_status'] == "loa")?'selected':''; ?> value="loa">LOA</option>
                                <option <?php echo ($student['student_status'] == "awol")?'selected':''; ?> value="awol">AWOL</option>
                            </select>                        
                        </div>
                        
                    </div>
                    <hr />
                    <h4>Other Details</h4>
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label>Mother's Maiden Name</label>
                            <input type="text" value="<?php echo $student['mother']; ?>" name="mother"
                                class="form-control" id="mother" placeholder="Enter Name">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Mother's Contact Number</label>
                            <input type="text" value="<?php echo $student['mother_contact']; ?>" name="mother_contact"
                                class="form-control" id="mother_contact" placeholder="Enter Contact">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Mother's Email Address</label>
                            <input type="text" value="<?php echo $student['mother_email']; ?>" name="mother_email"
                                class="form-control" id="mother_email" placeholder="Enter Email">
                        </div>

                        <div class="form-group col-md-4">
                            <label>Father's Name</label>
                            <input type="text" value="<?php echo $student['father']; ?>" name="father"
                                class="form-control" id="father" placeholder="Enter Name">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Father's Contact Number</label>
                            <input type="text" value="<?php echo $student['father_contact']; ?>" name="father_contact"
                                class="form-control" id="father_contact" placeholder="Enter Contact">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Father's Email Address</label>
                            <input type="text" value="<?php echo $student['father_email']; ?>" name="father_email"
                                class="form-control" id="father_email" placeholder="Enter Email">
                        </div>


                        <div class="form-group col-md-4">
                            <label>Guardian's Name</label>
                            <input type="text" value="<?php echo $student['guardian']; ?>" name="guardian"
                                class="form-control" id="guardian" placeholder="Enter Name">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Guardian's Contact Number</label>
                            <input type="text" value="<?php echo $student['guardian_contact']; ?>"
                                name="guardian_contact" class="form-control" id="guardian_contact"
                                placeholder="Enter Contact">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Guardian's Email Address</label>
                            <input type="text" value="<?php echo $student['guardian_email']; ?>" name="guardian_email"
                                class="form-control" id="guardian_email" placeholder="Enter Email">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label>High School</label>
                            <input type="text" value="<?php echo $student['high_school']; ?>" name="high_school"
                                class="form-control" id="high_school" placeholder="Enter Name">
                        </div>
                        <div class="form-group col-md-4">
                            <label>High School Address</label>
                            <textarea name="high_school_address" class="form-control" id="high_school_address"
                                placeholder="Enter Address"><?php echo $student['high_school_address'] ?></textarea>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Attended</label>
                            <input type="text" value="<?php echo $student['high_school_attended']; ?>"
                                name="high_school_attended" class="form-control" id="high_school_attended"
                                placeholder="Enter Attended Date">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label>Senior High School</label>
                            <input type="text" value="<?php echo $student['senior_high']; ?>" name="senior_high"
                                class="form-control" id="senior_high" placeholder="Enter Name">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Senior High School Address</label>
                            <textarea name="senior_high_address" class="form-control" id="senior_high_address"
                                placeholder="Enter Address"><?php echo $student['senior_high_address'] ?></textarea>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Attended</label>
                            <input type="text" value="<?php echo $student['senior_high_attended']; ?>"
                                name="senior_high_attended" class="form-control" id="senior_high_attended"
                                placeholder="Enter Attended Date">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label>College</label>
                            <input type="text" value="<?php echo $student['college']; ?>" name="college"
                                class="form-control" id="college" placeholder="Enter Name">
                        </div>
                        <div class="form-group col-md-4">
                            <label>College Address</label>
                            <textarea name="college_address" class="form-control" id="college_address"
                                placeholder="Enter Address"><?php echo $student['college_address'] ?></textarea>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Attended From</label>
                            <input type="text" value="<?php echo $student['college_attended_from']; ?>"
                                name="college_attended_from" class="form-control" id="college_attended_from"
                                placeholder="Enter Attended Date">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Attended To</label>
                            <input type="text" value="<?php echo $student['college_attended_to']; ?>"
                                name="college_attended_to" class="form-control" id="college_attended_to"
                                placeholder="Enter Attended Date">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>Strand</label>
                            <input type="text" value="<?php echo $student['strand']; ?>" name="strand"
                                class="form-control" id="strand" placeholder="Enter Strand">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Student Type</label>
                            <select class="form-control" name="student_type" id="student_type">
                                <option <?php echo ($student['student_type'] == "freshman")?'selected':''; ?>
                                    value="freshman">Freshman</option>
                                <option <?php echo ($student['student_type'] == "transferee")?'selected':''; ?>
                                    value="transferee">Transferee</option>
                                <option <?php echo ($student['student_type'] == "foreign")?'selected':''; ?>
                                    value="foreign">Foreign</option>
                                <option <?php echo ($student['student_type'] == "second degree")?'selected':''; ?>
                                    value="second degree">Second Degree</option>
                            </select>
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