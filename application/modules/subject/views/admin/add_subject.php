<aside class="right-side">
<section class="content-header">
                    <h1>
                        Subject
                        <small></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Subject</a></li>
                        <li class="active">New Subject</li>
                    </ol>
                </section>
<div class="content">
    <div class="span10 box box-primary">
        <div class="box-header">
                <h3 class="box-title">New Subject</h3>
        </div>
       
            
            <form id="validate-subject" action="<?php echo base_url(); ?>subject/submit_subject" method="post" role="form">
                <div class="box-body">
                         <div class="form-group col-xs-6">
                            <label for="strCode">Subject Code</label>
                            <input type="text" name="strCode" class="form-control" id="strCode" placeholder="Enter Subject Code">
                        </div>

                        <div class="form-group col-xs-6">
                            <label for="strUnits">Number of Units</label>
                            <input type="number" name="strUnits" class="form-control" id="strUnits" placeholder="Enter Number of Units">
                        </div>
                        <div class="form-group col-xs-6">
                            <label for="strUnits">Number of Units for Tuition</label>
                            <input type="number" name="strTuitionUnits" class="form-control" id="strTuitionUnits" placeholder="Enter Number of Units">
                        </div>                        
                        <?php echo cms_dropdown('strLabClassification','Lab Type',$lab_types,'col-sm-6'); ?>
                        <div class="form-group col-xs-6">
                            <label for="intLab">Laboratory Units</label>
                            <input type="number" class="form-control" value="0" name="intLab" id="intLab" /> 
                        </div>
                        <div class="form-group col-xs-6">
                            <label for="intLectHours">Lecture Units</label>
                            <input type="number" class="form-control" value="0" name="intLectHours" id="intLectHours" /> 
                        </div>                        
                        <div class="form-group col-xs-6">
                            <label for="isNSTP">NSTP Subject?</label>
                            <select class="form-control" name="isNSTP" id="isNSTP" >
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </select>
                        </div>
                        <div class="form-group col-xs-6">
                            <label for="isInternshipSubject">Internship Subject?</label>
                            <select class="form-control" name="isInternshipSubject" id="isInternshipSubject" >
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </select>
                        </div>
                        <div class="form-group col-xs-6">
                            <label for="isThesisSubject">Thesis Subject?</label>
                            <select class="form-control" name="isThesisSubject" id="isThesisSubject" >
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </select>
                        </div>                                                                 
                        <div class="form-group col-xs-6">
                            <label for="intBridging">Bridging</label>
                            <select class="form-control" name="intBridging" id="intBridging" >
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </select>
                        </div>
                        <div class="form-group col-xs-6">
                            <label for="intYearLevel">Major Subject</label>
                            <select class="form-control" name="intMajor" id="intMajor" >
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </select>
                        </div>
                        <div class="form-group col-xs-6">
                            <label for="include_gwa">Include in GWA?</label>
                            <select class="form-control" name="include_gwa" id="include_gwa" >
                                <option value="0">No</option>
                                <option selected value="1">Yes</option>
                            </select>
                        </div> 
                        <?php echo cms_dropdown('strDepartment','Department',$dpt,'col-sm-6'); ?>
                       <div class="form-group col-xs-12">
                            <label>Description</label>
                            <textarea class="form-control" name="strDescription" rows="3" placeholder="Enter Description"></textarea>
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