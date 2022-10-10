<aside class="right-side">
<section class="content-header">
    <h1>
        Completion Form
    </h1>
    <ol class="breadcrumb">
        <li><a href="<?php echo base_url(); ?>unity/view_classlist"><i class="ion ion-android-book"></i> Student Grade</a></li>
        <li class="active">Comply Grade</li>
    </ol>
</section>
<div class="content">
    <div class="row">
        <div class="col-sm-3">
            <div class="box box-widget widget-user-2">
            <!-- Add the bg color to the header using any of the bg-* classes -->
            <div class="widget-user-header bg-red">
              <!-- /.widget-user-image -->
              <h3 class="widget-user-username" style="text-transform:capitalize;margin-left:0;font-size:1.3em;"><?php echo strtolower($cs['strLastname'].", ". $cs['strFirstname']); ?>
                        <?php echo ($cs['strMiddlename'] != "")?' '.strtolower($cs['strMiddlename']):''; ?></h3>
              <h5 class="widget-user-desc" style="margin-left:0;"></h5>
            </div>
            <div class="box-footer no-padding">
              <ul class="nav nav-stacked">
                <li><a href="#" style="font-size:13px;">Student Number <span class="pull-right text-blue"><?php echo $cs['strStudentNumber']; ?></span></a></li>
                   <li><a href="#" style="font-size:13px;">Program<span class="pull-right text-blue"><?php echo $cs['strProgramCode']." Major in ".$cs['strMajor']; ?></span></a></li>
                   <li><a href="#" style="font-size:13px;">Course Code<span class="pull-right text-blue"><input type="hidden" name="intCSID" class="form-control" id="intCSID" value="<?php echo $cs['intCSID'];?>"><?php echo $cs['strCode']; ?></span></a></li>
                   <li><a href="#" style="font-size:13px;">Prelim Grade<span class="pull-right text-blue" ><input type="hidden" name="floatPrelimGrade" class="form-control" id="floatPrelimGrade" value="<?php echo $cs['floatPrelimGrade'];?>"><?php echo $cs['floatPrelimGrade']; ?></span></a></li>
                   <li ><a href="#" style="font-size:13px;">Midterm Grade<span class="pull-right text-blue"><input type="hidden" name="floatMidtermGrade" class="form-control" id="floatMidtermGrade" value="<?php echo $cs['floatMidtermGrade'];?>"><?php echo $cs['floatMidtermGrade']; ?></span></a></li>
                   <li ><a href="#" style="font-size:13px;">Previous Final Term Grade<span class="pull-right text-blue"><input type="hidden" name="floatFinalTermGrade" class="form-control" id="floatFinalTermGrade" value="<?php echo $cs['floatFinalsGrade'];?>"><?php echo $cs['floatFinalsGrade']; ?></span></a></li>
                   <li>
                   
                       <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <td>New Final Term Grade 
                                    
                                    </td>
                                    <td><input type="number" name="floatNewFinalTermGrade" class="form-control" id="floatNewFinalTermGrade" style="width: 120px; text-align: right;" placeholder="Enter Grade" ></td>
                                </tr>
                                <tr>
                                    <td >Sem. Grade<input type="hidden" name="floatComputedSemGrade" class="form-control" id="floatComputedSemGrade" disabled="disabled"></td>
                                    <td> <div class="col-3" style = "text-align: center; font-weight: bold;" id="strComputedSemGrade" ></div></td>
                                    
                                </tr>
                            </thead>
                </li>
        
                <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <td style="text-align: right;"><a href="#" id="compute-completion" class="btn btn-primary  btn-flat">Compute Grade</a></td>

                                </tr>
                </table>
                                

                   
              </ul>
            </div>
        </div>   
    </div>

    <?php if(empty($st)): ?>
           
        <div class="col-md-9">
    <div class="box box-primary">
        <div class="box-header">
                <h3 class="box-title">PROCESS COMPLETION OF GRADE</h3>
        </div>
       
            
            
                 <div class="box-body">
                 <input type="hidden" id="intClasslistStudentID" name="intClasslistStudentID" value="<?php echo $cs['intCSID']; ?>" />
                 <table class="table table-bordered">
                    <thead>
                        <tr>
                       
                            <th>Course Code</th>
                            <th>Course Title</th>
                            <th>Previous Grade</th>
                            <th>Sem./Summer/AY Taken</th>
                            <th>Date of Completion</th>
                            <th style="text-align: center;">Numerical Rating</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                            <tr>
                                <td><?php echo $cs['strCode'];?></td>
                                <td><?php echo $cs['strDescription'];?></td>
                                <td><?php echo $cs['enumStatus'];?></td>
                                <td><?php echo $cs['enumSem'] . " sem " . $cs['strYearStart'] . "-" . $cs['strYearEnd'];?></td>
                                <td><?php echo date("m-d-Y"); ?></td>
                                <td><input type="hidden" name="floatNumericalRating" class="form-control" id="floatNumericalRating" disabled="disabled"><div id="strNumericalRating" style="text-align: center; font-weight: bold;"></div></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td><a target="#" id="submit-completion" class="btn btn-primary  btn-success">Submit Completion</a></td>
                            </tr>
                        
                    </tbody>
                </table>
                </div>
          
                <div class="form-group col-xs-6">
                    
                        <!-- <a href="#" id="submit-completion" class="btn btn-primary  btn-flat">Submit Completion</a> -->
                </div> 
           
            <div style="clear:both"></div>
                 
        </div>
 
    <?php else: ?>
        <div class="box">
            <div class="box-body">
                <?php print_r($st); ?>
            </div>
        </div>
    <?php endif; ?>
</div>
</aside>

