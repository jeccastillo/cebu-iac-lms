<aside class="right-side">
<section class="content-header">
                    <h1>
                        Classlist
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
                    <li><a href="#tab_2" data-toggle="tab">Subject Info</a></li>
                    <li><a href="#tab_4" data-toggle="tab"><i class="fa fa-calendar"></i> Section Schedule</a></li>
                    <!-- <li><a href="#tab_3" data-toggle="tab"><i class="fa fa-table"></i> Upload Data</a></li> -->
                    <li><a href="#tab_5" data-toggle="tab">Incompletes</a></li>
                    <!--li><a href="#tab_3" data-toggle="tab">More Class Data</a></li-->
                    <!--li><a href="#tab_3" data-toggle="tab">Quiz Record</a></li-->
                    <li class="pull-right"><a href="<?php echo base_url() ?>unity/edit_classlist/<?php echo $classlist['intID']; ?>" class="text-muted"><i class="fa fa-gear"></i> Edit</a></li>
                    <li class="pull-right"><a href="<?php echo base_url() ?>excel/download_classlist/<?php echo $classlist['intID']; ?>" class="text-muted"><i class="fa fa-table"></i> Download Spreadsheet</a></li>
                    <li class="pull-right"><a href="<?php echo base_url() ?>pdf/print_classlist_registrar/<?php echo $classlist['intID']; ?>/front" class="text-muted"><i class="fa fa-print"></i> CL Report (front) </a></li>
                    <li class="pull-right"><a href="<?php echo base_url() ?>pdf/print_classlist_registrar/<?php echo $classlist['intID']; ?>/back" class="text-muted"><i class="fa fa-print"></i> CL Report (back) </a></li>
                    <li class="pull-right hide"><a href="#" id="addStudentModal" class="text-muted"><i class="fa fa-plus"></i> Add Student</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="tab_1">
                        <div class="box">
                            <div class="overlay" style="display:none;"></div>
                            <div class="loading-img" style="display:none;"></div>
                            <div class="box-header">
                                    <h3 class="box-title"><?php echo $classlist['strClassName'].'-'.$classlist['strSection']; ?> <small><?php echo $classlist['enumSem']." ".$term_type." ".$classlist['strYearStart']."-".$classlist['strYearEnd']; ?></small></h3>
                            </div>
                            
                            <table class="table table-striped">
                            <?php if($is_super_admin): ?>    
                                <th></th>
                                <?else: ?>
                                <?php endif; ?>
                                <th></th>
                                <th>Name</th>
                                <th>Program</th>
                                <?php if($classlist['is_numerical']): ?>
                                <th>Prelim<br/>(30%)</th>
                                <th>Midterm<br/>(30%)</th>
                                <th>Finals<br/>(40%)</th>
                                <th>FINAL GRADE<br/>(100%)</th>
                                <th>Numerical Rating</th>
                                <?php endif; ?>
                                <th>Status</th>
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
                                    <?php if($classlist['is_numerical']): ?>
                                    <td>
                                        
                        <input type="number" min="50" max="100" <?php echo (($classlist['intFinalized'] > 0 || $student['enumStatus'] == "drp" || $student['enumStatus'] == "odrp" || $active_prelim_grading['enumGradingPeriod'] != 'active' || empty($student['registered']))  && !$is_super_admin)?'disabled':''; ?> id="inputPrelimID-<?php echo $student['intCSID']; ?>"class="prelimInput grade-input form-control" rel="<?php echo $student['intCSID'] ?>" value="<?php echo $student['floatPrelimGrade']; ?>">
                                    </td>
                                    <td>
                                        
                        <input type="number" min="50" max="100" <?php echo (($classlist['intFinalized'] == 0 || $classlist['intFinalized'] > 1 || $student['enumStatus'] == "drp" || $student['enumStatus'] == "odrp" || $active_midterm_grading['enumMGradingPeriod'] != 'active'|| empty($student['registered']))  && !$is_super_admin)?'disabled':''; ?> id="inputMidtermID-<?php echo $student['intCSID']; ?>"class="midtermInput grade-input form-control" rel="<?php echo $student['intCSID'] ?>" value="<?php echo $student['floatMidtermGrade']; ?>">

                                    </td>
                                    <td>
                                        
                        <input type="number" min="50" max="100" <?php echo (($classlist['intFinalized'] > 2 || $classlist['intFinalized'] <= 1 || $student['enumStatus'] == "drp" || $student['enumStatus'] == "odrp" ||  $student['enumStatus'] =="inc" || $active_finals_grading['enumFGradingPeriod'] != 'active'|| empty($student['registered']) )  && !$is_super_admin)?'disabled':''; ?> id="inputFinalsID-<?php echo $student['intCSID']; ?>"class="finalsInput grade-input form-control" rel="<?php echo $student['intCSID'] ?>" value="<?php echo $student['floatFinalsGrade']; ?>">
                                    </td>
                                    <td id="eq2-<?php echo $student['intCSID'] ?>">
                                        <?php 
                                            if ($student['enumStatus']=='drp'){
                                                echo "UD";
                                            }
                                            else if ($student['enumStatus']=='odrp'){
                                                echo "OD";
                                            }
                                            else if ($student['enumStatus']=='inc') {
                                                echo "inc";
                                            }
                                            else {
                                                echo number_format(getAve($student['floatPrelimGrade'],$student['floatMidtermGrade'],$student['floatFinalsGrade']), 2);
                                            }
                                         ?>
                                    </td>
                                    <td style="text-align:center;" id="eq-<?php echo $student['intCSID'] ?>"><?php echo ($student['enumStatus']!='odrp')? number_format($student['floatFinalGrade'], 2):'---' ?></td>
                                    <td>
                                    <select id="gradeStat-<?php echo $student['intCSID'] ?>" <?php echo (($classlist['intFinalized'] < 3 && ($student['enumStatus'] != "odrp" || $is_admin)) || $is_super_admin)?'':'disabled';  ?> 
                                    <?php 
                                     if($is_super_admin && $student['enumStatus'] =="drp")
                                        echo "";
                                     else if ($student['enumStatus'] =="drp") 
                                        echo "disabled";
                                    
                                    endif;
                                    ?>
                                    
                                    class="studentStatus form-control" rel="<?php echo $student['intCSID'] ?>">
                                            <option <?php echo ($student['enumStatus'] == "act")?'selected':''; ?> value="act">Active</option>
                                            <?php if($classlist['intFinalized'] == 2 || $is_super_admin || $student['enumStatus'] == "inc"): ?>
                                            <option <?php echo ($student['enumStatus'] == "inc")?'selected':''; ?> value="inc">Incomplete</option>
                                            <?php endif; ?>
                                            
                                            <option <?php echo ($student['enumStatus'] == "drp")?'selected':''; ?> value="drp">Unofficially Dropped</option>
                                            <?php if($is_admin): ?>
                                            <option <?php echo ($student['enumStatus'] == "odrp")?'selected':''; ?> value="odrp">Officially Dropped</option>                                            
                                            <?php endif; ?>
                                            <option <?php echo ($student['enumStatus'] == "passed")?'selected':''; ?> value="passed">Passed</option>
                                            <option <?php echo ($student['enumStatus'] == "failed")?'selected':''; ?> value="failed">Failed</option>
                                        </select>
                                    </td>
                                    
                                    
                                    <td><textarea rows="1" cols="10" style="resize: none;font-weight:bold;" disabled="disabled" <?php echo ($classlist['intFinalized'] < 3)?'':'disabled'; ?>  rel="<?php echo $student['intCSID'] ?>" class="remarks" id="rem-<?php echo $student['intCSID']; ?>"><?php echo ($student['strRemarks']!="")?$student['strRemarks']:getRemarks(getEquivalent($student['floatFinalGrade'])); ?></textarea>
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
                            endforeach; ?>
                            </table>
                            <?php if($classlist['intFinalized'] < 3): ?>
                            <div class="row">
                                <div class="col-sm-4">
                                    <select id="transfer-to" class="form-control">
                                        <?php foreach($cl as $c): ?>
                                            <option value="<?php echo $c['intID']; ?>"><?php echo $c['strSection']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-sm-8">
                                    <a href="#" id="transfer-classlist" class="btn btn-warning"><i class="fa fa-arrow-left"></i> Transfer Selected To</a>
                                     <a href="#" id="view-classlist" class="btn btn-primary"><i class="fa fa-arrow-right"></i> View Classlist</a>
                                    <a href="#" data-csid="<?php echo $classlist['intID']; ?>" rel="<?php echo $classlist['intFinalized']; ?>" id="finalize-term" class="btn btn-success <?php 
                                        if ($classlist['intFinalized'] == 0){
                                                if ($active_prelim_grading['enumGradingPeriod'])
                                                    echo '';
                                                else
                                                    echo 'disabled';
                                            }
                                        else if  ($classlist['intFinalized'] == 1) {
                                            if ($active_midterm_grading['enumMGradingPeriod'])
                                                    echo '';
                                                else
                                                    echo 'disabled';

                                        }
                                        else if  ($classlist['intFinalized'] == 2) {
                                            if ($active_finals_grading['enumFGradingPeriod'])
                                                    echo '';
                                                else
                                                    echo 'disabled';

                                        }
                                 
                                    ?>">
                                    <i class="fa fa-arrow-right"></i> Finalize Term</a>
                                    
                                </div>
                            </div>
                            <?php endif; ?>
                            <hr />
                             <div class="col-lg-4 col-xs-12">
                         <!-- Donut chart -->
                            <div class="box box-primary">
                                <div class="box-header">
                                    <i class="fa fa-bar-chart-o"></i>
