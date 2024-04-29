<aside class="right-side">
    <section class="content-header">
            <h1>
            Student Account Report
            <small>
                <button class="btn btn-app" id="export_student_account_report" target="_blank" href="#" ><i class="fa fa-book"></i>Download Excel</button> 
            </small>
            
        </h1>                          
        <ol class="breadcrumb">
        </ol>
    </section>
    <div class="content">
        <div class="box">

            <div class="box-body" style="display: block;">
                <div class="row">                                            
                    <div class="col-sm-4">
                        <label for="sem">Select Term:</label>
                        <select id="sem" name="sem" class="form-control select2" >
                            <?php foreach($sy as $s): ?>
                            <option <?php echo ($current_sem == $s['intID'])?'selected':''; ?>
                            value="<?php echo $s['intID']; ?>">
                            <?php echo $s['term_student_type']." ".$s['enumSem']." ".$s['term_label']." ".$s['strYearStart']."-".$s['strYearEnd']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <label for="report_date">As Of Date:</label>
                        <input required type="date" id="report_date" name="report_date" class="form-control" />                     
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</aside>
