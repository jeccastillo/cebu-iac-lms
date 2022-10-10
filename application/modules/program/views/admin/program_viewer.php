<aside class="right-side">
<section class="content-header">
                    <h1>
                        <small>
                                                  <a class="btn btn-app" href="<?php echo base_url() ?>program/view_all_programs" ><i class="ion ion-arrow-left-a"></i>Back</a> 
                            <a class="btn btn-app trash-program2" rel="<?php echo $program['intProgramID']; ?>" href="#"><i class="ion ion-android-close"></i> Delete</a>   
                            <a class="btn btn-app" href="<?php echo base_url()."program/edit_program/".$program['intProgramID']; ?>"><i class="ion ion-edit"></i> Edit</a> 
                        </small>
                        <div class="pull-right">
                        <select id="select-sem-program" class="form-control" >
                            <?php foreach($sy as $s): ?>
                                <option <?php echo ($selected_ay == $s['intID'])?'selected':''; ?> value="<?php echo $s['intID']; ?>"><?php echo $s['enumSem']." ".$term_type." ".$s['strYearStart']."-".$s['strYearEnd']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    </h1>
                    
                    
                </section>
<div class="content">
    <div class="box box-solid">
        <div class="box-header">
            <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Program</a></li>
                        <li class="active">View Program</li>
                    </ol>
        </div>
        <input type="hidden" value="<?php echo $program['intProgramID']; ?>" id="program-id" />
        <input type="hidden" value="<?php echo $program['strProgramCode']; ?>" id="program-code-viewer" />
        <div class="box-body">
            <div class="alert alert-danger" style="display:none;">
                <i class="fa fa-ban"></i>
                <b>Alert!</b> Program cannot be deleted it is connected to classlist.
            </div>
            <div class="col-xs-4 col-lg-4 size-96">
             
            </div>
            <div class="col-xs-8 col-lg-4">
              <h3><?php 
                        
                        echo $program['strProgramCode']; ?>
             </h3>
                <h5><?php echo $program['strProgramDescription']; ?></h5>
            </div>

            <div class="col-lg-4 col-xs-12">
              <p><strong>Major: </strong><?php echo $program['strMajor']; ?></p>
            </div>
            <div style="clear:both"></div>
            
    </div>
    </div>
    <div class="box box-solid box-primary">
         <div class="box-header">
            <h4 class="box-title">List of Sections Enrolled - 
		<?php echo "A.Y." . " " .$active_sem['strYearStart']."-".$active_sem['strYearEnd'] . " " . $active_sem['enumSem']." ".$term_type ; ?>
			
			
			</h4>
        </div><!-- /.box-header -->
        <div class="box-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Section Code</th>
                        <th>Year Level </th>
<!--                        <th>Course Description</th>-->
                        <th>Section</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php 
                    if(!empty($classlist)): 
                    foreach($classlist as $class): ?>
                    <tr>
                        <td>
                            <a href="<?php echo base_url().'program/program_course_viewer/'.$class['strSection']; ?>">
                            <?php echo $class['strSection']; ?>
                                </a>
                        </td>
                        <td</td>
                        <td>
                        </td>
                        
                            <td>
                                
                            </td>
                        
                            <td></td>
                   
                        <td>
            
                            
                             </td>
                    </tr>
                    <?php endforeach; 
                        else:
                    ?>
                    <tr>
                        <th>No Sections for this term</th>
                    </tr>
                    <?php
                        endif;
                    ?>
                </tbody>

            </table>
        </div>
    </div>
<!--
    <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">Grades Comparison Chart</h3>

              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
              </div>
            </div>
            <div class="box-body">
              <div class="chart">
                <canvas id="gradesChart" height="300" ></canvas>
              </div>
            </div>
             /.box-body 
          </div>
-->
</div>

