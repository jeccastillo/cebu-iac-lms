<aside class="right-side">
<section class="content-header">
                    <h1>
                        Department
                        <small></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i>Advising</a></li>
                        <li class="active">Sectioning</li>
                    </ol>
                </section>
<div class="content">
    <div class="span10 box box-primary">
        <div class="box-header">
                <h3 class="box-title">Sectioning</h3>
                <a class="btn btn-app" href="<?php echo base_url()."department/load_subjects/".$student['intID'];?>">
                    <i class="fa fa-arrow-left"></i>
                    back
                </a>
                
        </div>
       
        <div class="box box-solid">
            <div class="box-body">
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
                            <th>Scholarship</th>
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
                            <td><?php echo $student['enumScholarship']; ?></td>
                            <td><?php echo $student['strProgramCode']; ?></td>
                            <td><?php echo $reg_status; ?></td>
                        </tr>
                    </tbody>
                </table>
                <hr />
                <form id="validate-student" action="<?php echo base_url(); ?>registrar/submit_registration_old" method="post" role="form" enctype="multipart/form-data">
                <input type="hidden" name="intProgramID" value="<?php echo $student['intProgramID']; ?>" id="addStudentCourse">
                <input type="hidden" value="<?php echo $student['intCurriculumID']; ?>" id="intCurriculumID">
                <input type="hidden" value="<?php echo $student['intID']; ?>" name="studentID" id="studentID">
                <input type="hidden" value="<?php echo $student['strStudentNumber']; ?>" name="studentNumber">
                <input type="hidden" value="<?php echo $active_sem['intID']; ?>" name="activeSem" id="activeSem">
                <input type="hidden" value="0" id="total-units">
                <input type="hidden" value="<?php echo $student['enumScholarship']; ?>" id="enumScholarship" />
                
                    <div style="border:1px solid #d2d2d2;">
                    <div class="col-sm-8" style="padding:1rem;background:#f2f2f2;">
                Set Active Terms
                <select class="form-control" id="strAcademicYear" name="strAcademicYear">
                            <?php foreach($sy as $s): ?>
                                <option rel="<?php echo $s['intProcessing']; ?>" <?php echo ($s['intProcessing'] == 1)?'selected':''; ?>  value="<?php echo $s['intID'] ?>"><?php echo $s['enumSem']." ".$term_type." ".$s['strYearStart']."-".$s['strYearEnd'];  ?></option>
                            <?php endforeach; ?>
                </select>
                        <hr />
                <div id="regular-option" class="row">
                        <div class="col-sm-12" style="margin-bottom:1rem">
                        <a href="#" id="load-subjects" class="btn btn-default  btn-flat">Load Subjects for Sectioning <i class="fa fa-arrow-circle-down"></i></a>
                            </div>
                    </div>
                    <hr />
                    <div id="subject-list">
                    
                    </div>
                    </div>
                    <div class="col-sm-4" style="padding:1rem;">
                    
    
                    <div id="tuitionContainer">
                    
                    </div>
                    <input type="button" id="submit-button" <?php echo ($reg_status!="For Sectioning")?'disabled':''; ?> value="Confirm" class="btn btn-default  btn-flat btn-block">
                    </div>
                    <div style="clear:both"></div>
                </div>
                </form>
                </div>
            </div>
        </div>
       
        </div>
</aside>