<aside class="right-side">
<section class="content-header">
                    <h1>
                        Faculty
                        <small></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Faculty</a></li>
                        <li class="active">Add Faculty</li>
                    </ol>
                </section>
<div class="content">
    <div class="span10 box box-primary">
        <div class="box-header">
                <h3 class="box-title">New Faculty</h3>
        </div>
       
            
            <form id="validate-faculty" action="<?php echo base_url(); ?>faculty/submit_faculty" method="post" role="form">
                 <div class="box-body">
                     <div class="form-group col-xs-4">
                        <label for="strFirstname">First Name*</label>
                        <input type="text" name="strFirstname" class="form-control" id="strFirstname" placeholder="Enter First Name">
                    </div>
                    <div class="form-group col-xs-4">
                        <label for="strLastname">Last Name*</label>
                        <input type="text" name="strLastname" class="form-control" id="strLastname" placeholder="Enter Last Name">
                    </div>
                    <div class="form-group col-xs-4">
                        <label for="strMiddlename">Middle Name</label>
                        <input type="text" name="strMiddlename" class="form-control" id="strMiddlename" placeholder="Enter Middle Name">
                    </div>
                    <div class="form-group col-xs-6">
                        <label for="strMiddlename">Employee Number</label>
                        <input required type="text" name="strFacultyNumber" class="form-control" id="strFacultyNumber" placeholder="Enter Employee Number">
                    </div>
                    <div class="form-group col-xs-6">
                        <label for="strEmail">Email</label>
                        <input type="email" name="strEmail" class="form-control" id="strEmail" placeholder="Enter Email Address">
                    </div>
                
                <div class="form-group col-xs-6">
                        <label for="strMobileNumber">Contact Number</label>
                        <input type="number" name="strMobileNumber" class="form-control" id="strMobileNumber" placeholder="Enter Contact Number">
                    </div>
                <div class="form-group col-xs-6">
                        <label>Address</label>
                        <textarea class="form-control" name="strAddress" rows="3" placeholder="Enter Address"></textarea>
                    </div>
                    <div class="form-group col-xs-6">
                        <label for="strSchool">School</label>
                        <select class="form-control" name="strSchool" >
                            <option value="Select">--Select--</option>                      
                            <option value="Computing">School of Computing</option>
                            <option value="Business">School of Business</option>
                            <option value="Design">School of Design</option>
                            <option value="Other">Other</option>                           
                        </select>
                    </div>
                    <div class="form-group col-xs-6">
                        <label for="strDepartment">Department</label>
                        <select class="form-control" name="strDepartment" >
                            <option value="Select">--Select--</option>                         
                            <?php foreach ($department_config as $df): ?>
                                <option value="<?php echo $df; ?>"><?php echo $df; ?></option>
                            <?php endforeach; ?>                       
                        </select>
                    </div>
                    
                </div>
                <div class="form-group col-xs-6">
                        <label for="strUsername">Username</label>
                        <input type="text" name="strUsername" class="form-control" id="strUsername" placeholder="Enter Username">
                    </div>
                
                <div class="form-group col-xs-6">
                        <label for="strPass">Password</label>
                        <input type="text" name="strPass" class="form-control" id="strPass" placeholder="Enter Password">
                    </div>
              
                <div class="form-group col-xs-6">
                    <label for="intUserLevel">User Level</label>
                    <select class="form-control" name="intUserLevel" > 
                        <option value="0">Academics</option>
                        <option value="1">Building Admin</option>
                        <?php if($this->session->userdata('intUserLevel') == 2): ?>
                        <option value="2">Super Admin</option>
                        <?php endif; ?>
                        <option value="3">Registrar</option>
                        <option value="4">Dean</option>
                        <option value="5">Admissions Officer</option>
                        <option value="6">Finance</option>
                        <option value="7">OSAS</option>
                        <option value="8">Library</option>
                        <option value="9">Discipline</option>
                        <option value="10">Clinic</option>
                        <option value="11">IT</option>
                    </select>
                </div>  
                <div class="form-group col-xs-6">
                    <label for="teaching">Teaching</label>
                    <select class="form-control" name="teaching" > 
                        <option value="0">No</option>
                        <option value="1">Yes</option>                        
                    </select>
                </div>   
                <div class="form-group col-xs-6">
                    <label for="special_role">Special Role</label>
                    <select class="form-control" name="special_role" > 
                        <option value="0">None</option>
                        <option value="1">Asst. Manager</option>
                        <option value="2">Manager</option>
                    </select>
                </div>     
                <div class="form-group col-xs-12">
                    <input type="submit" value="add" class="btn btn-default  btn-flat">
                </div>
                <div style="clear:both"></div>
            </form>
       
        </div>
</aside>