<aside class="right-side">
    <section class="content-header">
            <h1>
            Student Account Report
            <small>
                <a class="btn btn-app" target="_blank" href="<?php echo $excel_link; ?>" ><i class="fa fa-book"></i>Download Excel</a> 
            </small>
            
        </h1>                          
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Student</a></li>
            <li class="active">View All Student</li>
        </ol>
    </section>
    <div class="content">
        <!-- <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Advanced Search</h3>              
            </div>

            <div class="box-body" style="display: block;">
                <div class="row">                                            
                    <div class="col-sm-4">
                        <label for="sem">Select Term:</label>
                        <select id="sem" class="form-control select2" >
                            <option <?php echo ($sem == 0)?'selected':''; ?> value="0">-----------------ACTIVE TERM---------------</option>
                            <?php foreach($sy as $s): ?>
                                <option <?php echo ($sem == $s['intID'])?'selected':''; ?> value="<?php echo $s['intID']; ?>"><?php echo $s['enumSem']." ".$term_type." ".$s['strYearStart']."-".$s['strYearEnd']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div> -->
    </div>
    
</aside>