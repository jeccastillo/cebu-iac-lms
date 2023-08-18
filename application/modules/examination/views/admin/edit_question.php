<aside class="right-side">
    <section class="content-header">
        <h1>
            Exam Question
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Exam Question</a></li>
            <li class="active">Edit Program</li>
        </ol>
    </section>
    <div class="content">
        <div class="span10 box box-primary">
            <div class="box-header">
                <h3 class="box-title">Edit Exam Question</h3>
            </div>


            <form id="validate-program" action="<?php echo base_url(); ?>program/submit_edit_program" method="post"
                role="form">
                <!-- <input type="hidden" name="intQuestionID" value="<?php echo $item['intQuestionID']; ?>" /> -->
                <div class="box-body">

                    <div class="form-group col-xs-6">
                        <label for="strProgramCode">Question</label>
                        <input type="text" name="strTitle" class="form-control" id="strTitle"
                            placeholder="Enter Question Title">
                    </div>

                    <div class="form-group col-xs-6">
                        <label for="type">Exam Type</label>
                        <select class="form-control" name="type" id="type">
                        </select>
                    </div>

                    <div class="form-group col-xs-6">
                        <label for="type">Section</label>
                        <select class="form-control" name="srtSection" id="type">
                        </select>
                    </div>

                    <div class="form-group col-xs-12">
                        <input type="submit" value="update" class="btn btn-default  btn-flat">
                    </div>
                    <div style="clear:both"></div>
                </div>
            </form>
        </div>
    </div>

    <div class="content">
        <div class="span10 box box-primary">
            <div class="box-header">
                <h3 class="box-title">Question Choices</h3>
            </div>


            <form id="validate-program" action="<?php echo base_url(); ?>program/submit_edit_program" method="post"
                role="form">
                <!-- <input type="hidden" name="intQuestionID" value="<?php echo $item['intQuestionID']; ?>" /> -->
                <div class="box-body">

                    <div class="form-group col-xs-6">
                        <label for="strProgramCode">A.</label>
                        <input type="text" name="strTitle" class="form-control" id="strTitle"
                            placeholder="Enter Answer">
                    </div>
                    <div class="form-group col-xs-6">
                        <label for="strProgramCode">B.</label>
                        <input type="text" name="strTitle" class="form-control" id="strTitle"
                            placeholder="Enter Answer">
                    </div>
                    <div class="form-group col-xs-6">
                        <label for="strProgramCode">C.</label>
                        <input type="text" name="strTitle" class="form-control" id="strTitle"
                            placeholder="Enter Answer">
                    </div>
                    <div class="form-group col-xs-6">
                        <label for="strProgramCode">D.</label>
                        <input type="text" name="strTitle" class="form-control" id="strTitle"
                            placeholder="Enter Answer">
                    </div>

                    <hr>

                    <div class="form-group col-xs-6">
                        <label for="strProgramCode">Correct Answer:</label>
                        <select type="text" required name="strTitle" class="form-control">
                            <option value="" disable>--select correct answer--</option>
                            <option value="A">A</option>
                            <option value="A">B</option>
                            <option value="A">C</option>
                            <option value="A">D</option>
                        </select>
                    </div>


                    <div class="form-group col-xs-12">
                        <input type="submit" value="update" class="btn btn-default  btn-flat">
                    </div>


                    <div style="clear:both"></div>
                </div>
            </form>
        </div>
    </div>
</aside>