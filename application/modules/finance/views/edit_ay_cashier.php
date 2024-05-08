<aside class="right-side">
<section class="content-header">
                    <h1>
                        Finance Term Setup
                        <small></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Admin</a></li>
                        <li class="active">Finance Term Setup</li>
                    </ol>
                </section>
<div class="content">
    <div class="box box-primary">
        <div class="box-header">
                <h3 class="box-title">Finance Term Edit</h3>                
        </div>                   
        <div class="box-body">
            <div class="row">
                <div class="mb-5 col-sm-6">
                    <label for="sem">Select Term:</label>
                    <select id="sem-select-edit-ay" class="form-control select2" >                        
                        <?php foreach($sy as $s): ?>
                            <option <?php echo ($item['intID'] == $s['intID'])?'selected':''; ?> value="<?php echo $s['intID']; ?>"><?php echo $s['term_student_type']." ".$s['enumSem']." ".$s['term_label']." ".$s['strYearStart']."-".$s['strYearEnd']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>       
            <hr />     
            <form id="validate-subject" action="<?php echo base_url(); ?>finance/edit_submit_ay" method="post" role="form">
                <input type="hidden" name="intID" value="<?php echo $item['intID'] ?>" />                                   
                    <div class="form-group col-xs-12 col-lg-4">
                        <label>First Installment Date</label>
                        <input required type="date" name="installment1" value="<?php echo $item['installment1']; ?>" class="form-control" />                         
                    </div>
                    <div class="form-group col-xs-12 col-lg-4">
                        <label>Second Installment Date</label>
                        <input required type="date" name="installment2" value="<?php echo $item['installment2']; ?>" class="form-control" />                         
                    </div>
                    <div class="form-group col-xs-12 col-lg-4">
                        <label>Third Installment Date</label>
                        <input required type="date" name="installment3" value="<?php echo $item['installment3']; ?>" class="form-control" />                         
                    </div>
                    <div class="form-group col-xs-12 col-lg-4">
                        <label>Fourth Installment Date</label>
                        <input required type="date" name="installment4" value="<?php echo $item['installment4']; ?>" class="form-control" />                         
                    </div>
                    <div class="form-group col-xs-12 col-lg-4">
                        <label>Fifth Installment Date</label>
                        <input required type="date" name="installment5" value="<?php echo $item['installment5']; ?>" class="form-control" />                         
                    </div>  
                    <div class="form-group col-xs-12 col-lg-4">
                        <label>Late Enrollment Date Start</label>
                        <input required type="date" name="reconf_start" value="<?php echo $item['reconf_start']; ?>" class="form-control" />                         
                    </div> 
                    <!-- <div class="form-group col-xs-12 col-lg-4">
                        <label>Late Enrollment Date End</label>
                        <input required type="date" name="reconf_end" value="<?php echo $item['reconf_end']; ?>" class="form-control" />                         
                    </div>                                                        -->
                    <div class="form-group col-xs-12 col-lg-4">
                        <label>AR Report Generation Date</label>
                        <input required type="date" name="ar_report_date_generation" value="<?php echo $item['ar_report_date_generation']; ?>" class="form-control" />                         
                    </div>                            
                    <div class="form-group col-xs-12">
                        <input type="submit" value="update" class="btn btn-default  btn-flat">
                    </div>
                <div style="clear:both"></div>
            </form>    
        </div>
    </div>
    
</aside>