<aside class="right-side">
<section class="content-header">
                    <h1>
                        Advised Students
                        <small></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i>Advising</a></li>
                        <li class="active">Advised Students</li>
                    </ol>
                </section>
<div class="content">
        <div class="form-group">
                <label>Select Course</label>
                <select id="course-select" class="form-control">
                     <option <?php echo ($course['intProgramID'] == 0)?'selected':'';  ?> value="0">all</option>
                    <?php foreach($courses as $c): ?>
                    <option <?php echo ($c['intProgramID'] == $course['intProgramID'])?'selected':'';  ?> value="<?php echo $c['intProgramID'] ?>"><?php echo $c['strProgramCode']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
               <div class="row">
                <div class="col-sm-12">
                <div class="box box-solid box-default">
                    <div class="box-header">                  
                        <div>

                        </div>

                        <h3 class="box-title">Advised Students For <?php echo $course['strProgramCode']; ?></h3>
                        <div class="box-tools">

                        </div>
                    </div><!-- /.box-header -->
                    <div class="box-body table-responsive">
                        <table id="users_table" class="table table-hover">
                            <thead><tr><th>id</th><th>Student Number</th><th>Name</th><th>Course</th><th>Year Level</th><th>Status</th><th>Actions</th></tr></thead>
                            <tbody></tbody>
                        </table>
                    </div><!-- /.box-body -->
                </div><!-- /.box -->
                </div>
                </div>
                 
   </aside>         