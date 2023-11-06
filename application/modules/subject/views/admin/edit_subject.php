<aside class="right-side">
<section class="content-header">
                    <h1>
                        Subject
                        <small></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Subject</a></li>
                        <li class="active">Edit Subject <?php echo $subject['strCode']; ?></li>
                    </ol>
                </section>
<div class="content">
    <div class="span10 box box-primary">
        <div class="box-header">
                <h3 class="box-title">Edit Subject <?php echo $subject['strCode']; ?></h3>
        </div>
       
            
            <form id="validate-subject" action="<?php echo base_url(); ?>subject/edit_submit_subject" method="post" role="form">
                <input type="hidden" name="intID"  id="intID" value="<?php echo $subject['intID']; ?>">

                 <div class="box-body">
                    <?php if($userlevel != 6): ?>
                        <div class="form-group col-xs-6">
                            <label for="strCode">Subject Code</label>
                            <input type="text" value="<?php echo $subject['strCode']; ?>" name="strCode" class="form-control" id="strCode" placeholder="Enter Subject Code">
                        </div>
                        
                        <div class="form-group col-xs-6">
                            <label for="strUnits">Number of Units</label>
                            <input type="number" value="<?php echo $subject['strUnits']; ?>"  name="strUnits" class="form-control" id="strUnits" placeholder="Enter Number of Units">
                        </div>
                    <?php endif; ?>
                    <div class="form-group col-xs-6">
                            <label for="strUnits">Number of Units for Tuition</label>
                            <input type="number" name="strTuitionUnits" value="<?php echo $subject['strTuitionUnits'] ?>" class="form-control" id="strTuitionUnits" placeholder="Enter Number of Units">
                    </div>
                    <?php if($userlevel != 6): ?>
                        <?php echo cms_dropdown('strLabClassification','Lab Type',$lab_types,'col-sm-6',$subject['strLabClassification']); ?>                                                
                        <div class="form-group col-xs-6">
                                <label for="intLab">Laboratory Units</label>
                                <input type="number" class="form-control" value="<?php echo $subject['intLab'] ?>" name="intLab" id="intLab" />
                            </div>
                        <div class="form-group col-xs-6">
                                <label for="intLectHours">Lecture/Class Units</label>
                                <input type="number" class="form-control" value="<?php echo $subject['intLectHours'] ?>" name="intLectHours" id="intLectHours" />
                        </div>                        
                            <div class="form-group col-xs-6">
                                <label for="isNSTP">NSTP Subject?</label>
                                <select class="form-control" name="isNSTP" id="isNSTP" >
                                    <option <?php echo ($subject['isNSTP'] == 0)?'selected':''; ?> value="0">No</option>
                                    <option <?php echo ($subject['isNSTP'] == 1)?'selected':''; ?> value="1">Yes</option>
                                </select>
                            </div>
                            <div class="form-group col-xs-6">
                                <label for="isInternshipSubject">Internship Subject?</label>
                                <select class="form-control" name="isInternshipSubject" id="isInternshipSubject" >
                                    <option <?php echo ($subject['isInternshipSubject'] == 0)?'selected':''; ?> value="0">No</option>
                                    <option <?php echo ($subject['isInternshipSubject'] == 1)?'selected':''; ?> value="1">Yes</option>
                                </select>
                            </div> 
                            <div class="form-group col-xs-6">
                                <label for="isThesisSubject">Thesis Subject?</label>
                                <select class="form-control" name="isThesisSubject" id="isThesisSubject" >
                                    <option <?php echo ($subject['isThesisSubject'] == 0)?'selected':''; ?> value="0">No</option>
                                    <option <?php echo ($subject['isThesisSubject'] == 1)?'selected':''; ?> value="1">Yes</option>
                                </select>
                            </div>
                            <div class="form-group col-xs-6">
                                <label for="grading_system_id">Select Grading System</label>
                                <select class="form-control" name="grading_system_id" id="grading_system_id" >
                                    <?php foreach($grading_systems as $gs): ?>
                                        <option <?php echo ($subject['grading_system_id'] == $gs['id'])?'selected':''; ?> value="<?php echo $gs['id'] ?>"><?php echo $gs['name']; ?></option> 
                                    <?php endforeach; ?>
                                </select>
                            </div> 
                            <div class="form-group col-xs-6">
                                <label for="include_gwa">Include in GWA?</label>
                                <select class="form-control" name="include_gwa" id="include_gwa" >
                                    <option <?php echo ($subject['include_gwa'] == 0)?'selected':''; ?> value="0">No</option>
                                    <option <?php echo ($subject['include_gwa'] == 1)?'selected':''; ?> value="1">Yes</option>
                                </select>
                            </div> 
                            <div class="form-group col-xs-6">
                                <label for="intBridging">Bridging</label>
                                <select class="form-control" name="intBridging" id="intBridging" >
                                    <option  <?php echo ($subject['intBridging'] == 0)?'selected':''; ?> value="0">No</option>
                                    <option <?php echo ($subject['intBridging'] == 1)?'selected':''; ?> value="1">Yes</option>
                                </select>
                            </div>
                        <div class="form-group col-xs-6">
                                <label for="intYearLevel">Subject Type</label>
                                <select class="form-control" name="intMajor" id="intMajor" >
                                    <option <?php echo ($subject['intMajor'] == 0)?'selected':''; ?> value="0">College</option>
                                    <option <?php echo ($subject['intMajor'] == 1)?'selected':''; ?> value="1">SHS</option>
                                </select>
                            </div>                        
                            <div class="form-group col-xs-6">
                                <label>Description</label>
                                <textarea class="form-control"  name="strDescription" rows="3" placeholder="Enter Description"><?php echo $subject['strDescription']; ?></textarea>
                            </div>
                        <?php endif; ?>
                <div class="form-group col-xs-12">
                    <input type="submit" value="update" class="btn btn-default  btn-flat">
                </div>
                <div style="clear:both"></div>
            </form>
            <?php if($userlevel != 6): ?>

                <h4>Select Prerequisites and Program</h4>
                <div class="row">
                    <div class="col-sm-6">
                        <label>Pre-requisite</label>
                        <select class="form-control select2" id="prereq-selector">
                            <?php foreach($prereq as $pre): ?>
                                <option value="<?php echo $pre['intID']; ?>"><?php echo $pre['strCode'].' '.$pre['strDescription']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-sm-6">
                        <label>Program</label>
                        <select class="form-control select2" id="program-selector">
                            <option value="">None</option>
                            <?php foreach($programs as $prog): ?>                                
                                <option value="<?php echo $prog['intProgramID']; ?>"><?php echo $prog['strProgramCode'].' '.$prog['strProgramDescription']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <hr />
                <a href="#" id="save-prereq" class="btn btn-default  btn-flat btn-block">Save</a>
                <hr />
                <table class="table table-striped table-bordered">
                    <tr>
                       <th>Subject</th>
                       <th>Program</th>
                       <th>Actions</th>
                    </tr>
                    <?php foreach($selected_prereq as $pre): ?>
                        <tr>
                            <td><?php echo $pre['strCode']." ".$pre['strDescription']; ?></td>
                            <td><?php echo $pre['program']?$pre['program']['strProgramCode']:"Not Specified"; ?></td>
                            <td><a href="#" class="btn btn-danger" class="remove-prereq" rel="<?php echo $pre['prereq_subject_id']; ?>">Remove</a></td>
                        </tr>                        
                    <?php endforeach; ?>
                </table>
            <!---Put table here and field to add prerequisite with program id make program optional-->
            <!-- <div class="row">
                <div class="col-md-5">
                    <h4>Select Prerequisites</h4>
                    <select style="height:300px" class="form-control select2" id="prereq-selector" multiple>
                        <?php foreach($prereq as $pre): ?>
                            <option value="<?php echo $pre['intID']; ?>"><?php echo $pre['strCode'].' '.$pre['strDescription']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <br /><br />
                    <a href="#" id="load-prereq" class="btn btn-default  btn-flat btn-block">Load <i class="ion ion-arrow-right-c"></i> </a>
                    <a href="#" id="unload-prereq" class="btn btn-default  btn-flat btn-block"><i class="ion ion-arrow-left-c"></i> Remove</a>
                    <a href="#" id="save-prereq" class="btn btn-default  btn-flat btn-block">Save</a>

                </div>
                <div class="col-md-5">
                    <h4>Prerequisites</h4>
                    <select style="height:100px" class="form-control" id="prereq-selected" multiple>
                        <?php foreach($selected_prereq as $pre): ?>
                            <option value="<?php echo $pre['intID']; ?>"><?php echo $pre['strCode']." ".$pre['strDescription']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div> -->

            <div class="row">
                <div class="col-md-5">
                    <h4>Select Equivalent Subjects</h4>
                    <select style="height:300px" class="form-control select2" id="eq-selector" multiple>
                        <?php foreach($all_eq as $pre): ?>
                            <option value="<?php echo $pre['intID']; ?>"><?php echo $pre['strCode'].' '.$pre['strDescription']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <br /><br />
                    <a href="#" id="load-eq" class="btn btn-default  btn-flat btn-block">Load <i class="ion ion-arrow-right-c"></i> </a>
                    <a href="#" id="unload-eq" class="btn btn-default  btn-flat btn-block"><i class="ion ion-arrow-left-c"></i> Remove</a>
                    <a href="#" id="save-eq" class="btn btn-default  btn-flat btn-block">Save</a>

                </div>
                <div class="col-md-5">
                    <h4>Equivalent Subjects</h4>
                    <select style="height:100px" class="form-control" id="eq-selected" multiple>
                        <?php foreach($selected_eq as $eq): ?>
                            <option value="<?php echo $eq['intID']; ?>"><?php echo $eq['strCode']." ".$eq['strDescription']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <!-- <div class="row">
                <div class="col-md-5">
                    <h4>Select Schema</h4>
                    <select style="height:150px" class="form-control" id="days-selector" multiple>
                        <?php foreach($days as $val): ?>
                            <option value="<?php echo $val; ?>"><?php echo switch_day_schema($val); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <br /><br />
                    <a href="#" id="load-days" class="btn btn-default  btn-flat btn-block">Load <i class="ion ion-arrow-right-c"></i> </a>
                    <a href="#" id="unload-days" class="btn btn-default  btn-flat btn-block"><i class="ion ion-arrow-left-c"></i> Remove</a>
                    <a href="#" id="save-days" class="btn btn-default  btn-flat btn-block">Save</a>

                </div>
                <div class="col-md-5">
                    <h4>Schema for Scheduling</h4>
                    <select style="height:150px" class="form-control" id="days-selected" multiple>
                        <?php foreach($selected_days as $val): ?>
                            <option value="<?php echo $val; ?>"><?php echo switch_day_schema($val); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-5">
                    <h4>Select Rooms</h4>
                    <select style="height:300px" class="form-control" id="room-selector" multiple>
                        <?php foreach($rooms as $room): ?>
                            <option value="<?php echo $room['intID']; ?>"><?php echo $room['strRoomCode']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <br /><br />
                    <a href="#" id="load-rooms" class="btn btn-default  btn-flat btn-block">Load <i class="ion ion-arrow-right-c"></i> </a>
                    <a href="#" id="unload-rooms" class="btn btn-default  btn-flat btn-block"><i class="ion ion-arrow-left-c"></i> Remove</a>
                    <a href="#" id="save-rooms" class="btn btn-default  btn-flat btn-block">Save</a>

                </div>
                <div class="col-md-5">
                    <h4>Rooms for Use</h4>
                    <select style="height:300px" class="form-control" id="room-selected" multiple>
                        <?php foreach($selected_rooms as $room): ?>
                            <option value="<?php echo $room['intID']; ?>"><?php echo $room['strRoomCode']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div> -->
            <?php endif; ?>
       
        </div>
</aside>