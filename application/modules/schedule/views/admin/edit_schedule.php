<aside class="right-side">
<section class="content-header">
                    <h1>
                        Edit Schedule
                        <small></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Admin</a></li>
                        <li class="active">Add Schedule</li>
                    </ol>
                </section>
<div class="content">
    <div class="span10 box box-primary">
        <div class="box-header">
                <h3 class="box-title">Update Schedule for <?php echo $item['strCode']." ".$item['strClassName'].$item['year'].$item['strSection']; ?></h3>
        </div>
        <div class="col-md-6">
            <div class="alert alert-danger <?php echo ($alert == "" )?'hide':''; ?>  alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h4><i class="icon fa fa-ban"></i> Alert!</h4>
                <?php echo $alert; ?>
            </div>
        </div>
        
        <hr style="clear:both" />
            
            <form id="validate-schedule" action="<?php echo base_url(); ?>schedule/submit_edit_schedule" method="post" role="form">
                <input type="hidden" name="intRoomSchedID" value="<?php echo $item['intRoomSchedID'] ?>" />
                <input type="hidden" name="intEncoderID" value="<?php echo $item['intEncoderID'] ?>" />
                 <input type="hidden" name="strScheduleCode" value="<?php echo $item['strScheduleCode'] ?>" />
                <input type="hidden" name="intSem" value="<?php echo $item['intSem'] ?>" />
                 <div class="box-body">
                    
                    <div class="form-group col-xs-12 col-lg-6">
                        <label for="intRoomID">Room</label>
                        <select name="intRoomID" class="form-control">                            
                            <?php foreach($rooms as $rm): ?>
                                <option <?php echo ($rm['intID'] == $item['intRoomID'])?'selected':''; ?> value="<?php echo $rm['intID'] ?>"><?php echo $rm['strRoomCode']; ?></option>
                            <?php endforeach; ?>
                         </select>
                    </div>
                    <div class="form-group col-xs-12 col-lg-6">
                        <label for="blockSectionID">Block Section</label>
                        <select name="blockSectionID" class="form-control select2">                                
                            <?php foreach($block_sections as $bs): ?>
                                <option <?php echo ($bs['intID'] == $item['blockSectionID'])?'selected':''; ?> value="<?php echo $bs['intID'] ?>"><?php echo $bs['name']; ?></option>
                            <?php endforeach; ?>
                         </select>
                    </div>                    
                    <div class="form-group col-xs-12 col-lg-6">
                        <label for="strDay">Day</label>
                        <select name="strDay" class="form-control">
                            <?php foreach($days as $key=>$val): ?>
                                <option <?php echo ($val == $item['strDay'])?'selected':''; ?> value="<?php echo $key; ?>"><?php echo $val; ?></option>
                            <?php endforeach; ?>
                         </select>
                    </div>                                
                    <div class="form-group col-xs-12 col-lg-6">
                        <label for="date_specific">Date (for one day class)</label>
                        <input class="form-control" value="<?php echo $item['date_specific']; ?>" type="date" id="date_specific" name="date_specific" /> 
                    </div>
                     <div class="form-group col-xs-12 col-lg-6">
                        <label for="dteStart">Time Start</label>
                        <select name="dteStart" id="dteStart" class="form-control">
                            <?php foreach($timeslots as $ts): ?>
                                <option <?php echo ($ts == date('G:i',strtotime($item['dteStart'])))?'selected':''; ?> value="<?php echo $ts; ?>"><?php echo $ts; ?></option>
                            <?php endforeach; ?>
                         </select>
                    </div>
                     <div class="form-group col-xs-12 col-lg-6">
                        <label for="dteEnd">Time End</label>
                        <select name="dteEnd" id="dteEnd" class="form-control">
                            <?php foreach($timeslots as $ts): ?>
                                <option <?php echo ($ts == date('G:i',strtotime($item['dteEnd'])))?'selected':''; ?> value="<?php echo $ts; ?>"><?php echo $ts; ?></option>
                            <?php endforeach; ?>
                         </select>
                    </div>
                     <div class="form-group col-xs-12 col-lg-6">
                        <label for="enumClassType">Room Type</label>
                        <select name="enumClassType" class="form-control">
                            <?php foreach($types as $t): ?>
                                <option <?php echo ($t == $item['enumClassType'])?'selected':''; ?> value="<?php echo $t; ?>"><?php echo $t; ?></option>
                            <?php endforeach; ?>
                         </select>
                    </div>
                     
                    
                     
                
                     <div class="form-group col-xs-12">
                         <input type="submit" value="update" class="btn btn-default  btn-flat">
                     </div>
                <div style="clear:both"></div>
            </form>
       
        </div>
</aside>