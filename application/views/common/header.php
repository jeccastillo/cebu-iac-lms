<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>iACADEMY-Cebu</title>
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

    <!-- <script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script> -->
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.6.2/sweetalert2.min.css"
        integrity="sha512-5aabpGaXyIfdaHgByM7ZCtgSoqg51OAt8XWR2FHr/wZpTCea7ByokXbMX2WSvosioKvCfAGDQLlGDzuU6Nm37Q=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.12"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
        integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>

    <style>
    .swal2-popup {
        font-size: 1.6rem !important;
    }
    </style>

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
        $skin = 'skin-black';
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
            <!-- Sidebar user panel -->
            <div class="user-panel">
                <div class="pull-left image">
                    <img src="<?php echo ($user['strPicture']=="")?$img_dir."default_image.jpg":base_url().IMAGE_UPLOAD_DIR.$user['strPicture']; ?>"
                        class="img-circle" alt="User Image">
                </div>
                <div class="pull-left info">
                    <p> <?php echo $user['strFirstname']; ?></p>
                    <i class="fa fa-users text-green"></i> <small>
                        <?php echo switch_user_level($user['intUserLevel']); ?></small>
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

                <?php if($user['intUserLevel'] == 2): ?>
                <li class="header">Admissions</li>
                <li class="treeview <?php echo (isset($opentree) && $opentree=="leads")?'active':''; ?>">
                    <a href="#">
                        <i class="ion ion-email"></i> <span>Student Applicants</span>
                        <i class="fa pull-right fa-angle-left"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li class="<?php echo (isset($page) && $page=="view_leads")?'active':''; ?>"><a
                                href="<?php echo base_url(); ?>admissionsV1/view_all_leads"
                                style="margin-left: 10px;"><i class="fa fa-book"> </i> View Applicants</a></li>
                    </ul>

                </li>
                <?php endif; ?>
                <li class="header">Admin Menu</li>
                <?php if(in_array($user['intUserLevel'],array(0,1,2,3,4,5,6)) ): ?>
                    
                    <li class="treeview <?php echo (isset($opentree) && $opentree=="faculty")?'active':''; ?>">
                        <a href="#">
                            <i class="fa-user fa text-teal"></i> <span>Faculty Menu</span>
                            <i class="fa pull-right fa-angle-left"></i>
                        </a>
                        <ul class="treeview-menu">
                            <li class="<?php echo (isset($page) && $page=="view_classlist")?'active':''; ?>"><a href="<?php echo base_url(); ?>unity/view_classlist" style="margin-left: 10px;"><i class="ion ion-android-person-add"></i> My Classlists</a></li>
                        </ul>
                    </li>
                    <li class="treeview <?php echo (isset($opentree) && $opentree=="students")?'active':''; ?>">
                        <a href="#">
                            <i class="fa-user fa text-teal"></i> <span>Students</span>
                            <i class="fa pull-right fa-angle-left"></i>
                        </a>
                        <ul class="treeview-menu">
                            <?php if(in_array($user['intUserLevel'],array(2,3,4,5)) ): ?>
                            <li class="<?php echo (isset($page) && $page=="add_student")?'active':''; ?>"><a href="<?php echo base_url(); ?>student/add_student" style="margin-left: 10px;"><i class="ion ion-android-person-add"></i> Add a Student Record</a></li>
                            <?php endif; ?>
                            <li class="<?php echo (isset($page) && $page=="view_students")?'active':''; ?>"><a href="<?php echo base_url(); ?>student/view_all_students" style="margin-left: 10px;"><i class="ion ion-eye"></i> View Students</a></li>
