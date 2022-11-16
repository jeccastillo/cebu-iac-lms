<aside class="right-side">
    <section class="content-header">
        <h1>
            <small>
                <a class="btn btn-app" href="<?php echo base_url() ?>subject/view_all_subjects"><i
                        class="ion ion-arrow-left-a"></i>Back</a>
                <a class="btn btn-app trash-subject2" rel="<?php echo $subjects['intID']; ?>" href="#"><i
                        class="ion ion-android-close"></i> Delete</a>
                <a class="btn btn-app" href="<?php echo base_url()."subject/edit_subject/".$subjects['intID']; ?>"><i
                        class="ion ion-edit"></i> Edit</a>
            </small>
            <div class="pull-right">
                <select id="select-sem-subject" class="form-control">
                    <?php foreach($sy as $s): ?>
                    <option <?php echo ($selected_ay == $s['intID'])?'selected':''; ?>
                        value="<?php echo $s['intID']; ?>">
                        <?php echo $s['enumSem']." ".$term_type." ".$s['strYearStart']."-".$s['strYearEnd']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </h1>


    </section>
    <div class="content">
        <div class="box box-solid">
            <div class="box-header">
                <ol class="breadcrumb">
                    <li><a href="#"><i class="fa fa-dashboard"></i> Subject</a></li>
                    <li class="active">View Subject</li>
                </ol>
            </div>
            <input type="hidden" value="<?php echo $subjects['intID']; ?>" id="subject-id" />
            <input type="hidden" value="<?php echo $subjects['strCode']; ?>" id="subject-code-viewer" />
            <div class="box-body">
                <div class="alert alert-danger" style="display:none;">
                    <i class="fa fa-ban"></i>
                    <b>Alert!</b> Subject cannot be deleted it is connected to classlist.
                </div>
                <div class="col-xs-4 col-lg-4 size-96">

                </div>
                <div class="col-xs-8 col-lg-4">
                    <h3><?php 
                        
                        echo $subjects['strCode']; ?>
                    </h3>
                    <h5><?php echo $subjects['strDescription']; ?></h5>
                </div>

                <div class="col-lg-4 col-xs-12">
                    <p><strong>Units: </strong><?php echo $subjects['strUnits']; ?></p>                    
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
                            <th>Class Section</th>
                            <th>Course Code</th>
                            <!--                        <th>Course Description</th>-->
                            <th>Assigned Instructor/Professor</th>
                            <th>Schedule</th>
                            <th>Remarks</th>
                            <!--                        <th>Action</th>-->
                        </tr>
                    </thead>

                    <tbody>
                        <?php 
                    if(!empty($classlist)): 
                    foreach($classlist as $class): ?>
                        <tr>
                            <td><a
                                    href="<?php echo base_url().'unity/classlist_viewer/'.$class['intID']; ?>"><?php echo $class['strSection']; ?></a>
                            </td>
                            <td><?php echo $class['strCode']; ?></td>
                            <td>
                                <?php echo $class['strLastname'] . ", " . $class['strFirstname'] ;?>
                            </td>
                            <?php if(!empty($class['schedule'])): ?>

                            <td>
                                <?php foreach($class['schedule'] as $sched): ?>

                                <?php echo date('g:ia',strtotime($sched['dteStart'])).' - '.date('g:ia',strtotime($sched['dteEnd'])); ?>
                                <?php echo $sched['strDay']; ?> <?php echo $sched['strRoomCode']; ?>
                                <br />
                                <?php endforeach; ?>
                            </td>
                            <?php else: ?>
                            <td></td>

                            <?php endif; ?>
                            <td>
                                <?php 
                                                            if ($class['intFinalized'] == 1) {
                                                                echo "Submitted";
                                                            }
                                                            else {
                                                                echo "Not Yet Submitted";
                                                            }                                        
                                                    ?>

                            </td>
                        </tr>
                        <?php endforeach; 
                        else:
                    ?>
                        <tr>
                            <th>No Classlists for this term</th>
                        </tr>
                        <?php
                        endif;
                    ?>
                    </tbody>

                </table>
            </div>
        </div>
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Grades Comparison Chart</h3>

                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                    </button>
                    <button type="button" class="btn btn-box-tool" data-widget="remove"><i
                            class="fa fa-times"></i></button>
                </div>
            </div>
            <div class="box-body">
                <div class="chart">
                    <canvas id="gradesChart" height="300"></canvas>
                </div>
            </div>
            <!-- /.box-body -->
        </div>
    </div>