<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
<title><?php echo ($title=="")?"":$title; ?></title>
<!-----CSS----------------------------------------------->
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/lib/adminlte/css/jQueryUI/jquery-ui-1.10.3.custom.min.css">
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/lib/adminlte/css/bootstrap.min.css">    
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/lib/adminlte/css/font-awesome.min.css">   
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/lib/adminlte/css/datepicker/datepicker3.css">  
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/lib/adminlte/css/daterangepicker/daterangepicker-bs3.css">
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/lib/adminlte/css/ionicons.min.css">  
<link href="<?php echo base_url(); ?>assets/lib/adminlte/css/iCheck/all.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/lib/adminlte/css/AdminLTE.min.css">
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/lib/adminlte/css/skins/skin-blue-light.css">
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/lib/adminlte/css/datatables/dataTables.bootstrap.css">
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/lib/adminlte/css/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css" />
<link href="<?php echo $css_dir; ?>token-input.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $css_dir; ?>token-input-facebook.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.6.2/sweetalert2.min.css"
        integrity="sha512-5aabpGaXyIfdaHgByM7ZCtgSoqg51OAt8XWR2FHr/wZpTCea7ByokXbMX2WSvosioKvCfAGDQLlGDzuU6Nm37Q=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />    

<link rel="stylesheet" href="<?php echo $css_dir; ?>style.css">

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js" integrity="sha512-AA1Bzp5Q0K1KanKKmvN/4d3IRKVlv9PYgwFPvm32nPO6QS8yH1HO7LbgB1pgiOxPtfeg5zEn2ba64MUcqJx6CA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.6.12/vue.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/vue-the-mask/0.11.1/vue-the-mask.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
    integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>
<!-----END CSS------------------------------------------->
 <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
          <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->

</head>
<style>
.swal2-popup {
font-size: 1.6rem !important;
}
</style>
    <body class="sidebar-mini skin-blue-light">
        <header class="main-header">
            <!-- Logo -->
            <a href="<?php echo base_url(); ?>" class="logo">
              <!-- mini logo for sidebar mini 50x50 pixels -->
              <span class="logo-mini"><b>i</b>AC</span>
              <!-- logo for regular state and mobile devices -->
              <span class="logo-lg"><b>iACADEMY</b>Student Portal</span>
            </a>
            <nav class="navbar navbar-static-top">
              <!-- Sidebar toggle button-->
              <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                <span class="sr-only">Toggle navigation</span>
              </a>
                <div class="navbar-right">
                    <ul class="nav navbar-nav">
                        <!-- User Account: style can be found in dropdown.less -->
                        <li class="dropdown user user-menu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <i class="glyphicon glyphicon-user"></i>
                                <span><?php echo $student['strFirstname']; ?> <i class="caret"></i></span>
                            </a>
                            <ul class="dropdown-menu">
                                <!-- User image -->
                                <li class="user-header bg-light-red">
                                    <img src="<?php echo ($student['strPicture']=="")?$img_dir."default_image.jpg":$photo_dir.$student['strPicture']; ?>" class="img-circle" alt="User Image">
                                    <p>
                                        <a style="color:#fff;" href="<?php echo base_url(); ?>portal/my_profile"><?php echo $student['strFirstname']." ".$student['strLastname']; ?></a>
                                        
                                    </p>
                                    
                                </li>
                                <!-- Menu Body -->
                                <li class="user-body">
                                    
                                </li>
                                <!-- Menu Footer-->
                                <li class="user-footer">
                                  
                                  
                                    <div class="pull-right">
                                        <a href="<?php echo base_url(); ?>users/logout_student" class="btn btn-default btn-flat">Sign out</a>
                                    </div>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>
        </header>
        
        <!-- Left side column. contains the logo and sidebar -->
          <aside class="main-sidebar">
            <!-- sidebar: style can be found in sidebar.less -->
            <section class="sidebar">                 
                    <!-- Sidebar user panel -->
                    <div class="user-panel">
                        <img style="max-height:120px;margin:0 auto;" class="img-responsive" src="<?php echo base_url(); ?>assets/img/iacademy-logo.png" />
                        <hr />
                        <div class="pull-left image">
                            <img src="<?php echo ($student['strPicture']=="")?$img_dir."default_image.jpg":$photo_dir.$student['strPicture']; ?>" class="img-circle" alt="User Image">
                        </div>
                        <div class="pull-left info">
                            <p>Hello, <?php echo $student['strFirstname'] . "!"; ?></p>
                        </div>
                </div>
                    
                   
                    <!-- /.search form -->
                    <!-- sidebar menu: : style can be found in sidebar.less -->
                    <ul class="sidebar-menu">
                        <li class="<?php echo (isset($page) && $page=="dashboard")?'active':''; ?>"><a href="<?php echo base_url() ?>portal"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a></li>
                    </ul>
                    <?php if(!$first_login): ?>
                    <ul class="sidebar-menu">
                        <li class="<?php echo (isset($page) && $page=="profile")?'active':''; ?>"><a href="<?php echo base_url() ?>portal/profile"><i class="fa fa-user"></i> <span>Profile</span></a></li>
                    </ul>
                    <ul class="sidebar-menu">
                        <li class="<?php echo (isset($page) && $page=="mycourses")?'active':''; ?>"><a href="<?php echo base_url() ?>portal/mycourses"><i class="fa fa-book"></i> <span>My Subjects</span></a></li>
                    </ul>
                    <ul class="sidebar-menu">
                        <li class="<?php echo (isset($page) && $page=="grades")?'active':''; ?>"><a href="<?php echo base_url() ?>portal/grades"><i class="fa fa-pencil"></i> <span>Grades</span></a></li>
                    </ul>
                    <ul class="sidebar-menu">
                        <li class="<?php echo (isset($page) && $page=="schedule")?'active':''; ?>"><a href="<?php echo base_url() ?>portal/schedule"><i class="fa fa-calendar"></i> <span>Schedule</span></a></li>
                    </ul>
                    <ul class="sidebar-menu">
                        <li class="<?php echo (isset($page) && $page=="ledger")?'active':''; ?>"><a href="<?php echo base_url() ?>portal/ledger"><i class="fa fa-book"></i> <span>View Balance</span></a></li>
                    </ul>
                    <!-- <ul class="sidebar-menu">
                        <li class="<?php echo (isset($page) && $page=="accounting_summary")?'active':''; ?>"><a href="<?php echo base_url() ?>portal/accounting_summary"><i class="ion ion-calculator"></i> <span>Accounting</span></a></li>
                    </ul> -->
                    <ul class="sidebar-menu">
                        <li class="<?php echo (isset($page) && $page=="deficiencies")?'active':''; ?>">
                            <a href="<?php echo base_url() ?>portal/deficiencies">
                                <i class="fa fa-user"></i> <span>My Deficiencies</span>
                                <?php if($deficiencies_count > 0): ?>
                                <span class="badge badge-danger"><?php echo $deficiencies_count; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                    </ul>
                    <ul class="sidebar-menu">
                        <li class="<?php echo (isset($page) && $page=="change_password")?'active':''; ?>"><a href="<?php echo base_url() ?>portal/change_password"><i class="ion ion-locked"></i> <span>Change Password</span></a></li>
                    </ul>
                    
                    <?php endif; ?>
                    
                   
                </section>
                <!-- /.sidebar -->
            </aside>

        