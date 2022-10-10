<aside class="right-side">
<section class="content-header">
                    <h1>
                        <small>
                                                  <a class="btn btn-app" href="<?php echo base_url() ?>classroom/view_classrooms" ><i class="ion ion-arrow-left-a"></i>Back</a> 
                            <a class="btn btn-app trash-classroom" rel="<?php echo $item['intID']; ?>" href="#"><i class="ion ion-android-close"></i> Delete</a>   
                            <a class="btn btn-app" href="<?php echo base_url()."classroom/edit_classroom/".$item['intID']; ?>"><i class="ion ion-edit"></i> Edit</a> 
                        
                        </small>
                        <div class="pull-right">
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
        <input type="hidden" value="<?php echo $item['intID']; ?>" id="classroom-id" />
        <input type="hidden" value="<?php echo $item['strRoomCode']; ?>" id="subject-code-viewer" />
        <div class="box-body">
            <div class="alert alert-page alert-danger" style="display:none;">
                <i class="fa fa-ban"></i>
                <b>Alert!</b> Schedules can only be deleted by Administrator.
            </div>
            <div class="col-xs-8 col-lg-4">
              <h3><?php 
                        
                        echo $item['strRoomCode']; ?>
             </h3>
                <h5><?php echo $item['enumType']; ?></h5>
            </div>

            <div style="clear:both"></div>
        </div>
       
        </div>
    
    
    <div class="box box-solid box-primary">
         <div class="box-header">
            <h4 class="box-title">Schedule</h4>
        </div><!-- /.box-header -->
        <div class="box-body">
            <table class="table table-bordered">

                <tbody>
                    <?php 
                    $prev_day = "Sun";
                    if(!empty($schedules)): 
                    foreach($schedules as $class): 
                        if($class['strDay'] != $prev_day):
                    ?>
                    <tr>
                        <th style="background:#f2f2f2;" colspan=4><?php echo $class['strDay']; ?></th>
                    </tr>
                    <?php endif; ?>
                    
                    <tr>
                        <td width="30%"><?php echo date('g:ia',strtotime($class['dteStart'])).' - '.date('g:ia',strtotime($class['dteEnd'])); ?></td>
                        <td width="40%"><?php echo $class['strCode']." ".$class['strSection']; ?></td>
                        <td class="text-center" width="15%">
                            <a style="color:#333" href="<?php echo base_url().'schedule/edit_schedule/'.$class['intRoomSchedID']; ?>"><i class="ion ion-edit" style="font-size:2em;"></i></a> </td>
                        <?php
                                                    $hourdiff = round((strtotime($class['dteEnd']) - strtotime($class['dteStart']))/3600, 1);
                                                    
                                                ?>
                        <input type="hidden" class="<?php echo $class['strDay']; ?>" value="<?php echo date('gia',strtotime($class['dteStart'])); ?>" href="<?php echo $hourdiff*2; ?>" rel="<?php echo $class['strCode']; ?> <?php echo $class['strSection']; ?>" data-faculty="<?php echo $class['strLastname'].", ".$class['strFirstname']; ?>" />
                        <td class="text-center" width="15%">
                        <a style="color:#333;" href="#" rel="<?php echo $class['intRoomSchedID']; ?>" class="trash-schedule"><i class="ion ion-trash-a" style="font-size:2em;"></i></a>
                        </td>
                        
                    </tr>
                    <?php 
                        $prev_day = $class['strDay'];
                        endforeach; 
                        else:
                    ?>
                    <tr>
                        <th>No Schedules for this Room</th>
                    </tr>
                    <?php
                        endif;
                    ?>
                </tbody>

            </table>
        </div>
    </div>
    <div class="box box-primary">
        <div class="box-body">
            <form method="post" action="<?php echo base_url() ?>pdf/print_sched">   
            <input type="hidden" name="sched-table" id="sched-table" />
            <input type="hidden" value="<?php echo "CLASSROOM: ".$item['strRoomCode']; ?>" name="studentInfo" id="studentInfo" />

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
    
<div class="modal fade" id="addSched" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Add a Schedule for Room <?php echo $item['strRoomCode']; ?></h4>
      </div>
      <div class="modal-body">
          <div class="alert alert-modal alert-danger" style="display:none;">
                <i class="fa fa-ban"></i>
              <span id="sched-alert"></span>
            </div>
                <div class="form-group col-xs-12 col-lg-6">
                        <label for="subject">Subject</label>
                        <br />
                        <select id="subject" style="width:100%" name="subject" class="form-control select2">
                            <?php foreach($subjects as $subject): ?>
                                <option value="<?php echo $subject['intID']; ?>"><?php echo $subject['strCode']; ?></option>
                            <?php endforeach; ?>
                         </select>
                    </div>
                    <div class="form-group col-xs-12 col-lg-6">
                        <label for="section">Section</label>
                        <input id="section" type="text" name="section" class="form-control">
                    </div>
                    
                     <input type="hidden" id="intRoomID" name="intRoomID" value="<?php echo $item['intID']; ?>" />
                     <hr />
                     <div class="form-group col-xs-12 col-lg-6">
                        <label for="strDay">Day</label>
                        <select id="strDay" name="strDay" class="form-control">
                            <?php foreach($days as $key=>$val): ?>
                                <option value="<?php echo $key; ?>"><?php echo $val; ?></option>
                            <?php endforeach; ?>
                         </select>
                    </div>
                  
                     <hr />
                     <div class="form-group col-xs-12 col-lg-6">
                        <label for="dteStart">Time Start</label>
                        <select id="dteStart" name="dteStart" class="form-control">
                            <?php foreach($timeslots as $ts): ?>
                                <option value="<?php echo $ts; ?>"><?php echo $ts; ?></option>
                            <?php endforeach; ?>
                         </select>
                    </div>
                     <div class="form-group col-xs-12 col-lg-6">
                        <label for="dteEnd">Time End</label>
                        <select id="dteEnd" name="dteEnd" class="form-control">
                            <?php foreach($timeslots as $ts): ?>
                                <option value="<?php echo $ts; ?>"><?php echo $ts; ?></option>
                            <?php endforeach; ?>
                         </select>
                    </div>
                     <div class="form-group col-xs-12 col-lg-6">
                        <label for="enumClassType">Room Type</label>
                        <select id="enumClassType" name="enumClassType" class="form-control">
                            <?php foreach($types as $t): ?>
                                <option value="<?php echo $t; ?>"><?php echo $t; ?></option>
                            <?php endforeach; ?>
                         </select>
                    </div>
                     
      </div>
      <div class="modal-footer">
        <button type="button" id="addSchedBtn" class="btn btn-default  btn-flat">Add Schedule</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

