<aside class="right-side">
<section class="content-header">
                    <h1>
                        Classlist
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="<?php echo base_url(); ?>unity/view_classlist"><i class="ion ion-android-book"></i> Classlist</a></li>
                        <li class="active">Edit Classlists</li>
                    </ol>
                </section>
<section class="content">
    <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    
                    <!--li><a href="#tab_3" data-toggle="tab">Quiz Record</a></li-->
                    <li class="pull-right"><a href="<?php echo base_url() ?>unity/classlist_viewer/<?php echo $classlist['intID']; ?>" class="text-muted"><i class="fa fa-bars"></i> View</a></li>
                    <li class="pull-right hide"><a href="#" id="addStudentModal" class="text-muted"><i class="fa fa-plus"></i> Add Student</a></li>
                </ul>
        <div class="span10 box box-primary">
            <div class="box-header">
                 <h3 class="box-title">Edit Classlist</h3>
            </div>
            <form action="<?php echo base_url(); ?>unity/edit_class" method="post" role="form">
                
                
                <input type="hidden" value="<?php echo $classlist['intID']; ?>" name="intID">
                <input type="hidden" value="<?php echo $classlist['intFacultyID']; ?>" name="intFacultyID">
                <input type="hidden" value="<?php echo $classlist['strUnits']; ?>" name="strUnits">
            <div class="box-body">
                <label for="intSubjectID">Subject <a rel="locked" href="#" id="subject-lock"><i class="ion ion-locked"></i></a></label>     
                <div class=" form-group">                    
                    <select disabled id="subjects" class="form-control select2" name="intSubjectID" >
                        <?php foreach($subjects as $s): ?>
                            <option <?php echo ($classlist['intSubjectID'] == $s['intID'])?'selected':''; ?> value="<?php echo $s['intID'] ?>"><?php echo $s['strCode']." ".$s['strDescription']; ?></option> 
                        <?php endforeach; ?>
                    </select>                                        
                </div>                
                <div class="row">
                    <div class="form-group col-xs-6">
                        <label for="slots">Maximum Slots:</label>
                        <input type="number" name="slots" class="form-control" id="slots" placeholder="ex. 30" value="<?php echo $classlist['slots']; ?>" />                        
                    </div>
                </div>
                <div class="form-group col-xs-6">
                        <label for="strUnits">Checked By:</label>
                        <input type="text" name="strSignatory1Name" class="form-control" id="strSignatory1Name" placeholder="Enter Name" value="<?php echo $classlist['strSignatory1Name']; ?>">
                        <input type="text" name="strSignatory1Title" class="form-control" id="strSignatory1Title" placeholder="Enter Title" value="<?php echo $classlist['strSignatory1Title']; ?>">
                    </div>
                <div class="form-group col-xs-6">
                        <label for="strUnits">Noted By:</label>
                        <input type="text" name="strSignatory2Name" class="form-control" id="strSignatory2Name" placeholder="Enter Name" value="<?php echo $classlist['strSignatory2Name']; ?>">
                        <input type="text" name="strSignatory2Title" class="form-control" id="strSignatory2Title" placeholder="Enter Title" value="<?php echo $classlist['strSignatory2Title']; ?>">
                    </div>
                
                <div class="form-group">
                    <label for="strSection">Section <a rel="locked" href="#" id="section-lock"><i class="ion ion-locked"></i></a></label>
                    <input type="text" disabled id="section" name="strSection" class="form-control"  value="<?php echo $classlist['strSection']; ?>" placeholder="">
                </div>
                <div class="form-group">
                    <label for="strSection">Sub Section <a rel="locked" href="#" id="sub-section-lock"><i class="ion ion-locked"></i></a></label>
                    <input type="text" disabled id="sub_section" name="sub_section" class="form-control" value="<?php echo $classlist['sub_section']; ?>" placeholder="">
                </div>
                <div class="form-group">
                        <label for="strAcademicYear">Term/Sem</label>
                        <select class="form-control" name="strAcademicYear">
                            <?php foreach($sy as $s): ?>
                                <option <?php echo ($classlist['strAcademicYear'] == $s['intID'])?'selected':''; ?> value="<?php echo $s['intID'] ?>"><?php echo $s['enumSem']." ".$term_type." ".$s['strYearStart']."-".$s['strYearEnd'];  ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                
                
                    
              
                <div class="form-group">
                    <label for="intSubjectID">Grades for Submission</label>
                    <select <?php echo (!$admin)?'disabled':'';?> class="form-control" name="intFinalized" >
                            <option <?php echo ($classlist['intFinalized'] == 0)?'selected':''; ?> value="0">Midterm</option> 
                            <option  <?php echo ($classlist['intFinalized'] == 1)?'selected':''; ?> value="1">Final</option> 
                            <option  <?php echo ($classlist['intFinalized'] == 2)?'selected':''; ?> value="2">Done</option>                             
                    </select>
                </div>
                
                <!-- <div class="form-group">
                    <label for="intSubjectID">Payable</label>
                    <select class="form-control" name="intWithPayment" >
                            <option <?php echo ($classlist['intWithPayment'] == 0)?'selected':''; ?> value="0">Yes</option> 
                            <option  <?php echo ($classlist['intWithPayment'] == 1)?'selected':''; ?> value="1">No</option> 
                    </select>
                </div> -->
                
           
                
                <input type="hidden" value="student" name="r1" >
                <hr />
                <input type="submit" value="update" class="btn btn-default  btn-flat">
            </form>
            
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