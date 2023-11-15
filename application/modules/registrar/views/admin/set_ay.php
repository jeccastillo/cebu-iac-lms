<aside class="right-side">
<section class="content-header">
                    <h1>
                        School Term
                        <small></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Admin</a></li>
                        <li class="active">Set Term</li>
                    </ol>
                </section>
<div class="content">
    <div class="span10 box box-primary">
        <div class="box-header">
                <h3 class="box-title">Set Term</h3>
        </div>
       
            
            <form id="set-ay-form" action="<?php echo base_url(); ?>registrar/submit_ay" method="post" role="form">
                 <div class="box-body">
                     
                     
                     
                <div class="form-group col-xs-6">
                    <label>Choose Term to activate (College)</label>
                    <select class="form-control" name="current">
                        <?php foreach($sy as $s): ?>
                            <option <?php echo ($current == $s['intID'])?'selected':''; ?>  value="<?php echo $s['intID'] ?>"><?php echo $s['enumSem']." ".$s['term_label']." ".$s['strYearStart']."-".$s['strYearEnd'];  ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>                

                <div class="form-group col-xs-6">
                    <label>Choose Default Term for Application/Enrollment (College)</label>
                    <select class="form-control" name="application">
                        <?php foreach($sy as $s): ?>
                            <option <?php echo ($application == $s['intID'])?'selected':''; ?>  value="<?php echo $s['intID'] ?>"><?php echo $s['enumSem']." ".$s['term_label']." ".$s['strYearStart']."-".$s['strYearEnd'];  ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if($campus != "Cebu"): ?>
                <div class="form-group col-xs-6">
                    <label>Choose Sem to activate (SHS)</label>
                    <select class="form-control" name="currentshs">
                        <?php foreach($sy_shs as $s): ?>
                            <option <?php echo ($current_shs == $s['intID'])?'selected':''; ?>  value="<?php echo $s['intID'] ?>"><?php echo $s['enumSem']." ".$s['term_label']." ".$s['strYearStart']."-".$s['strYearEnd'];  ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-xs-6">
                    <label>Choose Default Sem for Application/Enrollment (SHS)</label>
                    <select class="form-control" name="applicationshs">
                        <?php foreach($sy_shs as $s): ?>
                            <option <?php echo ($application_shs == $s['intID'])?'selected':''; ?>  value="<?php echo $s['intID'] ?>"><?php echo $s['enumSem']." ".$s['term_label']." ".$s['strYearStart']."-".$s['strYearEnd'];  ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php endif; ?>
                
                <div class="form-group col-xs-12">
                    <input type="button" id = "submit-ay" value="set" class="btn btn-default  btn-flat">
                </div>
                <div style="clear:both"></div>
            </form>
       
        </div>
</aside>