<?php
// Ensure user data is available to prevent undefined index warnings
if (!isset($user) || !is_array($user)) {
    $user = [
        'intUserLevel' => 2,
        'strPicture' => '',
        'strUsername' => 'Guest',
        'strFirstname' => 'Guest',
        'strLastname' => 'User',
        'teaching' => 0,
        'special_role' => 0,
        'cashier_admin' => false
    ];
}

// Determine skin based on user level
$skin_map = [
    0 => 'skin-black-light',
    1 => 'skin-red-light',
    2 => 'skin-black',
    3 => 'skin-green-light',
    4 => 'skin-purple-light',
    5 => 'skin-black',
    6 => 'skin-yellow-light'
];
$skin = isset($skin_map[$user['intUserLevel']]) ? $skin_map[$user['intUserLevel']] : 'skin-blue';

// Set default values for template variables
$campus = isset($campus) ? $campus : 'iACADEMY';
$unread_messages = isset($unread_messages) ? $unread_messages : 0;
$page = isset($page) ? $page : '';
$opentree = isset($opentree) ? $opentree : '';
$css_dir = isset($css_dir) ? $css_dir : base_url() . 'assets/css/';
$img_dir = isset($img_dir) ? $img_dir : base_url() . 'assets/img/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>iACADEMY-SMS</title>
    <link rel="icon" href="https://iacademy.edu.ph/assets/img/fav_new.png">
    
    <!-- CSS Libraries -->
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/lib/adminlte/css/jQueryUI/jquery-ui-1.10.3.custom.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/lib/adminlte/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/datatables.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/lib/adminlte/css/font-awesome.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/lib/adminlte/css/select2/select2.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/lib/adminlte/css/datepicker/datepicker3.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/lib/adminlte/css/daterangepicker/daterangepicker-bs3.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/lib/adminlte/css/ionicons.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/lib/adminlte/css/iCheck/all.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/lib/adminlte/css/AdminLTE.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/lib/adminlte/css/skins/<?php echo $skin; ?>.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/lib/adminlte/css/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">
    
    <!-- External CDN CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.6.2/sweetalert2.min.css" integrity="sha512-5aabpGaXyIfdaHgByM7ZCtgSoqg51OAt8XWR2FHr/wZpTCea7ByokXbMX2WSvosioKvCfAGDQLlGDzuU6Nm37Q==" crossorigin="anonymous" referrerpolicy="no-referrer">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo $css_dir; ?>token-input.css">
    <link rel="stylesheet" href="<?php echo $css_dir; ?>token-input-facebook.css">
    <link rel="stylesheet" href="<?php echo $css_dir; ?>style.css">
    
    <!-- JavaScript Libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.12.1/sweetalert2.min.js" integrity="sha512-TV1UlDAJWH0asrDpaia2S8380GMp6kQ4S6756j3Vv2IwglqZc3w2oR6TxN/fOYfAzNpj2WQJUiuel9a7lbH8rA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="<?php echo base_url(); ?>assets/themes/default/js/vue.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/themes/default/js/vue-the-mask.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js" integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="<?php echo base_url(); ?>assets/themes/default/js/axios.min.js"></script>
    
    <!-- HTML5 Shim for IE8 support -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
    
    <style>
        .swal2-popup {
            font-size: 1.6rem !important;
        }
    </style>
    
    <audio id="ping" src="<?php echo base_url(); ?>assets/ping.mp3" preload="auto"></audio>
</head>

