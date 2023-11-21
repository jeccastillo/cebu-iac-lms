<aside class="right-side">
    <section class="content-header">
        <h1>
            <div class="pull-right">
                <select id="select-sem-schedule" class="form-control" >
                    <?php foreach($sy as $s): ?>
                        <option <?php echo ($sem == $s['intID'])?'selected':''; ?> value="<?php echo $s['intID']; ?>"><?php echo $s['term_student_type']." ".$s['enumSem']." ".$s['term_label']." ".$s['strYearStart']."-".$s['strYearEnd']; ?></option>
                    <?php endforeach; ?>
                </select>
                <hr />
                <select id="select-section-schedule" class="form-control select2" >
                    <option value="0">All</option>
                    <?php foreach($block_sections as $s): ?>
                        <option <?php echo ($section == $s['intID'])?'selected':''; ?> value="<?php echo $s['intID']; ?>"><?php echo $s['name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <a class="btn btn-success" href="<?php echo base_url()."excel/download_schedules/".$sem; ?>">Download Schedules</a>
            <div style="clear:both"></div>
        </h1>
    </section>
    <div class="content">
        <div class="alert alert-danger" style="display:none;">
            <i class="fa fa-ban"></i>
            <b>Alert!</b> <span id="alert-container"></span>
        </div>
        <div class="box box-solid box-danger">
            <div class="box-header">                  
                <h3 class="box-title"><i class="ion ion-calendar"></i> Schedules</h3>
                <div class="box-tools">
                </div>
            </div><!-- /.box-header -->
            
            <div class="box-body" style="overflow-x:auto;">
                <table id="users_table" class="table table-hover table-bordered">
                    <thead>
                        <tr>
                            <th>id</th>
                            <th>Subject</th>
                            <th>Section Name</th>
                            <th>Section Year</th>
                            <th>Section #</th>                            
                            <th>Sub-Section</th>
                            <th>Block</th>
                            <th>Day</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Class Type</th>
                            <th>Room</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                    <tr>
                        <th>id</th>
                        <th>Subject</th>
                        <th>Section Name</th>
                        <th>Section Year</th>
                        <th>Section #</th>                        
                        <th>Sub-Section</th>
                        <th>Block</th>
                        <th>Day</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Class Type</th>
                        <th>Room</th>
                        <th>Actions</th>                                           
                    </tr>
                        </tfoot>
                </table>
                </table>
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </div>
</aside>

