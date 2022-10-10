<aside class="right-side">
<section class="content-header">
                    <h1>
                        Academic Year
                        <small></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Admin</a></li>
                        <li class="active">Edit Academic Year</li>
                    </ol>
                </section>
<div class="content">
    <div class="span10 box box-primary">
        <div class="box-header">
                <h3 class="box-title">Edit Academic Year</h3>
        </div>
       
            
            <form id="validate-subject" action="<?php echo base_url(); ?>registrar/edit_submit_ay" method="post" role="form">
                <input type="hidden" name="intID" value="<?php echo $item['intID'] ?>" />
                 <div class="box-body">
                     <div class="form-group col-xs-12 col-lg-4">
                        <label for="enumSem"><?php echo $term_type; ?></label>
                        <select name="enumSem" class="form-control">
                            <?php foreach($terms as $term): ?>
                                <option <?php echo $item['enumSem']==$term?'selected':''; ?> value="<?php echo $term ?>"><?php echo $term ?></option>
                            <?php endforeach; ?>
                         </select>
                    </div>
                     <div class="form-group col-xs-12 col-lg-4">
                        <label for="strYearStart">Year</label>
                         <select id="year-start" name="strYearStart" class="form-control">
                            <?php for($y=2001;$y<2060;$y++): ?>
                                <option <?php echo $item['strYearStart']==$y?'selected':''; ?> value="<?php echo $y; ?>"><?php echo $y."-".($y+1); ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                     <div class="form-group col-xs-12 col-lg-4">
                        <label for="enumStatus">Status</label>
                        <select name="enumStatus" class="form-control">
                            <?php if($item['enumStatus'] != "active"): ?> 
                                <option <?php echo $item['enumStatus']=='inactive'?'selected':''; ?>  value="inactive">Inactive</option>
                            <?php endif; ?>
                            <option <?php echo $item['enumStatus']=='active'?'selected':''; ?> value="active">Active</option>
                         </select>
                    </div>      
                     <div class="form-group col-xs-12 col-lg-4">
                        <label for="enumFinalized">Prelim Grading Period</label>
                        <select name="enumGradingPeriod" class="form-control">
                            <option <?php echo $item['enumGradingPeriod']=='inactive'?'selected':''; ?>  value="inactive">Inactive</option>
                            <option <?php echo $item['enumGradingPeriod']=='active'?'selected':''; ?> value="active">Active</option>
                         </select>
                    </div>
                    <div class="form-group col-xs-12 col-lg-4">
                        <label for="enumFinalized">Midterm Grading Period</label>
                        <select name="enumMGradingPeriod" class="form-control">
                            <option <?php echo $item['enumMGradingPeriod']=='inactive'?'selected':''; ?>  value="inactive">Inactive</option>
                            <option <?php echo $item['enumMGradingPeriod']=='active'?'selected':''; ?> value="active">Active</option>
                         </select>
                    </div>
                    <div class="form-group col-xs-12 col-lg-4">
                        <label for="enumFinalized">Final Term Grading Period</label>
                        <select name="enumFGradingPeriod" class="form-control">
                            <option <?php echo $item['enumFGradingPeriod']=='inactive'?'selected':''; ?>  value="inactive">Inactive</option>
                            <option <?php echo $item['enumFGradingPeriod']=='active'?'selected':''; ?> value="active">Active</option>
                         </select>
                    </div>
                    <div class="form-group col-xs-12 col-lg-4">
                        <label for="enumFinalized">Finalized</label>
                        <select name="enumFinalized" class="form-control">
                            <option <?php echo $item['enumFinalized']=='no'?'selected':''; ?>  value="no">No</option>
                            <option <?php echo $item['enumFinalized']=='yes'?'selected':''; ?> value="yes">Yes</option>
                         </select>
                    </div>              
                     <div class="form-group col-xs-12">
                         <input type="submit" value="update" class="btn btn-default  btn-flat">
                     </div>
                <div style="clear:both"></div>
            </form>
       
        </div>
</aside>