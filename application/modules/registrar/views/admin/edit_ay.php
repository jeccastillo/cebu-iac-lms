<aside class="right-side">
<section class="content-header">
                    <h1>
                        School Term
                        <small></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Admin</a></li>
                        <li class="active">Edit Term</li>
                    </ol>
                </section>
<div class="content">
    <div class="box box-primary">
        <div class="box-header">
                <h3 class="box-title">Edit Term</h3>
        </div>                   
        <form id="validate-subject" action="<?php echo base_url(); ?>registrar/edit_submit_ay" method="post" role="form">
            <input type="hidden" name="intID" value="<?php echo $item['intID'] ?>" />
            <div class="box-body">
                <h3>Details</h3>
                <hr />
                <div class="row">
                    <div class="form-group col-xs-12 col-lg-4">
                        <label for="enumSem"><?php echo $term_type; ?></label>
                        <select name="enumSem" class="form-control">
                            <?php foreach($terms as $term): ?>
                                <option <?php echo $item['enumSem']==$term?'selected':''; ?> value="<?php echo $term ?>"><?php echo $term ?></option>
                            <?php endforeach; ?>
                            </select>
                    </div>
                    <div class="form-group col-xs-12 col-lg-4">
                        <label>Label</label>
                        <input required type="text" name="term_label" value="<?php echo $item['term_label']; ?>" class="form-control" />                         
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
                        <label for="term_student_type">Student Type for Term</label>
                        <select name="term_student_type" class="form-control">
                            <option <?php echo $item['term_student_type']=='college'?'selected':''; ?>  value="college">College</option>
                            <option <?php echo $item['term_student_type']=='shs'?'selected':''; ?> value="shs">SHS</option>
                            </select>
                    </div>
                    <div class="form-group col-xs-12 col-lg-4">
                        <label for="pay_student_visa">Pay Student Visa this term?</label>
                        <select name="pay_student_visa" class="form-control">
                            <option <?php echo $item['pay_student_visa']=='0'?'selected':''; ?>  value="0">No</option>
                            <option <?php echo $item['pay_student_visa']=='1'?'selected':''; ?> value="1">Yes</option>
                        </select>
                    </div>                     
                </div>
                <hr />
                <h3>Dates Setup</h3>
                <hr />
                <div class="row">
                    <div class="form-group col-xs-12 col-lg-4">
                        <label>Start of Classes</label>
                        <input type="date" name="start_of_classes" value="<?php echo $item['start_of_classes']; ?>" class="form-control" />                         
                    </div>
                    <div class="form-group col-xs-12 col-lg-4">
                        <label>Start of Final Exams</label>
                        <input type="date" name="final_exam_start" value="<?php echo $item['final_exam_start']; ?>" class="form-control" />                         
                    </div>
                    <div class="form-group col-xs-12 col-lg-4">
                        <label>End of Final Exams</label>
                        <input type="date" name="final_exam_end" value="<?php echo $item['final_exam_end']; ?>" class="form-control" />                         
                    </div>           
                    <div class="form-group col-xs-12 col-lg-4">
                        <label>Deadline for Submission of Requirements</label>
                        <input type="date" name="end_of_submission" value="<?php echo $item['end_of_submission']; ?>" class="form-control" />                         
                    </div>                                       
                </div>
                <hr />
                <h3>Grading Period Setup</h3>
                <hr />
                <div class="row">
                    
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
                </div>
                <hr />  
                <h3>Student Viewing Period Setup</h3>
                <hr />
                <div class="row">
                    
                    <div class="form-group col-xs-12 col-lg-4">
                        <label for="midterm_start">Start of Midterm Viewing</label>
                        <input type="date" name="viewing_midterm_start" value="<?php echo $item['viewing_midterm_start']; ?>" class="form-control" />                         
                    </div>
                    <div class="form-group col-xs-12 col-lg-4">
                        <label for="midterm_end">End of Midterm Viewing</label>
                        <input type="date" name="viewing_midterm_end" value="<?php echo $item['viewing_midterm_end']; ?>" class="form-control" />                         
                    </div>
                    <div class="form-group col-xs-12 col-lg-4">
                        <label for="final_start">Start of Final Viewing</label>
                        <input type="date" name="viewing_final_start" value="<?php echo $item['viewing_final_start']; ?>" class="form-control" />                         
                    </div>
                    <div class="form-group col-xs-12 col-lg-4">
                        <label for="final_end">End of Final Viewing</label>
                        <input type="date" name="viewing_final_end" value="<?php echo $item['viewing_final_end']; ?>" class="form-control" />                         
                    </div>
                </div>
                <hr />         
                <h3>Term Finalization</h3>
                <hr />
                <div class="row">
                    <div class="form-group col-xs-12 col-lg-4">
                        <label for="enumFinalized">Finalized</label>
                        <select name="enumFinalized" class="form-control">
                            <option <?php echo $item['enumFinalized']=='no'?'selected':''; ?>  value="no">No</option>
                            <option <?php echo $item['enumFinalized']=='yes'?'selected':''; ?> value="yes">Yes</option>
                            </select>
                    </div>           
                </div>           
                <div class="row">             
                    <div class="form-group col-xs-12 text-right">
                        <input type="submit" value="update" class="btn btn-default btn-lg  btn-flat">
                    </div>
                </div>  
            </div>          
        </form>    
    </div>
    <hr />
    <div class="box box-primary">
        <div class="box-header">
                <h3 class="box-title">Add Term Months</h3>
        </div>                           
        <form action="<?php echo base_url(); ?>registrar/submit_month" method="post" role="form">
            <input type="hidden" name="id" value="<?php echo $item['intID'] ?>" />                
            <div class="box-body">               
                <div class="form-group col-md-4">                        
                </div>
                <div style="clear:both"></div>            
                <div class="form-group col-md-4">                    
                    <select required name="month" class="form-control">
                        <option value="January">January</option>
                        <option value="February">February</option>
                        <option value="March">March</option>
                        <option value="April">April</option>
                        <option value="May">May</option>
                        <option value="June">June</option>
                        <option value="July">July</option>
                        <option value="August">August</option>
                        <option value="September">September</option>
                        <option value="October">October</option>
                        <option value="November">November</option>
                        <option value="December">December</option>                            
                    </select>
                </div>                              
                <div class="form-group col-md-4">
                    <label for="submit">&nbsp;</label>
                    <input type="submit" value="add" class="btn btn-default  btn-flat">
                </div>
                <div style="clear:both"></div>
                <hr />
                <table class="table table-bordered table-striped">
                    <tr>
                        <th>Month</th>                        
                        <th>Actions</th>
                    </tr>                    
                    <?php foreach($term_months as $item): ?>
                    <tr>
                        <td><?php echo $item['month']; ?></td>                        
                        <td>
                            <button class="delete-month btn btn-danger" rel="<?php echo $item['id']; ?>">Delete</button>                            
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table> 
            </div>            
        </form>    
    </div>
    <hr />
    <div class="box box-primary">
        <div class="box-header">
                <h3 class="box-title">Add Grading Extension</h3>
        </div>                           
        <form action="<?php echo base_url(); ?>registrar/submit_extension" method="post" role="form">
            <input type="hidden" name="id" value="<?php echo $item['intID'] ?>" />                
            <div class="box-body">
                <div class="form-group col-md-4">
                    <label for="type">Period</label>
                </div>
                <div class="form-group col-md-4">
                    <label for="date">End Extension</label>
                </div>
                <div class="form-group col-md-4">                        
                </div>
                <div style="clear:both"></div>            
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
                <hr />
                <table class="table table-bordered table-striped">
                    <tr>
                        <th>Period</th>
                        <th>End Extension</th>
                        <th>Actions</th>
                    </tr>
                    <?php foreach($midterm_extensions as $item): ?>
                    <tr>
                        <td><?php echo $item['type']; ?></td>
                        <td><?php echo date("M j, Y",strtotime($item['date'])); ?></td>
                        <td>
                            <button class="delete-extension btn btn-danger" rel="<?php echo $item['id']; ?>">Delete</button>
                            <a class="btn btn-success" href="<?php echo base_url().'registrar/view_extension/'.$item['id']; ?>">View</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php foreach($final_extensions as $item): ?>
                    <tr>
                        <td><?php echo $item['type']; ?></td>
                        <td><?php echo date("M j, Y",strtotime($item['date'])); ?></td>
                        <td>
                            <button class="delete-extension btn btn-danger" rel="<?php echo $item['id']; ?>">Delete</button>
                            <a class="btn btn-success" href="<?php echo base_url().'registrar/view_extension/'.$item['id']; ?>">View</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table> 
            </div>            
        </form>    
    </div>
</aside>