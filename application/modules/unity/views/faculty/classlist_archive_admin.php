<aside class="right-side">
<section class="content-header">
                    <h1>
                        All Classlists
                        <small></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="<?php echo base_url(); ?>unity/view_classlist"><i class="ion ion-ios7-locked"></i> Admin</a></li>
                        <li class="active">Classlist Archive</li>
                    </ol>
                </section>
    <div class="content">
        <div class="alert alert-danger" style="display:none;">
            <i class="fa fa-ban"></i>
            <b>Alert!</b> Classlist is already finalized and cannot be deleted.
        </div>
        <div class="box box-solid box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">Classlist Admin</h3>
                <div class="box-tools pull-right">
                    <select id="select-sem-admin" class="form-control input-sm" >
                        <?php foreach($sy as $s): ?>
                            <option <?php echo ($selected_ay == $s['intID'])?'selected':''; ?> value="<?php echo $s['intID']; ?>"><?php echo $s['term_student_type']." ".$s['enumSem']." ".$s['term_label']." ".$s['strYearStart']."-".$s['strYearEnd']; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#mergeClasslistModal">Merge Classlist</button>
                </div>
            </div><!-- /.box-header -->
            <div class="box-body table-responsive">
                <table id="classlist-table-admin" class="table table-bordered table-hover">
                    <thead><tr>
                        <th>Section</th>
                        <th>Instructor</th>
                        <th>Students</th>
                        <th>Finalized Status</th>
                        <th>Change Status</th>
                        <th>Subject</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php 
                        $ctr = 1;
                        foreach($classlists as $class): ?>
                        <tr>
                            <!--<td><?php echo $ctr; ?>.</td>-->
                            <td><?php echo $class['strClassName'].'-'.$class['strSection']; ?></td>
                            <td><?php echo $class['strFirstname']." ".$class['strLastname']; ?></td>
                            <td><?php echo $class['students'] . " students"; ?></td>
                            <td><?php echo ($class['intFinalized'] == 1)?'F-Yes':'F-No'; ?></td>
                            <td>
                                <select rel="<?php echo $class['intID']; ?>" class="form-control finalizedOption">
                                    <option value="0" <?php echo ($class['intFinalized'] == 0)?'selected':''; ?>>Not Yet Submitted</option>
                                    <option value="1" <?php echo ($class['intFinalized'] == 1)?'selected':''; ?>>Finalized</option>
                                </select>
                            </td>
                            <td><?php echo $class['strCode']; ?></td>
                            <td>
                            <div class="btn-group">
                                <button type="button" class="btn btn-default">Actions</button>
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                    <span class="caret"></span>
                                    <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a href="<?php echo base_url() ?>unity/edit_classlist/<?php echo $class['intID']; ?>"><i class="ion ion-ios7-compose"></i> Edit</a></li>
                                     <li><a href="<?php echo base_url() ?>unity/reassign_classlist/<?php echo $class['intID']; ?>"><i class="ion ion-share"></i> Re-assign</a></li>
              <li><a href="<?php echo base_url() ?>unity/classlist_viewer/<?php echo $class['intID']; ?>"><i class="ion ion-ios7-eye"></i> View</a></li>
                                     <li><a href="<?php echo base_url() ?>unity/duplicate_classlist/<?php echo $class['intID']; ?>"><i class="ion ion-ios7-copy"></i> Duplicate</a></li>

              <li> <a href="#" class="trash-classlist" rel="<?php echo $class['intID']; ?>"><i class="ion ion-trash-a"></i> Delete</a></li>
                                </ul>

                        </div>
                    </td>
                        </tr>
                    <?php endforeach; ?>

                </tbody></table>
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
