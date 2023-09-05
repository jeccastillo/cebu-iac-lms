<div class="content-wrapper ">
    <section class="content-header container ">
        <h1>
            Daily Collection Report
            <small>            
                <a class="btn btn-app" href="#" id="print_form"><i class="fa fa-file"></i> Export to Excel</a>
            </small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Payments</a></li>
            <li class="active">Daily Collection Report</li>
        </ol>
    </section>
    <div class="content mcontainer container">
        <div class="alert alert-danger" style="display:none;">
            <i class="fa fa-ban"></i>
            <span id="alert-text"></span>
        </div>
        <div class="row">            
            <div class="col-sm-4">
                <div class="input-group">
                    <button class="btn btn-default pull-right" id="chooseDate">
                        <i class="fa fa-calendar"></i> Choose Date Range
                        <i class="fa fa-caret-down"></i>
                    </button>
                </div>
            </div>
        </div>
        <hr />
        <div class="box box-solid box-primary">
            <div class="box-header">
                <h3 class="box-title">Collection Report <?php echo ($other)?'(NON-STUDENT)':''; ?></h3>                
            </div><!-- /.box-header -->
            <div class="box-body table-responsive" style="overflow-x:auto;margin-right:60px;">
                <table id="subjects-table" class="table table-hover table-bordered">
                    <thead>
                        <tr>     
                            <th>id</th>
                            <th>slug</th>     
                            <th>Cashier</th>                  
                            <th>Date Updated</th>
                            <th>OR Number</th>
                            <th>Applicant Number</th>
                            <th>Name</th>
                            <th>Payment Mode</th>
                            <th>Check/CC/Debit #</th>
                            <th>Amount Paid</th>
                            <th>Payment For</th>                                                                                                                                          
                            <th>Remarks</th>
                            <th>Actions</th>
                        </tr>
                        <tr class="search">
                        <th>id</th>
                            <th>slug</th>     
                            <th>Cashier</th>                  
                            <th>Date Updated</th>
                            <th>OR Number</th>
                            <th>Applicant Number</th>
                            <th>Name</th>
                            <th>Payment Mode</th>
                            <th>Check/CC/Debit #</th>
                            <th>Amount Paid</th>
                            <th>Payment For</th>                                                                                                                                          
                            <th>Remarks</th>  
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
<form id="print_or" method="post" action="<?php echo base_url(); ?>pdf/print_or" target="_blank">
    <input type="hidden" id="student_name" name="student_name" />
    <input type="hidden" id="campus" name="campus">
    <input type="hidden" id="cashier_id" name="cashier_id" />
    <input type="hidden" id="student_id" name="student_id" />
    <input type="hidden" id="student_address" name="student_address" />
    <input type="hidden" id="is_cash" name="is_cash" />
    <input type="hidden" id="check_number" name="check_number" />
    <input type="hidden" id="or_number" name="or_number" />
    <input type="hidden" id="remarks" name="remarks" />
    <input type="hidden" id="description" name="description" />
    <input type="hidden" id="total_amount_due" name="total_amount_due" /> 
    <input type="hidden" id="name" name="name" />       
    <input type="hidden" id="transaction_date" name="transaction_date" />               
</form>