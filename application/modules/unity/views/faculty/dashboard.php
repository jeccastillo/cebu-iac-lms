 <div class="content-wrapper">
     <section class="content-header">
         <h1>
             Dashboard
             <small>Welcome</small>
         </h1>
         <ol class="breadcrumb">
             <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
             <li class="active">Dashboard</li>
         </ol>
     </section>
     <section class="content">
         <?php if($pwd == "1234"): ?>
         <div class="alert alert-warning" role="alert">
             <h4><i class="fa fa-exclamation-triangle"></i> Alert!</h4>
             Detected default password. Click <a href="<?php echo base_url(); ?>faculty/edit_profile">here</a> to update
             password.
         </div>
         <?php endif; ?>

         <div class="row">
             <div class="col-md-4 col-sm-8 col-xs-12">
                 <div class="info-box">
                     <span class="info-box-icon bg-red"><i class="fa fa-star"></i></span>

                     <div class="info-box-content">
                         <span class="info-box-text">ACTIVE TERM</span>                         
                         <span
                             class="info-box-number"><?php echo $active_sem['enumSem']." ".$term_type." ".$active_sem['strYearStart']."-".$active_sem['strYearEnd']; ?></span>


                     </div>
                     <!-- /.info-box-content -->
                 </div>
                 <!-- /.info-box -->
             </div>
             <div class="col-md-4 col-sm-8 col-xs-12">
                 <div class="info-box">
                     <span class="info-box-icon bg-red"><i class="fa fa-file"></i></span>

                     <div class="info-box-content">
                         <span class="info-box-text">APPLICATION TERM</span>                         
                         <span
                             class="info-box-number"><?php echo $app_sem['enumSem']." ".$term_type." ".$app_sem['strYearStart']."-".$app_sem['strYearEnd']; ?></span>


                     </div>
                     <!-- /.info-box-content -->
                 </div>
                 <!-- /.info-box -->
             </div>
             <div class="col-md-4 col-sm-8 col-xs-12">
                 <div class="info-box bg-blue">
                     <span class="info-box-icon"><i class="fa fa-calendar"></i></span>

                     <div class="info-box-content">
                         <span class="info-box-text">ROOM RESERVATION</span>                         
                         <span class="info-box-number">
                             <a href="<?php echo base_url(); ?>reservation" class="text-white">
                                 <i class="fa fa-plus"></i> Reserve Room
                             </a>
                         </span>
                     </div>
                     <!-- /.info-box-content -->
                 </div>
                 <!-- /.info-box -->
             </div>
             <div class="col-md-4 col-sm-8 col-xs-12" style="display:none">
                 <!-- small box -->
                 <div class="small-box bg-yellow">
                     <div class="inner">
                         <h3><?php echo $myclasses; ?></h3>

                         <p>My Classes this Term</p>
                     </div>
                     <div class="icon">
                         <i class="fa fa-list"></i>
                     </div>
                     <a href="<?php echo base_url(); ?>unity/view_classlist" class="small-box-footer">
                         View Classes <i class="fa fa-arrow-circle-right"></i>
                     </a>
                 </div>
                 <!-- small box -->
             </div>
             <div class="col-md-4 col-sm-8 col-xs-12" style="display:none">

                 <div class="small-box bg-yellow">
                     <div class="inner">
                         <h3>My Account</h3>
                         <p>User Account</p>
                     </div>
                     <div class="icon">
                         <i class="fa fa-user"></i>
                     </div>
                     <a href="<?php echo base_url(); ?>faculty/my_profile" class="small-box-footer">
                         View Profile <i class="fa fa-arrow-circle-right"></i>
                     </a>
                 </div>
             </div>
         </div>

         <div class="box box-primary" style="display:none">

             <div class="box-header with-border">
                 <h3 class="box-title">All Students</h3>

                 <div class="box-tools pull-right">
                     <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                     </button>
                     <button type="button" class="btn btn-box-tool" data-widget="remove"><i
                             class="fa fa-times"></i></button>
                 </div>
             </div>
             <div class="box-body">
                 <div class="chart">
                     <canvas id="studentsChart" height="300"></canvas>
                 </div>
             </div>
             <!-- /.box-body -->
         </div>

         <div class="box box-primary" style="display:none">
             <div class="box-header with-border">
                 <h3 class="box-title">Registration Status for
                     <?php echo $active_sem['enumSem']." ".$term_type." ".$active_sem['strYearStart']."-".$active_sem['strYearEnd']; ?>
                 </h3>

                 <div class="box-tools pull-right">
                     <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                     </button>
                     <button type="button" class="btn btn-box-tool" data-widget="remove"><i
                             class="fa fa-times"></i></button>
                 </div>
             </div>
             <div class="box-body">
                 <div class="chart">
                     <canvas id="eStudentsChart" height="300"></canvas>
                 </div>
             </div>
             <!-- /.box-body -->
         </div>

         <div class="box box-primary" style="display:none">
             <div class="box-header with-border">
                 <h3 class="box-title">Grades Comparison Chart</h3>

                 <div class="box-tools pull-right">
                     <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                     </button>
                     <button type="button" class="btn btn-box-tool" data-widget="remove"><i
                             class="fa fa-times"></i></button>
                 </div>
             </div>
             <div class="box-body">
                 <div class="chart">
                     <canvas id="gradesChart" height="300"></canvas>
                 </div>
             </div>
             <!-- /.box-body -->
         </div>
         <div class="row" style="display:none">
             <div class="col-md-8 col-md-offset-2">
                 <div class="box box-primary">
                     <div class="box-header with-border">
                         <h3 class="box-title">Classlists</h3>

                         <div class="box-tools pull-right">
                             <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                     class="fa fa-minus"></i>
                             </button>
                             <button type="button" class="btn btn-box-tool" data-widget="remove"><i
                                     class="fa fa-times"></i></button>
                         </div>
                     </div>
                     <div class="box-body">
                         <div class="chart">
                             <canvas id="classlistsChart" height="300"></canvas>
                         </div>
                     </div>
                     <!-- /.box-body -->
                 </div>
             </div>
         </div>

     </section>
 </div>