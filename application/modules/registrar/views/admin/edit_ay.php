<aside class="right-side">
<section class="content-header">
                    <h1>
                        Academic Year
                        <small></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Admin</a></li>
                        <li class="active">Edit Academic Year</li>
                    </ol>
                </section>
<div class="content">
    <div class="box box-primary">
        <div class="box-header">
                <h3 class="box-title">Edit Academic Year</h3>
        </div>                   
        <form id="validate-subject" action="<?php echo base_url(); ?>registrar/edit_submit_ay" method="post" role="form">
            <input type="hidden" name="intID" value="<?php echo $item['intID'] ?>" />
                <div class="box-body">
                    <div class="form-group col-xs-12 col-lg-4">
                    <label for="enumSem"><?php echo $term_type; ?></label>
                    <select name="enumSem" class="form-control">
                        <?php foreach($terms as $term): ?>
                            <option <?php echo $item['enumSem']==$term?'selected':''; ?> value="<?php echo $term ?>"><?php echo $term ?></option>
                        <?php endforeach; ?>
                        </select>
                </div>
                <div class="form-group col-xs-12 col-lg-4">
                    <label for="classType">Classes Type</label>
                    <select name="classType" class="form-control">
                            <option <?php echo $item['classType']=='regular'?'selected':''; ?> value="regular">Regular</option>
                            <option <?php echo $item['classType']=='online'?'selected':''; ?> value="online">Online</option>
                            <option <?php echo $item['classType']=='hyflex'?'selected':''; ?> value="hyflex">Hyflex</option>
                            <option <?php echo $item['classType']=='hybrid'?'selected':''; ?> value="hybrid">Hybrid</option>
                        </select>
                </div>
                    <div class="form-group col-xs-12 col-lg-4">
                    <label for="strYearStart">Year</label>
                        <select id="year-start" name="strYearStart" class="form-control">
                        <?php for($y=2001;$y<2060;$y++): ?>
                            <option <?php echo $item['strYearStart']==$y?'selected':''; ?> value="<?php echo $y; ?>"><?php echo $y."-".($y+1); ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                    <div class="form-group col-xs-12 col-lg-4">
                    <label for="enumStatus">Status</label>
                    <select name="enumStatus" class="form-control">
                        <?php if($item['enumStatus'] != "active"): ?> 
                            <option <?php echo $item['enumStatus']=='inactive'?'selected':''; ?>  value="inactive">Inactive</option>
                        <?php endif; ?>
                        <option <?php echo $item['enumStatus']=='active'?'selected':''; ?> value="active">Active</option>
                        </select>
                </div>                                                  
                <input type="hidden" name="enumGradingPeriod" value="inactive" class="form-control">                                                                    
                <input type="hidden" name="enumMGradingPeriod" value="inactive" class="form-control">
                <input type="hidden" name="enumFGradingPeriod" value="inactive" class="form-control">
                <div class="form-group col-xs-12 col-lg-4">
                    <label for="pay_student_visa">Pay Student Visa this term?</label>
                    <select name="pay_student_visa" class="form-control">
                        <option <?php echo $item['pay_student_visa']=='0'?'selected':''; ?>  value="0">No</option>
                        <option <?php echo $item['pay_student_visa']=='1'?'selected':''; ?> value="1">Yes</option>
                        </select>
                </div>     
                <div class="form-group col-xs-12 col-lg-4">
                    <label for="midterm_start">Start of Midterm Grading</label>
                    <input type="date" name="midterm_start" value="<?php echo $item['midterm_start']; ?>" class="form-control" />                         
                </div>
                <div class="form-group col-xs-12 col-lg-4">
                    <label for="midterm_end">End of Midterm Grading</label>
                    <input type="date" name="midterm_end" value="<?php echo $item['midterm_end']; ?>" class="form-control" />                         
                </div>
                <div class="form-group col-xs-12 col-lg-4">
                    <label for="final_start">Start of Final Grading</label>
                    <input type="date" name="final_start" value="<?php echo $item['final_start']; ?>" class="form-control" />                         
                </div>
                <div class="form-group col-xs-12 col-lg-4">
                    <label for="final_end">End of Final Grading</label>
                    <input type="date" name="final_end" value="<?php echo $item['final_end']; ?>" class="form-control" />                         
                </div>                                        
                <div class="form-group col-xs-12 col-lg-4">
                    <label for="enumFinalized">Finalized</label>
                    <select name="enumFinalized" class="form-control">
                        <option <?php echo $item['enumFinalized']=='no'?'selected':''; ?>  value="no">No</option>
                        <option <?php echo $item['enumFinalized']=='yes'?'selected':''; ?> value="yes">Yes</option>
                        </select>
                </div>              
                    <div class="form-group col-xs-12">
                        <input type="submit" value="update" class="btn btn-default  btn-flat">
                    </div>
            <div style="clear:both"></div>
        </form>    
    </div>
    <hr />
    <div class="box box-primary">
        <div class="box-header">
                <h3 class="box-title">Add Grading Extension</h3>
        </div>                   
        <form action="<?php echo base_url(); ?>registrar/submit_extension" method="post" role="form">
            <input type="hidden" name="intID" value="<?php echo $item['intID'] ?>" />                
            <div>
                <div class="form-group col-md-4">
                    <label for="type">Period</label>
                </div>
                <div class="form-group col-md-4">
                    <label for="date">Date</label>
                </div>
                <div class="form-group col-md-4">                        
                </div>
                <div style="clear:both"></div>
            </div>   
            <div>
                <div class="form-group col-md-4">                    
                    <select required name="type" class="form-control">
                        <option value="midterm">Midterm</option>
                        <option value="final">Final</option>                            
                    </select>
                </div>
                <div class="form-group col-md-4">                    
                    <input type="date" id="date" name="date" class="form-control" required />                        
                </div>                                       
                <div class="form-group col-md-4">
                    <label for="submit">&nbsp;</label>
                    <input type="submit" value="add" class="btn btn-default  btn-flat">
                </div>
                <div style="clear:both"></div>
            </div>
            
        </form>    
    </div>
</aside>