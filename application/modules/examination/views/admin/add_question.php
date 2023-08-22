<aside class="right-side">
    <section class="content-header">
        <h1>
            Exam Question
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Program</a></li>
            <li class="active">New Exam Question</li>
        </ol>
    </section>
    <div class="content">
        <div class="span10 box box-primary">
            <div class="box-header">
                <h3 class="box-title">New Exam Question</h3>
            </div>


            <form id="validate-program" action="<?php echo base_url(); ?>examination/submit_question" method="post"
                role="form">
                <div class="box-body">
                    <div class="form-group col-xs-6">
                        <label for="strProgramCode">Question</label>
                        <input type="text" name="strTitle" class="form-control" id="strTitle"
                            placeholder="Enter Question Title">
                    </div>

                    <div class="form-group col-xs-6">
                        <label for="type">Exam Type</label>
                        <select class="form-control" name="exam_id" id="exam_id">
                            <?php foreach ($exam_type as $cur): ?>
                            <option value="<?php echo $cur['intID']; ?>"><?php echo $cur['strName']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group col-xs-6">
                        <label for="type">Section</label>
                        <input type="text" name="strSection" class="form-control" id="strSection"
                            placeholder="Enter Section">
                    </div>

                    <div class="form-group col-xs-12">
                        <input type="submit" value="add" class="btn btn-default  btn-flat">
                    </div>
                    <div style="clear:both"></div>
                </div>
            </form>
        </div>
    </div>
</aside>