<aside class="right-side">
    <section class="content-header">
                    <h1>
                        Applicant
                        <small></small>
                    </h1> 
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Applicant</a></li>
                        <li class="active">View All Applicants</li>
                    </ol>
                </section>
    <div class="content">
            <div class="alert alert-danger" style="display:none;">
                <i class="fa fa-ban"></i>
                <b>Alert!</b> Only admins can delete applicant records.
            </div>
      
<!--        <div class="box box-solid box-default">-->
        <div class="box">
            <div class="box-header">                  
                <div class="box-header with-border">
                    <h3 class="box-title">Advanced Search</h3>
                        <div class="box-tools pull-right">
                            <div class="dropdown">
                                <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                    <i class="fa fa-table"></i> Download
                                        <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
                                    <li>
                                        <a href="<?php echo base_url() ?>excel/download_applicants/<?php echo $course.'/'.$appdate.'/'.$gender.'/'.$sem; ?>" class="text-muted">Download Applicants</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                </div>
            </div><!-- /.box-header -->
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
                        <label for="appDate">By Application Date:</label>
                        <select id="appDate" class="form-control select2">
                            <option <?php echo ($gender == 0)?'selected':''; ?> value="0">-----------------SELECT---------------</option>
                            <option <?php echo ($gender == 1)?'selected':''; ?> value="1">male</option>
                            <option <?php echo ($gender == 2)?'selected':''; ?> value="2">female</option>
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
                        <label for="sem">Admission School Term/Sem:</label>
                        <select id="sem" class="form-control select2" >
                            <option <?php echo ($sem == 0)?'selected':''; ?> value="0">-----------------SELECT---------------</option>
                            <?php foreach($sy as $s): ?>
                                <option <?php echo ($sem == $s['intID'])?'selected':''; ?> value="<?php echo $s['intID']; ?>"><?php echo $s['enumSem']." ".$term_type." ".$s['strYearStart']."-".$s['strYearEnd']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
              
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

                <h3 class="box-title">List of Applicants</h3>
                <div class="box-tools">

                </div>
            </div>
            <div class="box-body table-responsive">
                <table id="applicants_table" class="table table-hover">
                    <thead><tr><th>id</th><th>Applicant Number</th><th>Applicant Name</th><th>Applied Program</th><th>Application Date/Time</th><th>Confirmation Code</th><th>Actions</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </div>
    
</aside>