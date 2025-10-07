<aside class="right-side">
<section class="content-header">
                    <h1>
                        Subject Offering
                        <small>
                            <?php if($dissolved == 0): ?>
                                <a class="btn btn-app" href="<?php echo base_url()."unity/view_classlist_archive_admin/".$selected_ay."/".$program."/1/".$has_faculty."/".$status ?>" ><i class="fa fa-list"></i> Show Dissolved</a>
                            <?php else: ?>
                                <a class="btn btn-app" href="<?php echo base_url()."unity/view_classlist_archive_admin/".$selected_ay."/".$program."/0/".$has_faculty."/".$status ?>"><i class="fa fa-list"></i> Show Non Dissolved</a>
                            <?php endif; ?>
                            <a class="btn btn-app" href="<?php echo base_url() ?>excel/download_classlists/<?php echo $selected_ay.'/'.$program.'/'.$dissolved.'/'.$has_faculty."/".$status; ?>"><i class="fa fa-download"></i> Download Report</a>
                            <?php if($has_faculty == 0): ?>
                                <a class="btn btn-app" href="<?php echo base_url()."unity/view_classlist_archive_admin/".$selected_ay."/".$program."/".$dissolved."/1/".$status ?>" ><i class="fa fa-user"></i> With Faculty</a>                                                                
                            <?php else: ?>                                                                
                                <a class="btn btn-app" href="<?php echo base_url()."unity/view_classlist_archive_admin/".$selected_ay."/".$program."/".$dissolved."/0/".$status?>"><i class="fa fa-file"></i> Show All</a>
                            <?php endif; ?>
                            <?php if($status == 0): ?>
                                <a class="btn btn-app" href="<?php echo base_url()."unity/view_classlist_archive_admin/".$selected_ay."/".$program."/".$dissolved."/".$has_faculty."/1" ?>" ><i class="fa fa-list"></i> No Grades Submitted</a>
                                <a class="btn btn-app" href="<?php echo base_url()."unity/view_classlist_archive_admin/".$selected_ay."/".$program."/".$dissolved."/".$has_faculty."/2" ?>" ><i class="fa fa-list"></i> Midterms Grade Submitted</a>
                                <a class="btn btn-app" href="<?php echo base_url()."unity/view_classlist_archive_admin/".$selected_ay."/".$program."/".$dissolved."/".$has_faculty."/3" ?>" ><i class="fa fa-list"></i> Final Grade Submitted</a>                                                                                                
                            <?php elseif($status == 1): ?>                                                                
                                <a class="btn btn-app" href="<?php echo base_url()."unity/view_classlist_archive_admin/".$selected_ay."/".$program."/".$dissolved."/".$has_faculty."/0" ?>" ><i class="fa fa-list"></i> All Statuses</a>
                                <a class="btn btn-app" href="<?php echo base_url()."unity/view_classlist_archive_admin/".$selected_ay."/".$program."/".$dissolved."/".$has_faculty."/2" ?>" ><i class="fa fa-list"></i> Midterms Grade Submitted</a>
                                <a class="btn btn-app" href="<?php echo base_url()."unity/view_classlist_archive_admin/".$selected_ay."/".$program."/".$dissolved."/".$has_faculty."/3" ?>" ><i class="fa fa-list"></i> Final Grade Submitted</a>                                                                
                            <?php elseif($status == 2): ?>                                                                
                                <a class="btn btn-app" href="<?php echo base_url()."unity/view_classlist_archive_admin/".$selected_ay."/".$program."/".$dissolved."/".$has_faculty."/0" ?>" ><i class="fa fa-list"></i> All Statuses</a>
                                <a class="btn btn-app" href="<?php echo base_url()."unity/view_classlist_archive_admin/".$selected_ay."/".$program."/".$dissolved."/".$has_faculty."/1" ?>" ><i class="fa fa-list"></i> No Grades Submitted</a>
                                <a class="btn btn-app" href="<?php echo base_url()."unity/view_classlist_archive_admin/".$selected_ay."/".$program."/".$dissolved."/".$has_faculty."/3" ?>" ><i class="fa fa-list"></i> Final Grade Submitted</a>                                                                
                            <?php elseif($status == 3): ?>                                                                
                                <a class="btn btn-app" href="<?php echo base_url()."unity/view_classlist_archive_admin/".$selected_ay."/".$program."/".$dissolved."/".$has_faculty."/0" ?>" ><i class="fa fa-list"></i> All Statuses</a>
                                <a class="btn btn-app" href="<?php echo base_url()."unity/view_classlist_archive_admin/".$selected_ay."/".$program."/".$dissolved."/".$has_faculty."/1" ?>" ><i class="fa fa-list"></i> No Grades Submitted</a>
                                <a class="btn btn-app" href="<?php echo base_url()."unity/view_classlist_archive_admin/".$selected_ay."/".$program."/".$dissolved."/".$has_faculty."/2" ?>" ><i class="fa fa-list"></i> Midterms Grade Submitted</a>                                                                
                            <?php endif; ?>
                            <?php if($modular == 0): ?>
                                <a class="btn btn-app" href="<?php echo base_url()."unity/view_classlist_archive_admin/".$selected_ay."/".$program."/".$dissolved."/".$has_faculty."/".$status."/1" ?>" ><i class="fa fa-list"></i> Show Modular</a>
                            <?php else: ?>
                                <a class="btn btn-app" href="<?php echo base_url()."unity/view_classlist_archive_admin/".$selected_ay."/".$program."/".$dissolved."/".$has_faculty."/".$status."/0" ?>" ><i class="fa fa-list"></i> Show All</a>
                            <?php endif; ?>
                                <button type="button" class="btn btn-app" data-toggle="modal" data-target="#mergeClasslistModal">Merge Classlist</button>

                        </small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="<?php echo base_url(); ?>unity/view_classlist"><i class="ion ion-ios7-locked"></i> Admin</a></li>
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
                    <select id="select-sem-admin" class="form-control input-sm" >
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
                        <th>Subject</th>
                        <th>Section - Title</th>
                        <th>Section - Year</th>
                        <th>Section - Number</th>
                        <th>Section - Sub</th>
                        <th>Desc</th>
                        <th>Slots Enrolled</th>
                        <th>Slots Enlisted</th>
                        <th>Slots Remaining</th>
                        <th>Faculty</th>                        
                        <th>Finalized Status</th>
                        <th>Conduct Grade</th>
                        <th>Actions</th>
