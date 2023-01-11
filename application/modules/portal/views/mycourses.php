<?php 
    error_reporting(0);
?>
<aside class="right-side">
<section class="content-header">
            <h1>
                        My Courses
                        <small>view your courses information</small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="<?php echo base_url() ?>portal/dashboard"><i class="fa fa-home"></i> Home</a></li>
                        <li class="active">My Courses</li>
                    </ol>
                    <div class="box-tools pull-right">
                        <form action="#" method="get" class="sidebar-form">
                      
                            <select id="select-sem" class="form-control" >
                                <?php foreach($sy as $s): ?>
                                    <option rel='<?php echo $page ?>' <?php echo ($selected_ay == $s['intID'])?'selected':''; ?> value="<?php echo $s['intID']; ?>"><?php echo $s['enumSem']." ".$term_type." ".$s['strYearStart']."-".$s['strYearEnd']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                     </div>
                     <div style="clear:both"></div>
</section>

<div class="content"><input type="hidden" id="regStat" value="<?php echo $reg_status;?>"/>
<?php if ($reg_status =="For Advising"):  { ?>
    <div class="callout callout-warning">
    <!-- <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button> -->
        <h4> <i class="fa fa-warning"></i> iACADEMY Student Portal Advisory</h4>
        <p> No courses/subjects advised. Please contact your department chairman for the advising of courses/subjects.<p>
    </div>
<?php } ?>

