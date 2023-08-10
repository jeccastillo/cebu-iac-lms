<aside class="right-side">
<section class="content-header">
                    <h1>
                    OSAS
                        <small></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i>OSAS</a></li>
                        <li class="active">Assign Scholarship</li>
                    </ol>
                </section>
<div class="content">
    <div class="span10 box box-primary">
        <div class="box-header">
                <h3 class="box-title">Assign Scholarship</h3>
                
        </div>
       
        <div class="box box-solid">
            <div class="box-body">
                <?php if($error_message!=""): ?>
                    <div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <strong>Error</strong> <?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <hr />
            
             <form id="advise-student" action="<?php echo base_url(); ?>student/edit_student_scholarship" method="post" role="form">
                 <p>Search Student</p>
                 <div class="row">
                     <div class="col-sm-6">
                        <input type="text" id="select-student-id" name="studentID" placeholder="Enter Student Name/Number" class="form-control" />
                     </div>
                 </div>
                 <br />
                 <input class="btn btn-info btn-flat" type="submit" />
                 
            </form>
                <hr/>
                <h3>Student Scholars</h3>
                <table class="table table-bordered table-striped">
                    <tr>
                        <th>Name</th>
                        <th>Scholarship</th>
                        <th>Actions</th>
                    </tr>
                    <?php foreach($student_scholars as $scholar): ?>
                    <tr>
                        <th><?php echo $scholar['strLastname'].", ".$scholar['strFirstname']." ".$scholar['strMiddlename']; ?></th>
                        <th><a href="<?php echo base_url().'scholarship/view/'.$scholar['scholarship_id']; ?>" target="_blank"><?php echo $scholar['name']; ?></a></th>
                        <th><a href="<?php echo base_url().'student/edit_student_scholarship/'.$scholar['intID']; ?>" target="_blank">Update</a></th>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <hr/>
                <h3>Discounted</h3>
                <table class="table table-bordered table-striped">
                    <tr>
                        <th>Name</th>
                        <th>Discount Type</th>
                        <th>Actions</th>
                    </tr>
                    <?php foreach($discounted as $disc): ?>
                    <tr>
                        <th><?php echo $disc['strLastname'].", ".$disc['strFirstname']." ".$disc['strMiddlename']; ?></th>
                        <th><a href="<?php echo base_url().'scholarship/view/'.$scholar['scholarship_id']; ?>" target="_blank"><?php echo $disc['name']; ?></a></th>
                        <th><a href="<?php echo base_url().'student/edit_student_scholarship/'.$disc['intID']; ?>" target="_blank">Update</a></th>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
       
        </div>
</aside>