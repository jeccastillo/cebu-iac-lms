<aside class="right-side">
<section class="content-header">
                    <h1>
                    Grading System
                        <small></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Grading System</a></li>
                        <li class="active">New Grading System</li>
                    </ol>
                </section>
<div class="content">
    <div class="span10 box box-primary">
        <div class="box-header">
                <h3 class="box-title">New Grading System</h3>
        </div>
       
            
            <form id="validate-subject" action="<?php echo base_url(); ?>subject/submit_grading" method="post" role="form">
                <div class="box-body">
                         <div class="form-group col-xs-6">
                            <label for="name">Name</label>
                            <input type="text" name="name" class="form-control" id="name" placeholder="Enter Name">
                        </div>                        
                        <div class="form-group col-xs-12">
                            <input type="submit" value="add" class="btn btn-default  btn-flat">
                        </div>
                    <div style="clear:both"></div>
                </div>
            </form>
    </div>
    </div>
</aside>