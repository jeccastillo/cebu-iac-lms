<aside class="right-side">
<section class="content-header">
                    <h1>
                        Reset Student Status
                        <small></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i>Registration</a></li>
                        <li class="active">Delete Registration</li>
                    </ol>
                </section>
<div class="content">
    <div class="span10 box box-primary">
        <div class="box-header">
            <h3 class="box-title">Reset Status</h3>
            <hr />
            <p>Name: <?php echo $student['strLastname'].", ".$student['strFirstname'].", ".$student['strMiddlename']; ?></p>
            <p>Student Number: <?php echo $student['strStudentNumber']; ?></p>
        </div>
       
            
            <form action="<?php echo base_url(); ?>unity/delete_registration_confirm" method="post" role="form">
                <div class="box-body">
                         
                            <input type="hidden" name="studentid" class="form-control" value="<?php echo $student['intID']; ?>">
                    <input type="hidden" name="sem" class="form-control" value="<?php echo $sem; ?>">
                            <div class="alert alert-warning alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <h4><i class="icon fa fa-warning"></i> Alert!</h4>
                                Warning This will delete registration data and all records from advising and classlist.
                              </div>
                            <div class="form-group col-xs-12">
                                <input type="submit" value="Execute" class="btn btn-default  btn-flat">
                            </div>
                    <div style="clear:both"></div>
                </div>
            </form>
    </div>
    </div>
</aside>