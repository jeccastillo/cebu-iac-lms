
<aside class="right-side">
    <section class="content-header">
        <h1>
            Student
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
                <div>

                    <div style="width:50%;float:right; text-align:right;">
<!--
                        <form method="post" action="<?php echo base_url(). student/view_all_students/20 ?>">
                            <h5>Search: <input type="text" name="search_string"/>
                            </h5>
                        </form>
-->
                    </div>
                </div>

                <h3 class="box-title">List of Students</h3>
                <div class="box-tools">

                </div>
            </div><!-- /.box-header -->
            <div class="box-body table-responsive">
                <table id="users_table" class="table table-hover">
                    <thead>                        
                        <tr>
                            <th>id</th>
                            <th>id_number</th>
                            <th>Last Name</th>
                            <th>First Name</th>
                            <th>Middle Name</th>
                            <th>Tin</th>
                            <th>Address</th>
                            <th>Contact Number</th>
                            <th>Actions</th>
                        </tr>                        
                        <tr class="search">
                            <td>id</td>
                            <td>id_number</td>
                            <td>lastname</td>
                            <td>firstname</td>
                            <td>middlename</td>
                            <td>tin</td>
                            <td>address</td>
                            <td>contact_number</td>
                            <td>Actions</td>
                        </tr>
                    </thead>                    
                    <tbody></tbody>
                    <!-- <tfoot>
                        <tr>
                            <th>id</th>
                            <th>slug</th>
                            <th>Student Number</th>
                            <th>Last Name</th>
                            <th>Program</th>
                            <th>Year Level</th>
                            <th>Actions</th>
                        </tr>
                    </tfoot> -->
                </table>
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </div>
    
</aside>