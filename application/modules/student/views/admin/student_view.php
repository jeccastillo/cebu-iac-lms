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
                            <li><a href="<?php echo base_url() ?>excel/download_students/<?php echo $course.'/'.$postreg.'/'.$postyear.'/'.$gender.'/'.$graduate.'/'.$scholarship.'/'.$registered.'/'.$sem; ?>" class="text-muted">Students Data Report</a></li>                                                  
                            <li><a href="<?php echo base_url() ?>excel/download_student_grades/<?php echo $course.'/'.$postreg.'/'.$postyear.'/'.$gender.'/'.$graduate.'/'.$scholarship.'/'.$registered.'/'.$sem; ?>" class="text-muted">Student Grades</a></li>                                                                           
                            <li><a href="<?php echo base_url() ?>excel/download_enrolled_students_neo/<?php echo $course.'/'.$postreg.'/'.$postyear.'/'.$gender.'./'.$scholarship.'/'.$registered.'/'.$sem.'/'.'1'.'/'.$level; ?>" class="text-muted">Enrolled Students</a></li>                                                                           
                            <li><a href="<?php echo base_url() ?>excel/download_enrolled_students_neo/<?php echo $course.'/'.$postreg.'/'.$postyear.'/'.$gender.'./'.$scholarship.'/'.$registered.'/'.$sem.'/'.'0'.'/'.$level; ?>" class="text-muted">Enrolled Per Course</a></li>                                                                           
                      </ul>
                    </div>
              </div>
            </div>
            <!-- /.box-header -->            
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
                    <!-- <div class="col-sm-4">
                        <label for="graduate">Graduated:</label>
                        <select id="graduate" class="form-control select2">
                            <option <?php echo ($graduate == 0)?'selected':''; ?> value="0">-----------------SELECT---------------</option>
                            <option <?php echo ($graduate == 1)?'selected':''; ?> value="1">yes</option>
                            <option <?php echo ($graduate == 2)?'selected':''; ?> value="2">no</option>
                        </select>
                    </div>-->
                   <div class="col-sm-4">
                        <label for="registered">Enrollment Status:</label>
                        <select id="registered" class="form-control select2">
                            <option <?php echo ($registered == 0)?'selected':''; ?> value="0">-----------------SELECT---------------</option>                            
                            <option <?php echo ($registered == 1)?'selected':''; ?> value="1">enlisted</option>
                            <option <?php echo ($registered == 2)?'selected':''; ?> value="2">enrolled</option>                            
                        </select>
                    </div>
                    <!-- <div class="col-sm-4">
                        <label for="inactive">Active Student:</label>
                        <select id="inactive" class="form-control select2">
                            <option <?php echo ($inactive == 0)?'selected':''; ?> value="0">active</option>                            
                            <option <?php echo ($inactive == 1)?'selected':''; ?> value="1">inactive</option>                            
                        </select>
                    </div>  -->
                    <div class="col-sm-4">
                        <label for="level">Student Type:</label>
                        <select id="level" class="form-control select2">
                            <option <?php echo ($level == 0)?'selected':''; ?> value="0">-----------------SELECT---------------</option>                            
                            <option <?php echo ($level == 1)?'selected':''; ?> value="1">shs</option>
                            <option <?php echo ($level == 2)?'selected':''; ?> value="2">college</option>
                            <option <?php echo ($level == 3)?'selected':''; ?> value="3">other</option>
                            <option <?php echo ($level == 4)?'selected':''; ?> value="4">drive</option>
                        </select>
                    </div>                
                    <div class="col-sm-4">
                        <label for="sem">Sem For Grades Spreadsheet:</label>
                        <select id="sem" class="form-control select2" >
                            <option <?php echo ($sem == 0)?'selected':''; ?> value="0">-----------------SELECT---------------</option>
                            <?php foreach($sy as $s): ?>
                                <option <?php echo ($sem == $s['intID'])?'selected':''; ?> value="<?php echo $s['intID']; ?>"><?php echo $s['term_student_type']." ".$s['enumSem']." ".$s['term_label']." ".$s['strYearStart']."-".$s['strYearEnd']; ?></option>
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
                    <thead>                        
                        <tr>
                            <th>id</th>
                            <th>slug</th>
                            <th>Student Number</th>
                            <th>Name</th>
                            <th>Program</th>
                            <th>Year Level</th>
                            <th>Status</th>
                            <th>Student Type</th>
                            <th>Actions</th>
                        </tr>                        
                        <tr class="search">
                            <td>id</td>
                            <td>slug</td>
                            <td>Student Number</td>
                            <td>Last Name</td>
                            <td>Program</td>
                            <td>Year Level</td>
                            <td>Status</td>
                            <td>Student Type</td>
                            <td>Actions</td>
                        </tr>
                    </thead>                    
                    <tbody></tbody>
                    <!-- <tfoot>
                        <tr>
                            <th>id</th>
                            <th>slug</th>
                            <th>Student Number</th>
                            <th>Last Name</th>
                            <th>Program</th>
                            <th>Year Level</th>
                            <th>Actions</th>
                        </tr>
                    </tfoot> -->
                </table>
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </div>
    
</aside>