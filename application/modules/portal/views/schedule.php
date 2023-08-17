<?php 
    error_reporting(0);
?>
<aside class="right-side">
<section class="content-header">
<h1>
                        My Schedule
                        <small>view your schedule information</small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="<?php echo base_url() ?>portal/dashboard"><i class="fa fa-home"></i> Home</a></li>
                        <li class="active">Schedule</li>
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


                <div class="content"><input type="hidden" id="regStat" value="<?php echo $reg_status;?>"/>
<?php if ($reg_status =="For Subject Enlistment"):  { ?>
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
        <p>Your courses / subjects have been registered. To view your courses / subjects schedules, please wait for the accounting office to tag you as enrolled.<p>
    </div>
<?php } endif; ?>
<div class="content">
    <input type="hidden" value="<?php echo $student['intID'] ?>" id="student-id" />
    <input type="hidden" value="<?php echo $active_sem['intID']; ?>" id="active-sem-id" />
    <input type="hidden" id="regStat" value="<?php echo $reg_status;?>"/>
    <div class="box box-solid box-primary">
         <div class="box-header">
            <h4 class="box-title">My Schedule for <?php echo $active_sem['enumSem']." ".$term_type." ".$active_sem['strYearStart']."-".$active_sem['strYearEnd']; ?></h4>
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
        <div class="box-body">
           
            <table class="table table-bordered">
                    <thead>
                        <tr style="font-size: 11px;">
                            <th>Section</th>
                            <th>Course Code</th>
                            <th>Course Description</th>
                            <th style="text-align: center;">Units</th>
                            <th>Schedule</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalUnits = 0;
                        foreach($records as $record): ?>
                        <tr style="font-size: 12px;">
                            <td><?php echo $record['strClassName'].$record['year'].$record['strSection']." ".$record['sub_section']; ?></td>
                            <td><?php echo $record['strCode']; ?></td>
                            <td><?php echo $record['strDescription'] ?></td>
                            <td style="text-align: center;"><?php echo $record['strUnits']; ?></td>     
                            <?php if(!empty($record['schedule'])): ?>
                            
                            <td>
                                <?php echo $record['schedule']['schedString']; ?>
                                <?php foreach($record['schedule'] as $sched): ?>                                                                
                                <br />
                                <?php
                                      $hourdiff = round((strtotime($sched['dteEnd']) - strtotime($sched['dteStart']))/3600, 1);
                                ?>
                                <input type="hidden" class="<?php echo $sched['strDay']; ?>" value="<?php echo date('gia',strtotime($sched['dteStart'])); ?>" href="<?php echo $hourdiff*2; ?>" rel="<?php echo $record['strCode']; ?> <?php echo $record['strRoomCode']; ?>">
                                <?php endforeach; ?>
                            </td>
                            <?php else: ?>
                            <td></td>
                           
                            <?php endif; ?>

                        </tr>
                     
                        <?php endforeach; ?>
                        

                        

                    </tbody>
                </table>
            <hr />
            <?php endif; ?>
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
                        <p>Your courses / subjects have been registered. To view your courses / subjects schedules, please wait for the accounting office to tag you as enrolled.
                    </div>
                <?php endif; ?>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary pull-right" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>

</div>
