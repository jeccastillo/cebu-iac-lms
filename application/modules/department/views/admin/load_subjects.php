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
                  
                </section>
<div class="content">
    <input type="hidden" value="<?php echo $active_sem['intID']; ?>" id="active-sem" >
    <input type="hidden" value="<?php echo $faculty['intID']; ?>" id="faculty-id" >
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-widget widget-user-2">
            <!-- Add the bg color to the header using any of the bg-* classes -->
            <div class="widget-user-header bg-red">
              <!-- /.widget-user-image -->
              <h3 class="widget-user-username" style="text-transform:capitalize;margin-left:0;font-size:1.3em;"><?php echo strtolower($faculty['strLastname'].", ". $faculty['strFirstname']); ?>
                        <?php echo ($faculty['strMiddlename'] != "")?' '.strtolower($faculty['strMiddlename']):''; ?></h3>
            </div>
            <div class="box-footer no-padding">
              <ul class="nav nav-stacked">
                <li><a><?php echo $active_sem['enumSem']; ?> Sem</a></li>
                <li><a>AY <?php echo $active_sem['strYearStart']." - ".$active_sem['strYearEnd']; ?></a></li>
                  <li>
                      <a href="<?php echo base_url(); ?>faculty/faculty_viewer/<?php echo $faculty['intID']; ?>">
                          View Profile
                      </a>
                  </li>
              </ul>
            </div>
          </div>
            
        </div>
        
    
        <div class="col-sm-12">
            <div class="box box-solid box-primary hidden">
                <div class="box-header">
                    <h4 class="box-title">Schedule View of Courses Handled - 
                        <?php echo "A.Y." . " " .$active_sem['strYearStart']."-".$active_sem['strYearEnd'] . " " . $active_sem['enumSem']." ".$term_type ; ?>
                    </h4>
                </div><!-- /.box-header -->
                <div class="box-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Class Section</th>
                                <th>Course Code</th>
                                <th>Course Description</th>
                                <th>Units</th>
                                <th>Schedule</th>
                                <th>Remarks</th>
        <!--                        <th>Action</th>-->
                            </tr>
                        </thead>

                        <tbody>
                                <?php if(!empty($classlist)):
                                        foreach($classlist as $class): ?>
                                <tr>
                                    <td><?php echo $class['strSection']; ?></td>
                                    <td><?php echo $class['strClassName']; ?></td>
                                    <td><?php echo $class['strDescription']; ?></td>
                                    <td><?php echo $class['strUnits']; ?></td>
                                    <?php if(!empty($class['schedule'])): ?>

                                    <td>
                                        <?php echo $class['schedule']['schedString']; ?>
                                        <?php foreach($class['schedule'] as $sched):
                                                if(isset($sched['dteStart'])):                                
                                                    $hourdiff = round((strtotime($sched['dteEnd']) - strtotime($sched['dteStart']))/3600, 1);
                                                    $section = $class['strClassName'].$class['year'].$class['strSection']." ".$class['sub_section'];
                                        ?>
                                                <input type="hidden" class="<?php echo $sched['strDay']; ?>"
                                                    value="<?php echo date('gia',strtotime($sched['dteStart'])); ?>"
                                                    href="<?php echo $hourdiff*2; ?>"
                                                    rel="<?php echo $class['strCode']; ?> <?php echo $sched['strRoomCode']; ?>"
                                                    data-section="<?php echo $section; ?>">
                                        
                                        <?php 
                                                endif;
                                            endforeach; 
                                        ?>
                                    </td>
                                    <?php else: ?>
                                        <td></td>

                                    <?php endif; ?>
                                    <td>
                                        <?php if($class['intFinalized'] == 1): ?>
                                            Submitted
                                        <?php else: ?>
                                            Not Yet Submitted
                                        <?php endif;  ?>
                                    </td>                           
                                </tr>
                                    <?php endforeach; ?>
                                    <?php else: ?>
                                <tr>
                                    <th>No Classlists for this term</th>
                                </tr>
                                <?php endif; ?>

                            </tbody>

                    </table>
                </div>
            </div>
            <div class="box box-primary">
                <div class="box-body">
                    <form method="post" action="<?php echo base_url() ?>pdf/print_sched">   
                        <input type="hidden" name="sched-table" id="sched-table" />
                        <input type="hidden" value="<?php $middleInitial = substr($faculty['strMiddlename'], 0,1);
            echo $faculty['strLastname'].", ".$faculty['strFirstname'] . " " .  $middleInitial; ?>" name="facultyName" id="facultyName" />
                        <input type="hidden" value="<?php echo $faculty['strDepartment']; ?>" name="facultyDept" id="facultyDept" />

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
                        <input class="btn btn-flat btn-default" type="submit" value="print preview" />
                    </form>     
                </div>
            </div>
            <div class="box box-primary">
                <div class="box-header">
                    <h3>Load Classlists</h3>
                    
                </div>
                            
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-5">
                            <h4>Days Available</h4>
                            <select style="height:200px" class="form-control" id="day-selector" multiple>
                                <option value="1">Monday</option>
                                <option value="2">Tuesday</option>
                                <option value="3">Wednesday</option>
                                <option value="4">Thursday</option>
                                <option value="5">Friday</option>
                                <option value="6">Saturday</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <br /><br />
                            <!--a href="#" class="btn btn-default  btn-flat btn-block" id="autoload-advised">Autoload <br /> Subjects </a-->
                            <a href="#" id="load-days" class="btn btn-default  btn-flat btn-block">Load <i class="ion ion-arrow-right-c"></i> </a>
                            <a href="#" id="unload-days" class="btn btn-default  btn-flat btn-block"><i class="ion ion-arrow-left-c"></i> Remove</a>
                            
                        </div>
                        <div class="col-md-5">
                            <h4>Days</h4>
                            <select style="height:200px" class="form-control" id="day-selected" multiple>
                                
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <h4>Classlists</h4>
                            <select style="height:300px" class="select2" id="classlist-selector" multiple>
                                <?php foreach($all_classlist as $sn):                                         
                                    $section = $sn['strClassName'].$sn['year'].$sn['strSection']." ".$sn['sub_section'];
                                    ?>
                                    
                                    <option value="<?php echo $sn['intID']; ?>"><?php echo $sn['strCode']." Section: ".$section." Desc: ".$sn['strDescription']." Schedule:".$sn['schedule']['schedString']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-12" style="text-center">
                        <br />
                            <a href="#" id="load-classlist" class="btn btn-default  btn-flat">Load <i class="ion ion-arrow-down-c"></i> </a>
                            <a href="#" id="unload-classlist" class="btn btn-default  btn-flat"><i class="ion ion-arrow-up-c"></i> Remove</a>
                        
                        </div>
                        <div class="col-md-12">
                            <h4>Loaded Classlists</h4>
                            <select style="height:300px" class="form-control" id="loaded-classlist" multiple>
                                <?php foreach($classlist as $sn): 
                                    $section = $sn['strClassName'].$sn['year'].$sn['strSection']." ".$sn['sub_section'];
                                    ?>
                                    <option value="<?php echo $sn['intID']; ?>"><?php echo $sn['strCode']." ".$section." Desc: ".$sn['strDescription']." Schedule:".$sn['schedule']['schedString']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-12" style="text-center">
                            <br />
                    
                            <a href="#" id="save-classlist" class="btn btn-default  btn-flat">Save</a>
                            
                        </div>
                    </div>
                </div>
            </div>
            <div class="box box-primary">
                <div class="box-header">
                    <h3>Load Advising Sections</h3>
                    
                </div>
                            
                <div class="box-body">                    
                    <div class="row">
                        <div class="col-md-5">
                            <h4>Sections</h4>
                            <select style="height:200px" id="section-selector" multiple>
                                <?php foreach($unassigned_sections as $sn):                                                                             
                                    ?>
                                    
                                    <option value="<?php echo $sn['intID']; ?>"><?php echo $sn['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2" style="text-center">
                        <br />
                            <a href="#" id="load-section" class="btn btn-default  btn-flat">Load <i class="ion ion-arrow-down-c"></i> </a>
                            <a href="#" id="unload-section" class="btn btn-default  btn-flat"><i class="ion ion-arrow-up-c"></i> Remove</a>
                        
                        </div>
                        <div class="col-md-5">
                            <h4>Loaded Sections</h4>
                            <select style="height:200px" class="form-control" id="loaded-section" multiple>
                                <?php foreach($faculty_sections as $sn):                                    
                                    ?>
                                    <option value="<?php echo $sn['intID']; ?>"><?php echo $sn['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-12" style="text-center">
                            <br />
                    
                            <a href="#" id="save-sections" class="btn btn-default  btn-flat">Save</a>
                            
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
        <h4 class="modal-title"><?php echo $student['strName']; ?></h4>
      </div>
      <div class="modal-body">
            <?php 
            $prev_year_sem = '0';
            for($i = 0;$i<count($curriculum_subjects); $i++): 
            $key = array_search($curriculum_subjects[$i]['strCode'], array_column($grades, 'strCode'));
            //echo $prev_year_sem."<br />";

            ?>
            <?php if($prev_year_sem != $curriculum_subjects[$i]['intYearLevel']."_".$curriculum_subjects[$i]['intSem']): ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th colspan="4">
                            <?php echo switch_num($curriculum_subjects[$i]['intYearLevel'])." Year ".switch_num($curriculum_subjects[$i]['intSem'])." Sem"; ?>
                        </th>
                    </tr>
                    <tr>
                        <th>Subject</th>
                        <th>Description</th>
                        <th>Units</th>
                    </tr>
                </thead>
                <tbody>

            <?php 

                    endif; 
                    $prev_year_sem = $curriculum_subjects[$i]['intYearLevel']."_".$curriculum_subjects[$i]['intSem'];
                    ?>
            <tr style="<?php echo ($key)?'background-color:#669966':''; ?>">
                <td><?php echo $curriculum_subjects[$i]['strCode']; ?></td>
                <td>
                    <?php echo $curriculum_subjects[$i]['strDescription']; ?>
                </td>
                <td>
                    <?php echo $curriculum_subjects[$i]['strUnits']; ?> 
                </td>
            </tr>
        <?php if($prev_year_sem != $curriculum_subjects[$i+1]['intYearLevel']."_".$curriculum_subjects[$i+1]['intSem'] || count($curriculum_subjects) == $i+1): ?>   

            </tbody>
        </table>
        <?php endif; ?>
        <?php endfor; ?>
      </div>
      
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

