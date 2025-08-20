    <section class="content-header">
        <h1>
            Enhanced Faculty Dashboard
            <small>Welcome to your enhanced workspace</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Enhanced Dashboard</li>
        </ol>
    </section>
    
    <section class="content">
        <?php if($pwd == "1234"): ?>
        <div class="alert alert-warning" role="alert">
            <h4><i class="fa fa-exclamation-triangle"></i> Alert!</h4>
            Detected default password. Click <a href="<?php echo base_url(); ?>faculty/edit_profile">here</a> to update password.
        </div>
        <?php endif; ?>

        <!-- Quick Stats Row -->
        <div class="row">
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3><?php echo $my_classes_count; ?></h3>
                        <p>My Classes This Term</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-graduation-cap"></i>
                    </div>
                    <a href="<?php echo base_url(); ?>unity/view_classlist" class="small-box-footer">
                        View Classes <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3><?php echo $total_students_taught; ?></h3>
                        <p>Total Students</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-users"></i>
                    </div>
                    <a href="#" class="small-box-footer">
                        Students Taught <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3><?php echo $pending_grades; ?></h3>
                        <p>Pending Grades</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-clock-o"></i>
                    </div>
                    <a href="#" class="small-box-footer">
                        Grade Submissions <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-red">
                    <div class="inner">
                        <h3><?php echo $submitted_grades; ?></h3>
                        <p>Submitted Grades</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-check-circle"></i>
                    </div>
                    <a href="#" class="small-box-footer">
                        Completed <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Term Information Row -->
        <div class="row">
            <div class="col-md-6">
                <div class="info-box">
                    <span class="info-box-icon bg-blue"><i class="fa fa-calendar"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">ACTIVE TERM</span>
                        <span class="info-box-number"><?php echo $active_sem['enumSem']." ".$term_type." ".$active_sem['strYearStart']."-".$active_sem['strYearEnd']; ?></span>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="info-box">
                    <span class="info-box-icon bg-purple"><i class="fa fa-file-text"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">APPLICATION TERM</span>
                        <span class="info-box-number"><?php echo $app_sem['enumSem']." ".$term_type." ".$app_sem['strYearStart']."-".$app_sem['strYearEnd']; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Row -->
        <div class="row">
            <!-- Today's Schedule -->
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-clock-o"></i> Today's Schedule</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <?php if(!empty($today_schedule)): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Subject</th>
                                            <th>Time</th>
                                            <th>Room</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($today_schedule as $schedule): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo $schedule['strCode']; ?></strong><br>
                                                <small><?php echo $schedule['strDescription']; ?></small>
                                            </td>
                                            <td><?php echo isset($schedule['strTimeStart']) ? $schedule['strTimeStart'] . ' - ' . $schedule['strTimeEnd'] : 'TBA'; ?></td>
                                            <td><?php echo isset($schedule['strRoom']) ? $schedule['strRoom'] : 'TBA'; ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted">
                                <i class="fa fa-calendar-o fa-3x"></i>
                                <p>No classes scheduled for today</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="col-md-6">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-bolt"></i> Quick Actions</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <ul class="list-group list-group-unbordered">
                            <li class="list-group-item">
                                <a href="<?php echo base_url(); ?>unity/view_classlist" class="quick-action-link">
                                    <i class="fa fa-list text-aqua"></i> My Classes
                                    <span class="pull-right text-muted">
                                        <i class="fa fa-angle-right"></i>
                                    </span>
                                </a>
                            </li>
                            <li class="list-group-item">
                                <a href="<?php echo base_url(); ?>faculty/my_profile" class="quick-action-link">
                                    <i class="fa fa-user text-green"></i> My Profile
                                    <span class="pull-right text-muted">
                                        <i class="fa fa-angle-right"></i>
                                    </span>
                                </a>
                            </li>
                            <li class="list-group-item">
                                <a href="<?php echo base_url(); ?>unity/faculty_classlists" class="quick-action-link">
                                    <i class="fa fa-plus text-yellow"></i> Add Class
                                    <span class="pull-right text-muted">
                                        <i class="fa fa-angle-right"></i>
                                    </span>
                                </a>
                            </li>
                            <li class="list-group-item">
                                <a href="<?php echo base_url(); ?>faculty/edit_profile" class="quick-action-link">
                                    <i class="fa fa-cog text-red"></i> Settings
                                    <span class="pull-right text-muted">
                                        <i class="fa fa-angle-right"></i>
                                    </span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts and Analytics Row -->
        <div class="row">
            <!-- Program Distribution Chart -->
            <div class="col-md-6">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-pie-chart"></i> Students by Program</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <canvas id="programChart" height="200"></canvas>
                    </div>
                </div>
            </div>

            <!-- Grade Distribution Chart -->
            <div class="col-md-6">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-bar-chart"></i> Grade Distribution</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <canvas id="gradeChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-history"></i> Recent Activity</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <?php if(!empty($recent_submissions)): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Class</th>
                                            <th>Subject</th>
                                            <th>Section</th>
                                            <th>Submission Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($recent_submissions as $submission): ?>
                                        <tr>
                                            <td><?php echo $submission['strClassName']; ?></td>
                                            <td><?php echo $submission['strSection']; ?></td>
                                            <td><?php echo $submission['strSection']; ?></td>
                                            <td><?php echo date('M j, Y', strtotime($submission['date_final_submitted'])); ?></td>
                                            <td><span class="label label-success">Submitted</span></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted">
                                <i class="fa fa-clock-o fa-3x"></i>
                                <p>No recent activity in the last 7 days</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- My Classes Overview -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-graduation-cap"></i> My Classes Overview</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <?php if(!empty($my_classes)): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Subject Code</th>
                                            <th>Class Name</th>
                                            <th>Section</th>
                                            <th>Units</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($my_classes as $class): ?>
                                        <tr>
                                            <td><strong><?php echo $class['strClassName']; ?></strong></td>
                                            <td><?php echo $class['strClassName']; ?></td>
                                            <td><?php echo $class['strSection']; ?></td>
                                            <td><?php echo $class['strUnits']; ?></td>
                                            <td>
                                                <?php if($class['intFinalized'] == 0): ?>
                                                    <span class="label label-warning">Midterm Pending</span>
                                                <?php elseif($class['intFinalized'] == 1): ?>
                                                    <span class="label label-info">Final Pending</span>
                                                <?php else: ?>
                                                    <span class="label label-success">Completed</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="<?php echo base_url(); ?>unity/classlist_viewer/<?php echo $class['intID']; ?>" 
                                                   class="btn btn-xs btn-primary">
                                                    <i class="fa fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted">
                                <i class="fa fa-graduation-cap fa-3x"></i>
                                <p>No classes assigned for this term</p>
                                <a href="<?php echo base_url(); ?>unity/faculty_classlists" class="btn btn-primary">
                                    <i class="fa fa-plus"></i> Add New Class
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Custom CSS for Enhanced Dashboard -->
<style>
.small-box {
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s ease;
}

