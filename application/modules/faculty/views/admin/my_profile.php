<aside class="right-side">
    <section class="content-header container">
        <h1>
            <div class="pull-right">
                <select id="select-sem-profile" class="form-control">
                    <?php foreach($sy as $s): ?>
                    <option <?php echo ($selected_ay == $s['intID'])?'selected':''; ?>
                        value="<?php echo $s['intID']; ?>">
                        <?php echo $s['enumSem']." ".$term_type." ".$s['strYearStart']."-".$s['strYearEnd']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="clear:both"></div>
        </h1>
    </section>
    <div class="content container">
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
                    <img class="img-responsive" src="<?php echo base_url().IMAGE_UPLOAD_DIR.$faculty['strPicture']; ?>"
                        width="30%" height="30%" />
                    <?php endif; ?>
                </div>
                <div class="col-xs-8 col-lg-4">
                    <h3><?php 
                        $middleInitial = substr($faculty['strMiddlename'], 0,1);
                        echo $faculty['strLastname'].", ". $faculty['strFirstname'] . " " .  $middleInitial . "."; ?>
                    </h3>
                    <h5>Department of <?php echo $faculty['strDepartment']; ?></h5>
                    <a href="<?php echo base_url()."faculty/edit_profile"; ?>" class="btn btn-default  btn-flat">
                        <i class="ion ion-edit"></i> Edit profile
                    </a>
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
        <div class="box box-solid box-primary">
            <div class="box-header">
                <h4 class="box-title">List of Subjects Handled -
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
                                            rel="<?php echo $class['strCode']; ?> <?php echo $sched['strRoomCode']; ?> <?php echo $class['strSection']; ?>">
                                
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
                    <input type="hidden"
                        value="<?php echo $faculty['strLastname']."-".$faculty['strFirstname']."-".$faculty['intID']; ?>"
                        name="studentInfo" id="studentInfo" />

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
    </div>