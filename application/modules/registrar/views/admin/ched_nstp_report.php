<aside class="right-side" >
    <section class="content-header">
        <h1>
            NSTP Report
            <small>
                <a class="btn btn-app" href="<?php echo base_url(); ?>registrar/registrar_reports">
                    <i class="ion ion-arrow-left-a"></i>
                    All Reports
                </a>
            </small>
            <small>
            <button class="btn btn-app" id="ched_nstp_report_pdf" target="_blank" href="#" ><i class="fa fa-book"></i>Print PDF</button>
            </small>
            <small>
            <button class="btn btn-app" id="ched_nstp_report_excel" target="_blank" href="#" ><i class="fa fa-book"></i>Download Excel</button>
            </small>

        </h1>
        <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-dashboard"></i>NSTP Report</a></li>
      <li class="active">View All Ched NSTP Report</li>
    </ol>
    <hr />
    <form class="form-inline text-right" >
    <div class="">
      <select id="select-term-leads"
        class="form-control">
        <?php foreach($sy as $s): ?>
        <option <?php echo ($current_sem == $s['intID'])?'selected':''; ?>
          value="<?php echo $s['intID']; ?>">
          <?php echo $s['term_student_type']." ".$s['enumSem']." ".$s['term_label']." ".$s['strYearStart']."-".$s['strYearEnd']; ?>
        </option>
        <?php endforeach; ?>
      </select>
    </div>  
    </form>
    
    </section>
</aside>
