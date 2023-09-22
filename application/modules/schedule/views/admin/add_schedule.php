<aside class="right-side">
<section class="content-header">
    <h1>
        Add Schedule
        <small></small>
    </h1>                    
</section>
<div class="content">
    <div class="pull-right">
        <div class="form-group">
            <label>Select Term</label>
            <select class="form-control" id="select-term-schedule">
            <?php foreach($sy as $s): ?>
                <option value="<?php echo $s['intID']; ?>" <?php echo ($s['intID'] == $active_sem['intID'])?'selected':''; ?> >
                    <?php echo $s['term_student_type']." ".$s['enumSem']." ".$s['term_label']." ".$s['strYearStart']." ".$s['strYearEnd']; ?>
                </option>
            <?php endforeach; ?>
            </select>
        </div>
    </div>
    <hr style="clear:both" />
    <div class="span10 box box-primary">
        <div class="box-header">
                <h3 class="box-title">New Schedule</h3>
        </div>
        <div class="col-md-6">
            <div class="alert alert-danger <?php echo ($alert == "" )?'hide':''; ?>  alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h4><i class="icon fa fa-ban"></i> Alert!</h4>
                <?php echo $alert; ?> See Suggested Time slots Below
            </div>
        </div>
        
        <hr style="clear:both" />
       
                </tr>
            
            <?php if(isset($suggested) && $suggested!="" && !empty($suggested)):?>
            <div class="container">
             <table class="table table-bordered">
                <tr>
                    <th>Room</th>
                    <th>Day/Schema</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Select</th>
                 </tr>
            <?php
                foreach($suggested as $sug):
            ?>
                    <form action="<?php echo base_url(); ?>schedule/submit_schedule" method="post" role="form">
                        <input type="hidden" name="strScheduleCode" value="<?php echo $sug['strScheduleCode']; ?>">
                        <input type="hidden" name="intRoomID" value="<?php echo $sug['intRoomID']; ?>">
                        <input type="hidden" name="strDay" value="<?php echo $sug['strDay']; ?>">
                        <input type="hidden" name="strSchema" value="<?php echo isset($sug['strSchema'])?$sug['strSchema']:0; ?>">
                        <input type="hidden" name="dteStart" value="<?php echo $sug['dteStart']; ?>">
                        <input type="hidden" name="dteEnd" value="<?php echo $sug['dteEnd']; ?>">
                        <input type="hidden" name="intSem" value="<?php echo $active_sem['intID']; ?>">
                        <input type="hidden" name="enumClassType" value="<?php echo $sug['enumClassType']; ?>">
                         <tr>
                            <td><?php echo $sug['roomCode']; ?></td>
                            <td><?php echo isset($sug['strSchema'])?switch_day_schema($sug['strSchema']):get_day($sug['strDay']); ?></td>
                            <td><?php echo $sug['dteStart']; ?></td>
                            <td><?php echo $sug['dteEnd']; ?></td>
                            <td><input class="btn btn-primary btn-flat" type="submit" /></td>
                        </tr>
                    </form>
                   
            <?php 
                endforeach;?>
                 </table>    
                </div>
            <?php
                endif; ?>
            <form id="validate-schedule" action="<?php echo base_url(); ?>schedule/submit_schedule" method="post" role="form">
                 <div class="box-body">
                    <div class="form-group col-xs-12 col-lg-6">
                        <label for="strScheduleCode">Classlist</label>
                        <select name="strScheduleCode" class="form-control select2">
                            <?php foreach($classlists as $cl): ?>
                                <option value="<?php echo $cl['intID']; ?>"><?php echo $cl['strCode']." ".$cl['strClassName'].$cl['year'].$cl['strSection']; ?></option>
                            <?php endforeach; ?>
                         </select>
                    </div>                    
                    <div class="form-group col-xs-12 col-lg-6">
                        <label for="intRoomID">Room</label>
                        <select name="intRoomID" class="form-control">                                
                            <?php foreach($rooms as $rm): ?>
                                <option value="<?php echo $rm['intID'] ?>"><?php echo $rm['strRoomCode']; ?></option>
                            <?php endforeach; ?>
                         </select>
                    </div>                    
                    <div class="form-group col-xs-12 col-lg-6">
                        <label for="blockSectionID">Block Section</label>
                        <select name="blockSectionID" class="form-control select2">                                
                            <?php foreach($block_sections as $bs): ?>
                                <option value="<?php echo $bs['intID'] ?>"><?php echo $bs['name']; ?></option>
                            <?php endforeach; ?>
                         </select>
                    </div>                    
                    <div class="form-group col-xs-12 col-lg-6">
                        <label for="strDay">Day</label>
                        <select name="strDay" id="strDay" class="form-control">
                            <?php foreach($days as $key=>$val): ?>
                                <option value="<?php echo $key; ?>"><?php echo $val; ?></option>
                            <?php endforeach; ?>
                         </select>
                    </div>
                     <div class="form-group col-xs-12 col-lg-6">
                        <label for="strSchema">Schema</label>
                        <select name="strSchema" id="strSchema" class="form-control">
                            <?php foreach($schema as $key=>$val): ?>
                                <option value="<?php echo $key; ?>"><?php echo $val; ?></option>
                            <?php endforeach; ?>
                         </select>
                    </div>
                  
                     <hr />
                     <div class="form-group col-xs-12 col-lg-6">
                        <label for="dteStart">Time Start</label>
                        <select name="dteStart" id="dteStart" class="form-control">
                            <?php foreach($timeslots as $ts): ?>
                                <option value="<?php echo $ts; ?>"><?php echo $ts; ?></option>
                            <?php endforeach; ?>
                         </select>
                    </div>
                     <div class="form-group col-xs-12 col-lg-6">
                        <label for="dteEnd">Time End</label>
                        <select name="dteEnd" id="dteEnd" class="form-control">
                            <?php foreach($timeslots as $ts): ?>
                                <option value="<?php echo $ts; ?>"><?php echo $ts; ?></option>
                            <?php endforeach; ?>
                         </select>
                    </div>
                     <div class="form-group col-xs-12 col-lg-6">
                        <label for="enumClassType">Room Type</label>
                        <select name="enumClassType" class="form-control">
                            <?php foreach($types as $t): ?>
                                <option value="<?php echo $t; ?>"><?php echo $t; ?></option>
                            <?php endforeach; ?>
                         </select>
                    </div>
                     
                    
                     
                
                     <div class="form-group col-xs-12">
                         <input type="submit" value="add" class="btn btn-default  btn-flat">
                     </div>
                <div style="clear:both"></div>
            </form>
       
        </div>
</aside>