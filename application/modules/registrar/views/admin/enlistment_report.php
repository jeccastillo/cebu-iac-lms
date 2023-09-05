<aside class="right-side">
    <section class="content-header">
            <h1>
            Enlisted Students
            <small>
                <a class="btn btn-app" href="<?php echo base_url(); ?>registrar/registrar_reports" >
                    <i class="ion ion-arrow-left-a"></i>
                    All Reports
                </a> 
                <a class="btn btn-app" target="_blank" href="<?php echo $pdf_link; ?>" ><i class="fa fa-book"></i>Generate PDF</a> 
                <a class="btn btn-app" target="_blank" href="<?php echo $excel_link; ?>" ><i class="fa fa-book"></i>Generate Excel</a> 
            </small>
            
        </h1> 
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Student</a></li>
            <li class="active">View All Student</li>
        </ol>
    </section>
    <div class="box">
            <div class="box-header with-border">
              <h3 class="box-title">Advanced Search</h3>              
            </div>
            <!-- /.box-header -->            
            <div class="box-body" style="display: block;">
                <div class="row">
                    <div class="col-sm-4">
                        <label for="intProgramID">By Program:</label>
                        <select id="intProgramID" class="form-control select2">
                            <option class="text-muted" value="0">-----------------ALL PROGRAMS---------------</option>
                            <?php foreach($programs as $program): ?>
                            <option <?php echo ($course == $program['intProgramID'])?'selected':''; ?>  value="<?php echo $program['intProgramID']; ?>">
                                <?php echo $program['strProgramCode']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>                    
                    <div class="col-sm-4">
                        <label for="intYearLevel">By Year Level:</label>
                        <select id="intYearLevel" class="form-control select2">                            
                            <option <?php echo ($postyear == 0)?'selected':''; ?> value="0">-----------------ALL YEARS---------------</option>
                            <option <?php echo ($postyear == 1)?'selected':''; ?> value="1">1st</option>
                            <option <?php echo ($postyear == 2)?'selected':''; ?> value="2">2nd</option>
                            <option <?php echo ($postyear == 3)?'selected':''; ?> value="3">3rd</option>
                            <option <?php echo ($postyear == 4)?'selected':''; ?> value="4">4th</option>
                            <option <?php echo ($postyear == 5)?'selected':''; ?> value="5">5th</option>
                            <option <?php echo ($postyear == 5)?'selected':''; ?> value="6">6th</option>
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <label for="gender">By Gender:</label>
                        <select id="gender" class="form-control select2">
                            <option <?php echo ($gender == 0)?'selected':''; ?> value="0">-----------------ALL GENDERS---------------</option>
                            <option <?php echo ($gender == 1)?'selected':''; ?> value="1">male</option>
                            <option <?php echo ($gender == 2)?'selected':''; ?> value="2">female</option>
                        </select>
                    </div>                                                   
                    <div class="col-sm-4">
                        <label for="sem">Select Term:</label>
                        <select id="sem" class="form-control select2" >
                            <option <?php echo ($sem == 0)?'selected':''; ?> value="0">-----------------ACTIVE TERM---------------</option>
                            <?php foreach($sy as $s): ?>
                                <option <?php echo ($sem == $s['intID'])?'selected':''; ?> value="<?php echo $s['intID']; ?>"><?php echo $s['enumSem']." ".$term_type." ".$s['strYearStart']."-".$s['strYearEnd']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <label for="sem">Select Term:</label>
                        <input type="date" id="start" name="date_start" class="form-control" value="<?php echo $start; ?>" />
                    </div>
                    <div class="col-sm-4">
                        <label for="sem">Select Term:</label>
                        <input type="date" id="end" name="date_start" class="form-control" value="<?php echo $end; ?>" />
                    </div>
                </div>
              <!-- /.row -->
            </div>
            <!-- ./box-body -->
            
            <div class="box-footer" style="display: block;">
                <div class="row">
                    <div class="col-sm-4">
                        <a href="#" class="btn btn-flat btn-info" id="advanced-search-enrolled"><i class="ion ion-search"></i> Search</a>
                    </div>
                </div>
              <!-- /.row -->
            </div>
            <!-- /.box-footer -->
            
          </div>
    <div class="content">
            <div class="alert alert-danger" style="display:none;">
                <i class="fa fa-ban"></i>
                <b>Alert!</b> Only admins can delete student records.
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

                <h3 class="box-title">List of Enlisted Students</h3>
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
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr>
                            <th>id</th>
                            <th>slug</th>
                            <th>Student Number</th>
                            <th>Last Name</th>
                            <th>Program</th>
                            <th>Year Level</th>                            
                        </tr>
                    </tfoot>
                </table>
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </div>
    
</aside>