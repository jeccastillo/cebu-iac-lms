<aside class="right-side">
<section class="content-header">
                    <h1>
                        School Term
                        <small></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Admin</a></li>
                        <li class="active">Add New Term</li>
                    </ol>
                </section>
<div class="content">
    <div class="span10 box box-primary">
        <div class="box-header">
                <h3 class="box-title">New Term</h3>
        </div>
       
            
            <form id="validate-subject" action="<?php echo base_url(); ?>registrar/submit_new_ay" method="post" role="form">
                <input type="hidden" name="enumStatus" value="inactive" />
                <input type="hidden" name="enumFinalized" value="no" />
                 <div class="box-body">
                    <div class="row">
                        <div class="form-group col-xs-12 col-lg-4">
                            <label for="enumSem"><?php echo $term_type; ?></label>
                            <select name="enumSem" class="form-control">
                                <?php foreach($terms as $term): ?>
                                    <option value="<?php echo $term ?>"><?php echo $term ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group col-xs-12 col-lg-4">
                        <label for="term_student_type">Student Type for Term</label>
                            <select name="term_student_type" class="form-control">
                                <option value="college">College</option>
                                <option value="shs">SHS</option>
                                <option value="next">Next School</option>
                            </select>
                        </div>
                        <div class="form-group col-xs-12 col-lg-4">
                            <label for="classType">Classes Type</label>
                            <select name="classType" class="form-control">
                                    <option value="regular">Regular</option>
                                    <option value="online">Online</option>
                                    <option value="hyflex">Hyflex</option>
                                    <option value="hybrid">Hybrid</option>
                            </select>
                        </div>
                        <div class="form-group col-xs-12 col-lg-4">
                            <label for="pay_student_visa">Pay Student Visa this term?</label>
                            <select name="pay_student_visa" class="form-control">
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </select>
                        </div>
                        <div class="form-group col-xs-12 col-lg-4">
                            <label for="strYearStart">Year</label>
                            <select id="year-start" name="strYearStart" class="form-control">
                                <?php for($y=2001;$y<2060;$y++): ?>
                                    <option value="<?php echo $y; ?>"><?php echo $y."-".($y+1); ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <hr />
                <h3>Dates Setup</h3>
                <hr />
                <div class="row">
                    <div class="form-group col-xs-12 col-lg-4">
                        <label>Start of Classes</label>
                        <input type="date" name="start_of_classes" class="form-control" />                         
                    </div>
                    <div class="form-group col-xs-12 col-lg-4">
                        <label>Start of Final Exams</label>
                        <input type="date" name="final_exam_start" class="form-control" />                         
                    </div>
                    <div class="form-group col-xs-12 col-lg-4">
                        <label>End of Final Exams</label>
                        <input type="date" name="final_exam_end" class="form-control" />                         
                    </div>           
                    <div class="form-group col-xs-12 col-lg-4">
                        <label>Deadline for Submission of Requirements</label>
                        <input type="date" name="end_of_submission" class="form-control" />                         
                    </div>                                       
                </div> 
                <div class="row">
                     <div class="form-group col-xs-12">
                         <input type="submit" value="add" class="btn btn-default  btn-flat">
                     </div>
                </div>                
            </form>
        </div>
</aside>