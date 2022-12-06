<aside class="right-side">
<section class="content-header">
                    <h1>
                        Registrar
                        <small></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i>Register</a></li>
                        <li class="active">Register Student</li>
                    </ol>
                </section>
<div class="content">
    <div class="span10 box box-primary">
        <div class="box-header">
                <h3 class="box-title">Register Student</h3>
                
                
        </div>
       
        <div class="box box-solid">
            <div class="box-body">
                <?php if(!empty($reg_status)): ?>
                <?php if($message!=""): ?>
                    <div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <strong>Error</strong> <?php echo $message; ?></div>
                <?php endif; ?>
                <?php //print_r($student); ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Student Number</th>
                            <th>Lastname</th>
                            <th>Firstname</th>
                            <th>Middlename</th>
                            <th>Course</th>
                            <th>Registration Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo $student['strStudentNumber']; ?></td>
                            <td><?php echo $student['strLastname']; ?></td>
                            <td><?php echo $student['strFirstname']; ?></td>
                            <td><?php echo $student['strMiddlename']; ?></td>
                            <td><?php echo $student['strProgramCode']; ?></td>
                            <td><?php echo $reg_status; ?></td>
                        </tr>
                    </tbody>
                </table>
                <hr />
                <form id="validate-student" action="<?php echo base_url(); ?>registrar/submit_registration_old2" method="post" role="form" enctype="multipart/form-data">
                <input type="hidden" name="intProgramID" value="<?php echo $student['intProgramID']; ?>" id="addStudentCourse">
                <input type="hidden" value="<?php echo $student['intCurriculumID']; ?>" id="intCurriculumID">
                <input type="hidden" value="<?php echo $student['intID']; ?>" name="studentID" id="studentID">
                <input type="hidden" value="<?php echo $student['strStudentNumber']; ?>" name="studentNumber">
                <input type="hidden" value="<?php echo $active_sem['intID']; ?>" name="activeSem" id="activeSem">
                <input type="hidden" value="<?php echo $reg_status; ?>" name="regStatus" id="regStatus">
                <input type="hidden" value="0" id="total-units">
                
                    <div style="border:1px solid #d2d2d2;">
                    <div class="col-sm-8" style="padding:1rem;background:#f2f2f2;">
                    <h3>Registration Details</h3>
                Set Academic Year to Register
                <select class="form-control" id="strAcademicYear" name="strAcademicYear">
                            <?php foreach($sy as $s): ?>
                                <option rel="<?php echo $s['intProcessing']; ?>" <?php echo ($s['intProcessing'] == 1)?'selected':''; ?>  value="<?php echo $s['intID'] ?>"><?php echo $s['enumSem']." ".$term_type." ".$s['strYearStart']."-".$s['strYearEnd'];  ?></option>
                            <?php endforeach; ?>
                </select>
                    <hr />
                <label for="enumRegistrationStatus">Academic Status</label>
                    <select class="form-control" id="enumRegistrationStatus" name="enumRegistrationStatus">
                        <option value="0">---SELECT---</option>
                        <option value="regular">Regular</option>
                        <option value="irregular">Irregular</option>
                    </select>
                        <br />
                    <label for="enumScholarship">Scholarship Grant</label>
                    <select class="form-control" id="enumScholarship" name="enumScholarship">                        
                        <option value="None">None</option>                        
                        <option value="iACADEMY Scholar">iACADEMY Scholar</option>
                    </select>
                        <br />
                    <label for="enumStudentType">Student Type</label>
                    <select id="transcrossSelect" class="form-control" name="enumStudentType">
                        <option value="0">---SELECT---</option>
                         <option value="new">NEW</option>
                         <option value="old">RETURNING</option>
                         <option value="transferee">TRANSFEREE</option>
                        <option value="cross">CROSS REGISTRANT</option>
                    </select>
                    <br />
                    <input type="text" disabled name="strFrom" id="transcrossText" class="form-control" placeholder="Cross Registrant or Transferee from..." />
                    <br />
                    <label for="paymentType">Payment Type</label>
                    <select id="paymentType" class="form-control" name="paymentType">
                         <option value="full">FULL</option>
                         <option value="partial">PARTIAL</option>
                    </select>
                    
                    <hr />
                    
                <div id="regular-option" class="row">
                        <div class="col-sm-12" style="margin-bottom:1rem">
                        <a href="#" id="load-subjects2" class="btn btn-default  btn-flat">Check Classlists Enlisted and Assess Fees <i class="fa fa-arrow-circle-down"></i></a>
                            </div>
                    </div>
                    <hr />
                    <div id="subject-list">
                    
                    </div>
                    </div>
                    <div class="col-sm-4" style="padding:1rem;">
                    
                        <h3>Accounting</h3>
                    <?php /*
                    <div id="accounting">
                        <div><p><strong>Tuition: </strong><span id="tuition-fee">0</span></p></div>
                        <div><p><strong>Misc: </strong>
                        <span id="misc-fee">
                            <?php echo ($student['enumScholarship'] == "paying")?$misc_fee:'0'; ?>
                        </span>
                        </p></div>
                        <hr />
                        <div><p><strong>Total: </strong><span id="total-fee"></span></p></div>
                    </div>
                    <hr />
                    */ ?>
                    <div id="tuitionContainer">
                    
                    </div>
                    <input type="submit" <?php echo ($reg_status!="For Registration")?'disabled':''; ?> value="Register" class="btn btn-default  btn-flat btn-block">
                    </div>
                    <div style="clear:both"></div>
                </div>
                </form>
                <?php else: ?>
                    <h3>Student Already Registered</h3>
                <?php endif; ?>
                </div>
            </div>
        </div>
       
        </div>
</aside>