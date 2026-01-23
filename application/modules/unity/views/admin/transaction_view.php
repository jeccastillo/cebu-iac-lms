<aside class="right-side" style="padding: 24px 12px 12px 12px; background: #fafbfc; min-height: 100vh;">
     <section class="content-header">
                    <h1>
                        Transactions
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Admin</a></li>
                        <li class="active">Logs</li>
                    </ol>
                </section>
    <section class="content">
            <div class="form-group">
                <label>Filter Logs:</label>
                <div class="row">
                <div class="input-group pull-right">
                    <button class="btn btn-default pull-right" id="daterange-btn-transactions">
                        <i class="fa fa-calendar"></i> Choose Date Range
                        <i class="fa fa-caret-down"></i>
                    </button>
                </div>
                <div class="pull-right">
                    <div class="dropdown">
                      <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                        <i class="fa fa-table"></i> Download
                        <span class="caret"></span>
                      </button>
                      <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
                        <li><a href="<?php echo base_url() ?>excel/download_transactions/<?php echo $start.'/'.$end; ?>" class="text-muted">Download Spreadsheet</a></li>
                      </ul>
                    </div>
              </div>
            </div>
            </div>
        <div class="box box-solid box-danger">
            <div class="box-header">
                <h3 class="box-title">Transactions</h3>
                <div class="box-tools">

                </div>
            </div><!-- /.box-header -->
            <div class="box-body table-responsive">
                <?php if(isset($dateF)):?>
                    <div class="alert alert-info">
                        <?php echo $dateF; ?>
                    </div>
                <?php endif; ?>
                <table id="transactions-table" class="table table-bordered">
                    <thead><tr>
                        <th>ORNumber</th>
                        <th>Transaction Type</th>
                        <th>Date Paid</th>
                        <th>Payee</th>
                        <th>Amount Paid</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach($transactions as $tr): ?>
                        <tr>

                            <td><?php echo $tr['intORNumber']; ?></td>
                            <td><?php echo $tr['strTransactionType']; ?></td>
                            <td><?php echo date("M j,Y",strtotime($tr['dtePaid'])); ?></td>
                            <td><a href="<?php echo base_url()."unity/student_viewer/".$tr['studentID']; ?>"><?php echo $tr['strLastname'].", ".$tr['strFirstname']; ?></td>
                            <td>P<?php echo $tr['intAmountPaid'] ?></td>
                        </tr>
                    <?php endforeach; ?>

                </tbody></table>
            </div><!-- /.box-body -->
        </div>
    </section>
</aside>