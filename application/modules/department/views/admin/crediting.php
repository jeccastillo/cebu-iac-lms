<aside class="right-side">
<section class="content-header">
                    <h1>
                        Add Credits
                        <small></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Department</a></li>
                        <li class="active">Add Credits</li>
                    </ol>
                </section>
<div class="content">
    <div class="row">
        <div class="col-sm-3">
            <div class="box box-widget widget-user-2">
            <!-- Add the bg color to the header using any of the bg-* classes -->
            <div class="widget-user-header bg-red">
              <!-- /.widget-user-image -->
              <h3 class="widget-user-username" style="text-transform:capitalize;margin-left:0;font-size:1.3em;"><?php echo strtolower($student['strLastname'].", ". $student['strFirstname']); ?>
                        <?php echo ($student['strMiddlename'] != "")?' '.strtolower($student['strMiddlename']):''; ?></h3>
              <h5 class="widget-user-desc" style="margin-left:0;"><?php echo $student['strProgramCode']." Major in ".$student['strMajor']; ?></h5>
            </div>
            <div class="box-footer no-padding">
              <ul class="nav nav-stacked">
                <li><a href="#" style="font-size:13px;">Student Number <span class="pull-right text-blue"><?php echo $student['strStudentNumber']; ?></span></a></li>
                   <li><a href="#" style="font-size:13px;">Curriculum <span class="pull-right text-blue"><?php echo $student['strName']; ?></span></a></li>
                <li><a style="font-size:13px;" href="#">Registration Status <span class="pull-right"><?php echo $reg_status; ?></span></a></li>
                  <li><a style="font-size:13px;" href="#">Scholarship Type <span class="pull-right"><?php echo $student['enumScholarship']; ?></span></a></li>
                  <li><a style="font-size:13px;" href="<?php echo base_url().'unity/student_viewer/'.$student['intID']; ?>">View Profile</a></li>
                  
                  
              </ul>
            </div>
          </div>
            
        </div>
    <div class="col-md-9">
    <div class="box box-primary">
        <div class="box-header">
                <h3 class="box-title">Add Credits</h3>
        </div>
       
            
            <form id="validate-schedule" action="<?php echo base_url(); ?>department/submit_crediting" method="post" role="form">
                 <input type="hidden" name="intStudentID" value="<?php echo $student['intID'] ?>" />
                 <input type="hidden" name="intCurriculumID" value="<?php echo $student['intCurriculumID'] ?>" />
                 <div class="box-body">
                    <div class="row">
                        <div class="form-group col-xs-12 col-lg-6">
                            <label for="subject">Subject</label>
                            <select name="intSubjectID" class="form-control select2">
                                <?php foreach($subjects_not_taken as $subject): ?>
                                    <option value="<?php echo $subject['intSubjectID']; ?>"><?php echo $subject['strCode']; ?></option>
                                <?php endforeach; ?>
                             </select>
                        </div>
                         <div class="form-group col-xs-12 col-lg-6">
                            <label for="section">Grade</label>
                            <select class="form-control" name="floatFinalGrade">
                                <option value="3.5">INC</option>
                                <option value="1.00">1.00</option>
                                <option value="1.25">1.25</option>
                                <option value="1.50">1.50</option>
                                <option value="1.75">1.75</option>
                                <option value="2.00">2.00</option>
                                <option value="2.25">2.25</option>
                                <option value="2.50">2.50</option>
                                <option value="2.75">2.75</option>
                                <option value="3.00">3.00</option>
                                <option value="5.00">5.00</option>
                            </select>

                        </div>
                    </div>  
                </div>
                
                     <div class="form-group col-xs-12">
                         <input type="submit" value="add" class="btn btn-default  btn-flat">
                     </div>
                <div style="clear:both"></div>
            </form>
        </div>
        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title">Credited Subjects</h3>
            </div>
            <div class="box-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Grade</th>
                            <th>Units</th>
                            <th>Delete</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($credited_subjects as $cr): ?>
                            <tr>
                                <td><?php echo $cr['strCode']; ?></td>
                                <td><?php echo $cr['floatFinalGrade']; ?></td>
                                <td><?php echo $cr['strUnits']; ?></td>
                                <td><a rel="<?php echo $cr['intID']; ?>" href="#" class="trash-credited btn btn-danger"><i class="ion ion-trash-b"></i> Remove</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
       
        </div>
        </div>
    </div>
    </div>
</aside>