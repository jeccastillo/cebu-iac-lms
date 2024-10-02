<aside class="right-side">
    <section class="content-header">
        <h1>
            Student Grades
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Student</a></li>
            <li class="active">Upload Student Grades</li>
        </ol>
    </section>
    <div class="content">
        <div id="add-student" class="span10 box box-primary">
        <form action="<?php echo base_url(); ?>excel/import_student_grades" method="post" role="form" enctype="multipart/form-data">   
            <div class="box-body">
                <div class="row">                     
                    <div class="form-group col-sm-4">
                        <label for="sem">Select Term:</label>
                        <select id="sem" name="sem" class="form-control select2">
                            <?php foreach($sy as $s): ?>
                            <option <?php echo ($current_sem == $s['intID'])?'selected':''; ?>
                            value="<?php echo $s['intID']; ?>">
                            <?php echo $s['term_student_type']." ".$s['enumSem']." ".$s['term_label']." ".$s['strYearStart']."-".$s['strYearEnd']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-sm-4">
                        <input type="file" name="studentGradeExcel" size="20" />
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-sm-4">
                        <input type="submit" value="Import" class="btn btn-lg btn-default  btn-flat">
                    </div>
                </div>
            </div>
        </form>
        </div>
</aside>