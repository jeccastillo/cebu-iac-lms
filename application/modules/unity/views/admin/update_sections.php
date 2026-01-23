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
                    <table class="table table-bordered">
                        <tr>
                            <th>Subject</th>
                            <th>Section</th>
                        </tr>
                    <?php foreach($records as $rec): ?>
                        <tr>
                            <td><?php echo $rec['strCode']; ?></td>
                            <td>
                                <select class="section-update form-control" rel="<?php echo $rec['intCSID']; ?>">
                                    <?php foreach($rec['sections'] as $sec): ?>
                                    <option <?php echo ($sec['intID'] == $rec['classlistID'])?'selected':''; ?> value="<?php echo $sec['intID']; ?>"><?php echo $sec['strSection']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td> 
                        </tr>
                    <?php endforeach; ?>
                    </table>
                </div><!-- /.box-body -->
            </div><!-- /.box -->
     </div>
    </div>
</div>
