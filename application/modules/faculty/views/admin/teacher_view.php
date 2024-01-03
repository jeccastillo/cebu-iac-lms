<aside class="right-side">
<section class="content-header">
                    <h1>
                        Faculty
                        <small></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Faculty</a></li>
                        <li class="active">View All Faculty</li>
                    </ol>
                </section>
    <div class="content">
            <div class="alert alert-danger" style="display:none;">
                <i class="fa fa-ban"></i>
                <b>Alert!</b> Only admins can delete student records.
            </div>            

            <div class="box box-solid box-danger">
                <div class="box-header">
                    <h3 class="box-title">List of Faculty</h3>
                    <div class="box-tools">
                        <div class="form-group">
                            <label>Select Term</label>
                            <select id="sem">
                                <?php foreach($sy as $s): ?>
                                    <option value="<?php echo $s['intID']; ?>">
                                        <?php echo $s['term_student_type']." ".$s['enumSem']." ".$s['term_label']." ".$s['strYearStart']."-".$s['strYearEnd']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div><!-- /.box-header -->
                <div class="box-body table-responsive">
                    <table id="faculty-table" class="table">
                        <thead><tr>
                            <th>id</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Select Actions</th>
                        </tr>
                        </thead>
                        <tbody>

                    </tbody></table>
                </div><!-- /.box-body -->
            </div><!-- /.box -->
    </div>
    </div>
</aside>