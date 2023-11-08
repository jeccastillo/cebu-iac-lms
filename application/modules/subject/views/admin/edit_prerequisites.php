<aside class="right-side">
<section class="content-header">
                    <h1>
                        Subject
                        <small></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Subject</a></li>
                        <li class="active">Edit Pre-requisites <?php echo $subject['strCode']; ?></li>
                    </ol>
                </section>
<div class="container">
    <div class="span10 box box-primary">
        <div class="box-header">
                <h3 class="box-title">Edit Pre-requisites <?php echo $subject['strCode']; ?></h3>
        </div>
        <div class="box-body">
            <?php if($userlevel != 6): ?>

                <h4>Select Prerequisites and Program</h4>
                <div class="row">
                    <div class="col-sm-6">
                        <label>Pre-requisite</label>
                        <select class="form-control select2" id="prereq-selector">
                            <?php foreach($prereq as $pre): ?>
                                <option value="<?php echo $pre['intID']; ?>"><?php echo $pre['strCode'].' '.$pre['strDescription']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-sm-6">
                        <label>Curriculum</label>
                        <select class="form-control select2" id="program-selector">
                            <option value="">None</option>
                            <?php foreach($programs as $prog): ?>                                
                                <option value="<?php echo $prog['intID']; ?>"><?php echo $prog['strName']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <hr />
                <div class="text-center">
                    <a href="#" id="save-prereq" class="btn btn-default  btn-flat">Save</a>
                </div>
                <hr />
                <table class="table table-striped table-bordered">
                    <tr>
                       <th>Subject</th>
                       <th>Curriculum</th>
                       <th>Actions</th>
                    </tr>
                    <?php foreach($selected_prereq as $pre): ?>
                        <tr>
                            <td><?php echo $pre['strCode']." ".$pre['strDescription']; ?></td>
                            <td><?php echo $pre['program']?$pre['program']['strName']:"Not Specified"; ?></td>
                            <td><a href="#" class="btn btn-danger remove-prereq" rel="<?php echo $pre['prereq_subject_id']; ?>">Remove</a></td>
                        </tr>                        
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
       
        </div>
    </div>
</div>
</aside>