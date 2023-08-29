<aside class="right-side">
    <section class="content-header">
        <h1>
            Exam Type
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Exam Type</a></li>
            <li class="active">New Exam Type</li>
        </ol>
    </section>
    <div class="content">
        <div class="span10 box box-primary">
            <div class="box-header">
                <h3 class="box-title">New Exam Type</h3>
            </div>


            <form id="validate-program" action="<?php echo base_url(); ?>examination/submit_exam_type" method="post"
                role="form">
                <div class="box-body">
                    <div class="form-group col-xs-6">
                        <label for="strProgramCode">Name</label>
                        <input type="text" name="strName" class="form-control" id="strName" placeholder="Enter Name">
                    </div>

                    <div class="form-group col-xs-6">
                        <label for="type">Exam Type</label>
                        <select class="form-control" name="type" id="type" required>
                            <option value="" disabled selected>--select type--</option>
                            <option>shs</option>
                            <option>college</option>
                            <option>other</option>
                        </select>
                    </div>
                    <div class="form-group col-xs-6">
                        <label for="type">Program</label>
                        <select class="form-control" name="programType" id="programType" required>
                            <option value="" disabled selected>--select type--</option>
                            <option>shs</option>
                            <option>computing</option>
                            <option>business</option>
                            <option>design</option>
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
</aside>