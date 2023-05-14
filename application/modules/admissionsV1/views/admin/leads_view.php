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
        <div class="row">
            <div class="col-sm-4">
                <p>Filter by Status</p>
                <select class="form-select form-control" id="status_filter">
                    <option value="none" selected>None</option>
                    <option value="New">New Applicant</option>
                    <option value="Waiting for Interview">Waiting for Interview</option>
                    <option value="For Interview">For Interview</option>
                    <option value="For Reservation">For Reservation</option>
                    <option value="Reserved">Reserved</option>
                    <option value="For Enrollment">For Enrollment</option>
                    <option value="Confirmed">Complete Confirmed Information</option>                    
                    <option value="Enlisted">Enlisted</option>
                    <option value="Enrolled">Enrolled</option>
                    <option value="Rejected">Rejected</option>
                </select>
            </div>
            <div class="col-sm-4">
                <div class="input-group pull-right">
                    <a href="<?php echo base_url(); ?>admissionsV1/admissions_report" class="btn btn-primary">
                        Quick Stats
                    </a>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-group  pull-left">
                    <select class="form-select form-control" id="range-to-select">                        
                        <option value="created_at">Date Applied</option>
                        <option value="date_interviewed">Date Interviewed</option>
                        <option value="date_reserved">Date Reserved</option>
                        <option value="date_enrolled">Date Enrolled</option>
                    </select>
                </div>
                <div class="input-group pull-right">
                    <button class="btn btn-default pull-right" id="daterange-btn-users">
                        <i class="fa fa-calendar"></i> Choose Date Range
                        <i class="fa fa-caret-down"></i>
                    </button>
                </div>
            </div>
        </div>
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