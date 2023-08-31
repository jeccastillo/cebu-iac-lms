<aside class="right-side">
<section class="content-header">
                    <h1>
                        Academic Year
                        <small></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Admin</a></li>
                        <li class="active">Add Academic Year</li>
                    </ol>
                </section>
<div class="content">
    <div class="span10 box box-primary">
        <div class="box-header">
                <h3 class="box-title">New Academic Year</h3>
        </div>
       
            
            <form id="validate-subject" action="<?php echo base_url(); ?>registrar/submit_new_ay" method="post" role="form">
                <input type="hidden" name="enumStatus" value="inactive" />
                <input type="hidden" name="enumFinalized" value="no" />
                 <div class="box-body">
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
                     
                    
                     
                
                     <div class="form-group col-xs-12">
                         <input type="submit" value="add" class="btn btn-default  btn-flat">
                     </div>
                <div style="clear:both"></div>
            </form>
       
        </div>
</aside>