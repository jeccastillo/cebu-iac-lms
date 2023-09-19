<aside class="right-side">
<section class="content-header">
                    <h1>
                        Classlist
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="<?php echo base_url(); ?>unity/view_classlist"><i class="ion ion-android-book"></i> Classlist</a></li>
                        <li class="active">New Classlist</li>
                    </ol>
                </section>
<section class="content">
    <div class="row">
        <div class="col-sm-6 col-sm-offset-3">
            <div class="box box-primary">
                <div class="box-header">
                        <h3 class="box-title">New Classlist</h3>
                </div>
            
                    
                <form action="<?php echo base_url(); ?>unity/submit_class" method="post" role="form">
                    
                        <div class="box-body">
                        
                        <?php if(in_array($user['intUserLevel'],array(2,1)) ): ?>
                            <label for="intSubjectID">Faculty Assigned:</label>    
                            <select class="form-control select2" id="facultyID" name="intFacultyID" >
                                <?php foreach($faculty as $f): ?>
                                    <option value="<?php echo $f['intID'] ?>"><?php echo $f['strLastname']." ".$f['strFirstname']; ?></option> 
                                <?php endforeach; ?>
                            </select>
                            
                    <?php else: ?>
                        <input type="hidden" value="<?php echo $faculty_data['intID']; ?>" name="intFacultyID">
                    <?php endif; ?>
                    <label for="intSubjectID">Subject:</label>     
                    <div class="input-group">
                        
                            <select class="form-control select2" id="subjects" name="intSubjectID" >
                                <?php foreach($subjects as $s): ?>
                                    <option value="<?php echo $s['intID'] ?>"><?php echo $s['strCode']." ".$s['strDescription']; ?></option> 
                                <?php endforeach; ?>
                            </select>
                        <div class="input-group-btn">
                            <button data-toggle="modal" href="#myModal" type="button" class="btn btn-default  btn-flat">Add New</button>
                        </div><!-- /btn-group -->
                        
                    </div>
                                                    
                    <div class="form-group">
                        <label for="strClassName">Class Name:</label>
                        <input type="text" name="strClassName" class="form-control" id="strClassName" >                                
                    </div>
                    <div class="form-group">
                        <label for="year">Year:</label>
                        <input type="number" name="year" class="form-control" id="year" >                                
                    </div>                    
                    <div class="form-group">
                        <label for="strSection">Section:</label>
                        <input type="text" name="strSection" class="form-control" id="strSection" >                                
                    </div>
                    <div class="form-group">
                        <label for="sub_section">Sub Section:</label>
                        <input type="text" name="sub_section" class="form-control" id="sub_section" >                                
                    </div>
                    <div class="form-group">
                    <label for="">Curriculum</label>
                        <select class="form-control" name="intCurriculumID" id="intCurriculumID" >
                            <?php foreach ($curriculum as $curr): ?>
                            <option value="<?php echo $curr['intID']; ?>"><?php echo $curr['strName']; ?></option>
                            <?php endforeach; ?>
                        </select>                        
                    </div>
                        
                            
                    
                    <div class="form-group">
                        <label for="strAcademicYear">Select Term:</label>
                        <select class="form-control" name="strAcademicYear">
                            <?php foreach($sy as $s): ?>
                                <option <?php echo ($selected_ay == $s['intID'])?'selected':''; ?> value="<?php echo $s['intID']; ?>"><?php echo $s['enumSem']." ".$term_type." ".$s['strYearStart']."-".$s['strYearEnd']; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <!--<select class="form-control" name="strAcademicYear">
                            <?php foreach($sy as $s): ?>
                                <option value="<?php echo $s['intID'] ?>"><?php echo $s['term_student_type']." ".$s['enumSem']." ".$s['term_label']." ".$s['strYearStart']."-".$s['strYearEnd'];  ?></option>
                            <?php endforeach; ?>
                        </select>-->
                    </div>
                    
                    <hr />
                    <input type="submit" value="add" class="btn btn-default  btn-flat">
                </form>
            </div>
        </div>
    </div>
</section>
</aside>
    
<div class="modal" id="myModal">
	<div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
          <h4 class="modal-title">Add New Subject</h4>
        </div>
        <div class="modal-body">
            <div class="box-body">
                     <div class="form-group col-xs-6">
                        <label for="strCode">Subject Code*</label>
                        <input type="text" name="strCode" class="form-control" id="strCode" placeholder="Enter Subject Code">
                    </div>

                     <div class="form-group col-xs-6">
                        <label for="strUnits">Number of Units*</label>
                        <input type="number" name="strUnits" class="form-control" id="strUnits" placeholder="Enter Number of Units">
                    </div>

                <div class="form-group col-xs-6">
                        <label>Description</label>
                        <textarea class="form-control" id="strDescription" name="strDescription" rows="3" placeholder="Enter Description"></textarea>
                    </div>

                <div style="clear:both"></div>
            </div>
        </div>
        <div class="modal-footer">
          <a href="#" data-dismiss="modal" class="btn">Close</a>
          <a href="#" id="submit-subject" class="btn btn-default  btn-flat">Add Subject</a>
        </div>
      </div>
    </div>
</div>