<div class="content-wrapper" style="padding: 24px 12px 12px 12px; background: #fafbfc; min-height: 100vh;">
<section class="content-header">
                    <h1>
                        Sections
                        <small>
                            <a class="btn btn-app" href="<?php echo base_url()."unity/student_viewer/".$student['intID'] ."/". $active_sem['intID']; ?>">
                                <i class="fa fa-arrow-left"></i> Student Viewer</a> 
                        </small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Sections</a></li>
                        <li class="active">Edit Sections</li>
                    </ol>
                </section>
    <div class="content" style="margin-top: 10px;">
        <div class="alert alert-danger" style="display:none;">
            <i class="fa fa-ban"></i>
            <span id="alert-text"></span>
        </div>
            <div class="box box-solid box-danger">
                <div class="box-header">
                    <h3 class="box-title">Update Sections</h3>

                </div><!-- /.box-header -->
                <div class="box-body">
                    <h4><?php echo $student['strLastname'].", ".$student['strFirstname']; ?></h4>
                    <hr />
                    <h4>Student already registered!</h4>
                </div><!-- /.box-body -->
            </div><!-- /.box -->
     </div>
    </div>
</div>
