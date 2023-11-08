<aside class="right-side">
<section class="content-header">
                    <h1>
                        Curriculum
                        <small></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Curriculum</a></li>
                        <li class="active">Edit Curriculum</li>
                    </ol>
                </section>
    <div class="content">
    <div class="span10 box box-primary">
        <div class="box-header">
                <h3 class="box-title">Edit Curriculum</h3>
        </div>
       
            
            <form id="validate-curriculum" action="<?php echo base_url(); ?>unity/submit_edit_curriculum" method="post" role="form">
                <input type="hidden" name="intID"  id="intID" value="<?php echo $item['intID']; ?>">
                <div class="box-body">
                         <div class="form-group col-xs-6">
                            <label for="strName">Name</label>
                            <input type="text" name="strName" value="<?php echo $item['strName']; ?>" class="form-control" id="strName" placeholder="Enter Name/Code">
                        </div>

                        <div class="form-group col-xs-6">
                            <label for="intYearLevel">Program</label>
                            <select class="form-control" name="intProgramID" id="addStudentCourse" >
                                <?php foreach ($programs as $prog): ?>
                                <option <?php echo ($item['intProgramID'] == $prog['intProgramID'])?'selected':''; ?> value="<?php echo $prog['intProgramID']; ?>"><?php echo $prog['strProgramCode']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>                        

                         
                        <div class="form-group col-xs-12">
                            <input type="submit" value="update" class="btn btn-default  btn-flat">
                        </div>
                    <div style="clear:both"></div>
                    
                </div>
            </form>
        </div>
    <div class="box box-primary">
        <div class="box-header">
                <h3 class="box-title">Subjects</h3>
        </div>
        <div class="box-body">
            <form action="<?php echo base_url(); ?>unity/add_subjects_curriculum" method="post" role="form">
            <div class="box-body">
                <div class="box box-warning direct-chat direct-chat-warning">
                <div class="box-header with-border">
                    <h3 class="box-title">Subject Select</h3>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                  <!-- Conversations are loaded here -->
                  <div class="direct-chat-messages">
                   <?php foreach($subjects as $s): ?>
                <div class="col-md-4 col-sm-6">
                    <div class="checkbox">
                        <label>
                          <input type="checkbox" name="subject[]" value="<?php echo $s['intID']; ?>">
                            <?php echo $s['strCode']; ?>
                          <a data-toggle="popover" data-placement="right" title="<?php echo $s['strCode']; ?>" data-content="<?php echo $s['strDescription']; ?>" href="#"><i style="font-size:1.2em" class="ion ion-ios-information"></i></a>
                        </label>
                    </div>
                    
                </div>
            <?php endforeach; ?>
                    
                  </div>
                  <!--/.direct-chat-messages-->

                  <!-- /.direct-chat-pane -->
                </div>
                <!-- /.box-body -->
                <div class="box-footer">
                    <div class="form-group col-xs-4">
                    <input type="hidden" name="intCurriculumID" value="<?php echo $item['intID']; ?>" >
                        <label for="intYearLevel">Year Level</label>
                        <select class="form-control" name="intYearLevel" id="intYearLevel" >
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                        </select>
                    </div>
                    <div class="form-group col-xs-4">
                        <label for="intSem">Term</label>
                        <select class="form-control" name="intSem" id="intSem" >
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                        </select>
                    </div>
                    <div class="form-group col-xs-4">
                        <label for="intSem"></label>
                        <input type="submit" value="Add Subjects" class="form-control btn btn-default  btn-flat">
                    </div>
                </div>
                <!-- /.box-footer-->
              </div>
            
                <div style="clear:both"></div>
                
            </div>
        </form>     
            <?php 
                $prev_year_sem = '0_0';
                $i = 0;
                $unitsPerSem = 0;
                $totalUnits = 0;
                foreach($curriculum_subjects as $s): 
                   $totalUnits += $s['strUnits'];
                   $unitsPerSem += $s['strUnits'];
                //echo $prev_year_sem."<br />";
                ?>
                <?php if($prev_year_sem != $s['intYearLevel'].'_'.$s['intSem']): ?>
                
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th colspan="2">
                                <?php echo switch_num($s['intYearLevel'])." Year | ".switch_num($s['intSem'])." Term"; ?>
                               
                            </th>
                        </tr>
                        <tr>
                            <th>Course Code</th>
                            <th>Course Description</th>
                            <th>Lecture Units</th>
                            <th>Lab Units</th>
                            <th>Total Units</th>
                            <th>Pre-requisites</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                <?php 
                        $prev_year_sem = $s['intYearLevel'].'_'.$s['intSem'];
                        endif; ?>
                        
                <tr>
                    <td><a target="_blank" href="<?php echo base_url(); ?>subject/subject_viewer/<?php echo $s['intSubjectID']; ?>"><?php echo $s['strCode']; ?></a></td>
                    <td><?php echo $s['strDescription']; ?></td>
                    <td><?php echo $s['intLectHours']; ?></td>
                    <td><?php echo $s['intLab']; ?></td>
                    <td><?php echo $s['strUnits']; ?></td>
                    <td style="width:20%;">
                        <?php
                        $i = 0;
                        foreach($s['prereq'] as $pre){ 
                            if($i != 0)
                                echo ", ";
                                echo $pre['strCode']; 
                            
                            $i++;
                        }
                        ?>
                        <br />
                        <a target="_blank" href="<?php echo base_url(); ?>subject/edit_subject/<?php echo $s['intSubjectID']; ?>">[add/edit prerequisites]</a>
                    </td>
                    <td>
                        <a rel="<?php echo $s['intID']; ?>" class="btn btn-danger remove-subject-curriculum" href="#">Remove</a>
                    </td>
                        </tr>
                        <?php if($prev_year_sem != $s['intYearLevel'].'_'.$s['intSem']):
                        
                        ?>
<!--
                        <tr>
                            <td colspan="3">Units <?php echo $unitsPerSem; ?></td>
                        </tr>
-->
                        <?php
                        $unitsPerSem = 0;
                        endif; ?>
            <?php if($prev_year_sem != $s['intYearLevel'].'_'.$s['intSem'] || count($curriculum_subjects) == $i+1): ?>   
                
                <tr>
                    <th><?php echo "TOTAL UNITS : " .  $totalUnits; ?></th>
                </tr>
                </tbody>
            </table>
                <?php endif; ?>
            <?php 
            $i++;
            endforeach; ?>
            
        
        </div>
        </div>
    </div>
    
    </div>
</aside>