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
    <div class="span10 box box-primary">
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
                     <div class="row">
                         <div class="form-group col-sm-4">
                            <label for="strSection">Program:</label>
                            <select class="form-control select2" name="program">
                                <?php foreach($programs as $program): ?>
                                    <option value="<?php echo $program['strProgramCode']; ?>"><?php echo $program['strProgramCode']; ?></option>
                                <?php endforeach; ?>
                                <option value="OPEN-SPL">OPEN-SPL</option>
                                <option value="OPEN-REG">OPEN-REG</option>
                                <option value="MODULAR">MODULAR</option>
                            </select>
                        </div>
                         <div class="form-group col-sm-4">
                            <label for="strSection">Year:</label>
                            <select type="text" name="year" class="form-control" id="year" >
                                <option value="1">1st</option> 
                                <option value="2">2nd</option> 
                                <option value="3">3rd</option> 
                                <option value="4">4th</option> 
                                <option value="5">5th</option> 
                            </select>
                         </div>
                         <div class="form-group col-sm-4">
                            <label for="strSection">Section:</label>
                             <select type="text" name="section" class="form-control" id="section" >
                                <option value="A">A</option> 
                                <option value="B">B</option> 
                                <option value="C">C</option> 
                                <option value="D">D</option> 
                                <option value="E">E</option> 
                                <option value="F">F</option> 
                                <option value="G">G</option> 
                                <option value="H">H</option> 
                                <option value="I">I</option> 
                                <option value="J">J</option>
                                <option value="K">K</option>
                                <option value="L">L</option> 
                                <option value="1">1</option> 
                                <option value="2">2</option> 
                                <option value="3">3</option> 
                                <option value="4">4</option> 
                                <option value="5">5</option> 
                                <option value="6">6</option> 
                                <option value="7">7</option> 
                                <option value="8">8</option> 
                                <option value="9">9</option> 
                                <option value="10">10</option>
                                <option value="11">11</option>
                                <option value="12">12</option> 
                            </select>
                            
                         </div>
                    </div>
                     
               
                    <div class="form-group">
                        <label for="strAcademicYear">Academic Year:</label>
                        <select class="form-control" name="strAcademicYear">
                            <?php foreach($sy as $s): ?>
                                <option <?php echo ($selected_ay == $s['intID'])?'selected':''; ?> value="<?php echo $s['intID']; ?>"><?php echo $s['enumSem']." ".$term_type." ".$s['strYearStart']."-".$s['strYearEnd']; ?></option>
                            <?php endforeach; ?>
                        </select>
                       <!--<select class="form-control" name="strAcademicYear">
                            <?php foreach($sy as $s): ?>
                                <option value="<?php echo $s['intID'] ?>"><?php echo $s['enumSem']." ".$term_type." ".$s['strYearStart']."-".$s['strYearEnd'];  ?></option>
                            <?php endforeach; ?>
                        </select>-->
                    </div>
                    
                <hr />
                <input type="submit" value="add" class="btn btn-default  btn-flat">
            </form>
       
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