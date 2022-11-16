<aside class="right-side">
<section class="content-header">
                    <h1>
                        Student
                        <small></small>
                    </h1> 
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Student</a></li>
                        <li class="active">View All Student</li>
                    </ol>
                </section>
    <div class="content">
            <div class="alert alert-danger" style="display:none;">
                <i class="fa fa-ban"></i>
                <b>Alert!</b> Only admins can delete student records.
            </div>
        
        <div class="box">
            <div class="box-header with-border">
              <h3 class="box-title">Advanced Search</h3>
              <div class="box-tools pull-right">
                    <div class="dropdown">
                      <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                        <i class="fa fa-table"></i> Download
                        <span class="caret"></span>
                      </button>
                      <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
                        <?php if($registered >= 1): ?>
                        <li><a href="<?php echo base_url(); ?>pdf/zipAndDownload/<?php echo $course.'/'.$postreg.'/'.$postyear.'/'.$gender.'/'.$graduate.'/'.$scholarship.'/'.$registered.'/'.$sem; ?>">Download Registration Forms</a></li>
                        <?php endif; ?>
                        <li><a href="<?php echo base_url() ?>excel/download_students_with_grades/<?php echo $course.'/'.$postreg.'/'.$postyear.'/'.$gender.'/'.$graduate.'/'.$scholarship.'/'.$registered.'/'.$sem; ?>" class="text-muted">Download With Subjects</a></li>
                         <li><a href="<?php echo base_url() ?>excel/download_students/<?php echo $course.'/'.$postreg.'/'.$postyear.'/'.$gender.'/'.$graduate.'/'.$scholarship.'/'.$registered.'/'.$sem; ?>" class="text-muted">Download Spreadsheet</a></li>
                         <li><a href="<?php echo base_url() ?>excel/download_repeated_subjects/<?php echo $course.'/'.$postreg.'/'.$postyear.'/'.$gender.'/'.$graduate.'/'.$scholarship.'/'.$registered.'/'.$sem; ?>" class="text-muted">Download Repeated Subjects</a></li>                         
                          <li><a href="<?php echo base_url() ?>excel/free_he_billing_details/<?php echo $course.'/'.$postreg.'/'.$postyear.'/'.$gender.'/'.$graduate.'/'.$scholarship.'/'.$registered.'/'.$sem; ?>" class="text-muted">Download Free HE Billing Details</a></li>
                          <li><a href="<?php echo base_url() ?>excel/download_cor_data/<?php echo $course.'/'.$postreg.'/'.$postyear.'/'.$gender.'/'.$graduate.'/'.$scholarship.'/'.$registered.'/'.$sem; ?>" class="text-muted">Download COR Data Elements</a></li>  
                      </ul>
                    </div>
              </div>
            </div>
            <!-- /.box-header -->
            <?php /*
            <div class="box-body" style="display: block;">
                <div class="row">
                    <div class="col-sm-4">
                        <label for="intProgramID">By Program:</label>
                        <select id="intProgramID" class="form-control select2">
                            <option class="text-muted" value="0">-----------------SELECT---------------</option>
                            <?php foreach($programs as $program): ?>
                            <option <?php echo ($course == $program['intProgramID'])?'selected':''; ?>  value="<?php echo $program['intProgramID']; ?>">
                                <?php echo $program['strProgramCode']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <label for="academicStatus">By Academic Status:</label>
                        <select id="academicStatus" class="form-control select2">
                            <option <?php echo ($postreg == 0)?'selected':''; ?> value="0">-----------------SELECT---------------</option>
                            <option <?php echo ($postreg == 1)?'selected':''; ?> value="1">regular</option>
                            <option <?php echo ($postreg == 2)?'selected':''; ?> value="2">irregular</option>
                            <option <?php echo ($postreg == 3)?'selected':''; ?> value="3">new student</option>
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <label for="intYearLevel">By Year Level:</label>
                        <select id="intYearLevel" class="form-control select2">
                            <option <?php echo ($postyear == 0)?'selected':''; ?> value="0">-----------------SELECT---------------</option>
                            <option <?php echo ($postyear == 1)?'selected':''; ?> value="1">1st</option>
                            <option <?php echo ($postyear == 2)?'selected':''; ?> value="2">2nd</option>
                            <option <?php echo ($postyear == 3)?'selected':''; ?> value="3">3rd</option>
                            <option <?php echo ($postyear == 4)?'selected':''; ?> value="4">4th</option>
                            <option <?php echo ($postyear == 5)?'selected':''; ?> value="5">5th</option>
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <label for="gender">By Gender:</label>
                        <select id="gender" class="form-control select2">
                            <option <?php echo ($gender == 0)?'selected':''; ?> value="0">-----------------SELECT---------------</option>
                            <option <?php echo ($gender == 1)?'selected':''; ?> value="1">male</option>
                            <option <?php echo ($gender == 2)?'selected':''; ?> value="2">female</option>
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <label for="graduate">Graduated:</label>
                        <select id="graduate" class="form-control select2">
                            <option <?php echo ($graduate == 0)?'selected':''; ?> value="0">-----------------SELECT---------------</option>
                            <option <?php echo ($graduate == 1)?'selected':''; ?> value="1">yes</option>
                            <option <?php echo ($graduate == 2)?'selected':''; ?> value="2">no</option>
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <label for="registered">Registration Status:</label>
                        <select id="registered" class="form-control select2">
                            <option <?php echo ($registered == 0)?'selected':''; ?> value="0">-----------------SELECT---------------</option>
                            <option <?php echo ($registered == -1)?'selected':''; ?> value="-1">advised</option>
                            <option <?php echo ($registered == 1)?'selected':''; ?> value="1">registered</option>
                            <option <?php echo ($registered == 2)?'selected':''; ?> value="2">enrolled</option>
                            <option <?php echo ($registered == 3)?'selected':''; ?> value="3">cleared</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-4">
                     <label for="">Scholarship Grant</label>
                    <select id="scholarship" class="form-control select2">
                        <option value="0">-----------------SELECT---------------</option>
                        <option <?php echo ($scholarship == 1)?'selected':''; ?> value="1">Paying</option>
                         <option <?php echo ($scholarship == 2)?'selected':''; ?> value="2">Resident Scholar</option>
                        <option <?php echo ($scholarship == 3)?'selected':''; ?> value="3">7th District Scholar</option>
                        <option <?php echo ($scholarship == 4)?'selected':''; ?> value="4">DILG Scholar</option>
                          <option <?php echo ($scholarship == 5)?'selected':''; ?> value="5">Tagaytay Resident</option>
                        <option <?php echo ($scholarship == 6)?'selected':''; ?> value="6">FREE H.E.</option>
                    </select>         
                </div>
                    <div class="col-sm-4">
                        <label for="sem">Sem For Grades Spreadsheet:</label>
                        <select id="sem" class="form-control select2" >
                            <option <?php echo ($sem == 0)?'selected':''; ?> value="0">-----------------SELECT---------------</option>
                            <?php foreach($sy as $s): ?>
                                <option <?php echo ($sem == $s['intID'])?'selected':''; ?> value="<?php echo $s['intID']; ?>"><?php echo $s['enumSem']." ".$term_type." ".$s['strYearStart']."-".$s['strYearEnd']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
              <!-- /.row -->
            </div>
            <!-- ./box-body -->
            
            <div class="box-footer" style="display: block;">
                <div class="row">
                    <div class="col-sm-4">
                        <a href="#" class="btn btn-flat btn-info" id="advanced-search"><i class="ion ion-search"></i> Search</a>
                    </div>
                </div>
              <!-- /.row -->
            </div>
            <!-- /.box-footer -->
            */ ?>
          </div>
        <div class="box box-solid box-default">
            <div class="box-header">                  
                <div>

                    <div style="width:50%;float:right; text-align:right;">
<!--
                        <form method="post" action="<?php echo base_url(). student/view_all_students/20 ?>">
                            <h5>Search: <input type="text" name="search_string"/>
                            </h5>
                        </form>
-->
                    </div>
                </div>

                <h3 class="box-title">List of Students</h3>
                <div class="box-tools">

                </div>
            </div><!-- /.box-header -->
            <div class="box-body table-responsive">
                <table id="users_table" class="table table-hover">
                    <thead><tr><th>id</th><th>Student Number</th><th>Name</th><th>Course</th><th>Year Level</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr>
                            <th>id</th>
                            <th>Student Number</th>
                            <th>Last Name</th>
                            <th>First Name</th>
                            <th>Middle Name</th>
                            <th>Program</th>
                            <th>Year Level</th>
                        </tr>
                    </tfoot>
                </table>
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </div>
    
</aside>