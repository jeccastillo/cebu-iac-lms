<aside class="right-side">
<section class="content-header">
                    <h1>
                        Curriculum
                        <small></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Curriculum</a></li>
                        <li class="active">New Curriculum</li>
                    </ol>
                </section>
<div class="content">
    <div class="span10 box box-primary">
        <div class="box-header">
                <h3 class="box-title">New Curriculum</h3>
        </div>
       
            
            <form id="validate-curriculum" action="<?php echo base_url(); ?>unity/submit_curriculum" method="post" role="form">
                <div class="box-body">
                         <div class="form-group col-xs-6">
                            <label for="strName">Name</label>
                            <input type="text" name="strName" class="form-control" id="strName" placeholder="Enter Name/Code">
                        </div>

                        <div class="form-group col-xs-6">
                            <label for="intYearLevel">Program</label>
                            <select class="form-control" name="intProgramID" id="addStudentCourse" >
                                <?php foreach ($programs as $prog): ?>
                                <option value="<?php echo $prog['intProgramID']; ?>"><?php echo $prog['strProgramCode']; ?></option>
                                <?php endforeach; ?>
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