<!--                            <li class="<?php echo (isset($page) && $page=="view_students2")?'active':''; ?>"><a href="<?php echo base_url(); ?>student/view_all_students2" style="margin-left: 10px;"><i class="ion ion-eye"></i> View Students' Pass</a></li> -->
                            
                            
                            <!--li class="<?php echo (isset($page) && $page=="view_registered_students")?'active':''; ?>"><a href="<?php echo base_url(); ?>student/view_all_registered_students" style="margin-left: 10px;"><i class="ion ion-eye"></i>Registered Students</a></li-->  

                        </ul>
                    </li>
                <?php endif; ?>
                <?php if($user['intUserLevel'] == 2 || $user['intUserLevel'] == 4): ?>
                        <li class="treeview <?php echo (isset($opentree) && $opentree=="department")?'active':''; ?>">
                            <a href="#">
                                <i class="fa fa-circle text-aqua"></i> <span>Department</span>
                                <i class="fa pull-right fa-angle-left"></i>
                            </a>
                        
                            <ul class="treeview-menu">
                                <li class="<?php echo (isset($page) && $page=="advise_student")?'active':''; ?>"><a href="<?php echo base_url(); ?>department/advise_student" style="margin-left: 10px;"><i class="ion ion-compose"></i> Advise Student</a></li>
                                <li class="<?php echo (isset($page) && $page=="add_credits")?'active':''; ?>"><a href="<?php echo base_url(); ?>department/add_credits" style="margin-left: 10px;"><i class="fa fa-plus"></i> Credit Subjects</a></li>
                                <li class="<?php echo (isset($page) && $page=="rog")?'active':''; ?>"><a href="<?php echo base_url(); ?>department/student_function/rog" style="margin-left: 10px;"><i class="fa fa-book"></i> Report of Grades</a></li>
                                <li class="<?php echo (isset($page) && $page=="assessment")?'active':''; ?>"><a href="<?php echo base_url(); ?>department/student_function/assessment" style="margin-left: 10px;"><i class="fa fa-book"></i> Curriculum Assessment</a></li>
                                <li class="<?php echo (isset($page) && $page=="faculty_loading")?'active':''; ?>"><a href="<?php echo base_url(); ?>department/faculty_loading" style="margin-left: 10px;"><i class="fa fa-plus"></i> Faculty Loading</a></li>
                                <li class="<?php echo (isset($page) && $page=="classlist_archive")?'active':''; ?>"><a href="<?php echo base_url(); ?>unity/view_classlist_archive_dept" style="margin-left: 10px;"><i class="ion ion-android-list"></i> Classlists</a></li>
                                <!-- <li class="<?php echo (isset($page) && $page=="show_advised_students")?'active':''; ?>"><a href="<?php echo base_url(); ?>department/show_advised_students" style="margin-left: 10px;"><i class="fa fa-users"></i> Advised Students</a></li> -->
                                
                            </ul>
                        </li>
                <?php endif; ?>
                <?php if($user['intUserLevel'] == 2): ?>
                <li class="treeview <?php echo (isset($opentree) && $opentree=="admin")?'active':''; ?>">
                    <a href="#">
                        <i class="fa fa-circle text-muted"></i> <span>Admin</span>
                        <i class="fa pull-right fa-angle-left"></i>
                    </a>
                    <ul class="treeview-menu">


                    <!--li class="<?php echo (isset($page) && $page=="sync")?'active':''; ?>"><a href="<?php echo base_url(); ?>unity/execute_sync" style="margin-left: 10px;"><i class="ion ion-android-sync"></i> Sync Students DB</a></li-->
                    <li class="<?php echo (isset($page) && $page=="add_faculty")?'active':''; ?>"><a href="<?php echo base_url(); ?>faculty/add_faculty" style="margin-left: 10px;"><i class="ion ion-android-person-add"></i> Add Faculty</a></li>
                    <li class="<?php echo (isset($page) && $page=="view_all_faculty")?'active':''; ?>"><a href="<?php echo base_url(); ?>faculty/view_all_faculty" style="margin-left: 10px;"><i class="ion ion-eye"></i> View Faculty</a></li>
                    <li class="<?php echo (isset($page) && $page=="add_classroom")?'active':''; ?>"><a href="<?php echo base_url(); ?>classroom/add_classroom" style="margin-left: 10px;"><i class="ion ion-ios-plus-empty"></i> Add Classroom</a></li>
                    <li class="<?php echo (isset($page) && $page=="view_classrooms")?'active':''; ?>"><a href="<?php echo base_url(); ?>classroom/view_classrooms" style="margin-left: 10px;"><i class="ion ion-eye"></i> View Classrooms</a></li>
                    
                    
                    <li class="<?php echo (isset($page) && $page=="logs")?'active':''; ?>"><a href="<?php echo base_url(); ?>unity/logs" style="margin-left: 10px;"><i class="ion ion-ios-list-outline"></i> View Logs</a></li>

                    </ul>
                </li>
                <?php endif; ?>
                <?php if($user['intUserLevel'] == 2 || $user['intUserLevel'] == 4): ?>
                        <li class="treeview <?php echo (isset($opentree) && $opentree=="subject")?'active':''; ?>">
                            <a href="#">
                                <i class="fa-book fa"></i> <span>Subjects</span>
                                <i class="fa pull-right fa-angle-left"></i>
                            </a>
                            <ul class="treeview-menu">
                                <li class="<?php echo (isset($page) && $page=="add_subject")?'active':''; ?>"><a href="<?php echo base_url(); ?>subject/add_subject" style="margin-left: 10px;"><i class="ion ion-ios-plus-empty"></i> Add a subject</a></li>
                                <li class="<?php echo (isset($page) && $page=="view_subjects")?'active':''; ?>"><a href="<?php echo base_url(); ?>subject/view_all_subjects" style="margin-left: 10px;"><i class="fa fa-book"></i> View Subjects</a></li>
                                
                            </ul>
                        </li>
                        <li class="treeview <?php echo (isset($opentree) && $opentree=="curriculum")?'active':''; ?>">
                            <a href="#">
                                <i class="ion ion-university"></i> <span>Curriculum</span>
                                <i class="fa pull-right fa-angle-left"></i>
                            </a>
                            <ul class="treeview-menu">
                                <li class="<?php echo (isset($page) && $page=="add_curriculum")?'active':''; ?>"><a href="<?php echo base_url(); ?>unity/add_curriculum" style="margin-left: 10px;"><i class="ion ion-ios-plus-empty"></i> Add a Curriculum</a></li>
                                <li class="<?php echo (isset($page) && $page=="view_curriculum")?'active':''; ?>"><a href="<?php echo base_url(); ?>unity/view_all_curriculum" style="margin-left: 10px;"><i class="fa fa-book"></i> View Curriculum</a></li>
                                
                            </ul>
                        </li>
                        <li class="treeview <?php echo (isset($opentree) && $opentree=="programs")?'active':''; ?>">
                            <a href="#">
                                <i class="fa-book fa"></i> <span>Programs</span>
                                <i class="fa pull-right fa-angle-left"></i>
                            </a>
                            <ul class="treeview-menu">
                                <li class="<?php echo (isset($page) && $page=="add_program")?'active':''; ?>"><a href="<?php echo base_url(); ?>program/add_program" style="margin-left: 10px;"><i class="ion ion-ios-plus-empty"></i> Add a Program</a></li>
                                <li class="<?php echo (isset($page) && $page=="view_programs")?'active':''; ?>"><a href="<?php echo base_url(); ?>program/view_all_programs" style="margin-left: 10px;"><i class="fa fa-book"></i> View Programs</a></li>
                                
                            </ul>
                        </li>
                        <li class="treeview <?php echo (isset($opentree) && $opentree=="schedule")?'active':''; ?>">
                            <a href="#">
                                <i class="fa-calendar fa"></i> <span>Schedule</span>
                                <i class="fa pull-right fa-angle-left"></i>
                            </a>
                            <ul class="treeview-menu">
                        <li class="<?php echo (isset($page) && $page=="add_schedule")?'active':''; ?>"><a href="<?php echo base_url(); ?>schedule/add_schedule" style="margin-left: 10px;"><i class="ion ion-ios-plus-empty"></i> Add Schedule</a></li>
                                <li class="<?php echo (isset($page) && $page=="view_schedules")?'active':''; ?>"><a href="<?php echo base_url(); ?>schedule/view_schedules" style="margin-left: 10px;"><i class="ion ion-eye"></i> View Schedules</a></li>
                            </ul>
                        </li>
                <?php endif; ?>
                <?php if($user['intUserLevel'] == 2 || $user['intUserLevel'] == 3 ): ?>                   
                        <li class="treeview <?php echo (isset($opentree) && $opentree=="registrar")?'active':''; ?>">
                            <a href="#">
                                <i class="fa fa-circle text-green"></i> <span>Registrar</span>
                                <i class="fa pull-right fa-angle-left"></i>
                            </a>
                            <ul class="treeview-menu">
                                <li class="<?php echo (isset($page) && $page=="classlist_archive")?'active':''; ?>"><a href="<?php echo base_url(); ?>unity/view_classlist_archive_admin" style="margin-left: 10px;"><i class="ion ion-android-list"></i> Classlists</a></li>                                
                                <li class="<?php echo (isset($page) && $page=="add_ay")?'active':''; ?>"><a href="<?php echo base_url(); ?>registrar/completions" style="margin-left: 10px;"><i class="ion ion-android-list"></i> View Completions</a></li>                                        
                                <li class="<?php echo (isset($page) && $page=="register_student")?'active':''; ?>"><a href="<?php echo base_url(); ?>registrar/register_student" style="margin-left: 10px;"><i class="ion ion-compose"></i> Register Student</a></li>
                                
                                <li class="<?php echo (isset($page) && $page=="set_ay")?'active':''; ?>"><a href="<?php echo base_url(); ?>registrar/set_ay" style="margin-left: 10px;"><i class="ion ion-university"></i> Set Academic Year</a></li>
                                <li class="<?php echo (isset($page) && $page=="add_ay")?'active':''; ?>"><a href="<?php echo base_url(); ?>registrar/add_ay" style="margin-left: 10px;"><i class="ion ion-ios-plus-empty"></i> Add Academic Year</a></li>
                                <li class="<?php echo (isset($page) && $page=="view_academic_year")?'active':''; ?>"><a href="<?php echo base_url(); ?>registrar/view_all_ay" style="margin-left: 10px;"><i class="ion ion-university"></i> View Academic Year</a></li>
                            </ul>
                            
                        </li>
                        <li class="treeview <?php echo (isset($opentree) && $opentree=="tuitionyear")?'active':''; ?>">
                            <a href="#">
                                <i class="fa fa-circle text-green"></i> <span>Tuition Year</span>
                                <i class="fa pull-right fa-angle-left"></i>
                            </a>
                            <ul class="treeview-menu">                                        
                                <li class="<?php echo (isset($page) && $page=="tuitionyear")?'active':''; ?>"><a href="<?php echo base_url(); ?>tuitionyear/add_tuition_year/0" style="margin-left: 10px;"><i class="ion ion-ios-plus-empty"></i> Add Tuition Year</a></li>
                                <li class="<?php echo (isset($page) && $page=="tuitionyear_view")?'active':''; ?>"><a href="<?php echo base_url(); ?>tuitionyear/view_tuition_years" style="margin-left: 10px;"><i class="ion ion-android-list"></i> Tuition Years</a></li>
                            </ul>
                        </li>        
                            
                <?php endif; ?>


            </ul>
        </section>
        <!-- /.sidebar -->
    </aside>