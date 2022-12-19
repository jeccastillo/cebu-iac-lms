<aside class="right-side">
<section class="content-header">
                    <h1>
                        <small>
                                                  <a class="btn btn-app" href="<?php echo base_url() ?>faculty/view_all_faculty" ><i class="ion ion-arrow-left-a"></i>All Faculty</a> 
                            <a class="btn btn-app trash-faculty" rel="<?php echo $faculty['intID']; ?>" href="#"><i class="ion ion-android-close"></i> Delete</a>   
                            <a class="btn btn-app" href="<?php echo base_url()."faculty/edit_faculty/".$faculty['intID']; ?>"><i class="ion ion-edit"></i> Edit</a> 
                        </small>
                        
                        
                        <div class="pull-right">
                            <select id="select-sem-faculty" class="form-control" >
                                <?php foreach($sy as $s): ?>
                                    <option <?php echo ($selected_ay == $s['intID'])?'selected':''; ?> value="<?php echo $s['intID']; ?>"><?php echo $s['enumSem']." ".$term_type." ".$s['strYearStart']."-".$s['strYearEnd']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                
                        
                    </h1>
<!--
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Faculty</a></li>
                        <li class="active">View Faculty</li>
                    </ol>
                    
-->
                
                </section>
<div class="content">
    <input type="hidden" value="<?php echo $faculty['intID'] ?>" id="faculty-id" />
    <div class="box box-solid">
        <div class="box-body">
            <input type="hidden" id="fname" value="<?php echo $faculty['strFirstname']; ?>">
            <input type="hidden" id="lname" value="<?php echo $faculty['strLastname']; ?>">
            <div class="alert alert-danger" style="display:none;">
                <i class="fa fa-ban"></i>
                <b>Alert!</b> Only admins can delete faculty records.
            </div>
            <div class="col-xs-4 col-lg-4 size-96">
              <?php if($faculty['strPicture'] == "" ): ?>
               <i style="font-size:9em;" class="fa fa-user"></i>
              <?php else: ?>
                    <img class="img-responsive" src="<?php echo base_url().IMAGE_UPLOAD_DIR.$faculty['strPicture']; ?>" width="30%" height="30%" />
                  <?php endif; ?>
            </div>
            <div class="col-xs-8 col-lg-4">
              <h3><?php 
                        $middleInitial = substr($faculty['strMiddlename'], 0,1);
                        echo $faculty['strLastname'].", ". $faculty['strFirstname'] . " " .  $middleInitial . "."; ?>
             </h3>
                <h5>Department of <?php echo $faculty['strDepartment']; ?></h5>
            </div>

            <div class="col-lg-4 col-xs-12">
              <p><strong>Username: </strong><?php echo $faculty['strUsername']; ?></p>
              <p><strong>Address: </strong><?php echo $faculty['strAddress']; ?></p>
              <p><strong>Contact: </strong><?php echo $faculty['strMobileNumber']; ?></p>
              <p><strong>Email: </strong><?php echo $faculty['strEmail']; ?></p>    
                
            </div>
            
            <div style="clear:both"></div>
    </div>
    </div>
    
    <div class="box-body no-padding"></div>

    <div class="box box-solid box-primary">
            <div class="box-header">                  
                <div>

                    <div style="width:50%;float:right; text-align:right;">

                    </div>
                </div>

                <h3 class="box-title">List of Courses Handled - 
		          <?php echo "A.Y." . " " .$active_sem['strYearStart']."-".$active_sem['strYearEnd'] . " " . $active_sem['enumSem']." ".$term_type ; ?>
                </h3>
                <div class="box-tools">

                </div>
            </div><!-- /.box-header -->
            <div class="mailbox-controls">
                    <!-- Check all button -->
                    
                    
                    
                  
            </dvi>
            <hr />
            <div class="btn-group">
                        <button class="btn btn-default btn-sm checkbox-toggle"><i class="fa fa-square-o"></i></button>
                        <button type="button" class="btn btn-default btn-sm">With Selected</button>
                        <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                            <span class="caret"></span>
                            <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu" role="menu">
                            <li><a href="#" class="delete-classlist"><i class="fa fa-trash-o"></i> Delete Classlist</a></li>
                            <li><a href="#" class="download-classlist"><i class="fa fa-download"></i> Download Classlist</a></li>
                        </ul>
                    </div>
            <form action="<?php echo base_url().'excel/download_classlists_archive'; ?>"  method="post" id="download-archive">
            <div class="box-body table-responsive">
                <table id="classlist-archive-table" class="table table-hover">
                    <thead><tr>
                        <th>id</th>
                        <th>Section</th>
                        <th>Course Code</th>
                        <th>Course Title</th>
                        <th>Course Units</th>
                        <th>Remarks</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
              </table>
            </div>
              </form>
        </div>
    </div>             

    <div class="box box-solid box-primary">
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
                                ?>
                                        <input type="hidden" class="<?php echo $sched['strDay']; ?>"
                                            value="<?php echo date('gia',strtotime($sched['dteStart'])); ?>"
                                            href="<?php echo $hourdiff*2; ?>"
                                            rel="<?php echo $class['strCode']; ?> <?php echo $sched['strRoomCode']; ?>"
                                            data-section="<?php echo $class['strSection']; ?>">
                                
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
                        <tr id="hours-per-day">
                            <td style="border:1px solid #555;">Hours per Day</td>
                            <td style="text-align:center;">hrs./day</td>
                            <td style="text-align:center;">hrs./day</td>
                            <td style="text-align:center;">hrs./day</td>
                            <td style="text-align:center;">hrs./day</td>
                            <td style="text-align:center;">hrs./day</td>
                            <td style="text-align:center;">hrs./day</td>
                        </tr>
                    </tbody>
                </table>
                </div>
                <input class="btn btn-flat btn-default" type="submit" value="print preview" />
            </form>     
        </div>
    </div>
</div>
