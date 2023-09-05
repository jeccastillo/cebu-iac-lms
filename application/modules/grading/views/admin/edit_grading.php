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
        <form id="validate-subject" action="<?php echo base_url(); ?>grading/submit_grading" method="post" role="form">
            <input type="hidden" name="id"  id="id" value="<?php echo $grading['id']; ?>">
            <div class="box-body">
                    <div class="row mt-5">
                        <div class="col-sm-4">
                            VALUE
                        </div>
                        <div class="col-sm-4">
                            REMARKS
                        </div>
                        <div class="col-sm-4">
                            ACTIONS
                        </div>
                    </div>
                <?php foreach($grading_items as $item): ?>
                    <div class="row mt-5">
                        <div class="col-sm-4">
                            <input type="text" class="form-control" disabled value="<?php echo $item['value']; ?>" />
                        </div>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" disabled value="<?php echo $item['remarks']; ?>" />
                        </div>
                        <div class="col-sm-4">
                            <button class="btn btn-danger delete-grade-item"  data-val="<?php echo $item['id'] ?>">Remove</button>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div id="item-container">
                    <div class="row mt-5">
                        <div class="col-sm-4">
                            <input type="text" required name="item[]" class="form-control" placeholder="Enter Value" />
                        </div>
                        <div class="col-sm-4">
                            <input type="text" required name="remarks[]" class="form-control" placeholder="Enter Remarks" />
                        </div>
                    </div>
                </div>
                <hr />
                <button class="btn btn-default" id="remove-grade-line">-</button>
                <button class="btn btn-default" id="add-grade-line">+</button>
                <hr />
                <input type="submit" value="update" class="btn btn-default btn-flat">
            </div>
        </form>                       
        <div class="box-header">
            <h3 class="box-title">Final Grading</h3>
        </div>               
        <form  action="<?php echo base_url(); ?>grading/add_selected" method="post" role="form">
            <input type="hidden" name="id"  id="id" value="<?php echo $grading['id']; ?>">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>CODE</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach($subjects_selected as $item): ?>
                                <tr>
                                    <td><?php echo $item['strCode']; ?></td>
                                    <td><?php echo $item['strDescription']; ?></td>
                                </tr>        
                            <?php endforeach; ?>
                            </tbody>
                        </table>                                
                    </div>
                    <div class="col-md-6">                        
                        <label for="subjects">Select Subjects to Add</label>                                           
                        <select required name="subjects[]" multiple class="form-control" style="height: 300px;">                    
                        <?php foreach($subjects_not_selected as $item): ?>
                            <option value="<?php echo $item['intID']; ?>"><?php echo $item['strCode']; ?></option>
                        <?php endforeach; ?>
                        </select>       
                        <hr />             
                        <input type="submit" value="add subjects >>" class="btn btn-default btn-flat btn-lg">
                    </div>
                </div>                
                
            </div>
        </form>  
        <div class="box-header">
            <h3 class="box-title">Midterm grading</h3>
        </div>               
        <form  action="<?php echo base_url(); ?>grading/add_selected/midterm" method="post" role="form">
            <input type="hidden" name="id"  id="id" value="<?php echo $grading['id']; ?>">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>CODE</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach($subjects_selected_midterm as $item): ?>
                                <tr>
                                    <td><?php echo $item['strCode']; ?></td>
                                    <td><?php echo $item['strDescription']; ?></td>
                                </tr>        
                            <?php endforeach; ?>
                            </tbody>
                        </table>                                
                    </div>
                    <div class="col-md-6">                        
                        <label for="subjects">Select Subjects to Add</label>                                           
                        <select required name="subjects[]" multiple class="form-control" style="height: 300px;">                    
                        <?php foreach($subjects_not_selected_midterm as $item): ?>
                            <option value="<?php echo $item['intID']; ?>"><?php echo $item['strCode']; ?></option>
                        <?php endforeach; ?>
                        </select>       
                        <hr />             
                        <input type="submit" value="add subjects >>" class="btn btn-default btn-flat btn-lg">
                    </div>
                </div>                
                
            </div>
        </form>                      
    </div>

</aside>