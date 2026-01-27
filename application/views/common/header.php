<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no"
        name="viewport">
    <title>iACADEMY-SMS</title>
    <!-----CSS----------------------------------------------->
    <link rel="stylesheet"
        href="<?php echo base_url(); ?>assets/lib/adminlte/css/jQueryUI/jquery-ui-1.10.3.custom.min.css">
    <link rel="stylesheet"
        href="<?php echo base_url(); ?>assets/lib/adminlte/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/datatables.min.css">
    <link rel="stylesheet"
        href="<?php echo base_url(); ?>assets/lib/adminlte/css/font-awesome.min.css">
    <link rel="stylesheet"
        href="<?php echo base_url(); ?>assets/lib/adminlte/css/select2/select2.min.css">
    <link rel="stylesheet"
        href="<?php echo base_url(); ?>assets/lib/adminlte/css/datepicker/datepicker3.css">
    <link rel="stylesheet"
        href="<?php echo base_url(); ?>assets/lib/adminlte/css/daterangepicker/daterangepicker-bs3.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/lib/adminlte/css/ionicons.min.css">
    <link href="<?php echo base_url(); ?>assets/lib/adminlte/css/iCheck/all.css" rel="stylesheet"
        type="text/css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/lib/adminlte/css/AdminLTE.min.css">
    <link rel="icon" href="https://iacademy.edu.ph/assets/img/fav_new.png">
    <!-- <script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script> -->
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.12.1/sweetalert2.min.js"
        integrity="sha512-TV1UlDAJWH0asrDpaia2S8380GMp6kQ4S6756j3Vv2IwglqZc3w2oR6TxN/fOYfAzNpj2WQJUiuel9a7lbH8rA=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.6.2/sweetalert2.min.css"
        integrity="sha512-5aabpGaXyIfdaHgByM7ZCtgSoqg51OAt8XWR2FHr/wZpTCea7ByokXbMX2WSvosioKvCfAGDQLlGDzuU6Nm37Q=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="<?php echo base_url(); ?>assets/themes/default/js/vue.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/themes/default/js/vue-the-mask.min.js">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
        integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="<?php echo base_url(); ?>assets/themes/default/js/axios.min.js"></script>
    <style>
    .swal2-popup {
        font-size: 1.6rem !important;
    }
    </style> <?php 
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
        $skin = 'skin-black';
        break;
        case 6:
        $skin = 'skin-yellow-light';
        break;
        default: 
        $skin = 'skin-blue';
    } 
?>
    <link rel="stylesheet"
        href="<?php echo base_url(); ?>assets/lib/adminlte/css/skins/<?php echo $skin; ?>.css">
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
    <style>
    .custom-top-header {
        width: 100%;
        background: #2559a8;
        min-height: 56px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 32px 0 20px;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1040;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .custom-header-left {
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .custom-header-logo {
        height: 40px;
        background: #fff;
        border-radius: 4px;
        padding: 4px;
    }
    .custom-header-text {
        color: #fff;
        font-size: 1.15rem;
        font-weight: 400;
        letter-spacing: 0.05em;
    }
    .custom-header-portal-text {
        color: #fff;
        font-size: 1.15rem;
        font-weight: 700;
        margin-left: 8px;
    }
    .custom-header-right {
        display: flex;
        align-items: center;
        gap: 20px;
    }
    .custom-header-right .nav {
        display: flex;
        align-items: center;
        gap: 12px;
        list-style: none;
        margin: 0;
        padding: 0;
    }
    .custom-header-right .nav > li {
        position: relative;
    }
    .custom-header-right .nav > li > a {
        color: #fff;
        text-decoration: none;
        padding: 8px 12px;
        display: block;
        font-size: 1rem;
        border-radius: 4px;
        transition: background 0.2s;
    }
    .custom-header-right .nav > li > a:hover {
        background: rgba(255,255,255,0.1);
        text-decoration: none;
    }
    .custom-header-right .nav .dropdown-menu {
        position: absolute;
        top: 100%;
        right: 0;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        min-width: 200px;
        display: none;
        z-index: 1050;
        margin-top: 4px;
    }
    .custom-header-right .nav .dropdown.open .dropdown-menu {
        display: block;
    }
    .custom-header-right .nav .dropdown-menu .user-header {
        padding: 15px;
        text-align: center;
        background: #2559a8;
        color: #fff;
        border-radius: 4px 4px 0 0;
    }
    .custom-header-right .nav .dropdown-menu .user-header img {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        margin-bottom: 8px;
    }
    .custom-header-right .nav .dropdown-menu .user-header p {
        margin: 0;
    }
    .custom-header-right .nav .dropdown-menu .user-header a {
        color: #fff;
        text-decoration: none;
    }
    .custom-header-right .nav .dropdown-menu .user-footer {
        padding: 10px;
        display: flex;
        justify-content: space-between;
        border-top: 1px solid #eee;
    }
    .custom-header-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        background: #fff;
        border-radius: 50%;
        padding: 2px;
    }
    .custom-header-icon img {
        height: 32px;
        width: 32px;
    }
    body.sidebar-mini {
        padding-top: 56px;
    }
    .main-header {
        display: none !important;
    }
    
    /* Sidebar Customization */
    .main-sidebar {
        background: #fff !important;
        border-right: 1px solid #ddd;
    }
    .sidebar {
        background: #fff !important;
    }
    .sidebar-menu {
        background: #fff !important;
    }
    .sidebar-menu > li {
        border-bottom: 1px solid #e0e0e0;
    }
    .sidebar-menu > li > a {
        color: #333 !important;
        border-left: 3px solid transparent;
        transition: all 0.3s;
    }
    .sidebar-menu > li > a:hover {
        background: #f5f5f5 !important;
        border-left-color: #2559a8;
    }
    .sidebar-menu > li.active > a {
        background: #e8f0f8 !important;
        color: #2559a8 !important;
        border-left-color: #2559a8;
    }
    .sidebar-menu > li.header {
        background: #f8f8f8 !important;
        color: #666 !important;
        border-bottom: 1px solid #e0e0e0;
        font-weight: 600;
    }
    
    /* Treeview Popup/Flyout Style */
    .sidebar-menu .treeview-menu {
        display: none !important;
        position: absolute !important;
        left: 100% !important;
        top: 0 !important;
        background: #fff !important;
        border: 1px solid #ddd !important;
        border-radius: 4px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15) !important;
        min-width: 200px;
        z-index: 1050 !important;
        padding: 8px 0 !important;
        margin: 0 !important;
    }
    .sidebar-menu .treeview {
        position: relative !important;
    }
    .sidebar-menu .treeview.menu-open > .treeview-menu {
        display: block !important;
    }
    .sidebar-menu .treeview-menu > li {
        border-bottom: none !important;
        padding: 0 !important;
    }
    .sidebar-menu .treeview-menu > li > a {
        color: #555 !important;
        padding: 10px 20px !important;
        display: block !important;
        transition: background 0.2s;
        white-space: nowrap;
    }
    .sidebar-menu .treeview-menu > li > a:hover {
        background: #f0f0f0 !important;
        color: #2559a8 !important;
    }
    .sidebar-menu .treeview-menu > li.active > a {
        background: #e8f0f8 !important;
        color: #2559a8 !important;
    }
    
    /* Change angle-left to chevron-right for dropdowns */
    .sidebar-menu .treeview > a .fa-angle-left {
        display: none !important;
    }
    .sidebar-menu .treeview > a::after {
        content: "\f054";
        font-family: FontAwesome;
        float: right;
        color: #999;
        margin-top: 2px;
    }
    
    .user-panel {
        border-bottom: 1px solid #e0e0e0;
    }
    .user-panel .info {
        color: #333 !important;
    }
    .user-panel .info small {
        color: #666 !important;
    }
    </style>
    <script>
    // Toggle dropdown on click
    document.addEventListener('DOMContentLoaded', function() {
        var treeviews = document.querySelectorAll('.sidebar-menu .treeview > a');
        treeviews.forEach(function(item) {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                var parent = this.parentElement;
                var isOpen = parent.classList.contains('menu-open');
                
                // Close all other open menus
                document.querySelectorAll('.sidebar-menu .treeview.menu-open').forEach(function(openMenu) {
                    if (openMenu !== parent) {
                        openMenu.classList.remove('menu-open');
                    }
                });
                
                // Toggle current menu
                if (isOpen) {
                    parent.classList.remove('menu-open');
                } else {
                    parent.classList.add('menu-open');
                }
            });
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.treeview')) {
                document.querySelectorAll('.sidebar-menu .treeview.menu-open').forEach(function(openMenu) {
                    openMenu.classList.remove('menu-open');
                });
            }
        });
    });
    </script>
