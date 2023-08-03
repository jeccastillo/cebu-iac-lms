<aside class="right-side">
<section class="content-header">
                    <h1>
                        Student
                        <small></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Student</a></li>
                        <li class="active">Edit Student</li>
                    </ol>
                </section>
<div class="content">
    <div class="span10 box box-primary">
        <div class="box-header">
                <h3 class="box-title">Edit Student Scholarship</h3>
            <?php //echo print_r($student); ?>
        </div>
       
            
            <form id="validate-student" action="<?php echo base_url(); ?>student/edit_submit_student" method="post" role="form" enctype="multipart/form-data">
                <input type="hidden" name="intID" class="form-control" id="intID" value="<?php echo $student['intID']; ?>">
                <div class="box-body">
                    <div class="row">
                    <div class="form-group col-xs-4">
                            <label for="strLastname">Last Name*</label>
                            <input type="text" disabled value="<?php echo $student['strLastname']; ?>" class="form-control" id="strLastname" placeholder="Enter Last Name">
                        </div>
                        <div class="form-group col-xs-4">
                            <label for="strFirstname">First Name*</label>
                            <input type="text" disabled value="<?php echo $student['strFirstname']; ?>" class="form-control" id="strFirstname" placeholder="Enter First Name">
                        </div>
                        <div class="form-group col-xs-4">
                            <label for="strMiddlename">Middle Name</label>
                            <input type="text" disabled value="<?php echo $student['strMiddlename']; ?>" class="form-control" id="strMiddlename" placeholder="Enter Middle Name">
                        </div>
                        <div class="form-group col-xs-6">
                            <label for="enumScholarship">Scholarship: </label>
                            <select class="form-control" name="enumScholarship">
                                <option value="0">None</option>
                                <?php foreach($scholarships as $scholarship): ?>                                
                                <option <?php echo ($student['enumScholarship'] == $scholarship['intID'])?'selected':''; ?> value="<?php echo $scholarship['intID']; ?>"><?php echo $scholarship['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group col-xs-6">
                            <label for="enumScholarship">Discount: </label>
                            <select class="form-control" name="enumDiscount">
                                <option value="0">None</option>
                                <?php foreach($discounts as $discount): ?>                                
                                <option <?php echo ($student['enumDiscount'] == $discount['intID'])?'selected':''; ?> value="<?php echo $discount['intID']; ?>"><?php echo $discount['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-xs-12">
                            <input type="submit" value="update" class="btn btn-default  btn-flat">
                        </div>
                    </div>                
                </div>
            </form>
        </div>
</aside>