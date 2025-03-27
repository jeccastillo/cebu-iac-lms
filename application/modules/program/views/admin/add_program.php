<aside class="right-side">
    <section class="content-header">
        <h1>
            Program
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Program</a></li>
            <li class="active">New Program</li>
        </ol>
    </section>
    <div class="content">
        <div class="span10 box box-primary">
            <div class="box-header">
                <h3 class="box-title">New Program</h3>
            </div>


            <form id="validate-program" action="<?php echo base_url(); ?>program/submit_program" method="post"
                role="form">
                <div class="box-body">
                    <div class="form-group col-xs-6">
                        <label for="strProgramCode">Program Code</label>
                        <input type="text" name="strProgramCode" class="form-control" id="strProgramCode"
                            placeholder="Enter Code">
                    </div>

                    <div class="form-group col-xs-6">
                        <label for="strMajor">Major</label>
                        <input type="text" name="strMajor" class="form-control" id="strMajor" placeholder="Enter Major">
                    </div>

                    <div class="form-group col-xs-6">
                        <label for="strProgramDescription">Program Description</label>
                        <textarea name="strProgramDescription" class="form-control"></textarea>
                    </div>
                    <div class="form-group col-xs-6">
                        <label for="short_name">Short Name</label>
                        <input type="text" name="short_name" class="form-control" id="short_name"
                            placeholder="Enter Short Name">
                    </div>

                    <div class="form-group col-xs-6">
                        <label for="type">Type</label>
                        <select class="form-control" name="type" id="type">
                            <option value="college">College</option>
                            <option value="shs">SHS</option>
                            <option value="drive">DRIVE</option>
                            <option value="other">Other</option>
                            <option value="next">Next School</option>
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