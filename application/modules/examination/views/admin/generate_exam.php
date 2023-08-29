<div class="content-wrapper ">
    <section class="content-header container ">
        <h1>
            Student Exam Link Generation
            <small>
                <a class="btn btn-app" href="#" id="print_form"><i class="fa fa-file"></i> Export to Excel</a>
            </small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Student Applicants</a></li>
            <li class="active">View All Leads</li>
        </ol>
        <hr />
        <div class="pull-right">
            <select id="select-term-leads" class="form-control">
                <?php foreach($sy as $s): ?>
                <option <?php echo ($current_sem == $s['intID'])?'selected':''; ?> value="<?php echo $s['intID']; ?>">
                    <?php echo $s['enumSem']." ".$term_type." ".$s['strYearStart']."-".$s['strYearEnd']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <hr />
    </section>
    <div class="content container">
        <div class="alert alert-danger" style="display:none;">
            <i class="fa fa-ban"></i>
            <span id="alert-text"></span>
        </div>
        <div class="box box-solid box-primary">
            <div class="box-header">
                <h3 class="box-title">Student Applicants</h3>

            </div><!-- /.box-header -->
            <div class="box-body table-responsive">
                <table id="subjects-table" class="table table-hover table-bordered">
                    <thead>
                        <tr>
                            <th>slug</th>
                            <th>Date</th>
                            <th>Date Interviewed</th>
                            <th>Date Reserved</th>
                            <th>Date Enrolled</th>
                            <th>Last Name</th>
                            <th>First Name</th>
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