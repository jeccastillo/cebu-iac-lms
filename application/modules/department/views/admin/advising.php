<?php 
    error_reporting(0);
?>
<style>
    .green-bg
    {
        background-color:#77cc77;
    }
    .red-bg
    {
        background-color:#cc7777;
    }
</style>
<aside class="right-side">
<section class="content-header">
                    <h1>
                        <small>
                            <a class="btn btn-app" href="<?php echo base_url() ?>department/subject_loading" ><i class="ion ion-arrow-left-a"></i>Select Student</a>  
                            <a class="btn btn-app" href="<?php echo base_url()."unity/student_viewer/".$student['intID']; ?>"><i class="ion ion-eye"></i> View</a> 
                        
                             <a class="btn btn-app" target="_blank" href="<?php echo base_url()."pdf/student_viewer_advising_print/".$student['intID'] ."/". $active_sem['intID']; ?>">
                                <i class="ion ion-printer"></i>Print Subjects</a> 
                            
                            <a class="btn btn-app" data-toggle="modal" data-target="#curriculumModal" href="#"><i class="ion ion-clipboard"></i> Curriculum Outline</a> 
                            
                            <a target="_blank" class="btn btn-app" href="<?php echo base_url()."pdf/print_curriculum/".$student['intCurriculumID']."/".$student['intID'] ?>"><i class="fa fa-print"></i> Print Curriculum Outline</a> 
                            
                        </small>
                    </h1>
                    
                  
                </section>
<div class="content">
    <input type="hidden" value="<?php echo $student['intID'] ?>" id="student-id" />
    <input type="hidden" value="<?php echo $student['intCurriculumID'] ?>" id="curriculum-id" />
    <input type="hidden" value="<?php echo $active_sem['intID']; ?>" id="active-sem-id" />
    <input type="hidden" value="<?php echo $academic_standing['year']; ?>" id="academic-standing">
    <input type="hidden" value="<?php echo $academic_standing['status']; ?>" id="academic-standing-stat">
    <input type="hidden" value="<?php echo switch_num_rev($active_sem['enumSem']); ?>" id="active-sem" >        
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
                <li><a href="#" style="font-size:13px;">Academic Standing <span class="pull-right text-blue"><?php echo switch_num($academic_standing['year']); ?> Year / <?php echo $academic_standing['status']; ?></span></a></li>
                   <li><a href="#" style="font-size:13px;">Curriculum <span class="pull-right text-blue"><?php echo $student['strName']; ?></span></a></li>
                <li><a style="font-size:13px;" href="#">Registration Status <span class="pull-right"><?php echo $reg_status; ?></span></a></li>
                  <li><a style="font-size:13px;" href="#">Scholarship Type <span class="pull-right"><?php echo $student['enumScholarship']; ?></span></a></li>
                  
              </ul>
            </div>
          </div>
            
        </div>
        
    
        <div class="col-sm-9">
                <div class="box box-primary">
                    <div class="box-header">
                        <h3>Enlistment of Subjects</h3>
                        <h4 class="text-center">Currently processing: <?php echo $active_sem['enumSem']." ".$term_type." ".$active_sem['strYearStart']."-".$active_sem['strYearEnd'];  ?></h4>
                    </div>
                    <div class="box-body">
                    <?php if(isset($prev_records)): ?>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th colspan="5" class="text-center">
                                        Previous Record
                                    </th>
                                </tr>
                                <tr>
                                    <th colspan="5">
                                        <?php echo $prev_sem['enumSem']." Sem ".$prev_sem['strYearStart']."-".$prev_sem['strYearEnd']; ?>
                                    </th>
                                </tr>
                                <tr>
                                    <th>Course Code</th>
                                    <th class="text-center">Units</th>
                                    <th class="text-center">Final Grade</th>
                                    <th>Remarks</th>
                                    <th>Faculty</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach($prev_records as $rec): ?>
                                    <tr class="<?php echo (strtoupper($rec['strRemarks'])=="PASSED")?'bg-green':''; ?>">
                                        <td><?php echo $rec['strCode']; ?></td>
                                        <td class="text-center"><?php echo $rec['strUnits']; ?></td>
                                        <td class="text-center"><?php echo number_format($rec['v3'], 2, ".", ","); ?></td>
                                        <td><?php echo $rec['strRemarks']; ?></td>
                                        <td><?php echo $rec['strFirstname']." ".$rec['strLastname']; ?></td>
                                    </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                            
                       <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-5">
                                <h4>Suggested Subjects</h4>
                                <select style="height:300px" class="form-control" id="subject-selector" multiple>
                                    <?php foreach($subjects_not_taken as $sn): ?>
                                        <option value="<?php echo $sn['intID']; ?>"><?php echo $sn['strCode']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <br /><br />
                                <a href="#" class="btn btn-default  btn-flat btn-block" id="autoload-advised">Autoload <br /> Subjects </a>
                                <a href="#" id="load-advised" class="btn btn-default  btn-flat btn-block">Load <i class="ion ion-arrow-right-c"></i> </a>
                                <a href="#" id="unload-advised" class="btn btn-default  btn-flat btn-block"><i class="ion ion-arrow-left-c"></i> Remove</a>
                                <a href="#" id="save-advised" class="btn btn-default  btn-flat btn-block">Save</a>
                                
                            </div>
                            <div class="col-md-5">
                                <h4>Subject Load</h4>
                                <select style="height:300px" class="form-control" id="advised-subjects" multiple>
                                    <?php foreach($advised_subjects as $sn): ?>
                                        <option value="<?php echo $sn['intSubjectID']; ?>"><?php echo $sn['strCode']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
              
            </div>
            <!-- /.tab-content -->
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
