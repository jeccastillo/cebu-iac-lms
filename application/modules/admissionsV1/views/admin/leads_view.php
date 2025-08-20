<div class="content-wrapper">
    <div class="container my-4">
        <section class="content-header">
            <div class="row align-items-center mb-3">
                <div class="col-md-8">
                    <h1 class="mb-2">
                        Student Applicants
                        <small class="text-muted">Manage and view all student applications</small>
                    </h1>
                </div>
                <div class="col-md-4 text-right">
                    <a class="btn btn-success" href="#" id="print_form">
                        <i class="fa fa-file-excel-o"></i> Export to Excel
                    </a>
                </div>
            </div>
            
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="#"><i class="fa fa-dashboard"></i> Student Applicants</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">View All Leads</li>
                </ol>
            </nav>
        </section>

        <!-- Error Alert Container -->
        <div id="alert-container" class="alert alert-danger d-none" role="alert">
            <i class="fa fa-exclamation-triangle"></i>
            <span id="alert-text"></span>
        </div>

        <!-- Filters Section -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fa fa-filter"></i> Filters & Controls
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Term Selection -->
                    <div class="col-md-3 mb-3">
                        <label for="select-term-leads" class="form-label">
                            <strong>Academic Term</strong>
                        </label>
                        <select id="select-term-leads" class="form-control">
                            <?php foreach($sy as $s): ?>
                                <option <?php echo ($current_sem == $s['intID'])?'selected':''; ?> value="<?php echo $s['intID']; ?>">
                                    <?php echo $s['term_student_type']." ".$s['enumSem']." ".$s['term_label']." ".$s['strYearStart']."-".$s['strYearEnd']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div class="col-md-3 mb-3">
                        <label for="status_filter" class="form-label">
                            <strong>Application Status</strong>
                        </label>
                        <select class="form-control" id="status_filter">
                            <option value="none" selected>All Statuses</option>
                            <option value="New">New Applicant</option>
                            <option value="Waiting for Interview">Waiting for Interview</option>
                            <option value="Cancelled">Cancelled</option>
                            <option value="For Interview">For Interview</option>
                            <option value="For Reservation">For Reservation</option>
                            <option value="Reserved">Reserved</option>
                            <option value="Floating">Floating</option>
                            <option value="Will Not Proceed">Will Not Proceed</option>
                            <option value="Did Not Reserve">Did Not Reserve</option>
                            <option value="For Enrollment">For Enrollment</option>
                            <option value="Confirmed">Complete Confirmed Information</option>                    
                            <option value="Enlisted">Enlisted</option>
                            <option value="Enrolled">Enrolled</option>
                            <option value="Rejected">Rejected</option>
                            <option value="Disqualified">Disqualified</option>
                            <option value="Not Answering">Not Answering</option>
                        </select>
                    </div>

                    <!-- Date Range Type -->
                    <div class="col-md-3 mb-3">
                        <label for="range-to-select" class="form-label">
                            <strong>Date Range Type</strong>
                        </label>
                        <select class="form-control" id="range-to-select">                        
                            <option value="created_at">Date Applied</option>
                            <option value="date_interviewed">Date Interviewed</option>
                            <option value="date_reserved">Date Reserved</option>
                            <option value="date_enrolled">Date Enrolled</option>
                        </select>
                    </div>

                    <!-- Date Range Picker & Quick Stats -->
                    <div class="col-md-3 mb-3">
                        <label class="form-label">
                            <strong>Actions</strong>
                        </label>
                        <div class="d-flex flex-column">
                            <button class="btn btn-outline-primary mb-2" id="daterange-btn-users">
                                <i class="fa fa-calendar"></i> Choose Date Range
                                <i class="fa fa-caret-down"></i>
                            </button>
                            <a href="<?php echo base_url(); ?>admissionsV1/admissions_report" class="btn btn-info">
                                <i class="fa fa-chart-bar"></i> Quick Stats
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table Section -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fa fa-users"></i> Student Applicants List
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="subjects-table" class="table table-hover table-bordered table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th>ID</th>
                                <th>Date Applied</th>
                                <th>Date Interviewed</th>
                                <th>Date Reserved</th>
                                <th>Date Enrolled</th>
                                <th>Last Name</th>
                                <th>First Name</th> 
                                <th>Student Type</th>                           
                                <th>Program</th>                            
                                <th>Status</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
