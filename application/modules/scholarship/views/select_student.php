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
            </div>
        </div>
       
        </div>
</aside>