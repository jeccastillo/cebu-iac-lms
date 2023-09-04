<aside class="right-side">
<section class="content-header">
                    <h1>
                        Classlist
                        <small></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="<?php echo base_url(); ?>unity/view_classlist"><i class="ion ion-android-book"></i> Classlist</a></li>
                        <li class="active">Archive</li>
                    </ol>
                </section>
    <div class="content">
        <div class="alert alert-danger" style="display:none;">
            <i class="fa fa-ban"></i>
            <b>Alert!</b> Classlist is already finalized and cannot be deleted.
        </div>
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">My Classes</h3>
                <div class="box-tools pull-right">
                    <select id="select-sem" class="form-control" >
                        <?php foreach($sy as $s): ?>
                            <option <?php echo ($selected_ay == $s['intID'])?'selected':''; ?> value="<?php echo $s['intID']; ?>"><?php echo $s['enumSem']." ".$term_type." ".$s['strYearStart']."-".$s['strYearEnd']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div><!-- /.box-header -->
            
               <hr />
            <div class="box-body no-padding">
                  <div class="mailbox-controls">
                    <!-- Check all button -->
                    
                    <div class="btn-group">
                        <button class="btn btn-default btn-sm checkbox-toggle"><i class="fa fa-square-o"></i></button>
                        <button type="button" class="btn btn-default btn-sm">With Selected</button>
                        <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                            <span class="caret"></span>
                            <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu" role="menu">
                            <li><a href="#" class="delete-classlist"><i class="fa fa-trash-o"></i> Delete Classlist</a></li>
                            <li><a href="#" class="download-classlist"><i class="fa fa-download"></i> Download Classlist</a></li>
                        </ul>
                    </div>
                    
                  </div>
            </div>
            <hr />
            <form action="<?php echo base_url().'excel/download_classlists_archive'; ?>"  method="post" id="download-archive">
            <div class="box-body table-responsive">
                <table id="classlist-archive-table" class="table table-hover">
                    <thead><tr>
                        <th>id</th>
                        <th>Section</th>
                        <th>Term/Sem</th>
                        <th>Subject</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <!--<?php // foreach($classlists as $class): ?>
                        <tr>
                            <td><?php echo $class['strClassName'].'-'.$class['strSection']; ?></td>
                            <td><?php echo $class['enumSem']." ".$term_type." ".$class['strYearStart']."-".$class['strYearEnd']; ?></td>
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
              <li><a href="<?php echo base_url() ?>unity/classlist_viewer/<?php echo $class['intID']; ?>"><i class="ion ion-ios7-eye"></i> View</a></li>
              <li> <a href="#" class="trash-classlist" rel="<?php echo $class['intID']; ?>"><i class="ion ion-trash-a"></i> Delete</a></li>
                                </ul>

                        </div>
                    </td>
                        </tr>
                    <?php // endforeach; ?>
-->
                </tbody></table>
            </div><!-- /.box-body -->
            </form>
        </div><!-- /.box -->
                       
    </div>
</aside>