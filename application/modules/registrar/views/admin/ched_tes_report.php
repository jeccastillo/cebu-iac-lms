<aside class="right-side">
    <section class="content-header">
        <h1>
            TES Report
            <small>
                <a class="btn btn-app" href="<?php echo base_url(); ?>registrar/registrar_reports">
                    <i class="ion ion-arrow-left-a"></i>
                    All Reports
                </a>
            </small>
            <small>
                <button class="btn btn-app" id="ched_tes_report_pdf" target="_blank" href="#"><i
                        class="fa fa-book"></i>Print PDF</button>
            </small>
            <small>
                <button class="btn btn-app" id="ched_tes_report_excel" target="_blank" href="#"><i
                        class="fa fa-book"></i>Download Excel</button>
            </small>

        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i>TES Report</a></li>
            <li class="active">View All Ched TES Report</li>
        </ol>
        <hr />
        <form class="form-inline text-right">
            <div class="">
                <select id="select-term-leads" class="form-control">
                    <?php foreach($sy as $s): ?>
                    <option <?php echo ($current_sem == $s['intID'])?'selected':''; ?>
                        value="<?php echo $s['intID']; ?>">
                        <?php echo $s['term_student_type']." ".$s['enumSem']." ".$s['term_label']." ".$s['strYearStart']."-".$s['strYearEnd']; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>

    </section>
    <div class="content">
        <div class="box box-solid box-default">
            <div class="box-header">
                <div>
                </div>

                <h3 class="box-title">TES Report</h3>
                <div class="box-tools">

                </div>
            </div><!-- /.box-header -->
            <div class="box-body table-responsive">
                <table id="tes-report-table" class="table table-hover">
                    <thead>
                        <tr>
                            <th rowspan="2" style="text-align:center;vertical-align:middle;">Sec</th>
                            <th rowspan="2" style="text-align:center;vertical-align:middle;">Student Number</th>
                            <th colspan="4" style="text-align:center;">Student Name</th>
                            <th colspan="4" style="text-align:center;">Student Profile</th>
                            <th colspan="3" style="text-align:center;">Father's Name</th>
                            <th colspan="3" style="text-align:center;">Mother's Name</th>
                            <th colspan="2" style="text-align:center;">Permanent Address </th>
                            <th rowspan="2" style="text-align:center;vertical-align:middle;">DISABILITY</th>
                            <th rowspan="2" style="text-align:center;vertical-align:middle;">CONTACT NUMBER</th>
                            <th rowspan="2" style="text-align:center;vertical-align:middle;">EMAIL ADDRESS</th>
                            <th rowspan="2" style="text-align:center;vertical-align:middle;">INDIGENOUS PEOPLE GROUP
                            </th>
                        </tr>
                        <tr>
                            <th>Last Name</th>
                            <th>Given name</th>
                            <th>Ext name</th>
                            <th>Middle Name</th>
                            <th>Sex</th>
                            <th>Birthdate</th>
                            <th>Complete Program</th>
                            <th>Year Level</th>
                            <th>Last Name</th>
                            <th>Given name</th>
                            <th>Middle Name</th>
                            <th>Last Name</th>
                            <th>Given name</th>
                            <th>Middle Name</th>
                            <th>Street & Barangay</th>
                            <th>ZipCode</th>

                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </div>
</aside>