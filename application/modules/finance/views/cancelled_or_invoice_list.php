<aside class="right-side">
    <section class="content-header">
        <h1> Cancelled OR / Invoice <small>
                <a class="btn btn-app" href="<?php echo base_url(); ?>finance/finance_reports">
                    <i class="ion ion-arrow-left-a"></i> All Reports </a>
            </small>
            <small>
                <button class="btn btn-app" id="cancelled_or_invoice_list_excel" target="_blank"
                    href="#"><i class="fa fa-book"></i>Download Excel</button>
            </small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i>Cancelled OR / Invoice</a></li>
            <li class="active">View Cancelled OR / Invoice</li>
        </ol>
        <hr />
        <div class="row">
            <div class="col-md-6"></div>
            <div class="col-md-6">
                <form class="form-inline text-right">
                    <div class="" style="display: flex; align-items:center;gap:10px;">
                        <label for="date-picker-from">From:</label>
                        <input id="date-picker-from" type="date" class="form-control"
                            value="<?php echo htmlspecialchars($date_start); ?>">
                        <label for="date-picker-to">To:</label>
                        <input id="date-picker-to" type="date" class="form-control"
                            value="<?php echo htmlspecialchars($date_end); ?>">
                    </div>
                </form>
            </div>
        </div>
    </section>
    <div class="content">
        <div class="box box-solid box-default">
            <div class="box-header">
                <div>
                </div>
                <h3 class="box-title">Cancelled OR/Invoice</h3>
                <div class="box-tools">
                </div>
            </div><!-- /.box-header -->
            <div class="box-body table-responsive">
                <table id="cancelled-or-invoice-table" class="table table-hover">
                    <thead>
                        <tr>
                        </tr>
                    </thead>
                </table>
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </div>
</aside>