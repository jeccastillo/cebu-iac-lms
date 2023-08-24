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

                    <div class="form-group col-xs-12">
                        <label for="type">Exam Type</label>
                        <select class="form-control" name="exam_id" id="exam_id" disabled>
                            <?php foreach ($exam_type as $cur): ?>
                            <option value="<?php echo $cur['intID']; ?>"
                                <?php echo ($question['exam_id'] == $cur['intID'])?'selected':''; ?>>
                                <?php echo $cur['strName']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>


                    <div class="form-group col-xs-6">
                        <label for="strProgramCode">Question</label>
                        <input type="text" name="strTitle" value="<?php echo $question['strTitle']; ?>"
                            class="form-control" id="strTitle" placeholder="Enter Question Title">
                    </div>



                    <div class="form-group col-xs-6">
                        <label for="type">Section</label>
                        <!-- <input type="text" name="strSection" value="<?php echo $question['strSection']; ?>"
                            class="form-control" id="strSection" placeholder="Enter Section"> -->

                        <select name="strSection" required class="form-control" id="strSection">
                            <option value="" selected disabled>--select section--</option>
                            <option <?php echo $question['strSection'] == "I" ? "selected": "" ?>>I</option>
                            <option <?php echo $question['strSection'] == "II" ? "selected": "" ?>>II</option>
                            <option <?php echo $question['strSection'] == "III" ? "selected": "" ?>>III</option>
                            <option <?php echo $question['strSection'] == "IV" ? "selected": "" ?>>IV</option>
                            <option <?php echo $question['strSection'] == "V" ? "selected": "" ?>>V</option>
                            <option <?php echo $question['strSection'] == "VI" ? "selected": "" ?>>V</option>
                            <option <?php echo $question['strSection'] == "VII" ? "selected": "" ?>>V</option>
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



            <form id="choices-form" action="<?php echo base_url(); ?>examination/submit_choice" method="post"
                role="form">
                <input type="hidden" name="question_id" value="<?php echo $question['intID']; ?>" />
                <input type="hidden" id="selected_index" name="selected_index" value="" />
                <div class="box-body">

                    <div class="form-group col-xs-6" id="choices_container">
                        <?php if(count($choices) == 0): ?>
                        <!-- <div>
                            <label for="strProgramCode">Enter Choice Value</label>
                            <input type="text" name="strChoice[]" class="form-control" placeholder="Enter choice name">
                            <input type="radio" name="is_correct[]" value="1" required> is
                            Correct?
                            <hr>
                        </div> -->

                        <?php else:
                            foreach ($choices as $choice):
                        ?>

                        <div class="choice_box alert" style="background: #e6e9e9;">
                            <div class="form-group">
                                <label for="strProgramCode">Enter Choice Value</label>
                                <input type="text" name="strChoice[]" value="<?php echo $choice['strChoice'];?>"
                                    class="form-control" placeholder="Enter choice name">

                                <input type="hidden" name="choiceID[]" value="<?php echo $choice['intID'];?>"
                                    class="form-control" placeholder="">
                            </div>

                            <div class="form-group">
                                <label>Image (optional)</label>
                                <input type="file" name="choiceImage[]" required class="form-control" accept="image/*">
                            </div>


                            <div class="form-group">
                                <label>Correct Answer</label>
                                <select class="form-control" name="is_correct[]">
                                    <option <?php echo $choice['is_correct'] == 0?'selected':''; ?> value=0>No</option>
                                    <option <?php echo $choice['is_correct'] == 1?'selected':''; ?> value=1>Yes</option>
                                </select>
                            </div>
                            <br>
                            <button type="button" class="btn btn-sm btn-danger btn_remove">Remove</button>
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
                    <div>
                        <div class="form-group">
                            <label for="strProgramCode">Enter Choice Value</label>
                            <input type="hidden" name="choiceID[]" class="form-control" placeholder="">
                            <input type="text" name="strChoice[]" class="form-control" placeholder="Enter choice name">
                        </div>

                        <div class="form-group">
                                <label>Image (optional)</label>
                                <input type="file" name="choiceImage[]" required class="form-control" accept="image/*">
                            </div>
                       
                        <div class="form-group">
                            <label>Correct Answer</label>
                            <select class="form-control" name="is_correct[]">
                                <option value=0>No</option>
                                <option value=1>Yes</option>
                            </select>  
                        </div>

                    </div>
                     <br>
                    <button type="button"  class="btn btn-sm btn-danger btn_remove">Remove</button>
                    <hr>
                 </div>`

addNewBtn.on("click", () => {
    choicesBox.append(toAppend)
})


$("#choices_container").on("click", "button", function() {
    $(this).parent('div').remove();
})




$("#choices_container").on("click", ".radioBtn", function() {
    var radioButtons = $("#choices-form input:radio[name='is_correct[]']");
    var selectedIndex = radioButtons.index(radioButtons.filter(':checked'));

    $("#selected_index").val(selectedIndex);

})
</script>