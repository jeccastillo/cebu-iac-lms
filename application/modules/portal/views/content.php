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
    <div class="row">
        <div class="col-sm-12 text-right">
            <a href="#" data-toggle="modal" data-target="#curriculumModal">
                <i class="fa fa-file"></i>
                Curriculum Outline
            </a>
        </div>
    </div>
    <!-- <div class="box box-solid box-warning"> -->
    <div class="box box-warning">
          <div class="box-header">
            <h3 class="box-title" ><?php echo 'Grades - A.Y. ' . $active_sem['strYearStart']."-".$active_sem['strYearEnd'] . " " .  $active_sem['enumSem']." ".$term_type." "; ?></h3>

            <div class="box-tools pull-right">
                <div class="btn-group">
                    <button type="button" class="btn btn-box-tool dropdown-toogle" data-toggle="dropdown">
                        <i class ="fa fa-wrench"> </i></button>
                        <ul class="dropdown-menu" role="menu" style="font-weight:normal;text-transform:Capitalize;text-align:left;">
                            <li><a href="#" id="gradeToggle">Hide Breakdown</a></li>
                            <li><a target="_blank" href="<?php echo base_url()."pdf/student_viewer_rog_print/".$student['intID'] ."/". $active_sem['intID']; ?>">Print</a></li>
                        </ul>
                </div>
            </div>
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
                        <th class="g-header">M</th>                        
                        <th class="g-header">FG</th>                        
                        <th></th>
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
                        <?php if($record['intFinalized'] == 1): ?>                                
                            <td><strong><?php echo $record['v2']; ?></strong></td>
                        <?php else: ?>
                            <td>NGS</td>
                        <?php endif; ?>    
                        <?php if($record['intFinalized'] == 2): ?>                                
                            <td><strong><?php echo $record['v3']; ?></strong></td>
                        <?php else: ?>
                            <td>NGS</td>
                        <?php endif; ?>
                            <td>
                            <?php
                                $totalSub++;
                                $totalFinalized += $record['intFinalized']; 
                                if($record['v3'] != 3.50 && $record['v3'] != "0" )
                                
                                {

                                    
                                    if ($record['intBridging'] == 1){
                                        $countBridg  = $countBridg + $record['intBridging'];
                                        //$num_of_bridging = count($record['intBridging']);
                                        if ($record['intFinalized'] != 3) {
                                            $totalUnits = 0; 
                                        }
                                        else {
                                            $totalUnits += $record['strUnits'];
                                            $totalUnits-=3;
                                        }
                                    }
                                    else{
                                        $product = $record['strUnits'] * $record['v3']; 
                                        $products[] = $product;
                                        if ($record['intFinalized'] != 3) {
                                            $totalUnits = 0;
                                           
                                        }
                                        else {
                                            $totalUnits += $record['strUnits'];
                                              
                                        }
                                        
                                    }     
                                    /*
                                    //$productArray = array();
                                    $product = $record['strUnits'] * $record['v3']; 
                                    $products[] = $product;
                                    //echo $product
                                    $totalUnits += $record['strUnits'];*/
                                }
                            ?>
                            </td>
						<?php if($record['v3'] == 5.00): 
                        ?>  
                        <td><span class="text-red"><?php echo ($record['intFinalized'] == 3)?$record['strRemarks']:''; ?></span></td>
                        <?php else: ?>
                        <td><?php echo ($record['intFinalized'] == 3)?$record['strRemarks']:''; ?></td>
                        <?php endif; ?>
                        
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

    
<div class="box box-primary">
        <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr> 
                            <td><strong>UNITS CREDITED:</strong></td> <td>
                                <?php 
                                    if (($totalSub*3)== $totalFinalized) {
                                        echo $totalUnits;
                                    }
                                    else {
                                        echo '--';
                                }
                                ?></td>
                            <td><strong>GPA:</strong></td>
                            <td>
                                <?php
                                   if (($totalSub*3) == $totalFinalized) {
                                    $gpa = round(array_sum($products) / $totalUnits, 2) ;
                                    echo $gpa;
                                }
                                else {
                                    echo '--';
                                }
                                ?>
                            <input type="hidden" id="totalSub" value="<?php echo $totalSub*3;?> ">
                           <input type="hidden" id="totalFinalized" value="<?php echo $totalFinalized;?> ">    
                            </td>

                        
                        </tr>
                      
                        <?php if($countBridg > 0): ?>
                            <td colspan="4" style="font-style:italic;font-size:13px;"><small>Note: (<?php echo $countBridg; ?>) Bridging course/s - not computed in units & GPA.</small></td>
                            <?php endif; ?> 
                        </tr>
                           
                    </thead>
                    <tbody>
                    </tbody>
                </table>
                

        </div>
    </div>

</div>

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


