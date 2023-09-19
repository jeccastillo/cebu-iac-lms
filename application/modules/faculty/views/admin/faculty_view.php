<aside class="right-side">
<section class="content-header">
                    <h1>
                        User Accounts
                        <small></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> User Accounts</a></li>
                        <li class="active">View All User Accounts</li>
                    </ol>
                </section>
    <div class="content">
            <div class="alert alert-danger" style="display:none;">
                <i class="fa fa-ban"></i>
                <b>Alert!</b> Only admins can delete student records.
            </div>
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Advanced Search</h3>
                    <div class="box-tools pull-right">
                        <div class="dropdown">
                            <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                <i class="fa fa-table"></i> Download
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
                                <li><a href="<?php echo base_url() ?>excel/download_faculty/" class="text-muted">Download List of Faculty</a></li>  
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="box box-solid box-danger">
                <div class="box-header">
                    <h3 class="box-title">List of User Accounts</h3>
                    <div class="box-tools">

                    </div>
                </div><!-- /.box-header -->
                <div class="box-body table-responsive">
                    <table id="faculty-table" class="table">
                        <thead><tr>
                            <th>id</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>User Level</th>
                            <th>Username</th>
                            <th>Status</th>
                            <th>School</th>
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