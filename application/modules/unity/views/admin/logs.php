<aside class="right-side">
     <section class="content-header">
                    <h1>
                        Logs
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Admin</a></li>
                        <li class="active">Logs</li>
                    </ol>
                </section>
    <section class="content">
            <div class="form-group">
                <label>Filter Logs:</label>
                <div class="input-group">
                    <button class="btn btn-default pull-right" id="daterange-btn">
                        <i class="fa fa-calendar"></i> Choose Date Range
                        <i class="fa fa-caret-down"></i>
                    </button>
                </div>
            </div>
            <ul class="timeline">
            <?php 
                $prev = "";
                foreach($logs as $log):
                if(date("Y-m-d",strtotime($log['dteLogDate'])) != $prev ):
                ?>
                <!-- timeline time label -->
                <li class="time-label">
                    <span class="bg-red">
                        <?php echo date("M, j Y",strtotime($log['dteLogDate'])); ?>
                    </span>
                </li>
                <!-- /.timeline-label -->
                <?php endif; ?>
                <!-- timeline item -->
                <li>
                    <!-- timeline icon -->
                    <i class="fa fa-comments bg-<?php echo $log['strColor'] ?>"></i>
                    <div class="timeline-item">
                        <span class="time"><i class="fa fa-clock-o"></i> <?php echo date("H:i",strtotime($log['dteLogDate'])); ?></span>

                        <h3 class="timeline-header"><?php echo $log['strCategory'] ?></h3>

                        <div class="timeline-body">
                           <?php echo $log['strAction']; ?>
                        </div>

                        <div class='timeline-footer'>
                            <a class="btn btn-default  btn-flat btn-xs"><?php echo $log['strFirstname']." ".$log['strLastname']; ?></a>
                        </div>
                    </div>
                </li>
                <?php 
                $prev = date("Y-m-d",strtotime($log['dteLogDate']));
                endforeach; ?>
                <!-- END timeline item -->
            </ul>
    </section>
</aside>