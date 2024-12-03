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
              <h5  style="margin-top: -8px;"><strong>iACADEMY Inc.</strong></h5>
                <h6 style="margin-top: -8px;">Filinvest Cebu Cyberzone Tower 2 Salinas Drive corner W. Geonzon St., Brgy. Apas, Lahug, Cebu City</h6>
<!--              <h6 style="margin-top: -8px;">Department of Computer Science and Information Technology</h6>-->
        </div>
        <div style="float:right;width:25%;text-align:left">
            <img src="<?php echo $img_dir?>iacademy-logo.png"  width="70" height="70"/>
        </div>
        
    </div<!-- /.box-header --> 
<hr style="clear:both;"/>        
</div>

<div class="content" style="min-height:0;">
    <div>
        <div class="box-header" style="margin-top:-40px;">
                <h4 class="box-title text-center" style="letter-spacing: 15px;display:block;"><strong>REPORT OF GRADES</strong>
			 <p><h4 class="text-center"><?php echo "A.Y." . " " .$active_sem['strYearStart']."-".$active_sem['strYearEnd'] . " " . $active_sem['enumSem']." ".$term_type ; ?></h4></p>
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
              <h4 style="margin-top:0px;font-size:18px; ">
                  <strong>
                  <?php 
                        $middleInitial = substr($student['strMiddlename'], 0,1);
                        echo $student['strLastname'] . ", " . $student['strFirstname'] . " " . $middleInitial . ".";   
                  ?>
                  </strong>
              </h4>
              <h4 style="font-size:15px;" ><?php echo $student['strProgramCode']; ?></h4>
            </div>
            <div class="col-lg-4 col-xs-12" style="width:40%;">
                <p><strong>Student Number: </strong><?php echo date("Y",strtotime($student['dteCreated']))."-".$student['strStudentNumber']; ?></p>
              <p><strong>Address: </strong><?php echo $student['strAddress']; ?></p>
              <p><strong>Contact: </strong><?php echo $student['strMobileNumber']; ?></p>
              <p><strong>Email: </strong><?php echo $student['strEmail']; ?></p>    
            </div>
        
            <div class="col-xs-2 col-lg-4 size-98" style="width:20%" >
              <?php if($student['strPicture'] == "" ): ?>
                <img src="<?php echo $img_dir?>default_image2.png"  width="110" height="110"/>
              <?php else: ?>
                <img class="img-responsive" src="<?php echo $student_pics.$student['strPicture']; ?>" />
              <?php endif; ?>
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
                <table class="table table-condensed">
                    <thead>
                        <tr style="font-size: 11px;">
                           
                            <th>Course Code</th>
                            <th>Course Description</th>
                            <th>Units</th>
                            <th>Grade</th>
                            <th></th>
                            <th>Remarks</th>
                            <th>Faculty</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalUnits = 0;
                        foreach($records as $record): ?>
                        <tr style="font-size: 12px;">
                            
                            <td><?php echo $record['strCode']; ?></td>
                            <td><?php echo $record['strDescription'] ?></td>
                            <td><?php echo $record['strUnits']; ?></td>
                            <?php if($record['intFinalized'] == 1): 
                                     if( getEquivalent($record['v3']) == 5.00):    
                            ?>
                                 <td><span class="text-red"><?php echo getEquivalent($record['v3']) ?></span></td>
                            <?php else: ?>
                                <td><?php echo getEquivalent($record['v3']); ?></td>
                            <?php endif; ?>
                            <?php else: ?>
                          
                                <td><span class="text-green">Not Yet Encoded</span></td>
                            <?php endif; ?>  
                            <td>
                            <?php
                                
                                if(getEquivalent($record['v3']) != "inc" && getEquivalent($record['v3']) != "0"){
                                    //$productArray = array();
                                    $product = $record['strUnits'] * getEquivalent($record['v3']); 
                                    $products[] = $product;
                                    //echo $product
                                    $totalUnits += $record['strUnits'];
                                   
                                }
                                
                            ?>
                            </td>
                            <?php if( getEquivalent($record['v3']) == 5.00): 
                        ?>  
                        <td><span class="text-red"><?php echo $record['strRemarks']; ?></span></td>
                        <?php else: ?>
                        <td><?php echo $record['strRemarks']; ?></td>
                        <?php endif; ?>
                        <td>  
                           <?php 
                                $firstNameInitial = substr($record['strFirstname'], 0,1);
                                echo $firstNameInitial.". ".$record['strLastname'];  
                            ?>
                        </td>

                        </tr>
                     
                        <?php endforeach; ?>
                        

                        <tr>
                            <td></td>
                            <td align="right"><strong>TOTAL UNITS CREDITED:</strong></td>
                            <td><?php 
                                    echo $totalUnits;
                                    ?>
                            </td>
                            <td></td>
                            <td>
                                <?php 
                                    // echo array_sum($products); 
                                ?>
                            </td>
                            <td></td>
                            <td></td>
                        </tr>
                        

                           <tr>
                            <td></td>

                            <td align="right"><strong>GPA:</strong></td>
                            <td>
                                <?php
                                   $gpa = round(array_sum($products) /$totalUnits, 2)  ;
                                   echo $gpa;
                                ?>
                                
                                
                            </td>

                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
<div class="content" style="position:fixed;bottom:0;margin-top:5px; margin-bottom: -30px;width:100%">
    <div>
        <div style="width:70%;float:left">
            <div style="width:50%">
                <hr style="width:100%;text-align:left;border-top:1px solid #555;" />
            </div>    
            <h5 style="margin-top: -10px;">Department Coordinator</h5>
        </div>
        <div style="width:30%;float:left">
            <div style="width:50%">
               <u><?php echo date("m-d-Y"); ?></u>
<!--                <u> <?php echo date("F j, Y ") ?></u>-->
<!--               <hr style="width:100%;text-align:left;border-top:1px solid #555;" />-->
                
            </div>
            <h5 style="margin-top: 5px;">Date</h5>
        </div>
    </div>
    <div>
        <div style="width:70%;float:left">
            <h5 style="margin-top: 5px;">Prepared by: <u><?php echo $user['strFirstname']. " " . $user['strLastname']; ?></u></h5>
<!--                <u>></u>-->
            <h6 class="text-green" style="font-style: italic;">***Note: Incomplete subjects should be complied within 1 year, otherwise it will be considered failed.</h6>
            <div style="width:50%">
<!--                <hr style="width:100%;text-align:left;border-top:1px solid #555;" />-->
            </div>
            
        </div>
        
    </div>
</div>