<aside class="right-side">
    <section class="content-header">
        <h1>
            Regular List

            <small>
                <button class="btn btn-app" id="regular_list_pdf" target="_blank" href="#"><i
                        class="fa fa-book"></i>Print PDF</button>
            </small>
            <small>
                <button class="btn btn-app" id="regular_list_excel" target="_blank" href="#"><i
                        class="fa fa-book"></i>Download Excel</button>
            </small>

        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i>Student</a></li>
            <li class="active">View All Regular</li>
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

                <h3 class="box-title">Regular List</h3>
                <div class="box-tools">

                </div>
            </div><!-- /.box-header -->
            <div class="box-body table-responsive">
                <table id="regular-list-table" class="table table-hover">
                    <thead>
                        <tr>

                        </tr>
                    </thead>
                </table>
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </div>
</aside>