</head>

<body class="sidebar-mini <?php echo $skin; ?>">
    <!-- Custom Top Header -->
    <div class="custom-top-header">
        <div class="custom-header-left">
            <img src="https://iacademy.edu.ph/assets/img/fav_new.png" alt="iACADEMY Logo" class="custom-header-logo">
            <span class="custom-header-text">i A C A D E M Y</span>
            <span class="custom-header-portal-text">SCHOOL MANAGEMENT SYSTEM</span>
        </div>
        <div class="custom-header-right">
            <ul class="nav">
                <li class="dropdown user user-menu">
                    <a href="#">
                        <span><?php echo $campus; ?></span>
                    </a>
                </li>
                <li>
                    <a href="https://employeeportal.iacademy.edu.ph">
                        <span>Employee Portal</span>
                    </a>
                </li>
                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="glyphicon glyphicon-user"></i>
                        <span><?php echo $user['strUsername']; ?> <i class="caret"></i></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="user-header">
                            <img src="<?php echo ($user['strPicture']=="")?$img_dir."default_image.jpg":base_url().IMAGE_UPLOAD_DIR.$user['strPicture']; ?>"
                                class="img-circle" alt="User Image">
                            <p>
                                <a style="color:#fff;"
                                    href="<?php echo base_url(); ?>faculty/my_profile"><?php echo $user['strFirstname']." ".$user['strLastname']; ?></a>
                                <small><?php if($user['intUserLevel'] == 1): ?>Site Admin<?php endif; ?></small>
                            </p>
                        </li>
                        <li class="user-body"></li>
                        <li class="user-footer">
                            <div class="pull-left">
                                <a href="<?php echo base_url(); ?>faculty/edit_profile" class="btn btn-default btn-flat">Edit Profile</a>
                            </div>
                            <div class="pull-right">
                                <a href="<?php echo base_url(); ?>users/logout" class="btn btn-default btn-flat">Sign out</a>
                            </div>
                        </li>
                    </ul>
                </li>
            </ul>            
        </div>
    </div>
    <!-- End Custom Top Header -->
    
    <header class="main-header">
        <!-- Logo -->
        <a href="<?php echo base_url().'unity/faculty_dashboard'; ?>" class="logo">
            <!-- mini logo for sidebar mini 50x50 pixels -->
            <span class="logo-mini">iAC</span>
            <!-- logo for regular state and mobile devices -->
            <span class="logo-lg"><b>iACADEMY</b>SMS</span>
        </a>
        <!-- Header Navbar: style can be found in header.less -->
        <nav class="navbar navbar-static-top">
            <!-- Sidebar toggle button-->
            <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                <span class="sr-only">Toggle navigation</span>
            </a>
            <div class="navbar-right">
                <ul class="nav navbar-nav">
                    <!-- <li class="dropdown messages-menu hidden-xs">
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

                                <ul class="menu" id="message-list">

                                </ul>
                            </li>
                            <li class="footer"><a href="<?php echo base_url(); ?>messages/view_messages">See All
                                    Messages</a></li>
                        </ul>
                    </li> -->
                    <!-- User Account: style can be found in dropdown.less -->
                    <li class="dropdown user user-menu">
                        <a href="#">
                            <span><?php echo $campus; ?></span>
                        </a>
                    </li>
                    <li><a href="https://employeeportal.iacademy.edu.ph">
                            <span>Employee Portal</span></a>
                    </li>
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
                                    <small><?php if($user['intUserLevel'] == 1): ?>Site
                                        Admin<?php endif; ?></small>
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
                    <!-- <li>
                        <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
                    </li> -->
                </ul>
            </div>
        </nav>
    </header>
    <!-- Left side column. contains the logo and sidebar -->
    <aside class="main-sidebar">
        <!-- sidebar: style can be found in sidebar.less -->
        <section class="sidebar">                                    
            <ul class="sidebar-menu">                
                <li class="<?php echo (isset($page) && $page=="dashboard")?'active':''; ?>"><a
                        href="<?php echo base_url() ?>unity/faculty_dashboard"><i
                            class="fa fa-home text-green"></i>
                        <span>Dashboard</span></a>
                </li>
                <li class="<?php echo (isset($page) && $page=="my_profile")?'active':''; ?>"><a
                        href="<?php echo base_url()."faculty/my_profile" ?>"><i
                            class="fa fa-user text-blue"></i>
                        <span>My Profile</span></a></li>
                <?php if(in_array($user['intUserLevel'],array(2,3)) ): ?> <li
                    class="<?php echo (isset($page) && $page=="add_classlist")?'active':''; ?>"><a
                        href="<?php echo base_url() ?>unity/faculty_classlists"><i
                            class="fa fa-plus-square"></i>
                        <span>Add New Subject Offer</span> </a></li> <?php endif; ?>
                <?php if(in_array($user['intUserLevel'],array(0,1,2)) ): ?> <li
                    class="<?php echo (isset($page) && $page=="view_classlist")?'active':''; ?>"><a
                        href="<?php echo base_url() ?>unity/view_classlist"><i
                            class="fa fa-bars"></i>
                        <span>View My Classes</span></a></li> <?php endif; ?>
                <?php if(in_array($user['intID'],array(1747,1374)) ): ?> <li
                    class="<?php echo (isset($page) && $page=="view_leads")?'active':''; ?>">
                    <a href="<?php echo base_url(); ?>admissionsV1/view_all_leads"><i
                            class="fa fa-book"> </i> View Applicants</a>
                </li> <?php endif; ?>
                <?php if(in_array($user['intUserLevel'],array(2,5,3,6,7)) ): ?>
                <!-- <li class="<?php echo (isset($page) && $page=="transactions")?'active':''; ?>"><a href="<?php echo base_url() ?>unity/transactions"><i class="ion ion-cash"></i> <span>Transactions</span> </a></li> -->                
                <?php if(in_array($user['intUserLevel'],array(2,5)) ): ?> <li
                    class="<?php echo (isset($page) && $page=="admissions_sy_setup")?'active':''; ?>">
                    <a href="<?php echo base_url() ?>admissionsV1/edit_ay/"><i
                            class="fa fa-calendar"></i>
                        <span>Edit Application Dates</span> </a>
                </li>
                <li class="<?php echo (isset($page) && $page=="schools")?'active':''; ?>"><a
                        href="<?php echo base_url() ?>admissionsV1/schools/"><i
                            class="fa fa-list"></i>
                        <span>Schools</span> </a>
                </li>
                <li
                    class="<?php echo (isset($page) && $page=="enrollment_summary")?'active':''; ?>">
                    <a href="<?php echo base_url() ?>admissionsV1/enrollment_summary/"><i
                            class="fa fa-list"></i>
                        <span>Enrollment Summary</span> </a>
                </li> <?php endif; ?> <li
                    class="treeview <?php echo (isset($opentree) && $opentree=="leads")?'active':''; ?>">
                    <a href="#">
                        <i class="ion ion-email"></i> <span>Student Applicants</span>
                        <i class="fa pull-right fa-angle-left"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li
                            class="<?php echo (isset($page) && $page=="view_leads")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>admissionsV1/view_all_leads"
                                style="margin-left: 10px;"><i class="fa fa-book"> </i> View
                                Applicants</a>
                        </li>
                            <?php if(in_array($user['intUserLevel'],array(2,5,3,6)) ): ?> 
                        <li
                            class="<?php echo (isset($page) && $page=="classlist_archive")?'active':''; ?>">
                            <a href="<?php echo base_url()."admissionsV1/view_classlist_archive_admin" ?>"
                                style="margin-left: 10px;"><i class="fa fa-user"></i>Slot
                                Monitoring</a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="awareness_stats")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>admissionsV1/awareness_stats"
                                style="margin-left: 10px;"><i class="fa fa-book"> </i> Awareness
                                Report</a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="view_reserved")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>admissionsV1/view_reserved_leads"
                                style="margin-left: 10px;"><i class="fa fa-book"> </i> View Reserved
                                List</a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="fi_calendar")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>admissionsV1/fi_calendar"
                                style="margin-left: 10px;"><i class="fa fa-book"> </i> View FI
                                Calendar</a>
                        </li>
                        <li class="<?php echo (isset($page) && $page=="view_paid")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>admissionsV1/paid_applicants"
                                style="margin-left: 10px;"><i class="fa fa-book"> </i> View Paid
                                Applicants</a>
                        </li> <?php endif; ?>
                    </ul>
                </li> <?php endif; ?> <?php if(in_array($user['intUserLevel'],array(2,5)) ): ?> <li
                    class="treeview <?php echo (isset($opentree) && $opentree=="examination")?'active':''; ?>">
                    <a href="#">
                        <i class="fa fa-book"></i> <span>Student Examination</span>
                        <i class="fa pull-right fa-angle-left"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li
                            class="<?php echo (isset($page) && $page=="view_exams")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>examination/"
                                style="margin-left: 10px;"><i class="fa fa-book"> </i> View
                                Examination</a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="exam_type_list")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>examination/exam_type_list"
                                style="margin-left: 10px;"><i class="fa fa-book"> </i> View Exam
                                Types</a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="add_exam_type")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>examination/add_exam_type"
                                style="margin-left: 10px;"><i class="fa fa-book"> </i> Add Exam
                                Type</a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="student_generate_exam")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>examination/student_generate_exam"
                                style="margin-left: 10px;"><i class="fa fa-book"> </i> Generate Exam
                                Link</a>
                        </li>
                    </ul>
                </li> <?php endif; ?>
                <?php if($user['teaching'] == 1): ?> <li
                    class="treeview <?php echo (isset($opentree) && $opentree=="faculty")?'active':''; ?>">
                    <a href="#">
                        <i class="fa-user fa text-teal"></i> <span>Faculty Menu</span>
                        <i class="fa pull-right fa-angle-left"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li
                            class="<?php echo (isset($page) && $page=="view_classlist")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>unity/view_classlist"
                                style="margin-left: 10px;"><i
                                    class="ion ion-android-person-add"></i> My Classlists</a>
                        </li>
                    </ul>
                </li> <?php endif; ?>
                <?php if($user['special_role'] >= 2  || $user['intUserLevel'] == 2): ?> <li
                    class="treeview <?php echo (isset($opentree) && $opentree=="deficiencies")?'active':''; ?>">
                    <a href="#">
                        <i class="fa fa-circle"></i> <span>Deficiencies</span>
                        <i class="fa pull-right fa-angle-left"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li
                            class="<?php echo (isset($page) && $page=="deficiencies")?'active':''; ?>">
                            <a href="<?php echo base_url()."deficiencies/student_search" ?>"><i
                                    class="fa fa-user"></i> Student Search</a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="deficiency_report")?'active':''; ?>">
                            <a href="<?php echo base_url()."deficiencies/deficiency_report" ?>"><i
                                    class="fa fa-book"></i> Deficiency List</a>
                        </li>
                    </ul>
                </li> <?php endif; ?>
                <?php if(($user['special_role'] >= 1 && $user['intUserLevel'] == 0)  || $user['intUserLevel'] == 2): ?>
                <li
                    class="treeview <?php echo (isset($opentree) && $opentree=="academics_students")?'active':''; ?>">
                    <a href="#">
                        <i class="fa fa-circle"></i> <span>Academics</span>
                        <i class="fa pull-right fa-angle-left"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li class="<?php echo (isset($page) && $page=="students")?'active':''; ?>">
                            <a href="<?php echo base_url()."academics/view_all_students" ?>"><i
                                    class="fa fa-user"></i> View Students</a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="classlist_archive")?'active':''; ?>">
                            <a
                                href="<?php echo base_url()."academics/view_classlist_archive_admin" ?>"><i
                                    class="fa fa-user"></i> Slot Monitoring</a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="deans_listers")?'active':''; ?>">
                            <a href="<?php echo base_url()."academics/deans_listers" ?>"><i
                                    class="fa fa-user"></i> Dean's List</a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="deans_listers")?'active':''; ?>">
                            <a href="<?php echo base_url()."academics/discipline_report" ?>"><i
                                    class="fa fa-user"></i> Discipline Report</a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="enlistments")?'active':''; ?>">
                            <a href="<?php echo base_url()."academics/enlistments" ?>"><i
                                    class="fa fa-user"></i> For Advising</a>
                        </li>
                        <li class="<?php echo (isset($page) && $page=="advisers")?'active':''; ?>">
                            <a href="<?php echo base_url()."academics/faculty_advisers" ?>"><i
                                    class="fa fa-user"></i> View Advisers</a>
                        </li>
                    </ul>
                </li> <?php endif; ?> <?php if(in_array($user['intUserLevel'],array(2,3,7)) ): ?>
                <li
                    class="treeview <?php echo (isset($opentree) && $opentree=="students")?'active':''; ?>">
                    <a href="#">
                        <i class="fa-user fa text-teal"></i> <span>Students</span>
                        <i class="fa pull-right fa-angle-left"></i>
                    </a>
                    <ul class="treeview-menu">
                        <?php if(in_array($user['intUserLevel'],array(2,3,4,5)) ): ?> <li
                            class="<?php echo (isset($page) && $page=="add_student")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>student/add_student"
                                style="margin-left: 10px;"><i
                                    class="ion ion-android-person-add"></i> Add a Student Record</a>
                        </li>
                        <li class="<?php echo (isset($page) && $page=="loa_logs")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>unity/logs/null/null/Leave%20of%20Abscences"
                                style="margin-left: 10px;"><i class="fa fa-file"></i> LOA Logs</a>
                        </li> <?php endif; ?> <li
                            class="<?php echo (isset($page) && $page=="view_students")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>student/view_all_students"
                                style="margin-left: 10px;"><i class="ion ion-eye"></i> View
                                Students</a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="enhanced_list")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>unity/enhanced_list"
                                style="margin-left: 10px;"><i class="fa fa-user"></i> View Enhanced
                                List</a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="regular_list")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>unity/regular_list"
                                style="margin-left: 10px;"><i class="fa fa-user"></i> View Regular
                                List</a>
                        </li>
                        <!--                            <li class="<?php echo (isset($page) && $page=="view_students2")?'active':''; ?>"><a href="<?php echo base_url(); ?>student/view_all_students2" style="margin-left: 10px;"><i class="ion ion-eye"></i> View Students' Pass</a></li> -->
                        <!--li class="<?php echo (isset($page) && $page=="view_registered_students")?'active':''; ?>"><a href="<?php echo base_url(); ?>student/view_all_registered_students" style="margin-left: 10px;"><i class="ion ion-eye"></i>Registered Students</a></li-->
                    </ul>
                </li> <?php endif; ?>
                <?php if($user['intUserLevel'] == 2 || $user['intUserLevel'] == 4 || $user['intUserLevel'] == 3): ?>
                <li
                    class="treeview <?php echo (isset($opentree) && $opentree=="department")?'active':''; ?>">
                    <a href="#">
                        <i class="fa fa-circle text-aqua"></i> <span>Department</span>
                        <i class="fa pull-right fa-angle-left"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li
                            class="<?php echo (isset($page) && $page=="advise_student")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>department/subject_loading"
                                style="margin-left: 10px;"><i class="ion ion-compose"></i> Subject
                                Enlistment</a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="add_credits")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>department/add_credits"
                                style="margin-left: 10px;"><i class="fa fa-plus"></i> Credit
                                Subjects</a>
                        </li>
                        <li class="<?php echo (isset($page) && $page=="rog")?'active':''; ?>"><a
                                href="<?php echo base_url(); ?>department/student_function/rog"
                                style="margin-left: 10px;"><i class="fa fa-book"></i> Report of
                                Grades</a></li>
                        <li
                            class="<?php echo (isset($page) && $page=="assessment")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>department/student_function/assessment"
                                style="margin-left: 10px;"><i class="fa fa-book"></i> Curriculum
                                Assessment</a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="faculty_loading")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>department/faculty_loading"
                                style="margin-left: 10px;"><i class="fa fa-plus"></i> Faculty
                                Loading</a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="classlist_archive")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>unity/view_classlist_archive_dept"
                                style="margin-left: 10px;"><i class="ion ion-android-list"></i>
                                Subject Offering</a>
                        </li>
                        <!-- <li class="<?php echo (isset($page) && $page=="show_advised_students")?'active':''; ?>"><a href="<?php echo base_url(); ?>department/show_advised_students" style="margin-left: 10px;"><i class="fa fa-users"></i> Advised Students</a></li> -->
                    </ul>
                </li> <?php endif; ?> <?php if($user['intUserLevel'] == 2): ?> <li
                    class="treeview <?php echo (isset($opentree) && $opentree=="admin")?'active':''; ?>">
                    <a href="#">
                        <i class="fa fa-circle text-muted"></i> <span>Admin</span>
                        <i class="fa pull-right fa-angle-left"></i>
                    </a>
                    <ul class="treeview-menu">
                        <!--li class="<?php echo (isset($page) && $page=="sync")?'active':''; ?>"><a href="<?php echo base_url(); ?>unity/execute_sync" style="margin-left: 10px;"><i class="ion ion-android-sync"></i> Sync Students DB</a></li-->
                        <li
                            class="<?php echo (isset($page) && $page=="add_faculty")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>faculty/add_faculty"
                                style="margin-left: 10px;"><i
                                    class="ion ion-android-person-add"></i> Add User Account</a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="view_all_faculty")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>faculty/view_all_faculty"
                                style="margin-left: 10px;"><i class="ion ion-eye"></i> View User
                                Accounts</a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="view_groups")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>group/view_all_groups"
                                style="margin-left: 10px;"><i class="ion ion-eye"></i> All User
                                Groups</a>
                        </li>
                        <li class="<?php echo (isset($page) && $page=="group")?'active':''; ?>"><a
                                href="<?php echo base_url(); ?>group/add_group"
                                style="margin-left: 10px;"><i
                                    class="ion ion-android-person-add"></i> Add User Group</a></li>
                        <li class="<?php echo (isset($page) && $page=="logs")?'active':''; ?>"><a
                                href="<?php echo base_url(); ?>unity/logs"
                                style="margin-left: 10px;"><i class="ion ion-ios-list-outline"></i>
                                View Logs</a>
                        </li>
                    </ul>
                </li> <?php endif; ?> <?php if(in_array($user['intUserLevel'],array(2,3,4)) ): ?>
                <li
                    class="treeview <?php echo (isset($opentree) && $opentree=="subject")?'active':''; ?>">
                    <a href="#">
                        <i class="fa-book fa"></i> <span>Subjects</span>
                        <i class="fa pull-right fa-angle-left"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li
                            class="<?php echo (isset($page) && $page=="add_subject")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>subject/add_subject"
                                style="margin-left: 10px;"><i class="ion ion-ios-plus-empty"></i>
                                Add a subject</a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="view_subjects")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>subject/view_all_subjects"
                                style="margin-left: 10px;"><i class="fa fa-book"></i> View
                                Subjects</a>
                        </li>
                    </ul>
                </li> <?php endif; ?>
                <?php if($user['intUserLevel'] == 2 || $user['intUserLevel'] == 3): ?> <li
                    class="treeview <?php echo (isset($opentree) && $opentree=="curriculum")?'active':''; ?>">
                    <a href="#">
                        <i class="ion ion-university"></i> <span>Curriculum</span>
                        <i class="fa pull-right fa-angle-left"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li
                            class="<?php echo (isset($page) && $page=="add_curriculum")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>unity/add_curriculum"
                                style="margin-left: 10px;"><i class="ion ion-ios-plus-empty"></i>
                                Add a Curriculum</a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="view_curriculum")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>unity/view_all_curriculum"
                                style="margin-left: 10px;"><i class="fa fa-book"></i> View
                                Curriculum</a>
                        </li>
                    </ul>
                </li>
                <li
                    class="treeview <?php echo (isset($opentree) && $opentree=="programs")?'active':''; ?>">
                    <a href="#">
                        <i class="fa-book fa"></i> <span>Programs</span>
                        <i class="fa pull-right fa-angle-left"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li
                            class="<?php echo (isset($page) && $page=="add_program")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>program/add_program"
                                style="margin-left: 10px;"><i class="ion ion-ios-plus-empty"></i>
                                Add a Program</a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="view_programs")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>program/view_all_programs"
                                style="margin-left: 10px;"><i class="fa fa-book"></i> View
                                Programs</a>
                        </li>
                    </ul>
                </li>
                <li
                    class="treeview <?php echo (isset($opentree) && $opentree=="schedule")?'active':''; ?>">
                    <a href="#">
                        <i class="fa-calendar fa"></i> <span>Schedule</span>
                        <i class="fa pull-right fa-angle-left"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li
                            class="<?php echo (isset($page) && $page=="add_schedule")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>schedule/add_schedule"
                                style="margin-left: 10px;"><i class="ion ion-ios-plus-empty"></i>
                                Add Schedule</a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="view_schedules")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>schedule/view_schedules"
                                style="margin-left: 10px;"><i class="ion ion-eye"></i> View
                                Schedules</a>
                        </li>
                    </ul>
                </li> <?php endif; ?>
                <?php if($user['intUserLevel'] == 2 || $user['intUserLevel'] == 6 ): ?> <li
                    class="treeview <?php echo (isset($opentree) && $opentree=="cashier")?'active':''; ?>">
                    <a href="#">
                        <i class="fa fa-circle text-green"></i> <span>Cashiers</span>
                        <i class="fa pull-right fa-angle-left"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li
                            class="<?php echo (isset($page) && $page=="view_all_students")?'active':''; ?>">
                            <a style="margin-left: 10px;"
                                href="<?php echo base_url() ?>finance/view_all_students"><i
                                    class="ion ion-cash"></i>
                                <span>Collection</span> </a>
                        </li>
                        <li class="<?php echo (isset($page) && $page=="no_or")?'active':''; ?>"><a
                                style="margin-left: 10px;"
                                href="<?php echo base_url() ?>finance/payments_no_or"><i
                                    class="ion ion-cash"></i>
                                <span>Online Payment</span> </a></li>
                        <li
                            class="<?php echo (isset($page) && $page=="other_payments")?'active':''; ?>">
                            <a style="margin-left: 10px;"
                                href="<?php echo base_url() ?>finance/other_payments"><i
                                    class="ion ion-cash"></i>
                                <span>NS Collection</span> </a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="view_payees_cashier")?'active':''; ?>">
                            <a style="margin-left: 10px;"
                                href="<?php echo base_url() ?>finance/view_payees_cashier"><i
                                    class="fa fa-users"></i>
                                <span>NS Payee List</span> </a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="transactions")?'active':''; ?>">
                            <a style="margin-left: 10px;"
                                href="<?php echo base_url() ?>finance/payments"><i
                                    class="ion ion-cash"></i>
                                <span>Collection Report</span></a>
                        </li>
                        <li class="<?php echo (isset($page))?'active':''; ?>"><a
                                href="<?php echo base_url(); ?>finance/invoice_report"
                                style="margin-left: 10px;"><i class="ion ion-android-list"></i>
                                Invoice Report</a>
                        </li>
                        <li class="<?php echo (isset($page))?'active':''; ?>"><a
                                href="<?php echo base_url(); ?>finance/or_report"
                                style="margin-left: 10px;"><i class="ion ion-android-list"></i> OR
                                Report</a>
                        </li>
                        <!-- <li class="<?php echo (isset($page) && $page=="other_payments_report")?'active':''; ?>"><a
                                href="<?php echo base_url() ?>finance/payments/0/1"><i class="ion ion-cash"></i>
                                <span>Non Student Payment Report</span> </a></li> -->
                    </ul>
                </li> <?php if($user['cashier_admin'] || $user['special_role'] >= 2 ): ?> <li
                    class="treeview <?php echo (isset($opentree) && $opentree=="cashier_admin")?'active':''; ?>">
                    <a href="#">
                        <i class="fa fa-circle text-green"></i> <span>Cashier Admin</span>
                        <i class="fa pull-right fa-angle-left"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li
                            class="<?php echo (isset($page) && $page=="payee_setup")?'active':''; ?>">
                            <a href="<?php echo base_url() ?>finance/view_payees"
                                style="margin-left: 10px;"><i class="fa fa-users"></i>
                                <span>Payee Set-up</span> </a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="logs_cashier")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>unity/logs/null/null/Cashier"
                                style="margin-left: 10px;"><i class="ion ion-android-list"></i>
                                Cashier Logs</a>
                        </li>
                        <li class="<?php echo (isset($page) && $page=="cashier")?'active':''; ?>"><a
                                href="<?php echo base_url() ?>finance/cashier"
                                style="margin-left: 10px;"><i class="ion ion-android-list"></i>
                                <span>OR Assignment</span> </a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="cashier_invoice")?'active':''; ?>">
                            <a href="<?php echo base_url() ?>finance/cashier_invoice"
                                style="margin-left: 10px;"><i class="ion ion-android-list"></i>
                                <span>Invoice Assignment</span> </a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="scholarship_view_students")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>finance/scholarship_view"
                                style="margin-left: 10px;"> <i class="ion ion-android-list"></i>
                                Students with Scholarships</a>
                        </li>
                    </ul>
                </li> <?php endif; ?>
                <?php if(($user['special_role'] >= 1 && $user['intUserLevel'] == 6) || $user['intUserLevel'] == 2): ?>
                <li
                    class="treeview <?php echo (isset($opentree) && $opentree=="finance_student_account")?'active':''; ?>">
                    <a href="#">
                        <i class="fa fa-circle text-green"></i> <span>Student Account </span>
                        <i class="fa pull-right fa-angle-left"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li class="<?php echo (isset($page) && $page=="reports")?'active':''; ?>"><a
                                href="<?php echo base_url(); ?>finance/finance_reports"
                                style="margin-left: 10px;"><i class="ion ion-android-list"></i>
                                Student Accounts Reports</a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="modular_subjects")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>finance/modular_subjects"
                                style="margin-left: 10px;"><i class="ion ion-android-list"></i>
                                Modular Subjects</a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="view_all_students")?'active':''; ?>">
                            <a style="margin-left: 10px;"
                                href="<?php echo base_url() ?>finance/view_all_students_ledger"><i
                                    class="fa fa-file"></i>
                                <span>Student Ledger</span> </a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="import_previous_balance")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>finance/import_previous_balance"
                                style="margin-left: 10px;"><i class="ion ion-ios-plus-empty"></i>
                                Add Previous Balance</a>
                        </li>
                        <li class="<?php echo (isset($page))?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>finance/tuition_other_fees"
                                style="margin-left: 10px;"><i class="ion ion-ios-plus-empty"></i>
                                Tuition & Other Fees </a>
                        </li>
                        <!-- <li class="<?php echo (isset($page) && $page=="order_detailed_report")?'active':''; ?>">
              <a style="margin-left: 10px;" href="#"><i class="ion"></i>
                <span>Order Detailed Report</span> </a>
            </li> -->
                    </ul>
                </li> <?php endif; ?>
                <?php if(($user['special_role'] >= 2 && $user['intUserLevel'] == 6) || $user['intUserLevel'] == 2): ?>
                <li
                    class="treeview <?php echo (isset($opentree) && $opentree=="finance_admin")?'active':''; ?>">
                    <a href="#">
                        <i class="fa fa-circle text-green"></i> <span>Finance Admin </span>
                        <i class="fa pull-right fa-angle-left"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li
                            class="<?php echo (isset($page) && $page=="override_payment")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>finance/override_payment"
                                style="margin-left: 10px;"><i class="ion ion-ios-plus-empty"></i>
                                Override Payment </a>
                        </li> <?php if($user['special_role'] >= 2): ?> <li
                            class="<?php echo (isset($page) && $page=="update_payment")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>finance/update_payment"
                                style="margin-left: 10px;"><i class="ion ion-android-list"></i>
                                Update Payment Details </a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="logs_cashier")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>unity/logs/null/null/Finance_Admin"
                                style="margin-left: 10px;"><i class="ion ion-android-list"></i>
                                Finance Admin Logs</a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="logs_forwarded")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>unity/logs/null/null/Payment%20Term%20Forwarded"
                                style="margin-left: 10px;"><i class="ion ion-android-list"></i>
                                Forwarded Payments</a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="audit_trail")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>finance/audit_trail"
                                style="margin-left: 10px;"> <i class="ion ion-android-list"></i>
                                Audit Trail</a>
                        </li> <?php endif; ?>
                        <!--                        
                        <li class="<?php echo (isset($page) && $page=="view_all_students")?'active':''; ?>"><a
                                href="#" style="margin-left: 10px;"><i class="ion"></i>
                                <span>Delete & Cancel Receipt</span> </a>
                        </li>
                        <li class="<?php echo (isset($page) && $page=="view_all_students")?'active':''; ?>"><a
                                href="#" style="margin-left: 10px;"><i class="ion"></i>
                                <span>Overwrite Payment</span> </a>
                        </li> -->
                    </ul>
                </li> <?php endif; ?> <?php endif; ?>
                <!----------------------------------CLINIC-------------------------->
                <?php if(($user['intUserLevel'] == 10) || $user['intUserLevel'] == 2): ?> <li
                    class="treeview <?php echo (isset($opentree) && $opentree=="clinic")?'active':''; ?>">
                    <a href="#">
                        <i class="fa fa-circle text-green"></i> <span>Clinic</span>
                        <i class="fa pull-right fa-angle-left"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li
                            class="<?php echo (isset($page) && $page=="view_records")?'active':''; ?>">
                            <a style="margin-left: 10px;"
                                href="<?php echo base_url() ?>clinic/view_all_records"><i
                                    class="fa fa-file"></i>
                                <span>Health Records</span> </a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="health_records")?'active':''; ?>">
                            <a href="<?php echo base_url() ?>clinic/student_search/"
                                style="margin-left: 10px;"><i class="ion ion-android-list"></i>
                                <span>Student Records</span> </a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="health_records_employee")?'active':''; ?>">
                            <a href="<?php echo base_url() ?>clinic/employee_search/"
                                style="margin-left: 10px;"><i class="ion ion-android-list"></i>
                                <span>Employee Records</span> </a>
                        </li>
                    </ul>
                </li> <?php endif; ?>
                <!----------------------------------GUIDANCE-------------------------->
                <?php if($user['intUserLevel'] == 12 || $user['intUserLevel'] == 2): ?> <li
                    class="treeview <?php echo (isset($opentree) && $opentree=="guidance")?'active':''; ?>">
                    <a href="#">
                        <i class="fa fa-circle text-green"></i> <span>Guidance</span>
                        <i class="fa pull-right fa-angle-left"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li
                            class="<?php echo (isset($page) && $page=="view_records")?'active':''; ?>">
                            <a style="margin-left: 10px;"
                                href="<?php echo base_url() ?>guidance/view_all_records"><i
                                    class="fa fa-file"></i>
                                <span>Guidance Records</span> </a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="guidance_records")?'active':''; ?>">
                            <a href="<?php echo base_url() ?>guidance/student_search/"
                                style="margin-left: 10px;"><i class="ion ion-android-list"></i>
                                <span>Student Records</span> </a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="guidance_records_employee")?'active':''; ?>">
                            <a href="<?php echo base_url() ?>guidance/employee_search/"
                                style="margin-left: 10px;"><i class="ion ion-android-list"></i>
                                <span>Employee Records</span> </a>
                        </li>
                    </ul>
                </li> <?php endif; ?>
                <?php if($user['intUserLevel'] == 2 || $user['intUserLevel'] == 3 ): ?> <li
                    class="<?php echo (isset($page) && $page=="classlist_archive")?'active':''; ?>">
                    <a href="<?php echo base_url(); ?>unity/view_classlist_archive_admin"><i
                            class="ion ion-android-list"></i> <span>Subject Offering</span></a>
                </li>
                <li
                    class="treeview <?php echo (isset($opentree) && $opentree=="registrar")?'active':''; ?>">
                    <a href="#">
                        <i class="fa fa-circle text-green"></i> <span>Registrar</span>
                        <i class="fa pull-right fa-angle-left"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li class="<?php echo (isset($page) && $page=="reports")?'active':''; ?>"><a
                                href="<?php echo base_url(); ?>registrar/registrar_reports"
                                style="margin-left: 10px;"><i class="ion ion-android-list"></i>
                                Reports</a></li>
                        <!-- <li class="<?php echo (isset($page) && $page=="add_ay")?'active':''; ?>"><a
                                href="<?php echo base_url(); ?>registrar/completions" style="margin-left: 10px;"><i
                                    class="ion ion-android-list"></i> View Completions</a></li> -->
                        <li
                            class="<?php echo (isset($page) && $page=="grading_sheet_view")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>registrar/search_grading"
                                style="margin-left: 10px;"><i class="fa fa-file"></i> Grading
                                Sheet</a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="register_student")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>registrar/register_student"
                                style="margin-left: 10px;"><i class="ion ion-compose"></i> Student
                                Fee Assessment</a>
                        </li>
                        <li class="<?php echo (isset($page) && $page=="set_ay")?'active':''; ?>"><a
                                href="<?php echo base_url(); ?>registrar/set_ay"
                                style="margin-left: 10px;"><i class="ion ion-university"></i> Set
                                Active Terms</a>
                        </li>
                        <li class="<?php echo (isset($page) && $page=="add_ay")?'active':''; ?>"><a
                                href="<?php echo base_url(); ?>registrar/add_ay"
                                style="margin-left: 10px;"><i class="ion ion-ios-plus-empty"></i>
                                Add New Term</a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="view_academic_year")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>registrar/view_all_ay"
                                style="margin-left: 10px;"><i class="ion ion-university"></i> View
                                All Terms</a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="add_blocksection")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>blocksection/block_section"
                                style="margin-left: 10px;"><i class="ion ion-ios-plus-empty"></i>
                                Add Block Section</a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="view_blocksection")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>blocksection/view_block_sections"
                                style="margin-left: 10px;"><i class="ion ion-eye"></i> View Block
                                Sections</a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="add_student_grades")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>registrar/add_student_grades"
                                style="margin-left: 10px;"><i class="ion ion-ios-plus-empty"></i>
                                Add Student Grade</a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="import_credit_subjects")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>registrar/import_credit_subjects"
                                style="margin-left: 10px;"><i class="ion ion-ios-plus-empty"></i>
                                Import Credit Subject</a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="enrollment_statistics")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>registrar/enrollment_statistics"
                                style="margin-left: 10px;"><i class="ion ion-android-list"></i>
                                Enrollment Statistics</a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="import_subject_offering")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>registrar/import_subject_offering"
                                style="margin-left: 10px;"><i class="ion ion-ios-plus-empty"></i>
                                Import Subject Offering</a>
                        </li>
                    </ul>
                </li>
                <li
                    class="treeview <?php echo (isset($opentree) && $opentree=="classroom")?'active':''; ?>">
                    <a href="#">
                        <i class="fa fa-circle text-green"></i> <span>Classrooms</span>
                        <i class="fa pull-right fa-angle-left"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li
                            class="<?php echo (isset($page) && $page=="add_classroom")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>classroom/add_classroom"
                                style="margin-left: 10px;"><i class="ion ion-ios-plus-empty"></i>
                                Add Classroom</a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="view_classrooms")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>classroom/view_classrooms"
                                style="margin-left: 10px;"><i class="ion ion-eye"></i> View
                                Classrooms</a>
                        </li>
                    </ul>
                </li>
                <li
                    class="treeview <?php echo (isset($opentree) && $opentree=="grading")?'active':''; ?>">
                    <a href="#">
                        <i class="fa fa-circle text-green"></i> <span>Grading Systems</span>
                        <i class="fa pull-right fa-angle-left"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li
                            class="<?php echo (isset($page) && $page=="view_grading_systems")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>grading/view_all_grading"
                                style="margin-left: 10px;"><i class="ion ion-android-list"></i> View
                                Grading Systems</a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="add_grading_system")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>grading/add_grading"
                                style="margin-left: 10px;"><i class="ion ion-ios-plus-empty"></i>
                                Add Grading</a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="term_override")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>grading/term_override"
                                style="margin-left: 10px;"><i class="ion ion-android-list"></i> GS
                                Override</a>
                        </li>
                    </ul>
                </li> <?php endif; ?>
                <?php if($user['intUserLevel'] == 2 || $user['intUserLevel'] == 6 || $user['intUserLevel'] == 7): ?>
                <li
                    class="treeview <?php echo (isset($opentree) && $opentree=="scholarship")?'active':''; ?>">
                    <a href="#">
                        <i class="fa fa-circle text-green"></i> <span>Scholarship/Discount</span>
                        <i class="fa pull-right fa-angle-left"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li
                            class="<?php echo (isset($page) && $page=="assign_scholarship")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>scholarship/select_student"
                                style="margin-left: 10px;"><i class="fa fa-user"></i> Assign
                                Scholarship/Discount</a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="add_scholarship")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>scholarship/view/0"
                                style="margin-left: 10px;"><i class="ion ion-ios-plus-empty"></i>
                                Add Scholarship/Discount</a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="scholarships")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>scholarship/scholarships"
                                style="margin-left: 10px;"><i class="ion ion-android-list"></i>
                                Scholarships/Discount</a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="scholarship_view_students")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>scholarship/scholarship_view"
                                style="margin-left: 10px;"><i class="ion ion-android-list"></i>
                                Students with Scholarships</a>
                        </li>
                        <li
                            class="<?php echo (isset($page) && $page=="mutual_exclusions")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>scholarship/mutual_exclusions"
                                style="margin-left: 10px;"><i class="ion ion-android-list"></i>
                                Manage Mutual Exclusions</a>
                        </li>
                    </ul>
                </li> <?php endif; ?>
            </ul>
        </section>
        <!-- /.sidebar -->
    </aside>