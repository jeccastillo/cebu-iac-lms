<aside class="right-side">
<section class="content-header">
                    <h1>
                        User Accounts
                        <small></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> User Accounts</a></li>
                        <li class="active">Edit User Account</li>
                    </ol>
                </section>
<div class="content">
    <div class="span10 box box-primary">
        <div class="box-header">
                <h3 class="box-title">Edit User Account</h3>
        </div>
            
            
            <form id="validate-faculty" action="<?php echo base_url(); ?>faculty/edit_submit_faculty" method="post" role="form">
                <input value="<?php echo $faculty['intID'] ?>" type="hidden" name="intID" class="form-control" id="intID" >
                 <div class="box-body">
                     <div class="form-group col-xs-4">
                        <label for="strFirstname">First Name*</label>
                        <input value="<?php echo $faculty['strFirstname'] ?>" type="text" name="strFirstname" class="form-control" id="strFirstname" placeholder="Enter First Name">
                    </div>
                    <div class="form-group col-xs-4">
                        <label for="strLastname">Last Name*</label>
                        <input value="<?php echo $faculty['strLastname']; ?>" type="text" name="strLastname" class="form-control" id="strLastname" placeholder="Enter Last Name">
                    </div>
                    <div class="form-group col-xs-4">
                        <label for="strMiddlename">Middle Name</label>
                        <input value="<?php echo $faculty['strMiddlename']; ?>" type="text" name="strMiddlename" class="form-control" id="strMiddlename" placeholder="Enter Middle Name">
                    </div>

                    <div class="form-group col-xs-6">
                        <label for="strMiddlename">Employee Number</label>
                        <input value="<?php echo $faculty['strFacultyNumber']; ?>" type="text" name="strFacultyNumber" class="form-control" id="strFacultyNumber" placeholder="Enter Employee Number">
                    </div>
                 
                 <div class="form-group col-xs-6">
                        <label for="strEmail">Email</label>
                        <input value ="<?php echo $faculty['strEmail']; ?>" type="email" name="strEmail" class="form-control" id="strEmail" placeholder="Enter Email Address">
                    </div>
                
                <div class="form-group col-xs-6">
                        <label for="strMobileNumber">Contact Number</label>
                        <input value ="<?php echo $faculty['strMobileNumber']; ?>" type="number" name="strMobileNumber" class="form-control" id="strMobileNumber" placeholder="Enter Contact Number" >
                </div>
                <div class="form-group col-xs-6">
                        <label for="login_attempts">Failed Login Attempts</label>
                        <input value ="<?php echo $faculty['login_attempts']; ?>" type="number" name="login_attempts" class="form-control" id="login_attempts" />
                </div>
                <div class="form-group col-xs-6">
                        <label>Address</label>
                        <textarea  class="form-control" name="strAddress" rows="3" placeholder="Enter Address"><?php echo $faculty['strAddress']?></textarea>
                    </div>
                    <div class="form-group col-xs-6">
                        <label for="strSchool">School </label>
                        <select class="form-control" name="strSchool" >
                            <option <?php echo ($faculty['strSchool'] == '--Select--')?'selected':'' ?> value="Select">--Select--</option>                      
                            <option  <?php echo ($faculty['strSchool'] == 'SCS')?'selected':'' ?> value="SCS">School of Computer Studies</option>
                            <option  <?php echo ($faculty['strSchool'] == 'SBM')?'selected':'' ?> value="SBM">School of Business & Management</option>
                            <option  <?php echo ($faculty['strSchool'] == 'SED')?'selected':'' ?> value="SED">School of Education</option>
                            <option  <?php echo ($faculty['strSchool'] == 'SHTM')?'selected':'' ?> value="SHTM">School of Hospitality & Tourism Management</option>
                            <option  <?php echo ($faculty['strSchool'] == 'SAS')?'selected':'' ?> value="SAS">School of Arts & Sciences</option>
                            <option  <?php echo ($faculty['strSchool'] == 'OSAS')?'selected':'' ?> value="OSAS">Office of Student Affairs & Services</option>
                            <option  <?php echo ($faculty['strSchool'] == 'Non-teaching')?'selected':'' ?> value="Non-teaching">Non-teaching</option>
                        </select>
                    </div>
                        <div class="form-group col-xs-6">
                    <label for="strDepartment">Department</label>
                    <select class="form-control" name="strDepartment" >
                    <option value="Select">--Select--</option> 
                        <?php foreach ($department_config as $df): ?>
                            <option <?php echo ($faculty['strDepartment'] == $df)?'selected':'' ?> value="<?php echo $df; ?>"><?php echo $df; ?></option>
                        <?php endforeach; ?>      
                        
                        
                    </select>
                    
                </div>
                <div class="form-group col-xs-6">
                        <label for="strUsername">Username</label>
                        <input value="<?php echo $faculty['strUsername'] ?>" type="text" name="strUsername" class="form-control" id="strUsername" placeholder="Enter Username">
                    </div>
                
                <div class="form-group col-xs-6">
                    <label for="strPass">Password (<?php echo ($faculty['strPass']!="")?'has password':'no password'; ?>)</label>
                    <input value="" type="password" name="strPass" class="form-control" id="strPass" placeholder="Enter Password">

                </div>
              
                <div class="form-group col-xs-6">
                    <label for="intUserLevel">User Level</label>
                    <select class="form-control" name="intUserLevel" > 
                        <option <?php echo ($faculty['intUserLevel'] == 0)?'selected':'' ?> value="0">Faculty</option>
                        <option <?php echo ($faculty['intUserLevel'] == 1)?'selected':'' ?> value="1">Faculty Admin</option>
                        <?php if($this->session->userdata('intUserLevel') == 2): ?>
                        <option <?php echo ($faculty['intUserLevel'] == 2)?'selected':'' ?> value="2">Super Admin</option>
                        <?php endif; ?>
                        <option <?php echo ($faculty['intUserLevel'] == 3)?'selected':'' ?> value="3">Registrar</option>
                        <option <?php echo ($faculty['intUserLevel'] == 4)?'selected':'' ?> value="4">Dean</option>
                        <option <?php echo ($faculty['intUserLevel'] == 5)?'selected':'' ?> value="5">Admissions Officer</option>
                        <option <?php echo ($faculty['intUserLevel'] == 6)?'selected':'' ?> value="6">Finance</option>
                        <option <?php echo ($faculty['intUserLevel'] == 7)?'selected':'' ?> value="7">OSAS</option>
                        <option <?php echo ($faculty['intUserLevel'] == 8)?'selected':'' ?> value="8">Library</option>
                        <option <?php echo ($faculty['intUserLevel'] == 9)?'selected':'' ?> value="9">Discipline</option>
                        <option <?php echo ($faculty['intUserLevel'] == 10)?'selected':'' ?> value="10">Clinic</option>
                        <option <?php echo ($faculty['intUserLevel'] == 11)?'selected':'' ?> value="11">IT</option>                        
                    </select>
                </div>   
                <div class="form-group col-xs-6">
                    <label for="isActive">Status</label>
                    <select class="form-control" name="isActive" > 
                        <option <?php echo ($faculty['isActive'] == 0)?'selected':'' ?> value="0">Inactive</option>
                        <option <?php echo ($faculty['isActive'] == 1)?'selected':'' ?> value="1">Active</option>
                    </select>
                </div>
                <div class="form-group col-xs-6">
                    <label for="teaching">Teaching</label>
                    <select class="form-control" name="teaching" > 
                        <option <?php echo ($faculty['teaching'] == 0)?'selected':'' ?> value="0">No</option>
                        <option <?php echo ($faculty['teaching'] == 1)?'selected':'' ?> value="1">Yes</option>                        
                    </select>
                </div>
                <div class="form-group col-xs-6">
                    <label for="special_role">Special Role</label>
                    <select class="form-control" name="special_role" > 
                        <option <?php echo ($faculty['special_role'] == 0)?'selected':'' ?> value="0">None</option>
                        <option <?php echo ($faculty['special_role'] == 1)?'selected':'' ?> value="1">Asst. Manager</option>
                        <option <?php echo ($faculty['special_role'] == 2)?'selected':'' ?> value="2">Manager</option>
                    </select>
                </div>
                                
                <div class="form-group col-xs-6">
                    <label for="intUserLevel">Subjects</label>
                <select name="subject" class="form-control select2" multiple>
                    <?php foreach($subjects as $subject): ?>
                        <option <?php echo in_array($subject['intID'],$selectedSubjects)?'selected':''; ?> value="<?php echo $subject['intID']; ?>"><?php echo $subject['strCode']; ?></option>
                    <?php endforeach; ?>
                 </select>
                </div>
                <div class="form-group col-xs-12">
                    <input type="submit" value="update" class="btn btn-default  btn-flat">
                </div>
                <div style="clear:both"></div>
            </form>
       
        </div>
</aside>