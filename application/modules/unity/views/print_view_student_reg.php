<!--<?php error_reporting(0); ?>-->
<style>
    body{
        background: #fff;
    }
</style>
<section class="content-header">
                    <h1>
                        <small>
                            <a class="btn btn-app" href="<?php echo base_url()."unity/student_viewer/".$student['intID']; ?>" ><i class="ion ion-arrow-left-a"></i>Back</a> 
                            <a class="btn btn-app" onClick="printGrade()" ><i class="ion ion-printer"></i>Print</a> 
                        </small>
                        
                        
                    </h1>
</section>

<div class="content" style="margin-top:-20px;min-height:0;">
    <div class="box-header">
        <div style="float:left;width:25%;text-align:right">
            <img src="<?php echo $img_dir?>tagaytayseal.png"  width="70" height="70"/>
        </div>
        <div style="text-align:center;float:left;width:50%">
<!--            <h5 >City of Makati</h5>-->
              <h5 class="box-title"><p>City of Makati</p></h5>
              <h5  style="margin-top: -8px;"><strong>iACADEMY, Inc.</strong></h5>
            <h6 style="margin-top: -8px;">Filinvest Cebu Cyberzone Tower 2 Salinas Drive corner W. Geonzon St., Brgy. Apas, Lahug, Cebu City</h6>
            <h6 style="margin-top: -8px;">Telephone No: (046) 483-0470 / (046) 483-0672</h6>    
<!--              <h6 style="margin-top: -8px;">Department of Computer Science and Information Technology</h6>-->
        </div>
        <div style="float:right;width:25%;text-align:left">
            <img src="<?php echo $img_dir?>iacademy-logo.png"  width="70" height="70"/>
        </div>
        
    </div<!-- /.box-header --> 
<hr style="clear:both;"/>        
</div>

<div class="content" style="min-height:0;">
    <div class="col-xs-12">
        <div class="box-header" style="margin-top:-40px;">
                <h4 class="box-title text-center" style="display:block;"><strong>CERTIFICATE OF REGISTRATION</strong>
			 
			</h4>
        </div><!-- /.box-header --> 
    </div>
</div>

    <div class="box box-solid" style="margin-top:10px; border:1px solid #999;">
        <div class="box-body">
            <div class="alert alert-danger" style="display:none;">
                <i class="fa fa-ban"></i>
                <b>Alert!</b> Only admins can delete student records.
            </div>
          
            <div class="col-xs-8 col-lg-6" style="width:40%;">
              <p>
                  <strong>Name</strong>
                  <?php 
                        $middleInitial = substr($student['strMiddlename'], 0,1);
                        echo $student['strLastname'] . ", " . $student['strFirstname'] . " " . $middleInitial . ".";   
                  ?>
                  
              </p>
              <p><strong>PROGRAM</strong> <?php echo $student['strCourse']; ?></p>
              <p><strong>YEAR LEVEL</strong> <?php echo $registration['intYearLevel']; ?></p>
              <p><strong>ACADEMIC YEAR</strong> <?php echo $active_sem['enumSem'].' SEMESTER '.$active_sem['strYearStart']."-".$active_sem['strYearEnd']; ?></p>   
            </div>
            <div class="col-lg-4 col-xs-12" style="width:40%;">
                <p><strong>Student Number: </strong><?php echo date("Y",strtotime($student['dteCreated']))."-".$student['strStudentNumber']; ?></p>
                <p><strong>Registration Status: </strong><span style="text-transform:uppercase;"><?php echo $registration['enumRegistrationStatus']; ?></span></p>
                <p><strong>Date of Registration: </strong><?php echo $registration['date_enlisted']; ?></p>
                <p style="text-transform:capitalize"><strong>Scholarship Grant: </strong><?php echo $student['enumScholarship']; ?></p>
             
            </div>
        
        
            <div style="clear:both"></div>
        </div>
       
        <div style="clear:both"></div>    
    </div>
    </div>
    </div>
<div class="col-xs-12" style="margin-top:-30px;">
        <div class="box box-solid box-primary">
            <div class="box-body">
                <table class="table table-bordered">
                    <thead>
                        <tr style="font-size: 11px;">
                            <th>Section</th>
                            <th>Course Code</th>
                            <th>Course Description</th>
                            <th>Units</th>
                            <th>Schedule</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalUnits = 0;
                        foreach($records as $record): ?>
                        <tr style="font-size: 12px;">
                            <td><?php echo $record['strSection']; ?></td>
                            <td><?php echo $record['strCode']; ?></td>
                            <td><?php echo $record['strDescription'] ?></td>
                            <td><?php echo $record['strUnits']; ?></td>     
                            <?php if(!empty($record['schedule'])): ?>
                            
                            <td>
                                <?php foreach($record['schedule'] as $sched): ?>
                                
                                <?php echo date('g:ia',strtotime($sched['dteStart'])).' - '.date('g:ia',strtotime($sched['dteEnd'])); ?> <?php echo $sched['strDay']; ?> <?php echo $sched['strRoomCode']; ?>
                                <br />
                                <?php endforeach; ?>
                            </td>
                            <?php else: ?>
                            <td></td>
                           
                            <?php endif; ?>

                        </tr>
                     
                        <?php endforeach; ?>
                        

                        

                    </tbody>
                </table>
            </div>
        </div>
    </div>