<?php elseif ($reg_status =="For Registration"):  { ?>
    <div class="callout callout-info">
    
        <h4> <i class="fa fa-info"></i> iACADEMY Student Portal Advisory</h4>
        <p>Your courses have been advised. Please wait for the registrar to register your courses.<p>
    </div>
<?php } ?>
    <?php elseif ($reg_status =="Registered"):  { ?>
        <div class="callout callout-success">
    
        <h4> <i class="fa fa-check"></i> iACADEMY Student Portal Advisory</h4>
        <p>Your courses / subjects have been registered. To view your courses / subjects, please wait for the accounting office to tag you as enrolled.<p>
    </div>
<?php } endif; ?>
<input type="hidden" value="<?php echo $student['intID'] ?>" id="student-id" />
    <input type="hidden" value="<?php echo $active_sem['intID']; ?>" id="active-sem-id" />
    <!-- <div class="box box-solid box-success"> -->
    <div class="box box-warning">
        <div class="box-body">
            <div class="alert alert-danger" style="display:none;">
                <i class="fa fa-ban"></i>
                <b>Alert!</b> Only admins can delete student records.
            </div>
           
            <div class="col-xs-8 col-md-8">
              <h3 class="student-name" style="margin-top: 5px;"><?php 
                        $middleInitial = substr($student['strMiddlename'], 0,1);
                        echo $student['strLastname'].", ". $student['strFirstname'] . " " .  $middleInitial . "."; ?></h3>
              <?php echo $student['strProgramDescription']; ?>
              <p> <?php  echo 'major in '. $student['strMajor']; ?></p>
            </div>
            <div class="col-xs-4 col-md-4">
              <p><strong>Student Number: </strong><?php echo $student['strStudentNumber']; ?></p>
              <p><strong>Year Level: </strong><?php echo $academic_standing['year']; ?></p>
              <p><strong>Academic Status: </strong><?php echo $academic_standing['status']; ?></p>
              <p><strong>Enrollment Status: </strong><?php echo $reg_status; ?><input type="hidden" id="regStat" value="<?php echo $reg_status;?>"/></p>
            </div>
        </div>
    </div>
    <div class="box-tools pull-right">
        <div class="btn-group">
                    <!-- <button type="button" class="btn btn-box-tool dropdown-toogle" data-toggle="dropdown"> -->
                        <a target="_blank" href="<?php echo base_url()."pdf/student_viewer_registration_print2/".$student['intID'] ."/". $active_sem['intID']; ?>"><i class ="fa fa-download"> </i> Download COR </a>
                    <!-- </button> -->
    
        </div>
    </div>
    <div style="clear:both"></div>
   
    <!-- <div class="box box-solid box-warning"> -->
    <div class="box box-warning">
          <div class="box-header">
            <h3 class="box-title"><?php echo 'Courses Enrolled - A.Y. ' . $active_sem['strYearStart']."-".$active_sem['strYearEnd'] . " " .  $active_sem['enumSem']." ".$term_type." "; ?></h3>
            <!-- <div class="box-tools pull-right">
                <div class="btn-group">
                    <button type="button" class="btn btn-box-tool dropdown-toogle" data-toggle="dropdown">
                        <i class ="fa fa-print"> </i></button>
                        <ul class="dropdown-menu" role="menu" style="font-weight:normal;text-transform:Capitalize;text-align:left;">
                            <li><a target="_blank" href="<?php echo base_url()."pdf/student_viewer_registration_print2/".$student['intID'] ."/". $active_sem['intID']; ?>">Print</a></li>
                        </ul>
                </div>
            </div> -->
            
        </div>
        
        <?php if ($reg_status =="For Advising"):  { ?>
            <div class="box-body table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr> 
                        <td style="text-align:center;font-style:italic;">No data available</td>
                    </tr>
                </thead>
            </table>
        </div>
        <?php } ?>
        <?php elseif ($reg_status =="For Registration"):  { ?>
        <div class="box-body table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr> 
                        <td style="text-align:center;font-style:italic;">No data available</td>
                    </tr>
                </thead>
            </table>
        </div>
        <?php } ?>
        <?php elseif ($registration['intROG'] ==0):  { ?>
        <div class="box-body table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr> 
                        <td style="text-align:center;font-style:italic;">No data available</td>
                    </tr>
                </thead>
            </table>
        </div>
        <?php } ?>
        <?php else:  ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th width="13%">Section</th>
                        <th width="10%"> Course Code</th>
                        <th>Course Title</th>
                        <th style="text-align: center;">Units</th>
                        <th>Faculty</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                     $units = 0;
                     $totalUnits = 0;
                     $totalLab = 0;
                     $totalLec = 0;
                     $lec = 0;
                     $lecForLab = 0;
                     $totalNoSubjects = 0;
                     $noOfSubjs = 0;
                    foreach($records as $record): 
                        $noOfSubjs++;
                        $units += $record['strUnits'];
                            if($record['intLab']  > 0)
                            {
                                $totalLab += ceil($record['intLab']/3);
                            }

                            $lecForLab = $totalLab * 2;
                            $lec = $units - $lecForLab;
                            $totalLec = $totalLab + $lec;
                    ?>

                    <tr>
                        <td><?php echo $record['strSection']; ?></td>
                        <td><?php echo $record['strCode']; ?></td>
						<td><?php echo $record['strDescription']; ?></td>
                        <td style="text-align: center;"><?php echo $record['strUnits']?>
                        <td><?php if($record['strFirstname']!="unassigned"){
                                    $firstNameInitial = substr($record['strFirstname'], 0,1);
                                    echo $firstNameInitial.". ".$record['strLastname'];  
                                  }
                                  else echo "unassigned";  ?>
                        </td>
                    <?php endforeach; ?>
                        </tr>
                </tbody>
            </table>
        </div>
        
    </div>
    <?php endif; ?>
    <div class="box box-primary">
        <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <td><strong>Courses:</strong></td> <td><?php echo $noOfSubjs; ?></td>
                            <td><strong>Lec. Units:</strong></td><td><?php echo $totalLec; ?></td>
                            <td><strong>Lab Units:</strong></td><td><?php echo $totalLab; ?></td>
                            <td><strong>Total Credits:</strong></td><td><?php echo $units; ?></td>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
                

        </div>
    </div>

<div class="modal fade" id="modal-default" style="display:none;" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content"> 
        <?php if ($reg_status == "For Advising"): ?>
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">x</button>
                        <h3 class="modal-title"><i class="fa fa-warning"></i> iACADEMY Student Portal</h3>
                    </div>
                    <div class="modal-body">
                        <p>No courses / subjects advised. Please contact your department chairman for the advising of courses / subjects.</p>
                    </div>
                <?php elseif($reg_status == "For Registration"):?>
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">x</button>
                        <h3 class="modal-title"><i class="fa fa-info"></i> iACADEMY Student Portal</h3>
                    </div>
                    <div class="modal-body">
                        <p>Your courses / subjects have been advised. Please wait for the registrar to register your courses / subjects.
                    </div>
                    <?php elseif($reg_status == "Registered"):?>
                        <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">x</button>
                        <h3 class="modal-title"><i class="fa fa-check"></i> iACADEMY Student Portal</h3>
                    </div>
                    <div class="modal-body">
                        <p>Your courses / subjects have been registered. To view your courses / subjects, please wait for the accounting office to tag you as enrolled.
                    </div>
                <?php endif; ?>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary pull-right" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>

</div>