<div class="modal fade" id="curriculumModal" tabindex="-1" role="dialog" aria-labelledby="curriculumModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h3 class="modal-title"><?php echo $student['strName']; ?></h3>
      </div>
      <div class="modal-body">
            <?php 
            $prev_year_sem = '0';
            for($i = 0;$i<count($curriculum_subjects); $i++): 
            $key = array_search($curriculum_subjects[$i]['strCode'], array_column($grades, 'strCode'));
            //echo $prev_year_sem."<br />";

            ?>
            <?php if($prev_year_sem != $curriculum_subjects[$i]['intYearLevel']."_".$curriculum_subjects[$i]['intSem']): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th colspan="4">
                            <?php echo switch_num($curriculum_subjects[$i]['intYearLevel'])." Year ".switch_num($curriculum_subjects[$i]['intSem'])." Sem"; ?>
                        </th>
                    </tr>
                    <tr>
                        <th>Course Code</th>
                        <th>Course Title</th>
                        <th>Units</th>
                        <!-- <th>A.Y.</th>
                        <th>Sem.</th> -->
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody>

            <?php 

                    endif; 
                    $prev_year_sem = $curriculum_subjects[$i]['intYearLevel']."_".$curriculum_subjects[$i]['intSem'];
                    ?>
            <tr style="<?php echo ($key && $grades[$key]['floatFinalGrade'] <= "3" && $grades[$key]['floatFinalGrade'] != "0")?'color:green;':(($key && $grades[$key]['floatFinalGrade'] == "3.5")?'color:#995c00;':(($key && $grades[$key]['floatFinalGrade'] == "0")?'color:blue;':'color:gray;')); ?>;">
                <td><?php echo $curriculum_subjects[$i]['strCode']; ?></td>
                <td>
                    <?php echo $curriculum_subjects[$i]['strDescription']; ?>
                </td>
                <td style="text-align: center;">
                    <?php echo $curriculum_subjects[$i]['strUnits']; ?> 
                </td>
                <!-- <td> <?php echo substr($grades[$key]['strYearStart'], 2) . substr($grades[$key]['strYearEnd'], 2)?></td>
                <td><?php echo $grades[$key]['enumSem'];?>  </td> -->
                <td>
                     <?php echo ($key)?number_format($grades[$key]['floatFinalGrade'], 2, ".", ","):'NR'; ?>
                </td>
            </tr>
        <?php if($prev_year_sem != $curriculum_subjects[$i+1]['intYearLevel']."_".$curriculum_subjects[$i+1]['intSem'] || count($curriculum_subjects) == $i+1): ?>   

            </tbody>
        </table>
        <?php endif; ?>
        <?php endfor; ?>
        <hr />
        <small>
        <div class="legend"><span class="text-bold">Legend: </span>
        <span class="holder" style="padding-right: 15px;"><span class="legend normal" style="border: solid 1px; background-color:gray;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> - not yet taken</span>
        
        <span class="holder" style="padding-right: 15px;"><span class="legend passed" style="border: solid 1px; background-color:green;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> - passed subject</span>
        
        <span class="holder" style="padding-right: 15px;"><span class="legend currently-enrolled" style="border: solid 1px; background-color:blue;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> - currently enrolled</span>
        
        <span class="holder" style="padding-right: 15px;"><span class="legend incomplete" style="border: solid 1px; background-color:#995c00;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> - incomplete</span></div></small>
        <hr />
        <h3>Equivalent Courses</h3>
        <p style="font-style:italic;"><small>Note: The following are courses with different course code from the old curriculum but with same course title with your curriculum.</small></p>
        <hr />
        <?php 
            $prev_year_sem = '0';
            for($i = 0;$i<count($equivalent_subjects); $i++): 
            $key = array_search($equivalent_subjects[$i]['strCode'], array_column($grades, 'strCode'));            
            $key2 = array_search($equivalent_subjects[$i]['mainSubjectID'], array_column($curriculum_subjects,'intSubjectID'));            
            //echo $prev_year_sem."<br />";

            ?>
            <?php if($prev_year_sem != $equivalent_subjects[$i]['intYearLevel']."_".$equivalent_subjects[$i]['intSem']): ?>
            <table class="table table-bordered">
                <thead>
                    <tr >
                        <th colspan="5">
                            <?php echo switch_num($equivalent_subjects[$i]['intYearLevel'])." Year ".switch_num($equivalent_subjects[$i]['intSem'])." Sem"; ?>
                        </th>
                    </tr>
                    <tr>
                        <th>Course Code</th>
                        <th>Course Title</th>
                        <th>Units</th>
                        <th>Main</th>
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody>

            <?php 

                    endif; 
                    $prev_year_sem = $equivalent_subjects[$i]['intYearLevel']."_".$equivalent_subjects[$i]['intSem'];
                    ?>
            <tr style="<?php echo ($key && $grades[$key]['floatFinalGrade'] <= "3" && $grades[$key]['floatFinalGrade'] != "0")?'color:green;':(($key && $grades[$key]['floatFinalGrade'] == "3.5")?'color:#995c00;':(($key && $grades[$key]['floatFinalGrade'] == "0")?'color:blue;':'color:gray;')); ?>;">
                <td><?php echo $equivalent_subjects[$i]['strCode']; ?></td>
                <td>
                    <?php echo $equivalent_subjects[$i]['strDescription']; ?>
                </td>
                <td>
                    <?php echo $equivalent_subjects[$i]['strUnits']; ?> 
                </td>
                <td>
                <?php echo ($key2)?$curriculum_subjects[$key2]['strCode']:'None'; ?>
                </td>
                <td>
                     <?php echo ($key)?number_format($grades[$key]['floatFinalGrade'], 2, ".", ","):'NR'; ?>
                </td>
            </tr>
        <?php if($prev_year_sem != $equivalent_subjects[$i+1]['intYearLevel']."_".$equivalent_subjects[$i+1]['intSem'] || count($equivalent_subjects) == $i+1): ?>   

            </tbody>
        </table>
        <?php endif; ?>
        <?php endfor; ?>
      </div>
      
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->