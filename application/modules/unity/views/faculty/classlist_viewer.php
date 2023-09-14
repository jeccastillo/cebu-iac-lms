<aside class="right-side">
<section class="content-header">
                    <h1>
                        Classlist <?php echo ($classlist['intFinalized'] == 2)?"(Finalized)":""; ?>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="<?php echo base_url(); ?>unity/view_classlist"><i class="ion ion-android-book"></i> Classlist</a></li>
                        <li class="active">Classlist Info</li>
                    </ol>
                </section>
<section class="content">
    <div class="alert alert-danger <?php echo ($alert == "" )?'hide':''; ?>  alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h4><i class="icon fa fa-ban"></i> Alert!</h4>
                <?php echo $alert; ?>
            </div>
    <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#tab_1" data-toggle="tab">Class Data</a></li>                    
                    <?php if(($is_super_admin || $is_registrar) && $showall): ?>
                        <li class="pull-right"><a  href="<?php echo base_url() ?>unity/classlist_viewer/<?php echo $classlist['intID']; ?>/0" class="text-muted"><i class="fa fa-check"></i> Hide Enlisted</a></li>
                    <?php else: ?>
                        <li class="pull-right"><a  href="<?php echo base_url() ?>unity/classlist_viewer/<?php echo $classlist['intID']; ?>/1" class="text-muted"><i class="fa fa-check"></i> Show All</a></li>
                    <?php endif; ?>
                    <?php if($is_super_admin || $is_registrar): ?>   
                        <li class="pull-right"><a  href="<?php echo base_url() ?>unity/edit_classlist/<?php echo $classlist['intID']; ?>" class="text-muted"><i class="fa fa-gear"></i> Edit</a></li>                    
                        <li class="pull-right"><a href="<?php echo base_url() ?>excel/download_classlist/<?php echo $classlist['intID']."/".$showall; ?>" class="text-muted"><i class="fa fa-table"></i> Download Spreadsheet</a></li>
                    <?php endif; ?>
                    <li class="pull-right"><a target="_blank" href="<?php echo base_url() ?>pdf/print_classlist_registrar/<?php echo $classlist['intID']; ?>/front" class="text-muted"><i class="fa fa-print"></i>PDF Report</a></li>
                    <!-- <li class="pull-right"><a href="<?php echo base_url() ?>pdf/print_classlist_registrar/<?php echo $classlist['intID']; ?>/back" class="text-muted"><i class="fa fa-print"></i> CL Report (back) </a></li> -->
                    <!-- <li class="pull-right"><a href="#" id="addStudentModal" class="text-muted"><i class="fa fa-plus"></i> Add Student</a></li> -->
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="tab_1">
                        <div class="box">
                            <div class="overlay" style="display:none;"></div>
                            <div class="loading-img" style="display:none;"></div>
                            <div class="box-header">
                            <h3 class="box-title"><?php echo $classlist['strCode'].' - '.$classlist['strClassName'].' '.$classlist['year'].$classlist['strSection'].' '.$classlist['sub_section']; ?> <small><?php echo $classlist['enumSem']." ".$term_type." ".$classlist['strYearStart']."-".$classlist['strYearEnd']; ?></small></h3>
                            </div>
                            
                            <table class="table table-striped">
                                <?php if($is_super_admin): ?>    
                                <th></th>
                                <?else: ?>
                                <?php endif; ?>
                                <th></th>
                                <th>Name</th>
                                <th>Program</th>                                
                                <th>MIDTERM GRADE</th>
                                <th>FINAL GRADE</th>                                                                
                                <!-- <th>Status</th> -->
                                <th>Remarks</th>
                                <th>Enrolled</th>
                                <?php if($classlist['intFinalized']<3 ): ?>
                                <?php if($is_super_admin): ?>
                                    <th>Delete</th>
                                    <?else: ?>
                                    <?php endif; ?>
                                <?php
                                endif;
                                $ctr = 1;
                                
                            foreach($students as $student):
                                //print_r($student);
                                if($showall || !empty($student['registered'])):
                                ?>
                                <tr>                                    
                                <?php if($is_super_admin): ?> 
                                    <td><input type="checkbox" class="student-select minimal" value="<?php echo $student['intID']; ?>" /></td>
                                <?else: ?>
                                <?php endif; ?>
                                
                                    <td><?php echo $ctr; ?>.</td>
                                    <td><a href="<?php echo base_url(); ?>unity/student_viewer/<?php echo $student['intID'] ?>"><?php echo $student['strLastname'].", ".$student['strFirstname']." "; echo isset($student['strMiddlename'][0])?" ".$student['strMiddlename'][0].".":''; ?></a></td>
                                    <td><?php echo $student['strProgramCode']; ?></td>
                                    <!--  newly added ^_^ 4-22-2016            -->
                                    <!--td><input <?php echo ($classlist['intFinalized']==1 || !$is_super_admin)?'disabled':''; ?> type="text" value="<?php echo $student['strStudentNumber']; ?>" name="strStudentNumber" class="studNumInput form-control" id="strStudentNumber" placeholder="Enter Stud-Number" size="3" rel="<?php echo $student['intID'] ?>"></td-->
                                    
                                        <?php if(!empty($student['registered'])): ?>                                       
                                        <td>        
                                            <?php if(($student['floatMidtermGrade'] == "OW" || $student['floatFinalGrade'] == "OW" || $classlist['intFinalized'] >= 1 || ($classlist['midterm_start'] <= date("Y-m-d") && $classlist['midterm_end'] >= date("Y-m-d")) != 'active')  && !$is_super_admin):                                                     
                                                    echo $student['floatMidtermGrade']?$student['floatMidtermGrade']:"NGS";
                                                ?>                                                
                                            <?php else: ?>                                                                            
                                            <select id="inputMidtermID-<?php echo $student['intCSID']; ?>"class="midtermInput grade-input form-control" rel="<?php echo $student['intCSID'] ?>" value="<?php echo $student['floatMidtermGrade']; ?>">                              
                                                <option value="NGS">NGS</option>
                                                <?php foreach($grading_items_midterm as $grading_item): ?>
                                                    <option <?php echo $student['floatMidtermGrade'] == $grading_item['value']?'selected':''; ?> value="<?php echo $grading_item['value'].'-'.$grading_item['remarks'] ; ?>"><?php echo $grading_item['value']; ?></option>
                                                <?php endforeach; ?>                                                                                                
                                            </select>
                                            <?php endif; ?>
                                        </td>
                                        <td>   
                                        <?php if(($student['floatMidtermGrade'] == "OW" || $student['floatFinalGrade'] == "OW" || $classlist['intFinalized'] >= 2 || ($classlist['final_start'] <= date("Y-m-d") && $classlist['final_end'] >= date("Y-m-d")) != 'active')  && !$is_super_admin): 
                                                    echo $student['floatFinalGrade']?$student['floatFinalGrade']:"NGS";
                                                ?>                                                
                                            <?php else: ?>                                                                                                                     
                                            <select id="inputFinalsID-<?php echo $student['intCSID']; ?>"class="finalsInput grade-input form-control" rel="<?php echo $student['intCSID'] ?>">                              
                                                <option value="NGS">NGS</option>
                                                <?php foreach($grading_items as $grading_item): ?>
                                                    <option <?php echo $student['floatFinalGrade'] == $grading_item['value']?'selected':''; ?> value="<?php echo $grading_item['value'].'-'.$grading_item['remarks'] ; ?>"><?php echo $grading_item['value']; ?></option>
                                                <?php endforeach; ?>                                               
                                            </select>      
                                            <?php endif; ?>                                      
                                        </td>                                        
                                        <?php else: ?>                
                                            <td></td>
                                            <td></td>
                                        <?php endif; ?>                                    
                                    <!-- <td>
                                        <?php //if(!empty($student['registered'])): ?>
                                            <input type="hidden"  id="gradeStat-<?php echo $student['intCSID'] ?>" class="studentStatus form-control" rel="<?php echo $student['intCSID'] ?>" value="act" />                                           
                                        <?php //endif; ?>
                                    </td> -->
                                                                        
                                    <td><textarea rows="1" cols="10" style="resize: none;font-weight:bold;" disabled="disabled" <?php echo ($classlist['intFinalized'] < 3)?'':'disabled'; ?>  rel="<?php echo $student['intCSID'] ?>" class="remarks" id="rem-<?php echo $student['intCSID']; ?>"><?php echo ($student['strRemarks']!="")?$student['strRemarks']:getRemarks($student['floatFinalGrade']); ?></textarea>
                                   </td>
                                    <td style="text-align:center;">
                                        <?php echo (empty($student['registered']))?'<span style="color:#777;">no</span>':'<span style="color:#009000;">yes</span>'; ?>
                                    </td>
                                    <?php if($classlist['intFinalized'] < 3): ?>
                                        <?php if($is_super_admin): ?>
                                            <td style="text-align:center;">
                                                <a class="trash-student" rel="<?php echo $student['intCSID'] ?>"><i class="fa fa-times"></i></a>  
                                            </td>
                                        <?php else: ?>
                                            <?php endif; ?>        
                                    <?php endif; ?>

                                </tr>
                            <?php 
                                $ctr++; 
                                        endif;       
                            endforeach; ?>
                            </table>
                            <?php if($classlist['intFinalized'] < 3): ?>
                            <div class="row">
                                <div class="col-sm-4">
                                    <select id="transfer-to" class="form-control">
                                        <?php foreach($cl as $c): ?>
                                            <option value="<?php echo $c['intID']; ?>"><?php echo $c['strClassName']." ".$c['year'].$c['strSection']." ".$c['sub_section']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-sm-8">
                                    <a href="#" id="transfer-classlist" 
                                    
                                    <?php
                                        if($classlist['intFinalized'] > 0) {                                            
                                            echo 'disabled';
                                        }
                                     ?>

                                    class="btn btn-warning"><i class="fa fa-arrow-left"></i> Transfer Selected To</a>
                                     <a href="#" id="view-classlist" class="btn btn-primary"><i class="fa fa-arrow-right"></i> View Classlist</a>
                                     <?php 
                                        $label = "Submit"; 
                                        if  ($classlist['intFinalized'] == 0) {
                                            $label = "Submit Midterm Grades";
                                            if($classlist['midterm_start'] <= date("Y-m-d") && $classlist['midterm_end'] >= date("Y-m-d")){
                                                    $disable_submit =  '';                                                    
                                                }
                                                else
                                                    $disable_submit =  'disabled';

                                        }
                                        else if  ($classlist['intFinalized'] == 1) {
                                            $label = "Submit Final Grades";
                                            if($classlist['final_start'] <= date("Y-m-d") && $classlist['final_end'] >= date("Y-m-d")){
                                                    $disable_submit =  '';                                                
                                            }
                                                else
                                                    $disable_submit =  'disabled';

                                        }                                        
                                 
                                    ?>
                                    <?php if($classlist['intFinalized'] < 2): ?>
                                    <a href="#" data-csid="<?php echo $classlist['intID']; ?>" rel="<?php echo $classlist['intFinalized']; ?>" id="finalize-term" class="btn btn-success <?php echo $disable_submit; ?>">
                                    <i class="fa fa-arrow-right"></i> <?php echo $label; ?></a>
                                    <?php endif; ?>
                                    
                                </div>
                            </div>
                            <?php endif; ?>                                                     
                        </div>
                    </div><!-- /.tab-pane -->                    
                </div><!-- /.tab-content -->
            </div>
    
    
         
</section>
</aside>