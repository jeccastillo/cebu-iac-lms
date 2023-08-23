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

    <div class="content">
        <div class="span10 box box-primary">
            <div class="box-header">
                <h3 class="box-title">Add Exam Question</h3>
            </div>


            <form id="validate-program" action="<?php echo base_url(); ?>examination/submit_question" method="post"
                role="form">
                <div class="box-body">
                    <div class="form-group col-xs-6">
                        <label for="strProgramCode">Question</label>
                        <input type="text" name="strTitle" class="form-control" id="strTitle"
                            placeholder="Enter Question Title">
                    </div>

                    <input type="hidden" value="<?php echo $item['intID']; ?>" name="exam_id" id="exam_id">


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

    <div class="content">
        <div class="span10 box box-primary">
            <div class="box-header">
                <h3 class="box-title">Question Choices</h3>
            </div>



            <form id="validate-program" action="<?php echo base_url(); ?>examination/submit_choice" method="post"
                role="form">
                <input type="hidden" name="question_id" value="<?php echo $question['intID']; ?>" />
                <div class="box-body">

                    <div class="form-group col-xs-5" id="choices_container">
                        <?php if(count($choices) == 0): ?>
                        <div>
                            <label for="strProgramCode">Enter Choice Value</label>
                            <input type="text" name="strChoice[]" class="form-control" placeholder="Enter choice name">
                            <input type="radio" name="is_correct[]" value="1" required> is
                            Correct?
                            <hr>
                        </div>

                        <?php else:
                            foreach ($choices as $choice):
                        ?>

                        <div class="choice_box">
                            <label for="strProgramCode">Enter Choice Value</label>
                            <input type="hidden" name="choiceID[]" value="<?php echo $choice['intID'];?>"
                                class="form-control" placeholder="">

                            <div style="display:flex">
                                <input type="text" name="strChoice[]" value="<?php echo $choice['strChoice'];?>"
                                    class="form-control" placeholder="Enter choice name">
                                <button type="button" class="btn btn-sm btn-danger btn_remove"
                                    style="margin-left:1rem">Remove</button>

                            </div>

                            <div>
                                <input type="radio" name="is_correct[]" required
                                    <?php echo $choice['is_correct'] == 1 ? 'checked' : '' ?>
                                    value="<?php echo $choice['is_correct'];?>"> is
                                Correct?
                            </div>
                            <hr>

                        </div>


                        <?php endforeach; ?>
                        <?php endif; ?>


                    </div>

                    <div class=" col-sm-12" style="margin-bottom:1rem;">
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
const btnRemove = $(".btn_remove");

const toAppend = `<div class="choice_box">
                            <label for="strProgramCode">Enter Choice Value</label>
                            <input type="hidden" name="choiceID[]" class="form-control" placeholder="">

                            <div style="display:flex">
                                <input type="text" name="strChoice[]"
                                    class="form-control" placeholder="Enter choice name">
                                <button type="button" class="btn btn-sm btn-danger btn_remove"
                                    style="margin-left:1rem">Remove</button>

                            </div>

                            <div>
                                <input type="radio" name="is_correct[]" required value="1"> is
                                Correct?
                            </div>
                            <hr>

                        </div>`

addNewBtn.on("click", () => {
    choicesBox.append(toAppend)
})


$("#choices_container").on("click", "button", function() {
    $(this).parent('div').closest(".choice_box").remove();;
})
</script>