<aside class="right-side">
<section class="content-header">
                    <h1>
                        <small>
                    
                            <a class="btn btn-app" href="<?php echo base_url()."faculty/my_profile"; ?>"><i class="ion ion-eye"></i> View Profile</a> 
                        </small>
                        <div class="pull-right">
                    </div>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Faculty</a></li>
                        <li class="active">Profile</li>
                    </ol>
                </section>
<div class="content">
    <div class="span10 box box-primary">
        <div class="box-header">
                <h3 class="box-title">Edit My Profile</h3>
        </div>
            
            
            <form id="validate-faculty" action="<?php echo base_url(); ?>faculty/edit_submit_profile" method="post" role="form">
                <input value="<?php echo $faculty['intID'] ?>" type="hidden" name="intID" class="form-control" id="intID" >
                 <div class="box-body">
                     <div class="form-group col-xs-4">
                        <label for="strFirstname">First Name*</label>
                        <input value="<?php echo $faculty['strFirstname'] ?>" type="text" name="strFirstname" class="form-control" id="strFirstname" placeholder="Enter First Name">
                    </div>
                    <div class="form-group col-xs-4">
                        <label for="strLastname">Last Name*</label>
                        <input value="<?php echo $faculty['strLastname'] ?>" type="text" name="strLastname" class="form-control" id="strLastname" placeholder="Enter Last Name">
                    </div>
                    <div class="form-group col-xs-4">
                        <label for="strMiddlename">Middle Name</label>
                        <input value="<?php echo $faculty['strMiddlename'] ?>" type="text" name="strMiddlename" class="form-control" id="strMiddlename" placeholder="Enter Middle Name">
                    </div>
                 
                
                <div class="form-group col-xs-6">
                        <label for="strUsername">Username</label>
                        <p><?php echo $faculty['strUsername'] ?></p>
                    </div>
                
                <div class="form-group col-xs-6">
                <label for="strPass">Password (<?php echo ($faculty['strPass']!="")?'has password':'no password'; ?>)</label>
                        <input value="" type="password" name="strPass" class="form-control" id="strPass" placeholder="Enter Password">
                    </div>
                     
                    <hr style="clear:both" />
                  
                <div class="form-group col-xs-12">
                    <input type="submit" value="update" class="btn btn-default  btn-flat">
                </div>
                <div style="clear:both"></div>
            </form>
       
        </div>
</aside>