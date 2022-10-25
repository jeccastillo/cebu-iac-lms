<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>CCTUnity</title>
    <!-----CSS----------------------------------------------->
    <link rel="stylesheet"
        href="<?php echo base_url(); ?>assets/lib/adminlte/css/jQueryUI/jquery-ui-1.10.3.custom.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/lib/adminlte/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/datatables.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/lib/adminlte/css/font-awesome.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/lib/adminlte/css/select2/select2.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/lib/adminlte/css/datepicker/datepicker3.css">
    <link rel="stylesheet"
        href="<?php echo base_url(); ?>assets/lib/adminlte/css/daterangepicker/daterangepicker-bs3.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/lib/adminlte/css/ionicons.min.css">
    <link href="<?php echo base_url(); ?>assets/lib/adminlte/css/iCheck/all.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/lib/adminlte/css/AdminLTE.min.css">
    <link rel="icon" href="https://iacademy.edu.ph/assets/img/fav_new.png">
    <?php 
    switch($user['intUserLevel'] ){
        case 0:
        $skin = 'skin-black-light';
        break;
        case 1:
        $skin = 'skin-red-light';
        break;
        case 2:
        $skin = 'skin-black';
        break;
        case 3:
        $skin = 'skin-green-light';
        break;
        case 4:
        $skin = 'skin-purple-light';
        break;
        case 5:
        $skin = 'skin-blue-light';
        break;
        case 6:
        $skin = 'skin-yellow-light';
        break;
        default: 
        $skin = 'skin-blue';
    } 
?>
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/lib/adminlte/css/skins/<?php echo $skin; ?>.css">
    <link rel="stylesheet"
        href="<?php echo base_url(); ?>assets/lib/adminlte/css/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css" />
    <link href="<?php echo $css_dir; ?>token-input.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo $css_dir; ?>token-input-facebook.css" rel="stylesheet" type="text/css" />


    <link rel="stylesheet" href="<?php echo $css_dir; ?>style.css">
    <!-----END CSS------------------------------------------->
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
          <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->
    <audio id="ping" src="<?php echo base_url(); ?>assets/ping.mp3" preload="auto"></audio>
</head>

<body class="sidebar-mini <?php echo $skin; ?>">
    <header class="main-header">
        <!-- Logo -->
        <a href="<?php echo base_url(); ?>" class="logo">
            <!-- mini logo for sidebar mini 50x50 pixels -->
            <span class="logo-mini">iAC</span>
            <!-- logo for regular state and mobile devices -->
            <span class="logo-lg"><b>iACADEMY</b>Cebu</span>
        </a>
        <!-- Header Navbar: style can be found in header.less -->
        <nav class="navbar navbar-static-top">
            <!-- Sidebar toggle button-->
            <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                <span class="sr-only">Toggle navigation</span>
            </a>
            <div class="navbar-right">
                <ul class="nav navbar-nav">
                    <li class="dropdown messages-menu hidden-xs">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <i class="fa fa-envelope-o"></i>
                            <span
                                class="label <?php echo ($unread_messages==0)?'hide':'' ?> label-success unread-message-alert"><?php echo $unread_messages; ?></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li class="header">You have <span
                                    class="unread-message-text"><?php echo $unread_messages; ?></span> unread messages
                            </li>
                            <li>
                                <!-- inner menu: contains the actual data -->
                                <ul class="menu" id="message-list">

                                </ul>
                            </li>
                            <li class="footer"><a href="<?php echo base_url(); ?>messages/view_messages">See All
                                    Messages</a></li>
                        </ul>
                    </li>
                    <!-- User Account: style can be found in dropdown.less -->
                    <li class="dropdown user user-menu">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <i class="glyphicon glyphicon-user"></i>
                            <span><?php echo $user['strUsername']; ?> <i class="caret"></i></span>
                        </a>
                        <ul class="dropdown-menu">
                            <!-- User image -->
                            <li class="user-header">
                                <img src="<?php echo ($user['strPicture']=="")?$img_dir."default_image.jpg":base_url().IMAGE_UPLOAD_DIR.$user['strPicture']; ?>"
                                    class="img-circle" alt="User Image">
                                <p>
                                    <a style="color:#fff;"
                                        href="<?php echo base_url(); ?>faculty/my_profile"><?php echo $user['strFirstname']." ".$user['strLastname']; ?></a>
                                    <small><?php if($user['intUserLevel'] == 1): ?>Site Admin<?php endif; ?></small>
                                </p>

                            </li>
                            <!-- Menu Body -->
                            <li class="user-body">

                            </li>
                            <!-- Menu Footer-->
                            <li class="user-footer">
                                <div class="pull-left">
                                    <a href="<?php echo base_url(); ?>faculty/edit_profile"
                                        class="btn btn-default btn-flat">Edit Profile</a>
                                </div>

                                <div class="pull-right">
                                    <a href="<?php echo base_url(); ?>users/logout"
                                        class="btn btn-default btn-flat">Sign out</a>
                                </div>
                            </li>
                        </ul>
                    </li>

                    <li>
                        <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
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
                <div class="pull-left image">
                    <img src="<?php echo ($user['strPicture']=="")?$img_dir."default_image.jpg":base_url().IMAGE_UPLOAD_DIR.$user['strPicture']; ?>"
                        class="img-circle" alt="User Image">
                </div>
                <div class="pull-left info">
                    <p>Hello, <?php echo $user['strFirstname']; ?></p>
                    <i class="fa fa-users text-green"></i> <?php echo switch_user_level($user['intUserLevel']); ?>
                </div>
            </div>

            <!-- /.search form -->
            <!-- sidebar menu: : style can be found in sidebar.less -->
            <ul class="sidebar-menu">
                <li class="header">Main Menu</li>

                <li class="<?php echo (isset($page) && $page=="dashboard")?'active':''; ?>"><a
                        href="<?php echo base_url() ?>unity/faculty_dashboard"><i class="fa fa-home text-green"></i>
                        <span>Dashboard</span></a>
                </li>

                <li class="<?php echo (isset($page) && $page=="my_profile")?'active':''; ?>"><a
                        href="<?php echo base_url()."faculty/my_profile" ?>"><i class="fa fa-user text-blue"></i>
                        <span>My Profile</span></a></li>



                <?php if(in_array($user['intUserLevel'],array(1,2,3,4,5,6)) ): ?>
                <li class="header">Admissions</li>
                <?php endif; ?>

                <li class="treeview <?php echo (isset($opentree) && $opentree=="leads")?'active':''; ?>">
                    <a href="#">
                        <i class="ion ion-email"></i> <span>Leads</span>
                        <i class="fa pull-right fa-angle-left"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li class="<?php echo (isset($page) && $page=="view_leads")?'active':''; ?>"><a
                                href="<?php echo base_url(); ?>admissionsv1/view_all_leads"
                                style="margin-left: 10px;"><i class="fa fa-book"> </i> View Leads</a></li>
                    </ul>

                </li>


            </ul>
        </section>
        <!-- /.sidebar -->
    </aside>