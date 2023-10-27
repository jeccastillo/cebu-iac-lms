<?php 
    error_reporting(0);
?>

<aside class="right-side">
<section class="content-header">
            <h1>
                        My Grades
                        <small>view your grades information</small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="<?php echo base_url() ?>portal/dashboard"><i class="fa fa-home"></i> Home</a></li>
                        <li class="active">My Grades</li>
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
<div class="content">
<input type="hidden" id="regStat" value="<?php echo $reg_status;?>"/>
<?php if ($reg_status =="For Subject Enlistment"):  { ?>
    <!-- <div class="alert alert-info alert-dismissible"> -->
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
              <p><strong>Student Number: </strong><?php echo preg_replace("/[^a-zA-Z0-9]+/", "", $student['strStudentNumber']); ?></p>
              <p><strong>Year Level: </strong><?php echo $academic_standing['year']; ?></p>
              <p><strong>Academic Status: </strong><?php echo $academic_standing['status']; ?></p>
              <p><strong>Enrollment Status: </strong><?php echo $reg_status; ?></p>
            </div>
            <div style="clear:both"></div>
        </div>
       
    </div>    
    <!-- <div class="box box-solid box-warning"> -->
    <div class="box box-warning">
          <div class="box-header">
            <h3 class="box-title" ><?php echo 'Grades - A.Y. ' . $active_sem['strYearStart']."-".$active_sem['strYearEnd'] . " " .  $active_sem['enumSem']." ".$term_type." "; ?></h3>            
         </div>
        
        <?php if ($reg_status =="For Subject Enlistment"):  { ?>
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
                        <th >Course Title</th>
                        <th style="text-align: center;">Units</th>                        
                        <th>Midterm</th>                        
                        <th>Final Grade</th>                                                
						<th>Remarks</th>
                        <th>Faculty</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $totalUnits = 0;
                    $countBridg = 0;
                    foreach($records as $record): ?>
                    <tr>                        
                        <td><?php echo $record['strClassName'].$record['year'].$record['strSection']." ".$record['sub_section']; ?></td>
                        <td><?php echo $record['strCode']; ?></td>
						<td><?php echo $record['strDescription']; ?></td>
                        <td style="text-align: center;"><?php echo $record['strUnits']?>                                                
                        <?php if($record['intFinalized'] >= 1 && $active_sem['viewing_midterm_start'] <= date("Y-m-d") && $active_sem['viewing_midterm_end'] > date("Y-m-d")): ?>                                
                            <td><strong><?php echo $record['v2']; ?></strong></td>
                        <?php else: ?>
                            <td>Not Yet Available</td>
                        <?php endif; ?>    
                        <?php if($record['intFinalized'] >= 2 && $active_sem['viewing_final_start'] <= date("Y-m-d") && $active_sem['viewing_final_end'] > date("Y-m-d")): ?>                                
                            <td><strong><?php echo $record['v3']; ?></strong></td>
                        <?php else: ?>
                            <td>Not Yet Available</td>
                        <?php endif; ?>                                                        
                        <td><?php echo ($record['intFinalized'] >= 1)?$record['strRemarks']:''; ?></td>                        
                        <td><?php if($record['strFirstname']!="unassigned"){
                                    $firstNameInitial = substr($record['strFirstname'], 0,1);
                                    echo $firstNameInitial.". ".$record['strLastname'];  
                                  }
                                  else echo "unassigned";  ?>
                        </td>
                        
                    </tr>
                    <?php endforeach; ?>
                       
                        
        
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    

<div class="modal fade" id="modal-default" style="display:none;" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content"> 
           
                <?php if ($reg_status == "For Subject Enlistment"): ?>
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

