<aside class="right-side">
<section class="content-header">
                    <h1>
                        Registered Students for <?php echo "A.Y." . " " .$active_sem['strYearStart']."-".$active_sem['strYearEnd'] . " " . $active_sem['enumSem']." ".$term_type ; ?>
                        <small></small>
                    </h1> 
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Student</a></li>
                        <li class="active">View All Student</li>
                    </ol>
                </section>
    <div class="content">
            <div class="alert alert-danger" style="display:none;">
                <i class="fa fa-ban"></i>
                <b>Alert!</b> Only admins can delete student records.
            </div>
                            <div class="box box-solid box-default">
                                <div class="box-header">                  
                                    
                                    <h3 class="box-title">List of Students</h3>
                                    <div class="box-tools pull-right">
                                        <a href="<?php echo base_url() ?>excel/download_registered_students/" class="text-muted"><i class="fa fa-table"></i> Download Spreadsheet</a>
                                    </div>
                                </div><!-- /.box-header -->
                                <div class="box-body table-responsive">
                                    <table id="users_table" class="table table-hover">
                                        <thead><tr><th>id</th><th>regID</th><th>Student Number</th><th>Name</th><th>Course</th><th>Year Level</th><th>Status</th><th>Actions</th></tr></thead>
                                        <tbody></tbody>
                                    </table>
                                </div><!-- /.box-body -->
                            </div><!-- /.box -->
                    
    </div>
</aside>