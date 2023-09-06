
<aside class="right-side">
    <section class="content-header">
        <h1>
            Payee
            <small>
               <a class="btn btn-app" href="<?php echo base_url().'finance/payee'; ?>" ><i class="fa fa-user" aria-hidden="true"></i>Add Payee</a> 
               <a class="btn btn-app" href="<?php echo base_url().'finance/view_payees'; ?>" ><i class="fa fa-users" aria-hidden="true"></i>View All Payees</a> 
            </small>
        </h1>         
    </section>
    <div class="content">       

        <div class="box box-solid box-default">
            <div class="box-header">                  
                <div>
                    <div style="width:50%;float:right; text-align:right;">
                    </div>
                </div>

                <h3 class="box-title">List of Payees</h3>
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