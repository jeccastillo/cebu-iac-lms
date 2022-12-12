<div class="content-wrapper ">
    <section class="content-header container ">
        <h1>
            Student Applicants
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Student Applicants</a></li>
            <li class="active">View All Leads</li>
        </ol>
        <hr />
        <select id="status_filter">
            <option value="none" selected>None</option>
            <option value="New" selected>New Applicant</option>
            <option value="For Interview" selected>For Interview</option>
            <option value="For Reservation" selected>For Reservation</option>
            <option value="Reserved" selected>Reserved</option>
            <option value="For Enrollment" selected>For Enrollment</option>
        </select>
    </section>
    <div class="content mcontainer container">
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