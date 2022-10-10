<aside class="right-side">
<section class="content-header">
                    <h1>
                        Department
                        <small></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i>Advising</a></li>
                        <li class="active">Add to Classlistt</li>
                    </ol>
                </section>
<div class="content">
    <div class="span10 box box-primary">
        <div class="box-header">
                <h3 class="box-title">Add To Classlist</h3>
                
        </div>
       
        <div class="box box-solid">
        
            <div class="box-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Message</th>
                            <th>&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for($i = 0;$i<count($col1);$i++): ?>
                        <tr>
                            <td><?php echo $col1[$i]; ?></td>
                            <td><?php echo $col2[$i]; ?></td>
                            <td><?php echo $col3[$i]; ?></td>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
                <hr />
                <?php echo $student_link; ?>
                <a target="_blank" class="btn btn-primary" href="<?php echo base_url()."pdf/student_viewer_advising_print/".$sid ."/". $ayid; ?>">
                                <i class="ion ion-printer"></i> Print Advising Form</a>
                
                <a target="_blank" class="btn btn-primary" href="<?php echo base_url()."pdf/student_viewer_advising_print_data/".$sid ."/". $ayid; ?>">
                                <i class="ion ion-printer"></i> Print Advising Form Data Only</a>
            </div>
        </div>
       
        </div>
</aside>