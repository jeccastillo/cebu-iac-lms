<aside class="right-side">
<section class="content-header">
                    <h1>
                        Grading System
                        <small></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Grading System</a></li>
                        <li class="active">Edit Grading System <?php echo $grading['name']; ?></li>
                    </ol>
                </section>
<div class="content">
    <div class="span10 box box-primary">
        <div class="box-header">
                <h3 class="box-title">Edit Grading System <?php echo $grading['name']; ?></h3>
        </div>
       
            
            <form id="validate-subject" action="<?php echo base_url(); ?>grading/submit_edit_grading" method="post" role="form">
                <input type="hidden" name="intID"  id="intID" value="<?php echo $grading['id']; ?>">
                <div class="box-body">
                </div>
            </form>
       
        </div>
</aside>