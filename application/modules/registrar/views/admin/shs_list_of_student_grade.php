<aside class="right-side">
    <section class="content-header">
        <h1>
            SHS Student Grade
            <small>
                <a class="btn btn-app"
                    href="<?php echo base_url(); ?>registrar/registrar_reports">
                    <i class="ion ion-arrow-left-a"></i>
                    All Reports
                </a>
            </small>


            <small>
                <button class="btn btn-app"
                    id="shs_list_of_student_grade_excel"
                    target="_blank"
                    href="#"><i class="fa fa-book"></i>Download Excel</button>
            </small>

        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i>SHS Student Grade</a></li>
            <li class="active">View All SHS Student Grade</li>
        </ol>
        <hr />
        <form class="form-inline text-right"
            style="display: flex;justify-content: end;gap: 10px;">
            <div class="">
                <select id="select-term-leads"
                    class="form-control">
                    <?php foreach($sy as $s): ?>
                    <option <?php echo ($current_sem == $s['intID'])?'selected':''; ?>
                        value="<?php echo $s['intID']; ?>">
                        <?php echo $s['term_student_type']." ".$s['enumSem']." ".$s['term_label']." ".$s['strYearStart']."-".$s['strYearEnd']; ?>
                    </option>
                    <?php endforeach; ?>
                </select>

            </div>
            <div class="">
                <label for="intYearLevel">By Year Level:</label>
                <select id="int-year-level"
                    class="form-control select2">
                    <option <?php echo ($postyear == 0)?'selected':''; ?>
                        value="0">All</option>
                    <option <?php echo ($postyear == 1)?'selected':''; ?>
                        value="1">1st</option>
                    <option <?php echo ($postyear == 2)?'selected':''; ?>
                        value="2">2nd</option>
                    <option <?php echo ($postyear == 3)?'selected':''; ?>
                        value="3">3rd</option>
                    <option <?php echo ($postyear == 4)?'selected':''; ?>
                        value="4">4th</option>
                    <option <?php echo ($postyear == 5)?'selected':''; ?>
                        value="5">5th</option>
                    <option <?php echo ($postyear == 6)?'selected':''; ?>
                        value="6">6th</option>
                </select>

            </div>
        </form>

    </section>
    <div class="content">
        <div class="box box-solid box-default">
            <div class="box-header">
                <div>
                </div>

                <h3 class="box-title"> SHS Student Grades</h3>
                <div class="box-tools">

                </div>
            </div><!-- /.box-header -->
            <div class="box-body table-responsive">
                <table id="shs-student-grade-table"
                    class="table table-hover">
                    <thead>
                        <tr>

                        </tr>
                    </thead>
                </table>
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </div>
</aside>