<body class="sidebar-mini <?php echo $skin; ?>">
    <header class="main-header">
        <a href="<?php echo base_url().'unity/faculty_dashboard'; ?>" class="logo">
            <span class="logo-mini">iAC</span>
            <span class="logo-lg"><b>iACADEMY</b>SMS</span>
        </a>
        
        <nav class="navbar navbar-static-top">
            <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                <span class="sr-only">Toggle navigation</span>
            </a>
            
            <div class="navbar-right">
                <ul class="nav navbar-nav">
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
                                <img src="<?php echo ($user['strPicture']=="")?$img_dir."default_image.jpg":base_url().IMAGE_UPLOAD_DIR.$user['strPicture']; ?>" class="img-circle" alt="User Image">
                                <p>
                                    <a style="color:#fff;" href="<?php echo base_url(); ?>faculty/my_profile">
                                        <?php echo $user['strFirstname']." ".$user['strLastname']; ?>
                                    </a>
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
        </nav>
    </header>
    
    <aside class="main-sidebar">
        <section class="sidebar">
            <div class="user-panel">
                <div class="pull-left image">
                    <img src="<?php echo ($user['strPicture']=="")?$img_dir."default_image.jpg":base_url().IMAGE_UPLOAD_DIR.$user['strPicture']; ?>" class="img-circle" alt="User Image">
                </div>
                <div class="pull-left info">
                    <p><?php echo $user['strFirstname']; ?></p>
                    <i class="fa fa-users text-green"></i> 
                    <small><?php echo switch_user_level($user['intUserLevel']); ?></small>
                </div>
            </div>
            
            <ul class="sidebar-menu">
                <li class="header">Main Menu</li>
                <li class="<?php echo (isset($page) && $page=="dashboard")?'active':''; ?>">
                    <a href="<?php echo base_url() ?>unity/faculty_dashboard">
                        <i class="fa fa-home text-green"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="<?php echo (isset($page) && $page=="my_profile")?'active':''; ?>">
                    <a href="<?php echo base_url()."faculty/my_profile" ?>">
                        <i class="fa fa-user text-blue"></i>
                        <span>My Profile</span>
                    </a>
                </li>
                
                <?php if(in_array($user['intUserLevel'],array(2,3))): ?>
                <li class="<?php echo (isset($page) && $page=="add_classlist")?'active':''; ?>">
                    <a href="<?php echo base_url() ?>unity/faculty_classlists">
                        <i class="fa fa-plus-square"></i>
                        <span>Add New Subject Offer</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if(in_array($user['intUserLevel'],array(0,1,2))): ?>
                <li class="<?php echo (isset($page) && $page=="view_classlist")?'active':''; ?>">
                    <a href="<?php echo base_url() ?>unity/view_classlist">
                        <i class="fa fa-bars"></i>
                        <span>View My Classes</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if(in_array($user['intUserLevel'],array(2,5,3,6,7))): ?>
                <li class="header">Admissions</li>
                
                <?php if(in_array($user['intUserLevel'],array(2,5))): ?>
                <li class="<?php echo (isset($page) && $page=="admissions_sy_setup")?'active':''; ?>">
                    <a href="<?php echo base_url() ?>admissionsV1/edit_ay/">
                        <i class="fa fa-calendar"></i>
                        <span>Edit Application Dates</span>
                    </a>
                </li>
                <li class="<?php echo (isset($page) && $page=="schools")?'active':''; ?>">
                    <a href="<?php echo base_url() ?>admissionsV1/schools/">
                        <i class="fa fa-list"></i>
                        <span>Schools</span>
                    </a>
                </li>
                <li class="<?php echo (isset($page) && $page=="enrollment_summary")?'active':''; ?>">
                    <a href="<?php echo base_url() ?>admissionsV1/enrollment_summary/">
                        <i class="fa fa-list"></i>
                        <span>Enrollment Summary</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <li class="treeview <?php echo (isset($opentree) && $opentree=="leads")?'active':''; ?>">
                    <a href="#">
                        <i class="ion ion-email"></i> 
                        <span>Student Applicants</span>
                        <i class="fa pull-right fa-angle-left"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li class="<?php echo (isset($page) && $page=="view_leads")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>admissionsV1/view_all_leads" style="margin-left: 10px;">
                                <i class="fa fa-book"></i> View Applicants
                            </a>
                        </li>
                        <?php if(in_array($user['intUserLevel'],array(2,5,3,6))): ?>
                        <li class="<?php echo (isset($page) && $page=="classlist_archive")?'active':''; ?>">
                            <a href="<?php echo base_url()."admissionsV1/view_classlist_archive_admin" ?>" style="margin-left: 10px;">
                                <i class="fa fa-user"></i>Slot Monitoring
                            </a>
                        </li>
                        <li class="<?php echo (isset($page) && $page=="awareness_stats")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>admissionsV1/awareness_stats" style="margin-left: 10px;">
                                <i class="fa fa-book"></i> Awareness Report
                            </a>
                        </li>
                        <li class="<?php echo (isset($page) && $page=="view_reserved")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>admissionsV1/view_reserved_leads" style="margin-left: 10px;">
                                <i class="fa fa-book"></i> View Reserved List
                            </a>
                        </li>
                        <li class="<?php echo (isset($page) && $page=="fi_calendar")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>admissionsV1/fi_calendar" style="margin-left: 10px;">
                                <i class="fa fa-book"></i> View FI Calendar
                            </a>
                        </li>
                        <li class="<?php echo (isset($page) && $page=="view_paid")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>admissionsV1/paid_applicants" style="margin-left: 10px;">
                                <i class="fa fa-book"></i> View Paid Applicants
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>
                
                <li class="header">Menu</li>
                
                <?php if($user['teaching'] == 1): ?>
                <li class="treeview <?php echo (isset($opentree) && $opentree=="faculty")?'active':''; ?>">
                    <a href="#">
                        <i class="fa-user fa text-teal"></i> 
                        <span>Faculty Menu</span>
                        <i class="fa pull-right fa-angle-left"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li class="<?php echo (isset($page) && $page=="view_classlist")?'active':''; ?>">
                            <a href="<?php echo base_url(); ?>unity/view_classlist" style="margin-left: 10px;">
                                <i class="ion ion-android-person-add"></i> My Classlists
                            </a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>
                
            </ul>
        </section>
    </aside>
