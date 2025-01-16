<aside class="right-side">
    <section class="content-header">
        <h1> Deleted OR / Invoice <small>
                <a class="btn btn-app" href="<?php echo base_url(); ?>finance/finance_reports">
                    <i class="ion ion-arrow-left-a"></i> All Reports </a>
            </small>
            <small>
                <button class="btn btn-app" id="deleted_or_invoice_list_excel" target="_blank"
                    href="#"><i class="fa fa-book"></i>Download Excel</button>
            </small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i>Deleted OR / Invoice</a></li>
            <li class="active">View Deleted OR / Invoice</li>
        </ol>
        <hr />
        <form class="form-inline text-right" style="display: flex;justify-content: end;gap: 10px;">
            <div class="">
                <select id="select-term-leads" class="form-control"> <?php foreach($sy as $s): ?>
                    <option <?php echo ($current_sem == $s['intID'])?'selected':''; ?>
                        value="<?php echo $s['intID']; ?>">
                        <?php echo $s['term_student_type']." ".$s['enumSem']." ".$s['term_label']." ".$s['strYearStart']."-".$s['strYearEnd']; ?>
                    </option> <?php endforeach; ?> </select>
            </div>
            <div class="">
                <select id="select-report-type" class="form-control">
                    <option value="invoice"
                        <?php echo ($report_type === 'invoice') ? 'selected' : ''; ?>>Invoice
                    </option>
                    <option value="or" <?php echo ($report_type === 'or') ? 'selected' : ''; ?>>
                        Official Receipt </option>
                </select>
            </div>
            <div>
                <input id="date-picker" type="date" class="form-control"
                    value="<?php echo htmlspecialchars($date); ?>">
            </div>
        </form>
    </section>
    <div class="content">
        <div class="box box-solid box-default">
            <div class="box-header">
                <div>
                </div>
                <h3 class="box-title">Deleted OR/Invoice</h3>
                <div class="box-tools">
                </div>
            </div><!-- /.box-header -->
            <div class="box-body table-responsive">
                <table id="deleted-or-invoice-table" class="table table-hover">
                    <thead>
                        <tr>
                        </tr>
                    </thead>
                </table>
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </div>
</aside>