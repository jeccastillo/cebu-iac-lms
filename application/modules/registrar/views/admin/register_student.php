<aside class="right-side" id="registration-container">
<section class="content-header">
                    <h1>
                        Registrar
                        <small></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i>Registrar</a></li>
                        <li class="active">Fee Assessment</li>
                    </ol>
                </section>
<div class="content">
    <div class="span10 box box-primary">
        <div class="box-header">
                <h3 class="box-title">Fee Assessment</h3>
                
        </div>
       
        <div class="box box-solid">
        
            <div class="box-body">
                <?php if($error_message!=""): ?>
                    <div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <strong>Error</strong> <?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <a class="" href="<?php echo base_url(); ?>student/add_student">New Student</a>
                <hr />
            
             <form id="reg-old-student" action="<?php echo base_url(); ?>registrar/register_old_student2" method="post" role="form">
                 <p>Search Student</p>
                 <div class="row">
                     <div class="col-sm-6">
                        <input type="text" id="select-student-number" name="studentNumber" placeholder="Enter Student Number" class="form-control" />
                     </div>
                 </div>
                 <br />
                 <input class="btn btn-default  btn-flat" type="submit" />
                 
            </form>
            </div>
        </div>
       
        </div>
</aside>