<!--                        <th>No. of Students</th>-->
                        <!--th>Change Status</th-->                        
                    </tr>
                    </thead>
                    <tbody>

                </tbody>
                <tfoot>
                    <tr>
                    <th>id</th>                        
                        <th>Subject</th>
                        <th>Section - Title</th>
                        <th>Section - Year</th>
                        <th>Section - Number</th>
                        <th>Section - Sub</th>
                        <th>Desc</th>
                        <th>Slots Enrolled</th>
                        <th>Slots Enlisted</th>
                        <th>Slots Remaining</th>
                        <th>Faculty</th>                        
                        <th>Finalized Status</th>
                        <th>Conduct Grade</th>
                        <th>Actions</th>                                             
                    </tr>
                        </tfoot>
                </table>
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </div>

    <!-- Merge Classlist Modal -->
    <div class="modal fade" id="mergeClasslistModal" tabindex="-1" role="dialog" aria-labelledby="mergeClasslistModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="mergeClasslistModalLabel">Merge Classlist</h4>
                </div>
                <div class="modal-body">
                    <form id="mergeForm">
                        <div class="form-group">
                            <label for="mergeFrom">Section to Merge</label>
                            <select class="form-control" id="mergeFrom" name="mergeFrom">
                                <option value="">Select Section</option>
                                <?php foreach($classlists as $class): ?>
                                    <option value="<?php echo $class['intID']; ?>"><?php echo $class['strCode'] . ' - ' . $class['strClassName'].'-'.$class['strSection']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="mergeTo">Merge To</label>
                            <select class="form-control" id="mergeTo" name="mergeTo">
                                <option value="">Select Section</option>
                                <?php foreach($classlists as $class): ?>
                                    <option value="<?php echo $class['intID']; ?>"><?php echo $class['strCode'] . ' - ' . $class['strClassName'].'-'.$class['strSection']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="mergeBtn">Merge</button>
                </div>
            </div>
        </div>
    </div>
</aside>