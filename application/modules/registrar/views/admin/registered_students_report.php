<!--<?php error_reporting(0); ?>-->
<style>
    body{
        background: #fff;
    }
</style>
<section class="content-header">
                    <h1>
                        <small>
                            <a class="btn btn-app" href="<?php echo base_url()."unity/"; ?>" ><i class="ion ion-home"></i>Home</a> 
                            <a class="btn btn-app" onClick="printGrade()" ><i class="ion ion-printer"></i>Print</a> 
                        </small>
                        <select id="select-sem-report1" class="form-control" >
                            <?php foreach($sy as $s): ?>
                                <option <?php echo ($selected_ay == $s['intID'])?'selected':''; ?> value="<?php echo $s['intID']; ?>"><?php echo $s['enumSem']." ".$term_type." ".$s['strYearStart']."-".$s['strYearEnd']; ?></option>
                            <?php endforeach; ?>
                        </select>
                        
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
                <h4 class="box-title text-center" style="display:block;"><strong>NUMBER OF RESIDENT SCHOLARS, 7th DISTRICT SCHOLARS AND PAYING STUDENTS</strong>
			 
			</h4>
        </div><!-- /.box-header --> 
    </div>
</div>

    <div class="box box-solid" style="margin-top:10px; ">
        <div class="box-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th class="text-center" colspan="4"><?php echo $active_sem['enumSem']." ".$term_type." ".$active_sem['strYearStart']."-".$active_sem['strYearEnd']; ?></th>
                    </tr>
                    <tr>
                        <th>Course</th>
                        <th class="text-center">Resident</th>
                        <th class="text-center">Paying</th>
                        <th class="text-center">7th District</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($report as $r): ?>
                        <tr>
                            <td><?php echo $r['program']; ?></td>
                            <td class="text-center"><?php echo $r['resident_scholars']; ?></td>
                            <td class="text-center"><?php echo $r['paying']; ?></td>
                            <td class="text-center"><?php echo $r['seventh_district']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                        <tr>
                            <th style="color:#900" rowspan="2">TOTAL</th>
                            <td style="color:#900" class="text-center"><?php echo $total_resident; ?></td>
                            <td style="color:#900" class="text-center"><?php echo $total_paying; ?></td>
                            <td style="color:#900" class="text-center"><?php echo $total_seventh_district; ?></td>
                        </tr>
                        <tr>
                            <td style="color:#900" class="text-center" colspan=3><?php echo $total_all; ?></td>
                       </tr>
                </tbody>
            </table>
        </div>
    </div>
