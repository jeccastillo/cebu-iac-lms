<aside class="right-side">
<section class="content-header">
                    <h1>
                        Grading System
                        <small></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Grading System</a></li>
                        <li class="active"><?php echo $grading['name']; ?></li>
                    </ol>
                </section>
<div class="content">
    <div class="span10 box box-primary">
        <div class="box-header">
                <h3 class="box-title">Edit Grading System - <?php echo $grading['name']; ?></h3>
        </div>
       
            
            <form id="validate-subject" action="<?php echo base_url(); ?>grading/submit_edit_grading" method="post" role="form">
                <input type="hidden" name="id"  id="id" value="<?php echo $grading['id']; ?>">
                <div class="box-body">
                    <?php foreach($grading_items as $item): ?>
                        <div class="row">
                            <div class="col-sm-6">
                                <?php echo $item['value']; ?>
                            </div>
                            <div class="col-sm-6">
                                <button class="btn btn-danger delete-grade-item"  data-val="<?php echo $item['id'] ?>">Remove</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div id="item-container">
                        <div class="row mt-5"><div class="col-sm-4"><input type="text" required name="item[]" class="form-control" placeholder="Enter Value" /></div></div>
                    </div>
                    <hr />
                    <button class="btn btn-default" id="add-grade-line">+</button>
                    <hr />
                    <input type="submit" value="update" class="btn btn-default btn-flat">
                </div>
            </form>
       
        </div>
</aside>