<!--                                    <h3 class="box-title">Passing Percentage</h3>-->
                                    <h3 class="box-title">Summary Distribution</h3>
                                </div>
                                <div class="box-body">
                                    <div id="donut-chart" style="height: 300px;"></div>
                                </div><!-- /.box-body-->
                            </div><!-- /.box -->
                    
                        </div>
                            <div style="clear:both"></div>
                        </div>
                    </div><!-- /.tab-pane -->
                    <div class="tab-pane" id="tab_2">
                        <div class="box box-info">
                                <div class="box-header">
                                <h3 class="box-title"><?php echo $classlist['strClassName'].'-'.$classlist['strSection']; ?> <small><?php echo $classlist['enumSem']." ".$term_type." ".$classlist['strYearStart']."-".$classlist['strYearEnd']; ?></small></h3>
                                    <br />
                                    
                                </div>
                                <div class="box-body">
                                    <a class="btn btn-default  btn-flat" href="<?php echo base_url().'subject/subject_viewer/'.$subject['intID']; ?>">More Information on the Course</a><hr />
                                    Units: <code><?php echo $subject['strUnits']; ?></code>
                                    <p>
                                        <br />
                                        <?php echo $subject['strDescription']; ?>
                                    </p>
                                    <p>
                                        FACULTY: <?php echo $classlist['strLastname']." ".$classlist['strFirstname']; ?>
                                    </p>
                                    
                                    <p>
                                        <br />
                                        <h4 class="title">SUMMARY DISTRIBUTION</h4>
                                    <table class="table" style="margin: 0 9px;">
                                        <tr>
                                            <th>Grading Equivalent</th>
                                            <th>Number of Students</th>
                                            <th>Percentage</th>
                                        </tr>
                                        <tr>
                                            <td>1.00 - 1.75</td>
                                            <td><?php echo $lineOfOne; ?></td>
                                            <td>
                                                <?php 
                                                    $lineOfOnePE = $total == 0 ? '0' : round(($lineOfOne / $total) * 100, 2); 
                                                    echo $lineOfOnePE . " %"; 
                                                ?>
                                            </td>
                                            
                                        </tr>
                                        <tr>
                                            <td>2.00 - 2.75</td>
                                            <td><?php echo $lineOfTwo; ?></td>
                                            <td>
                                                <?php $lineOfTwoPE = $total == 0 ? '0' : round(($lineOfTwo / $total) * 100, 2); 
                                                    echo $lineOfTwoPE . " %"; 
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>3.00</td>
                                            <td><?php echo $lineOfThree; ?></td>
                                            <td>
                                                <?php $lineOfThreePE = $total == 0 ? '0' : round(($lineOfThree / $total) * 100, 2); 
                                                    echo $lineOfThreePE . " %"; 
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>5.00</td>
                                            <td><?php echo $totalFailed; ?></td>
                                            <td>
                                                <?php $lineOfFivePE = $total == 0 ? '0' : round(($totalFailed / $total) * 100, 2); 
                                                    echo $lineOfFivePE . " %"; 
                                                ?>
                                            
                                            </td>
                                        </tr>
                                    
                                        <tr>
                                            <td>INCOMPLETE</td>
                                            <td><?php echo $incomplete; ?></td>
                                            <td>
                                                <?php $incPE = $total == 0 ? '0' : round(($incomplete / $total) * 100, 2); 
                                                    echo $incPE . " %"; 
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>UNOFFICIALLY DROPPED</td>
                                            <td><?php echo $totalUD; ?></td>
                                            <td>
                                            <?php $udPE = $total == 0 ? '0' : round(($totalUD / $total) * 100, 2); 
                                                    echo $udPE . " %"; 
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>OFFICIALLY DROPPED</td>
                                            <td><?php echo $od; ?></td>
                                            <td>
                                            <?php $odPE = $total == 0 ? '0' : round(($od / $total) * 100, 2); 
                                                    echo $odPE . " %"; 
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>TOTAL</strong></td>
                                            <td>
                                                <?php 
                                                    echo $total; 
                                                ?></td>
                                            <td>
                                            <?php echo round(($lineOfOnePE + $lineOfTwoPE + $lineOfThreePE + $lineOfFivePE + $incPE + $udPE + $odPE), 0) . "%"; ?>
                                            </td>
                                        </tr>
                                    </table>
                                    <br />
                                    <p>
                                    <h4 /> GRADING SYSTEM 
                                    <table class="table" style="margin: 0 9px;">
                                        <tr>
                                            <th>Numerical Rating</th>
                                            <th>% Scale Rating</th>
                                        </tr>
                                        <tr>
                                            <td>1.00</td>
                                            <td>98 - 100</td>
                                        </tr>
                                        <tr>
                                            <td>1.25</td>
                                            <td>95 - 97</td>
                                        </tr>
                                        <tr>
                                            <td>1.50</td>
                                            <td>92 - 94</td>
                                        </tr>
                                        <tr>
                                            <td>1.75</td>
                                            <td>89 - 91</td>
                                        </tr>
                                        <tr>
                                            <td>2.00</td>
                                            <td>86 - 88</td>
                                        </tr>
                                        <tr>
                                            <td>2.25</td>
                                            <td>83 - 85</td>
                                        </tr>
                                        <tr>
                                            <td>2.50</td>
                                            <td>80 - 82</td>
                                        </tr>
                                        <tr>
                                            <td>2.75</td>
                                            <td>77 - 79</td>
                                        </tr>
                                        <tr>
                                            <td>3.00</td>
                                            <td>75 - 76</td>
                                        </tr>
                                        <tr>
                                            <td>5.00</td>
                                            <td>Below 75 - Failed</td>
                                        </tr>
                                        <tr>
                                            <td>inc</td>
                                            <td>Incomplete</td>
                                        </tr>
                                    </table>
                                    </p>
                                    </p>
                                  

                                </div><!-- /.box-body -->
                                <div class="box-footer">
                                   
                                </div><!-- /.box-footer-->
                            </div>
                    </div><!-- /.tab-pane -->
                    <div class="tab-pane" id="tab_3">
                        <div class="box">
                            <div class="overlay" style="display:none;"></div>
                            <div class="loading-img" style="display:none;"></div>
                            <div class="box-header">
                                    <h3 class="box-title">Upload Data</h3>
                            </div>
                            <?php if($classlist['intFinalized']!=1): ?>
                                <?php echo form_open_multipart(base_url().'excel/upload_classlist');?>
                                <input type="hidden" id="intClasslistID" name="intClasslistID" value="<?php echo $classlist['intID']; ?>" />
                                <input type="hidden" name="strUnits" value="<?php echo $subject['strUnits']; ?>" />
                                <input type="file" name="excelupload" />
                                <br /><br />
                                <input type="submit" value="upload" />
                                </form>
                            <?php else: ?>
                                <h3>Classlist finalized</h3>
                            <?php endif; ?>
                            <hr />
                             
                            <div style="clear:both"></div>
                        </div>
                    </div><!-- /.tab-pane -->
                    <div class="tab-pane" id="tab_4">
                        <div class="box">
                            <div class="overlay" style="display:none;"></div>
                            <div class="loading-img" style="display:none;"></div>
                            <div class="box-header">
                                    <h3 class="box-title"><?php echo $classlist['strSection']; ?> Schedule</h3>
                            </div>
                           
                            
                             <?php 
                            
                            foreach($schedule as $sched): 
                                                    $hourdiff = round((strtotime($sched['dteEnd']) - strtotime($sched['dteStart']))/3600, 1);
                                                    
                                                ?>
                                                <input type="hidden" class="<?php echo $sched['strDay']; ?>" value="<?php echo date('gia',strtotime($sched['dteStart'])); ?>" href="<?php echo $hourdiff*2; ?>" rel="<?php echo $sched['strCode']; ?> <?php echo $sched['strRoomCode']; ?>" data-section="<?php echo $sched['strLastname'].", ".$sched['strFirstname']; ?>">
                                               
                                <?php endforeach; ?>
                            
                            
                            
                            <div id="sched-table-container">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th style="text-align:center;border:1px solid #555;"></th>
                                            <th style="text-align:center;border:1px solid #555;">Mon</th>
                                            <th style="text-align:center;border:1px solid #555;">Tue</th>
                                            <th style="text-align:center;border:1px solid #555;">Wed</th>
                                            <th style="text-align:center;border:1px solid #555;">Thu</th>
                                            <th style="text-align:center;border:1px solid #555;">Fri</th>
                                            <th style="text-align:center;border:1px solid #555;">Sat</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody-sched">
                                        <tr id="700am">
                                        <td style="border:1px solid #555;">7:00-7:30</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                        <tr id="730am">
                                            <td style="border:1px solid #555;">7:30-8:00</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                        <tr id="800am">
                                            <td style="border:1px solid #555;">8:00-8:30</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                        <tr id="830am">
                                            <td style="border:1px solid #555;">8:30-9:00</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                        <tr id="900am">
                                            <td style="border:1px solid #555;">9:00-9:30</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                        <tr id="930am">
                                            <td style="border:1px solid #555;">9:30-10:00</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                        <tr id="1000am">
                                            <td style="border:1px solid #555;">10:00-10:30</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                        <tr id="1030am">
                                            <td style="border:1px solid #555;">10:30-11:00</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                        <tr id="1100am">
                                            <td style="border:1px solid #555;">11:00-11:30</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                       <tr id="1130am">
                                            <td style="border:1px solid #555;">11:30-12:00</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                        <tr id="1200pm">
                                            <td style="border:1px solid #555;">12:00-12:30</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                        <tr id="1230pm">
                                            <td style="border:1px solid #555;">12:30-1:00</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                        <tr id="100pm">
                                            <td style="border:1px solid #555;">1:00-1:30</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                        <tr id="130pm">
                                            <td style="border:1px solid #555;">1:30-2:00</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                        <tr id="200pm">
                                            <td style="border:1px solid #555;">2:00-2:30</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                        <tr id="230pm">
                                            <td style="border:1px solid #555;">2:30-3:00</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                        <tr id="300pm">
                                            <td style="border:1px solid #555;">3:00-3:30</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                        <tr id="330pm">
                                            <td style="border:1px solid #555;">3:30-4:00</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                        <tr id="400pm">
                                            <td style="border:1px solid #555;">4:00-4:30</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                        <tr id="430pm">
                                            <td style="border:1px solid #555;">4:30-5:00</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                        <tr id="500pm">
                                            <td style="border:1px solid #555;">5:00-5:30</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                        <tr id="530pm">
                                            <td style="border:1px solid #555;">5:30-6:00</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                        <tr id="600pm">
                                            <td style="border:1px solid #555;">6:00-6:30</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                        <tr id="630pm">
                                            <td style="border:1px solid #555;">6:30-7:00</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                                </div>
                             
                            <div style="clear:both"></div>
                        </div>
                    </div><!-- /.tab-pane -->
                    <div class="tab-pane" id="tab_5">
                        <div class="box">
                            <div class="overlay" style="display:none;"></div>
                            <div class="loading-img" style="display:none;"></div>
                            <div class="box-header">
                                    <h3 class="box-title"><?php echo $classlist['strClassName'].'-'.$classlist['strSection']; ?> <small><?php echo $classlist['enumSem']." ".$term_type." ".$classlist['strYearStart']."-".$classlist['strYearEnd']; ?></small></h3>
                            </div>
                            
                            <table class="table table-striped">                                
                                <th></th>
                                <th>Name</th>                                
                                <th>Prelim<br/>(30%)</th>
                                <th>Midterm<br/>(30%)</th>
                                <th>Finals<br/>(40%)</th>
                                <th>FINAL GRADE<br/>(100%)</th>
                                <th>Numerical Rating</th>
                                <th>Status</th>
                                <th>Remarks</th>   
                                <th>Actions</th>                                
                                                                
                                <?php                                
                                $ctr = 1;
                                
                            foreach($students as $student):
                                //print_r($student);
                                if($student['enumStatus'] == "inc"):
                                ?>
                                <tr>                                    
                                    <td><?php echo $ctr; ?>.</td>
                                    <td><a href="<?php echo base_url(); ?>unity/student_viewer/<?php echo $student['intID'] ?>"><?php echo $student['strLastname'].", ".$student['strFirstname']." "; echo isset($student['strMiddlename'][0])?" ".$student['strMiddlename'][0].".":''; ?></a></td>                                    
                                    <!--  newly added ^_^ 4-22-2016            -->
                                    <!--td><input <?php echo ($classlist['intFinalized']==1 || !$is_super_admin)?'disabled':''; ?> type="text" value="<?php echo $student['strStudentNumber']; ?>" name="strStudentNumber" class="studNumInput form-control" id="strStudentNumber" placeholder="Enter Stud-Number" size="3" rel="<?php echo $student['intID'] ?>"></td-->
                                    <td><?php echo $student['floatPrelimGrade']; ?></td>
                                    <td><?php echo $student['floatMidtermGrade']; ?></td>
                                    <td><?php echo $student['floatFinalsGrade']; ?></td>
                                    <td id="eq2-<?php echo $student['intCSID'] ?>">
                                        <?php echo number_format(getAve($student['floatPrelimGrade'],$student['floatMidtermGrade'],$student['floatFinalsGrade']), 2); ?>
                                    </td>
                                    <td style="text-align:center;" id="eq-<?php echo $student['intCSID'] ?>"><?php echo ($student['enumStatus']!='odrp')? number_format($student['floatFinalGrade'], 2):'---' ?></td>
                                    <td>Incomplete</td>                                                                        
                                    <td><?php echo $student['strRemarks']; ?></td>
                                    <td><a target="_blank" href="<?php echo base_url()."unity/comply/".$student['intCSID']; ?>">Complete Grade</a></td>
                                                                        

                                </tr>
                            <?php 
                                endif;
                                $ctr++;        
                            endforeach; ?>
                            </table>
                            
                    </div>
                </div><!-- /.tab-content -->
            </div>
    
    
         
</section>
</aside>