<aside class="right-side">
<section class="content-header">
                    <h1>
                        Suggested Schedules
                        <small></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Admin</a></li>
                        <li class="active">Add Schedule</li>
                    </ol>
                </section>
<div class="content">
    <div class="box box-primary col-md-10">
        <div class="box-header">
            <h3 class="box-title">New Schedule</h3>
        </div>
        <div>
            <div class="alert alert-danger <?php echo ($alert == "" )?'hide':''; ?>  alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h4><i class="icon fa fa-ban"></i> Alert!</h4>
                <?php echo $alert; ?>
            </div>
            <a href="<?php echo base_url(); ?>schedule/add_schedule" class="btn btn-flat btn-primary">Set Manual Schedule</a>
        </div>
        
        <hr style="clear:both" />
        
            
            <?php if(isset($suggested) && $suggested!="" && !empty($suggested)):?>
             <table id="schedule-table" class="table">
                 <thead>
                    <tr>
                        <th>Room</th>
                        <th>Day/Schema</th>
                        <th>Time</th>
                        <th>Select</th>
                     </tr>
                </thead>
                 <tbody>
            <?php
                foreach($suggested as $sug):
            ?>
                    <form action="<?php echo base_url(); ?>schedule/submit_schedule" method="post" role="form">
                        <input type="hidden" name="strScheduleCode" value="<?php echo $sug['strScheduleCode']; ?>">
                        <input type="hidden" name="intRoomID" value="<?php echo $sug['intRoomID']; ?>">
                        <input type="hidden" name="strDay" value="<?php echo $sug['strDay']; ?>">
                        <input type="hidden" name="strSchema" value="<?php echo isset($sug['schema'])?$sug['schema']:0; ?>">
                        <input type="hidden" name="dteStart" value="<?php echo $sug['dteStart']; ?>">
                        <input type="hidden" name="dteEnd" value="<?php echo $sug['dteEnd']; ?>">
                        <input type="hidden" name="enumClassType" value="<?php echo $sug['enumClassType']; ?>">
                        
                         <tr>
                            <td><?php echo $sug['roomCode']; ?></td>
                            <td><?php echo isset($sug['schema'])?switch_day_schema($sug['schema']):get_day($sug['strDay']); ?></td>
                            <td><?php echo date("h:ia",strtotime($sug['dteStart']))." - ".date("h:ia",strtotime($sug['dteEnd'])); ?></td>
                            <td><input class="btn btn-primary btn-flat" type="submit" value="select" /></td>
                        </tr>
                    </form>
                   
            <?php 
                endforeach;?>
                     </tbody>
                 </table> 
            <?php
                endif; ?>
       
        </div>
</aside>