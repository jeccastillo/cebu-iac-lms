<aside class="right-side">
<section class="content-header">
                    <h1>
                        Subject Offering
                        <small></small>
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
        <div class="box box-solid box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">Classlists/Subject Offered</h3>
                <div class="box-tools pull-right">
                    <select id="select-sem-admin" class="form-control input-sm" >
                        <?php foreach($sy as $s): ?>
                            <option <?php echo ($selected_ay == $s['intID'])?'selected':''; ?> value="<?php echo $s['intID']; ?>"><?php echo $s['enumSem']." ".$term_type." ".$s['strYearStart']."-".$s['strYearEnd']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div><!-- /.box-header -->
            <div class="box-body table-responsive">
                <table id="classlist-table-admin" class="table table-bordered table-hover">
                    <thead><tr>
                        <th>id</th>
                        <th>Program</th>
                        <th>Subject</th>
                        <th>Section</th>
                        <th>Faculty</th>                        
                        <th>Finalized Status</th>
<!--                        <th>No. of Students</th>-->
                        <!--th>Change Status</th-->
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>

                </tbody>
                <tfoot>
                    <tr>
                    <th>id</th>
                        <th>Program</th>
                        <th>Subject</th>
                        <th>Section</th>
                        <th>Faculty</th>                        
                        <th>Finalized Status</th>                                                
                    </tr>
                        </tfoot>
                </table>
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </div>
</aside>