<aside class="right-side">
<section class="content-header">
                    <h1>
                        View Grading Extension
                        <small></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="<?php echo base_url().'registrar/edit_ay/'.$item['syid']; ?>"><i class="fa fa-dashboard"></i> Academic Year</a></li>
                        <li class="active">View Grading Extension</li>
                    </ol>
                </section>
<div class="content">
    <div class="span10 box box-primary">
        <div class="box-header">
            <h3 class="box-title">View Grading Extension</h3>
        </div>
        <div class="box-body">
            <div class="row">   
                <div class="col-md-6">
                    Period: <?php echo $item['type']; ?>
                </div>
                <div class="col-md-6">
                    End of Extension: <?php echo date("M j,Y", strtotime($item['date'])); ?> ?>
                </div>
            </div>            
       
        </div>
</aside>