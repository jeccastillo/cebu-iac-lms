<aside class="right-side">
<section class="content-header">
                    <h1>
                        Department
                        <small></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i>Advising</a></li>
                        <li class="active">Add to Classlistt</li>
                    </ol>
                </section>
<div class="content">
    <div class="span10 box box-primary">
        <div class="box-header">
                <h3 class="box-title">Add To Classlist</h3>
                
        </div>
       
        <div class="box box-solid">
        
            <div class="box-body">
                <h4><?php echo $message; ?></h4>
                <hr />
                <?php echo $student_link; ?> | 
                <a target="_blank" class="btn btn-primary" href="<?php echo base_url()."pdf/student_viewer_registration_print/".$sid ."/". $ayid; ?>">
                                <i class="ion ion-printer"></i> Print Registration Form</a>
            </div>
        </div>
       
        </div>
</aside>