.small-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.info-box {
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.box {
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.btn-app {
    border-radius: 6px;
    margin: 5px;
    transition: all 0.2s ease;
}

.btn-app:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.table-striped > tbody > tr:nth-of-type(odd) {
    background-color: rgba(0,0,0,0.02);
}

.content-header h1 {
    color: #333;
    font-weight: 600;
}

.label {
    border-radius: 4px;
    font-size: 11px;
    padding: 4px 8px;
}

.text-muted i {
    opacity: 0.5;
    margin-bottom: 15px;
}

/* Quick Actions List Styling */
.quick-action-link {
    display: block;
    color: #333;
    text-decoration: none;
    padding: 8px 0;
    transition: all 0.2s ease;
}

.quick-action-link:hover {
    color: #337ab7;
    text-decoration: none;
    background-color: rgba(0,0,0,0.02);
    margin: 0 -15px;
    padding: 8px 15px;
    border-radius: 4px;
}

.quick-action-link i {
    margin-right: 10px;
    width: 16px;
    text-align: center;
}

.list-group-unbordered .list-group-item {
    border: none;
    border-bottom: 1px solid #f0f0f0;
    padding: 10px 15px;
}

.list-group-unbordered .list-group-item:last-child {
    border-bottom: none;
}

@media (max-width: 768px) {
    .small-box .inner h3 {
        font-size: 24px;
    }
    
    .btn-app {
        width: 100%;
        margin: 2px 0;
    }
    
    .quick-action-link {
        font-size: 16px;
        padding: 12px 0;
    }
}
</style>
