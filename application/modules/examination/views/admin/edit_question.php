<aside class="right-side">
    <section class="content-header">
        <h1>
            Exam Question
            <small></small>
        </h1>

        <a class="btn btn-primary btn-sm" style="margin-top:1rem;"
            href="<?php echo base_url();?>examination/edit_exam_type/<?php echo $question['exam_id']?>">
            BACK</a>
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
                enctype="multipart/form-data" role="form">
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


                    <div class="form-group col-xs-12">
                        <label for="strProgramCode">Question</label>
                        <textarea id="editorQuestion" name="strTitle"
                            required><?php echo $question['strTitle']; ?></textarea>
                    </div>


                </div>
                <div class="form-group col-xs-6">
                    <label>Image (optional)</label>
                    <div style="display:flex; align-items:center; gap:1rem;">
                        <input type="file" id="questionImage" name="questionImage" class="form-control"
                            accept="image/*">
                        <button type="button" onclick="document.querySelector('#questionImage').value = '';"
                            class="btn btn-primary">Reset</button>

                    </div>

                    <br>

                    <!-- question image -->
                    <?php if($question && $question['image']): ?>
                    <div>
                        <img src="<?php echo $question['image']?>"
                            style="max-width:100%; height:auto; display:block; margin:0 auto;" alt="">
                    </div>
                    <div style="text-align:center;" method="post">
                        <a href="<?php echo base_url(); ?>examination/delete_image_question/<?php echo $question['intID'] ?>"
                            class="btn btn-delete btn-sm btn-danger">Remove Image</a>
                    </div>
                    <?php endif; ?>
                    <!-- end -->


                </div>



                <div class="form-group col-xs-6">
                    <label for="type">Section</label>

                    <select name="strSection" required class="form-control" id="strSection">
                        <option value="" selected disabled>--select section--</option>
                        <?php if($question['type'] == 'college' || $question['type'] == 'other' ): ?>

                        <option <?php echo $question['strSection'] == "English" ? "selected": "" ?>>English</option>
                        <option <?php echo $question['strSection'] == "Mathematics" ? "selected": "" ?>>Mathematics
                        </option>
                        <option <?php echo $question['strSection'] == "Abstract" ? "selected": "" ?>>Abstract
                        </option>
                        <option <?php echo $question['strSection'] == "Visuospatial" ? "selected": "" ?>>
                            Visuospatial
                        </option>

                        <?php else : ?>
                        <option <?php echo $question['strSection'] == "English" ? "selected": "" ?>>English</option>
                        <option <?php echo $question['strSection'] == "Mathematics" ? "selected": "" ?>>Mathematics
                        </option>
                        <option <?php echo $question['strSection'] == "Filipino" ? "selected": "" ?>>Filipino
                        </option>
                        <option <?php echo $question['strSection'] == "Science" ? "selected": "" ?>>Science</option>
                        <?php endif; ?>

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



            <form id="choices-form" enctype="multipart/form-data"
                action="<?php echo base_url(); ?>examination/submit_choice" method="post" role="form">
                <input type="hidden" name="question_id" value="<?php echo $question['intID']; ?>" />
                <input type="hidden" id="selected_index" name="selected_index" value="" />
                <div class="box-body">

                    <div class="form-group col-md-6 col-xs-12" id="choices_container">
                        <?php 
                            foreach ($choices as $choice):
                        ?>

                        <div class="choice_box alert" style="background: #e6e9e9;">
                            <div class="form-group">
                                <label for="strProgramCode">Enter Choice Value</label>
                                <textarea type="text" name="strChoice[]" class="form-control" rows="5"
                                    placeholder="Enter choice name"><?php echo $choice['strChoice'];?></textarea>

                                <input type="hidden" name="choiceID[]" value="<?php echo $choice['intID'];?>"
                                    class="form-control" placeholder="">
                            </div>

                            <div class="form-group">
                                <label>Image (optional)</label>
                                <div style="display:flex; align-items:center; gap:1rem;" class="inputGroup">
                                    <input type="file" id="" name="choiceImage[]" class="form-control inputImage"
                                        accept="image/*">
                                    <button type="button" class="btn btn-primary btnResetImage">Reset</button>
                                </div>
                                <div action="" style="text-align:center; margin-top:1rem;">
                                    <div>
                                        <img src="<?php echo $choice['image']?>" style="max-width:100%; height:auto"
                                            alt="">
                                    </div>

                                    <?php if($choice && $choice['image']): ?>
                                    <div>
                                        <a href="<?php echo base_url() ?>examination/delete_image_choice/<?php echo $question['intID'] ?>/<?php echo $choice['intID'] ?>"
                                            class="btn btn-sm btn-danger">Remove
                                            Image</a>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>


                            <div class="form-group">
                                <label>Correct Answer</label>
                                <select class="form-control" name="is_correct[]">
                                    <option <?php echo $choice['is_correct'] == 0?'selected':''; ?> value=0>No</option>
                                    <option <?php echo $choice['is_correct'] == 1?'selected':''; ?> value=1>Yes</option>
                                </select>
                            </div>
                            <br>
                            <form action="<?php echo base_url(); ?>examination/delete_choice" method="post">
                                <input type="hidden" name="choice_id" value="<?php echo $choice['intID'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Remove Choice</button>
                            </form>
                            <hr>

                        </div>


                        <?php endforeach; ?>


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

<script src="<?php echo base_url(); ?>assets/themes/default/js/jquery.min.js"></script>
<script src="https://cdn.ckeditor.com/4.4.7/standard-all/ckeditor.js"></script>
<script>
const choicesBox = $("#choices_container");
const addNewBtn = $("#add_new");
const btnRemove = $(".btn_remove");

const toAppend = `<div class="choice_box alert" style="background: #e6e9e9;">
                    <div>
                        <div class="form-group">
                            <label for="strProgramCode">Enter Choice Value</label>
                            <input type="hidden" name="choiceID[]" class="form-control" placeholder="">
                            <textarea type="text" name="strChoice[]" class="form-control" rows="5"
                                    placeholder="Enter choice name"></textarea>
                        </div>

                        <div class="form-group">
                             <label>Image (optional)</label>
                            <div style="display:flex; align-items:center; gap:1rem;" class="inputGroup">
                                <input type="file" id="" name="choiceImage[]" class="form-control inputImage"
                                    accept="image/*">
                                <button type="button"
                                    class="btn btn-primary btnResetImage">Reset</button>

                            </div>
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
                    <button type="button"  class="btn btn-sm btn-danger btn_remove">Remove Choice </button>
                    <hr>
                 </div>`

addNewBtn.on("click", () => {
    choicesBox.append(toAppend)
})


$("#choices_container").on("click", ".btn_remove", function() {
    $(this).parent('div').remove();
})


// getting the selected radio button value
$("#choices_container").on("click", ".radioBtn", function() {
    var radioButtons = $("#choices-form input:radio[name='is_correct[]']");
    var selectedIndex = radioButtons.index(radioButtons.filter(':checked'));

    $("#selected_index").val(selectedIndex);

})
// end

//reset input file per choice
$("#choices_container").on("click", ".btnResetImage", function() {
    $(this).closest('.inputGroup').find(".inputImage").val('');
})
// end
</script>

<script>
CKEDITOR.config.extraPlugins = 'justify';
CKEDITOR.replace('strTitle');
</script>