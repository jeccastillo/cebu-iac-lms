<aside class="right-side">
<section class="content-header">
                    <h1>
                        Subject Offering
                        <small>
                            <?php if($dissolved == 0): ?>
                                <a class="btn btn-app" href="<?php echo base_url()."academics/view_classlist_archive_admin/".$selected_ay."/".$program."/1" ?>" ><i class="fa fa-list"></i> Show Dissolved</a>
                            <?php else: ?>
                                <a class="btn btn-app" href="<?php echo base_url()."academics/view_classlist_archive_admin/".$selected_ay."/".$program."/0" ?>"><i class="fa fa-list"></i> Show Non Dissolved</a>
                            <?php endif; ?>                                                       

                        </small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="<?php echo base_url(); ?>academics/view_classlist"><i class="ion ion-ios7-locked"></i> Admin</a></li>
                        <li class="active">Subject Offering</li>
                    </ol>
                </section>
    <div class="content">
        <div class="alert alert-danger" style="display:none;">
            <i class="fa fa-ban"></i>
            <b>Alert!</b> Classlist is already finalized and cannot be deleted.
        </div>
        <div class="alert alert-danger alert-dissolved" style="display:none;">
            <i class="fa fa-ban"></i>
            <b>Failed to dissolve!</b> Please check if classlist still has students enlisted or enrolled.
        </div>
        <div class="box box-solid box-danger">
            <div class="box-header with-border">
                <h3 class="box-title"><?php echo ($dissolved == 0)?'Classlists/Subjects Offered':'Dissolved Sections'; ?></h3>                
                <div class="box-tools pull-right">
                    <select id="select-sem-admin-ac" class="form-control input-sm" >
                        <?php foreach($sy as $s): ?>
                            <option <?php echo ($selected_ay == $s['intID'])?'selected':''; ?> value="<?php echo $s['intID']; ?>"><?php echo $s['term_student_type']." ".$s['enumSem']." ".$term_type." ".$s['strYearStart']."-".$s['strYearEnd']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div><!-- /.box-header -->
            <div class="box-body" style="overflow-x:auto;">
                <table id="classlist-table-admin" class="table table-bordered table-hover">
                    <thead><tr>
                        <th>id</th>
                        <th>Program</th>
                        <th>Subject</th>
                        <th>Section - Title</th>
                        <th>Section - Year</th>
                        <th>Section - Number</th>
                        <th>Section - Sub</th>
                        <th>Slots Enrolled</th>
                        <th>Slots Enlisted</th>
                        <th>Slots Remaining</th>
                        <th>Faculty</th>                        
                        <th>Finalized Status</th>
<!--                        <th>No. of Students</th>-->
                        <!--th>Change Status</th-->                        
                    </tr>
                    </thead>
                    <tbody>

                </tbody>
                <tfoot>
                    <tr>
                    <th>id</th>
                        <th>Program</th>
                        <th>Subject</th>
                        <th>Section - Title</th>
                        <th>Section - Year</th>
                        <th>Section - Number</th>
                        <th>Section - Sub</th>
                        <th>Slots Enrolled</th>
                        <th>Slots Enlisted</th>
                        <th>Slots Remaining</th>
                        <th>Faculty</th>                        
                        <th>Finalized Status</th>                                              
                    </tr>
                        </tfoot>
                </table>
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </div>
</aside>