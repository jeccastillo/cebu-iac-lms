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

                    <div class="form-group col-xs-6">
                        <label for="type">Program</label>
                        <select class="form-control" name="programType" id="programType" required>
                            <option value="" disabled selected>--select type--</option>
                            <option <?php echo ($item['programType'] == "shs")?'selected':''; ?>>shs</option>
                            <option <?php echo ($item['programType'] == "computing")?'selected':''; ?>>computing
                            </option>
                            <option <?php echo ($item['programType'] == "business")?'selected':''; ?>>business</option>
                            <option <?php echo ($item['programType'] == "design")?'selected':''; ?>>design</option>
                        </select>
                    </div>


                    <div class="form-group col-xs-12">
                        <input type="submit" value="Update" class="btn btn-default  btn-flat">
                    </div>

                    <hr>
                    <div class="col-lg-12 text-center">
                        <p> <strong>NOTE: </strong>The generated exam link will be based on the program type selected.
                        </p>
                        <a id="generateBtn" class="btn btn-success">GENERATE EXAM LINK</a>
                        <br><br>
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
                role="form" enctype="multipart/form-data">

                <div class="box-body">
                    <div class="form-group col-xs-12">
                        <label for="strProgramCode">Question</label>
                        <textarea id="editorQuestion" name="strTitle" required></textarea>
                    </div>

                    <div class="form-group col-xs-6">
                        <label for="strProgramCode">Image (optional)</label>

                        <div style="display:flex; align-items:center; gap:1rem;">
                            <input type="file" id="questionImage" name="questionImage" class="form-control"
                                accept="image/*">
                            <button type="button" onclick="document.querySelector('#questionImage').value = '';"
                                class="btn btn-primary">Reset</button>

                        </div>
                    </div>

                    <input type="hidden" value="<?php echo $item['intID']; ?>" name="exam_id" id="exam_id">


                    <div class="form-group col-xs-6">
                        <label for="type">Section</label>

                        <select name="strSection" required class="form-control" id="strSection">
                            <option value="" selected disabled>--select section--</option>
                            <?php if($item['type'] == 'college' || $item['type'] == 'other' ): ?>

                            <option>English</option>
                            <option>Mathematics</option>
                            <option>Abstract</option>
                            <option>Visuospatial</option>

                            <?php else : ?>
                            <option>English</option>
                            <option>Mathematics</option>
                            <option>Filipino</option>
                            <option>Science</option>
                            <?php endif; ?>

                        </select>
                    </div>



                    <div class="form-group col-xs-12">
                        <input type="submit" value="add" class="btn btn-default  btn-flat">
                    </div>
                    <div style="clear:both"></div>
                </div>
            </form>
        </div>
    </div>
    <hr />


    <div class="content">
        <div class="alert alert-danger" style="display:none;">
            <i class="fa fa-ban"></i>
            <span id="alert-text"></span>
        </div>
        <div class="box box-solid box-primary">
            <div class="box-header">
                <h3 class="box-title">Questions List</h3>

            </div><!-- /.box-header -->
            <div class="box-body table-responsive">
                <table class="table table-hover table-bordered">
                    <thead>
                        <tr>
                            <th>Question</th>
                            <th>Section</th>
                            <th>Actions</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($question as $q): ?>
                        <tr>
                            <td>
                                <div class="ellipsis">
                                    <?php echo $q['strTitle']; ?>
                                </div>
                            </td>
                            <td><?php echo $q['strSection']; ?></td>
                            <td style="white-space:nowrap">
                                <a href="<?php echo base_url(); ?>examination/edit_question/<?php echo $q['intID']; ?>"
                                    class="btn btn-primary">Edit</a>
                                <a class="delete-question btn btn-danger" rel="<?php echo $q['intID']; ?>"
                                    href="#">Delete</a>

                            </td>
                        </tr>

                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </div>
</aside>

<style>
.ellipsis {
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}
</style>
<script src="<?php echo base_url(); ?>assets/themes/default/js/jquery.min.js"></script>
<!-- <script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script> -->
<script src="https://cdn.ckeditor.com/4.4.7/standard-all/ckeditor.js"></script>


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
                                <input type="radio" name="is_correct[]" class="radioBtn" required value="1"> is
                                Correct?
                            </div>
                            <hr>

                        </div>`

addNewBtn.on("click", () => {
    choicesBox.append(toAppend)
})


$("#choices_container").on("click", "button", function() {
    $(this).parent('div').closest(".choice_box").remove();
})

$("#choices_container").on("click", ".radioBtn", function() {
    var radioButtons = $("#choices-form input:radio[name='is_correct[]']");
    var selectedIndex = radioButtons.index(radioButtons.filter(':checked'));

    $("#selected_index").val(selectedIndex);
})
</script>


<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script>
CKEDITOR.config.extraPlugins = 'justify';
CKEDITOR.replace('strTitle');

// for exam generation

const generateButton = document.querySelector("#generateBtn");
generateButton.addEventListener("click", async () => {


    axios.get(api_url +
        'admissions/applications?filter=New&current_sem=26&campus=Makati').then((data) => {

        // to generate student exam

        let formData = new FormData();
        formData.append("applicant", JSON.stringify(data.data.data))
        formData.append("exam_id", <?php  echo $item['intID']  ?>)
        formData.append("programType", '<?php  echo $item['programType']; ?>')

        axios.post("<?php echo base_url();?>" + "examination/generate_exam_link", formData)
            .then(
                () => {

                    const form = document.createElement('form');
                    form.method = "post";
                    form.action =
                        "<?php echo base_url() ?>excel/generate_excel_links";
                    form.dataType = "json";

                    const hiddenFieldExamId = document.createElement('input');
                    hiddenFieldExamId.type = 'hidden';
                    hiddenFieldExamId.name = 'exam_id';
                    hiddenFieldExamId.value = <?php  echo $item['intID']  ?>

                    const hiddenFieldProgramType = document.createElement('input');
                    hiddenFieldProgramType.type = 'hidden';
                    hiddenFieldProgramType.name = 'programType';
                    hiddenFieldProgramType.value = '<?php  echo $item['programType']  ?>'

                    form.appendChild(hiddenFieldExamId);
                    form.appendChild(hiddenFieldProgramType);


                    document.body.appendChild(form);
                    form.submit();


                }).catch((e) => {
                console.log(e)
            })


    }).catch((e) => {
        console.log(e)
    })



})
</script>