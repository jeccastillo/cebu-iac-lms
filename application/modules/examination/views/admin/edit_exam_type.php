<aside class="right-side">
    <section class="content-header">
        <h1>
            Edit Type
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Edit Type</a></li>
            <li class="active">Edit Exam Type</li>
        </ol>
    </section>
    <div class="content">
        <div class="span10 box box-primary">
            <div class="box-header">
                <h3 class="box-title">Exam Type</h3>
            </div>


            <form id="validate-program" action="<?php echo base_url(); ?>examination/submit_edit_exam_type"
                method="post" role="form">
                <div class="box-body">
                    <input type="hidden" name="intID" value="<?php echo $item['intID']; ?>" />
                    <div class="form-group col-xs-6">
                        <label for="strProgramCode">Name</label>
                        <input type="text" name="strName" value="<?php echo $item['strName']; ?>" class="form-control"
                            id="strName" placeholder="Enter Name">
                    </div>

                    <div class="form-group col-xs-6">
                        <label for="type">Exam Type</label>
                        <select class="form-control" name="type" id="type" required>
                            <option value="" disabled selected>--select type--</option>
                            <option <?php echo ($item['type'] == "shs")?'selected':''; ?>>shs</option>
                            <option <?php echo ($item['type'] == "college")?'selected':''; ?>>college</option>
                            <option <?php echo ($item['type'] == "other")?'selected':''; ?>>other</option>
                        </select>
                    </div>


                    <div class="form-group col-xs-12">
                        <input type="submit" value="Update" class="btn btn-default  btn-flat">
                    </div>
                    <div style="clear:both"></div>
                </div>
            </form>
        </div>
    </div>
</aside>