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

            <form id="validate-program" action="<?php echo base_url(); ?>examination/submit_edit_question" method="post"
                role="form">
                <input type="hidden" name="intID" value="<?php echo $question['intID']; ?>" />
                <div class="box-body">

                    <div class="form-group col-xs-6">
                        <label for="strProgramCode">Question</label>
                        <input type="text" name="strTitle" value="<?php echo $question['strTitle']; ?>"
                            class="form-control" id="strTitle" placeholder="Enter Question Title">
                    </div>

                    <div class="form-group col-xs-6">
                        <label for="type">Exam Type</label>
                        <select class="form-control" name="exam_id" id="exam_id">
                            <?php foreach ($exam_type as $cur): ?>
                            <option value="<?php echo $cur['intID']; ?>"
                                <?php echo ($question['exam_id'] == $cur['intID'])?'selected':''; ?>>
                                <?php echo $cur['strName']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group col-xs-6">
                        <label for="type">Section</label>
                        <input type="text" name="strSection" value="<?php echo $question['strSection']; ?>"
                            class="form-control" id="strSection" placeholder="Enter Section">
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


            <form id="validate-program" action="<?php echo base_url(); ?>examination/submit_choice" method="post"
                role="form">
                <input type="hidden" name="question_id" value="<?php echo $question['intID']; ?>" />
                <div class="box-body">

                    <div class="form-group col-xs-6" id="choices_container">
                        <div>
                            <label for="strProgramCode">Enter Choice Value</label>
                            <input type="text" name="strChoice[]" class="form-control" placeholder="Enter choice name">
                            <input type="radio" name="is_correct[]" value="1"> is
                            Correct?
                            <hr>
                        </div>
                    </div>

                    <div class="col-sm-12" style="margin-bottom:1rem;">
                        <button type="button" class="btn btn-default" id="add_new">Add New</button>
                        <hr>
                    </div>

                    <div class="col-sm-12" style="margin-bottom:1rem;">
                        <button type="submit" class="btn btn-primary" id="add_new">Submit Choices</button>
                    </div>
                    <div style="clear:both"></div>
            </form>
        </div>
    </div>
</aside>

<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<script>
const choicesBox = $("#choices_container");
const addNewBtn = $("#add_new");

const toAppend = `<div>
                    <label for="strProgramCode">Enter Choice Value</label>
                    <input type="text" name="strChoice[]" class="form-control" placeholder="Enter choice name">
                    <input type="radio" name="is_correct[]" value="1"> is Correct
                    <hr>
                 </div>`

addNewBtn.on("click", () => {
    choicesBox.append(toAppend)
})
</script>