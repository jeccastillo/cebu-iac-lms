<div class="content-wrapper ">
    <section class="content-header container ">
        <h1>
            Interviewed Applicants
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Applicants</a></li>
            <li class="active">Interviewed</li>
        </ol>
        <hr />        
    </section>
    <div class="content mcontainer container">
        <div class="alert alert-danger" style="display:none;">
            <i class="fa fa-ban"></i>
            <span id="alert-text"></span>
        </div>        
        <div class="box box-solid box-primary">
            <div class="box-header">
                <h3 class="box-title">Interviewed List</h3>
                                
            </div><!-- /.box-header -->
            <div class="box-body table-responsive">
                <div class="pull-right form-group">
                    <label>Term Filter</label>
                    <select id="select-term-reserved" class="form-control" >
                        <?php foreach($sy as $s): ?>
                            <option <?php echo ($current_sem == $s['intID'])?'selected':''; ?> value="<?php echo $s['intID']; ?>"><?php echo $s['term_student_type']." ".$s['enumSem']." ".$s['term_label']." ".$s['strYearStart']."-".$s['strYearEnd']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <hr style="clear:both" />
                <table id="subjects-table" class="table table-hover table-bordered">
                    <thead>
                        <tr>
                            <th>slug</th>
                            <th>Date</th>
                            <th>Last Name</th>
                            <th>First Name</th>
                            <th>Email</th>
                            <th>Program</th>
                            <th>Status</th>
                            <th>Actions</th>

                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </div>
</div>
</div>