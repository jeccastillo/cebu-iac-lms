<aside class="right-side">
<section class="content-header">
                    <h1>
                        Admissions Term Setup
                        <small></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Admin</a></li>
                        <li class="active">Admissions Term Setup</li>
                    </ol>
                </section>
<div class="content">
    <div class="box box-primary">
        <div class="box-header">
                <h3 class="box-title">Admissions Term Edit</h3>                
        </div>                   
        <div class="box-body">
            <div class="row">
                <div class="mb-5 col-sm-6">
                    <label for="sem">Select Term:</label>
                    <select id="sem-select-edit-ay" class="form-control select2" >                        
                        <?php foreach($sy as $s): ?>
                            <option <?php echo ($item['intID'] == $s['intID'])?'selected':''; ?> value="<?php echo $s['intID']; ?>"><?php echo $s['term_student_type']." ".$s['enumSem']." ".$term_type." ".$s['strYearStart']."-".$s['strYearEnd']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>       
            <hr />     
            <form id="validate-subject" action="<?php echo base_url(); ?>admissionsV1/edit_submit_ay" method="post" role="form">
                <input type="hidden" name="intID" value="<?php echo $item['intID'] ?>" />                                   
                <div class="form-group col-xs-12 col-lg-4">
                        <label for="midterm_start">End of Application Period</label>
                        <input type="datetime-local" name="endOfApplicationPeriod" value="<?php echo $item['endOfApplicationPeriod']; ?>" class="form-control" />                         
                </div>
                <div class="form-group col-xs-12">
                    <input type="submit" value="update" class="btn btn-default  btn-flat">
                </div>
                <div style="clear:both"></div>
            </form>    
        </div>
    </div>
    
</aside>