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
  <link rel="stylesheet"
    href="<?php echo base_url(); ?>assets/css/datatables.min.css">
  <link rel="stylesheet"
    href="<?php echo base_url(); ?>assets/lib/adminlte/css/font-awesome.min.css">
  <link rel="stylesheet"
    href="<?php echo base_url(); ?>assets/lib/adminlte/css/select2/select2.min.css">
  <link rel="stylesheet"
    href="<?php echo base_url(); ?>assets/lib/adminlte/css/datepicker/datepicker3.css">
  <link rel="stylesheet"
    href="<?php echo base_url(); ?>assets/lib/adminlte/css/daterangepicker/daterangepicker-bs3.css">
  <link rel="stylesheet"
    href="<?php echo base_url(); ?>assets/lib/adminlte/css/ionicons.min.css">
  <link href="<?php echo base_url(); ?>assets/lib/adminlte/css/iCheck/all.css"
    rel="stylesheet"
    type="text/css" />
  <link rel="stylesheet"
    href="<?php echo base_url(); ?>assets/lib/adminlte/css/AdminLTE.min.css">
  <link rel="icon"
    href="https://iacademy.edu.ph/assets/img/fav_new.png">

  <!-- <script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script> -->
  <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.6.2/sweetalert2.min.css"
    integrity="sha512-5aabpGaXyIfdaHgByM7ZCtgSoqg51OAt8XWR2FHr/wZpTCea7ByokXbMX2WSvosioKvCfAGDQLlGDzuU6Nm37Q=="
    crossorigin="anonymous"
    referrerpolicy="no-referrer" />
  <script src="https://cdn.jsdelivr.net/npm/vue@2.6.12"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/vue-the-mask/0.11.1/vue-the-mask.min.js">
  </script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
    integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
    crossorigin="anonymous"
    referrerpolicy="no-referrer"></script>
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
  <link rel="stylesheet"
    href="<?php echo base_url(); ?>assets/lib/adminlte/css/skins/<?php echo $skin; ?>.css">
  <link rel="stylesheet"
    href="<?php echo base_url(); ?>assets/lib/adminlte/css/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css" />
  <link href="<?php echo $css_dir; ?>token-input.css"
    rel="stylesheet"
    type="text/css" />
  <link href="<?php echo $css_dir; ?>token-input-facebook.css"
    rel="stylesheet"
    type="text/css" />


  <link rel="stylesheet"
    href="<?php echo $css_dir; ?>style.css">
  <!-----END CSS------------------------------------------->
  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
          <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->
  <audio id="ping"
    src="<?php echo base_url(); ?>assets/ping.mp3"
    preload="auto"></audio>
</head>

<body class="sidebar-mini <?php echo $skin; ?>">
  <header class="main-header">
    <!-- Logo -->
    <a href="<?php echo base_url().'unity/faculty_dashboard'; ?>"
      class="logo">
      <!-- mini logo for sidebar mini 50x50 pixels -->
      <span class="logo-mini">iAC</span>
      <!-- logo for regular state and mobile devices -->
      <span class="logo-lg"><b>iACADEMY</b>SMS</span>
    </a>
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top">
      <!-- Sidebar toggle button-->
      <a href="#"
        class="sidebar-toggle"
        data-toggle="offcanvas"
        role="button">
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
            <a href="#"
              class="dropdown-toggle"
              data-toggle="dropdown">
              <i class="glyphicon glyphicon-user"></i>
              <span><?php echo $user['strUsername']; ?> <i class="caret"></i></span>
            </a>
            <ul class="dropdown-menu">
              <!-- User image -->
              <li class="user-header">
                <img
                  src="<?php echo ($user['strPicture']=="")?$img_dir."default_image.jpg":base_url().IMAGE_UPLOAD_DIR.$user['strPicture']; ?>"
                  class="img-circle"
                  alt="User Image">
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
          <img
            src="<?php echo ($user['strPicture']=="")?$img_dir."default_image.jpg":base_url().IMAGE_UPLOAD_DIR.$user['strPicture']; ?>"
            class="img-circle"
            alt="User Image">
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
            href="<?php echo base_url() ?>unity/faculty_dashboard"><i
              class="fa fa-home text-green"></i>
            <span>Dashboard</span></a>

        </li>
        <li class="<?php echo (isset($page) && $page=="my_profile")?'active':''; ?>"><a
            href="<?php echo base_url()."faculty/my_profile" ?>"><i
              class="fa fa-user text-blue"></i>
            <span>My Profile</span></a></li>
        <?php if(in_array($user['intUserLevel'],array(2,3)) ): ?>
        <li class="<?php echo (isset($page) && $page=="add_classlist")?'active':''; ?>"><a
            href="<?php echo base_url() ?>unity/faculty_classlists"><i
              class="fa fa-plus-square"></i>
            <span>Add New Subject Offer</span> </a></li>
        <?php endif; ?>
        <?php if(in_array($user['intUserLevel'],array(0,1,2)) ): ?>
        <li class="<?php echo (isset($page) && $page=="view_classlist")?'active':''; ?>"><a
            href="<?php echo base_url() ?>unity/view_classlist"><i class="fa fa-bars"></i>
            <span>View My
              Classes</span></a></li>
        <?php endif; ?>
        <?php if(in_array($user['intUserLevel'],array(2,5,3,6)) ): ?>
        <!-- <li class="<?php echo (isset($page) && $page=="transactions")?'active':''; ?>"><a href="<?php echo base_url() ?>unity/transactions"><i class="ion ion-cash"></i> <span>Transactions</span> </a></li> -->
        <li class="header">Admissions</li>
        <?php if(in_array($user['intUserLevel'],array(2,5)) ): ?>
        <li class="<?php echo (isset($page) && $page=="admissions_sy_setup")?'active':''; ?>"><a
            href="<?php echo base_url() ?>admissionsV1/edit_ay/"><i class="fa fa-calendar"></i>
            <span>Edit Application Dates</span> </a>
        </li>
        <?php endif; ?>
        <li class="treeview <?php echo (isset($opentree) && $opentree=="leads")?'active':''; ?>">
          <a href="#">
            <i class="ion ion-email"></i> <span>Student Applicants</span>
            <i class="fa pull-right fa-angle-left"></i>
          </a>
          <ul class="treeview-menu">
            <li class="<?php echo (isset($page) && $page=="view_leads")?'active':''; ?>"><a
                href="<?php echo base_url(); ?>admissionsV1/view_all_leads"
                style="margin-left: 10px;"><i class="fa fa-book"> </i> View Applicants</a></li>
            <li class="<?php echo (isset($page) && $page=="view_reserved")?'active':''; ?>"><a
                href="<?php echo base_url(); ?>admissionsV1/view_reserved_leads"
                style="margin-left: 10px;"><i class="fa fa-book"> </i> View Reserved List</a></li>
            <li class="<?php echo (isset($page) && $page=="fi_calendar")?'active':''; ?>"><a
                href="<?php echo base_url(); ?>admissionsV1/fi_calendar"
                style="margin-left: 10px;"><i class="fa fa-book"> </i> View FI Calendar</a></li>

          </ul>
        </li>
        <?php endif; ?>
        <?php if(in_array($user['intUserLevel'],array(2,5)) ): ?>
        <li
          class="treeview <?php echo (isset($opentree) && $opentree=="examination")?'active':''; ?>">
          <a href="#">
            <i class="fa fa-book"></i> <span>Student Examination</span>
            <i class="fa pull-right fa-angle-left"></i>
          </a>
          <ul class="treeview-menu">
            <li class="<?php echo (isset($page) && $page=="view_exams")?'active':''; ?>"><a
                href="<?php echo base_url(); ?>examination/"
                style="margin-left: 10px;"><i class="fa fa-book"> </i> View Examination</a></li>
            <li class="<?php echo (isset($page) && $page=="exam_type_list")?'active':''; ?>"><a
                href="<?php echo base_url(); ?>examination/exam_type_list"
                style="margin-left: 10px;"><i class="fa fa-book"> </i> View Exam Types</a></li>
            <li class="<?php echo (isset($page) && $page=="add_exam_type")?'active':''; ?>"><a
                href="<?php echo base_url(); ?>examination/add_exam_type"
                style="margin-left: 10px;"><i class="fa fa-book"> </i> Add Exam Type</a></li>
            <li class="<?php echo (isset($page) && $page=="student_generate_exam")?'active':''; ?>">
              <a href="<?php echo base_url(); ?>examination/student_generate_exam"
                style="margin-left: 10px;"><i class="fa fa-book"> </i> Generate Exam Link</a>
            </li>

          </ul>
        </li>
        <?php endif; ?>
        <li class="header">Menu</li>

        <?php if($user['teaching'] == 1): ?>

        <li class="treeview <?php echo (isset($opentree) && $opentree=="faculty")?'active':''; ?>">
          <a href="#">
            <i class="fa-user fa text-teal"></i> <span>Faculty Menu</span>
            <i class="fa pull-right fa-angle-left"></i>
          </a>
          <ul class="treeview-menu">
            <li class="<?php echo (isset($page) && $page=="view_classlist")?'active':''; ?>"><a
                href="<?php echo base_url(); ?>unity/view_classlist"
                style="margin-left: 10px;"><i class="ion ion-android-person-add"></i> My
                Classlists</a></li>
          </ul>
        </li>
        <?php endif; ?>
        <?php if($user['special_role'] >= 2  || $user['intUserLevel'] == 2): ?>
        <li
          class="treeview <?php echo (isset($opentree) && $opentree=="deficiencies")?'active':''; ?>">
          <a href="#">
            <i class="fa fa-circle"></i> <span>Deficiencies</span>
            <i class="fa pull-right fa-angle-left"></i>
          </a>
          <ul class="treeview-menu">
            <li class="<?php echo (isset($page) && $page=="deficiencies")?'active':''; ?>">
              <a href="<?php echo base_url()."deficiencies/student_search" ?>"><i
                  class="fa fa-user"></i>
                Student Search</a>
            </li>
            <li class="<?php echo (isset($page) && $page=="deficiency_report")?'active':''; ?>"><a
                href="<?php echo base_url()."deficiencies/deficiency_report" ?>"><i
                  class="fa fa-book"></i>
                Deficiency List</a>
            </li>
          </ul>
        </li>
        <?php endif; ?>
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
                  class="fa fa-user"></i>
                View Students</a>
            </li>
            <li class="<?php echo (isset($page) && $page=="classlist_archive")?'active':''; ?>">
              <a href="<?php echo base_url()."academics/view_classlist_archive_admin" ?>"><i
                  class="fa fa-user"></i>
                Slot Monitoring</a>
            </li>
            <li class="<?php echo (isset($page) && $page=="deans_listers")?'active':''; ?>">
              <a href="<?php echo base_url()."academics/deans_listers" ?>"><i
                  class="fa fa-user"></i>
                Dean's List</a>
            </li>
            <li class="<?php echo (isset($page) && $page=="deans_listers")?'active':''; ?>">
              <a href="<?php echo base_url()."academics/discipline_report" ?>"><i
                  class="fa fa-user"></i>
                Discipline Report</a>
            </li>
          </ul>
        </li>
        <?php endif; ?>

        <?php if(in_array($user['intUserLevel'],array(2,3,7)) ): ?>
        <li class="treeview <?php echo (isset($opentree) && $opentree=="students")?'active':''; ?>">
          <a href="#">
            <i class="fa-user fa text-teal"></i> <span>Students</span>
            <i class="fa pull-right fa-angle-left"></i>
          </a>
          <ul class="treeview-menu">
            <?php if(in_array($user['intUserLevel'],array(2,3,4,5)) ): ?>
            <li class="<?php echo (isset($page) && $page=="add_student")?'active':''; ?>"><a
                href="<?php echo base_url(); ?>student/add_student"
                style="margin-left: 10px;"><i class="ion ion-android-person-add"></i> Add a Student
                Record</a></li>
            <li class="<?php echo (isset($page) && $page=="loa_logs")?'active':''; ?>"><a
                href="<?php echo base_url(); ?>unity/logs/null/null/Leave%20of%20Abscences"
                style="margin-left: 10px;"><i class="fa fa-file"></i> LOA Logs</a></li>
            <?php endif; ?>
            <li class="<?php echo (isset($page) && $page=="view_students")?'active':''; ?>"><a
                href="<?php echo base_url(); ?>student/view_all_students"
                style="margin-left: 10px;"><i class="ion ion-eye"></i> View Students</a></li>

            <!--                            <li class="<?php echo (isset($page) && $page=="view_students2")?'active':''; ?>"><a href="<?php echo base_url(); ?>student/view_all_students2" style="margin-left: 10px;"><i class="ion ion-eye"></i> View Students' Pass</a></li> -->


            <!--li class="<?php echo (isset($page) && $page=="view_registered_students")?'active':''; ?>"><a href="<?php echo base_url(); ?>student/view_all_registered_students" style="margin-left: 10px;"><i class="ion ion-eye"></i>Registered Students</a></li-->

          </ul>
        </li>
        <?php endif; ?>
        <?php if($user['intUserLevel'] == 2 || $user['intUserLevel'] == 4): ?>
        <li
          class="treeview <?php echo (isset($opentree) && $opentree=="department")?'active':''; ?>">
          <a href="#">
            <i class="fa fa-circle text-aqua"></i> <span>Department</span>
            <i class="fa pull-right fa-angle-left"></i>
          </a>

          <ul class="treeview-menu">
            <li class="<?php echo (isset($page) && $page=="advise_student")?'active':''; ?>"><a
                href="<?php echo base_url(); ?>department/subject_loading"
                style="margin-left: 10px;"><i class="ion ion-compose"></i> Subject Enlistment</a>
            </li>
            <li class="<?php echo (isset($page) && $page=="add_credits")?'active':''; ?>"><a
                href="<?php echo base_url(); ?>department/add_credits"
                style="margin-left: 10px;"><i class="fa fa-plus"></i> Credit Subjects</a></li>
            <li class="<?php echo (isset($page) && $page=="rog")?'active':''; ?>"><a
                href="<?php echo base_url(); ?>department/student_function/rog"
                style="margin-left: 10px;"><i class="fa fa-book"></i> Report of Grades</a></li>
            <li class="<?php echo (isset($page) && $page=="assessment")?'active':''; ?>"><a
                href="<?php echo base_url(); ?>department/student_function/assessment"
                style="margin-left: 10px;"><i class="fa fa-book"></i> Curriculum Assessment</a></li>
            <li class="<?php echo (isset($page) && $page=="faculty_loading")?'active':''; ?>"><a
                href="<?php echo base_url(); ?>department/faculty_loading"
                style="margin-left: 10px;"><i class="fa fa-plus"></i> Faculty Loading</a></li>
            <li class="<?php echo (isset($page) && $page=="classlist_archive")?'active':''; ?>"><a
                href="<?php echo base_url(); ?>unity/view_classlist_archive_dept"
                style="margin-left: 10px;"><i class="ion ion-android-list"></i> Subject Offering</a>
            </li>
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
            <li class="<?php echo (isset($page) && $page=="add_faculty")?'active':''; ?>"><a
                href="<?php echo base_url(); ?>faculty/add_faculty"
                style="margin-left: 10px;"><i class="ion ion-android-person-add"></i> Add User
                Account</a></li>
            <li class="<?php echo (isset($page) && $page=="view_all_faculty")?'active':''; ?>"><a
                href="<?php echo base_url(); ?>faculty/view_all_faculty"
                style="margin-left: 10px;"><i class="ion ion-eye"></i> View User Accounts</a></li>
            <li class="<?php echo (isset($page) && $page=="view_groups")?'active':''; ?>"><a
                href="<?php echo base_url(); ?>group/view_all_groups"
                style="margin-left: 10px;"><i class="ion ion-eye"></i> All User Groups</a></li>
            <li class="<?php echo (isset($page) && $page=="group")?'active':''; ?>"><a
                href="<?php echo base_url(); ?>group/add_group"
                style="margin-left: 10px;"><i class="ion ion-android-person-add"></i> Add User
                Group</a></li>
            <li class="<?php echo (isset($page) && $page=="logs")?'active':''; ?>"><a
                href="<?php echo base_url(); ?>unity/logs"
                style="margin-left: 10px;"><i class="ion ion-ios-list-outline"></i> View Logs</a>
            </li>

          </ul>
        </li>
        <?php endif; ?>
        <?php if(in_array($user['intUserLevel'],array(2,3,4,6)) ): ?>
        <li class="treeview <?php echo (isset($opentree) && $opentree=="subject")?'active':''; ?>">
          <a href="#">
            <i class="fa-book fa"></i> <span>Subjects</span>
            <i class="fa pull-right fa-angle-left"></i>
          </a>
          <ul class="treeview-menu">
            <li class="<?php echo (isset($page) && $page=="add_subject")?'active':''; ?>"><a
                href="<?php echo base_url(); ?>subject/add_subject"
                style="margin-left: 10px;"><i class="ion ion-ios-plus-empty"></i> Add a subject</a>
            </li>
            <li class="<?php echo (isset($page) && $page=="view_subjects")?'active':''; ?>"><a
                href="<?php echo base_url(); ?>subject/view_all_subjects"
                style="margin-left: 10px;"><i class="fa fa-book"></i> View Subjects</a></li>

          </ul>
        </li>
        <?php endif; ?>
        <?php if($user['intUserLevel'] == 2 || $user['intUserLevel'] == 3): ?>
        <li
          class="treeview <?php echo (isset($opentree) && $opentree=="curriculum")?'active':''; ?>">
          <a href="#">
            <i class="ion ion-university"></i> <span>Curriculum</span>
            <i class="fa pull-right fa-angle-left"></i>
          </a>
          <ul class="treeview-menu">
            <li class="<?php echo (isset($page) && $page=="add_curriculum")?'active':''; ?>"><a
                href="<?php echo base_url(); ?>unity/add_curriculum"
                style="margin-left: 10px;"><i class="ion ion-ios-plus-empty"></i> Add a
                Curriculum</a></li>
            <li class="<?php echo (isset($page) && $page=="view_curriculum")?'active':''; ?>"><a
                href="<?php echo base_url(); ?>unity/view_all_curriculum"
                style="margin-left: 10px;"><i class="fa fa-book"></i> View Curriculum</a></li>

          </ul>
        </li>
        <li class="treeview <?php echo (isset($opentree) && $opentree=="programs")?'active':''; ?>">
          <a href="#">
            <i class="fa-book fa"></i> <span>Programs</span>
            <i class="fa pull-right fa-angle-left"></i>
          </a>
          <ul class="treeview-menu">
            <li class="<?php echo (isset($page) && $page=="add_program")?'active':''; ?>"><a
                href="<?php echo base_url(); ?>program/add_program"
                style="margin-left: 10px;"><i class="ion ion-ios-plus-empty"></i> Add a Program</a>
            </li>
            <li class="<?php echo (isset($page) && $page=="view_programs")?'active':''; ?>"><a
                href="<?php echo base_url(); ?>program/view_all_programs"
                style="margin-left: 10px;"><i class="fa fa-book"></i> View Programs</a></li>

          </ul>
        </li>
        <li class="treeview <?php echo (isset($opentree) && $opentree=="schedule")?'active':''; ?>">
          <a href="#">
            <i class="fa-calendar fa"></i> <span>Schedule</span>
            <i class="fa pull-right fa-angle-left"></i>
          </a>
          <ul class="treeview-menu">
            <li class="<?php echo (isset($page) && $page=="add_schedule")?'active':''; ?>"><a
                href="<?php echo base_url(); ?>schedule/add_schedule"
                style="margin-left: 10px;"><i class="ion ion-ios-plus-empty"></i> Add Schedule</a>
            </li>
            <li class="<?php echo (isset($page) && $page=="view_schedules")?'active':''; ?>"><a
                href="<?php echo base_url(); ?>schedule/view_schedules"
                style="margin-left: 10px;"><i class="ion ion-eye"></i> View Schedules</a></li>
          </ul>
        </li>
        <?php endif; ?>
        <?php if($user['intUserLevel'] == 2 || $user['intUserLevel'] == 6 ): ?>
        <li class="treeview <?php echo (isset($opentree) && $opentree=="cashier")?'active':''; ?>">
          <a href="#">
            <i class="fa fa-circle text-green"></i> <span>Cashiers</span>
            <i class="fa pull-right fa-angle-left"></i>
          </a>
          <ul class="treeview-menu">
            <li class="<?php echo (isset($page) && $page=="view_all_students")?'active':''; ?>"><a
                href="<?php echo base_url() ?>finance/view_all_students"><i
                  class="ion ion-cash"></i>
                <span>Payments</span> </a></li>
            <li class="<?php echo (isset($page) && $page=="view_payees_cashier")?'active':''; ?>"><a
                href="<?php echo base_url() ?>finance/view_payees_cashier"><i
                  class="fa fa-users"></i>
                <span>NS Payees</span> </a></li>

            <li class="<?php echo (isset($page) && $page=="transactions")?'active':''; ?>"><a
                href="<?php echo base_url() ?>finance/payments"><i class="ion ion-cash"></i>
                <span>Collection Report</span></a></li>
            <li class="<?php echo (isset($page) && $page=="no_or")?'active':''; ?>"><a
                href="<?php echo base_url() ?>finance/payments_no_or"><i class="ion ion-cash"></i>
                <span>Online Payments</span> </a></li>
            <li class="<?php echo (isset($page) && $page=="other_payments")?'active':''; ?>"><a
                href="<?php echo base_url() ?>finance/other_payments"><i class="ion ion-cash"></i>
                <span>NS Payment</span> </a></li>
            <!-- <li class="<?php echo (isset($page) && $page=="other_payments_report")?'active':''; ?>"><a
                                href="<?php echo base_url() ?>finance/payments/0/1"><i class="ion ion-cash"></i>
                                <span>Non Student Payment Report</span> </a></li> -->
            <li
              class="treeview <?php echo (isset($opentree) && $opentree=="tuitionyear")?'active':''; ?>">
          </ul>
        </li>
        <?php if(($user['special_role'] >= 1 && $user['intUserLevel'] == 6) || $user['intUserLevel'] == 2): ?>
        <li
          class="treeview <?php echo (isset($opentree) && $opentree=="finance_student_account")?'active':''; ?>">
          <a href="#">
            <i class="fa fa-circle text-green"></i> <span>Student Account </span>
            <i class="fa pull-right fa-angle-left"></i>
          </a>
          <ul class="treeview-menu">
            <li class="<?php echo (isset($page) && $page=="view_all_students")?'active':''; ?>"><a
                href="<?php echo base_url() ?>finance/view_all_students_ledger"><i
                  class="fa fa-file"></i>
                <span>Student Ledger</span> </a>
            </li>
            <li class="<?php echo (isset($page) && $page=="student_account")?'active':''; ?>"><a
                href="#"><i class="ion"></i>
                <span>Student Account</span> </a>
            </li>
            <li class="<?php echo (isset($page) && $page=="order_detailed_report")?'active':''; ?>">
              <a href="#"><i class="ion"></i>
                <span>Order Detailed Report</span> </a>
            </li>
          </ul>
        </li>
        <?php endif; ?>
        <?php if(($user['special_role'] >= 1 && $user['intUserLevel'] == 6) || $user['intUserLevel'] == 2): ?>
        <li
          class="treeview <?php echo (isset($opentree) && $opentree=="finance_admin")?'active':''; ?>">
          <a href="#">
            <i class="fa fa-circle text-green"></i> <span>Finance Admin </span>
            <i class="fa pull-right fa-angle-left"></i>
          </a>
          <ul class="treeview-menu">
            <li class="<?php echo (isset($page) && $page=="tuitionyear")?'active':''; ?>"><a
                href="<?php echo base_url(); ?>tuitionyear/add_tuition_year/0"
                style="margin-left: 10px;"><i class="ion ion-ios-plus-empty"></i> Add New Tuition
                Fee
              </a>
            </li>
            <li class="<?php echo (isset($page) && $page=="tuitionyear_view")?'active':''; ?>"><a
                href="<?php echo base_url(); ?>tuitionyear/view_tuition_years"
                style="margin-left: 10px;"><i class="ion ion-android-list"></i> Tuition Fee List</a>
            </li>
            <li class="<?php echo (isset($page) && $page=="override_payment")?'active':''; ?>"><a
                href="<?php echo base_url(); ?>finance/override_payment"
                style="margin-left: 10px;"><i class="ion ion-ios-plus-empty"></i> Override Payment
              </a>
            </li>
            <li class="<?php echo (isset($page) && $page=="installment_dates")?'active':''; ?>"><a
                href="<?php echo base_url() ?>finance/edit_ay/"
                style="margin-left: 10px;"><i class="fa fa-calendar"></i>
                <span>Edit Dates</span> </a>
            </li>
            <li
              class="<?php echo (isset($page) && $page=="student_account_report")?'active':''; ?>">
              <a href="<?php echo base_url() ?>finance/student_account_report/"
                style="margin-left: 10px;"><i class="ion ion-android-list"></i>
                <span>Student Account Report</span> </a>
            </li>
            <?php if($user['special_role'] >= 2): ?>
            <li class="<?php echo (isset($page) && $page=="payee_setup")?'active':''; ?>"><a
                href="<?php echo base_url() ?>finance/view_payees"
                style="margin-left: 10px;"><i class="fa fa-users"></i>
                <span>Payee Set-up</span> </a>
            </li>
            <li class="<?php echo (isset($page) && $page=="cashier")?'active':''; ?>"><a
                href="<?php echo base_url() ?>finance/cashier"
                style="margin-left: 10px;"><i class="ion ion-android-list"></i>
                <span>OR Assignment</span> </a></li>
            <li class="<?php echo (isset($page) && $page=="logs_forwarded")?'active':''; ?>"><a
                href="<?php echo base_url(); ?>unity/logs/null/null/Payment%20Term%20Forwarded"
                style="margin-left: 10px;"><i class="ion ion-android-list"></i> Forwarded
                Payments</a>
            </li>
            <li class="<?php echo (isset($page) && $page=="logs_cashier")?'active':''; ?>"><a
                href="<?php echo base_url(); ?>unity/logs/null/null/Cashier"
                style="margin-left: 10px;"><i class="ion ion-android-list"></i> Cashier Logs</a>
            </li>
            <?php endif; ?>
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
        </li>
        <?php endif; ?>
        <?php endif; ?>
        <?php if($user['intUserLevel'] == 2 || $user['intUserLevel'] == 3 ): ?>
        <li class="<?php echo (isset($page) && $page=="classlist_archive")?'active':''; ?>"><a
            href="<?php echo base_url(); ?>unity/view_classlist_archive_admin"><i
              class="ion ion-android-list"></i> <span>Subject Offering</span></a></li>
        <li
          class="treeview <?php echo (isset($opentree) && $opentree=="registrar")?'active':''; ?>">
          <a href="#">
            <i class="fa fa-circle text-green"></i> <span>Registrar</span>
            <i class="fa pull-right fa-angle-left"></i>
          </a>
          <ul class="treeview-menu">
            <li class="<?php echo (isset($page) && $page=="reports")?'active':''; ?>"><a
                href="<?php echo base_url(); ?>registrar/registrar_reports"
                style="margin-left: 10px;"><i class="ion ion-android-list"></i> Reports</a></li>
            <!-- <li class="<?php echo (isset($page) && $page=="add_ay")?'active':''; ?>"><a
                                href="<?php echo base_url(); ?>registrar/completions" style="margin-left: 10px;"><i
                                    class="ion ion-android-list"></i> View Completions</a></li> -->

            <li class="<?php echo (isset($page) && $page=="grading_sheet_view")?'active':''; ?>"><a
                href="<?php echo base_url(); ?>registrar/search_grading"
                style="margin-left: 10px;"><i class="fa fa-file"></i> Grading Sheet</a></li>
            <li class="<?php echo (isset($page) && $page=="register_student")?'active':''; ?>"><a
                href="<?php echo base_url(); ?>registrar/register_student"
                style="margin-left: 10px;"><i class="ion ion-compose"></i> Student Fee
                Assessment</a></li>
            <li class="<?php echo (isset($page) && $page=="set_ay")?'active':''; ?>"><a
                href="<?php echo base_url(); ?>registrar/set_ay"
                style="margin-left: 10px;"><i class="ion ion-university"></i> Set Active Terms</a>
            </li>
            <li class="<?php echo (isset($page) && $page=="add_ay")?'active':''; ?>"><a
                href="<?php echo base_url(); ?>registrar/add_ay"
                style="margin-left: 10px;"><i class="ion ion-ios-plus-empty"></i> Add New Term</a>
            </li>
            <li class="<?php echo (isset($page) && $page=="view_academic_year")?'active':''; ?>"><a
                href="<?php echo base_url(); ?>registrar/view_all_ay"
                style="margin-left: 10px;"><i class="ion ion-university"></i> View All Terms</a>
            </li>
            <li class="<?php echo (isset($page) && $page=="add_blocksection")?'active':''; ?>"><a
                href="<?php echo base_url(); ?>blocksection/block_section"
                style="margin-left: 10px;"><i class="ion ion-ios-plus-empty"></i> Add Block
                Section</a></li>
            <li class="<?php echo (isset($page) && $page=="view_blocksection")?'active':''; ?>"><a
                href="<?php echo base_url(); ?>blocksection/view_block_sections"
                style="margin-left: 10px;"><i class="ion ion-eye"></i> View Block Sections</a></li>
          </ul>
        </li>
        <li
          class="treeview <?php echo (isset($opentree) && $opentree=="classroom")?'active':''; ?>">
          <a href="#">
            <i class="fa fa-circle text-green"></i> <span>Classrooms</span>
            <i class="fa pull-right fa-angle-left"></i>
          </a>
          <ul class="treeview-menu">
            <li class="<?php echo (isset($page) && $page=="add_classroom")?'active':''; ?>"><a
                href="<?php echo base_url(); ?>classroom/add_classroom"
                style="margin-left: 10px;"><i class="ion ion-ios-plus-empty"></i> Add Classroom</a>
            </li>
            <li class="<?php echo (isset($page) && $page=="view_classrooms")?'active':''; ?>"><a
                href="<?php echo base_url(); ?>classroom/view_classrooms"
                style="margin-left: 10px;"><i class="ion ion-eye"></i> View Classrooms</a></li>
          </ul>
        </li>
        <li class="treeview <?php echo (isset($opentree) && $opentree=="grading")?'active':''; ?>">
          <a href="#">
            <i class="fa fa-circle text-green"></i> <span>Grading Systems</span>
            <i class="fa pull-right fa-angle-left"></i>
          </a>
          <ul class="treeview-menu">
            <li class="<?php echo (isset($page) && $page=="view_grading_systems")?'active':''; ?>">
              <a href="<?php echo base_url(); ?>grading/view_all_grading"
                style="margin-left: 10px;"><i class="ion ion-android-list"></i> View Grading
                Systems</a>
            </li>
            <li class="<?php echo (isset($page) && $page=="add_grading_system")?'active':''; ?>"><a
                href="<?php echo base_url(); ?>grading/add_grading"
                style="margin-left: 10px;"><i class="ion ion-ios-plus-empty"></i> Add Grading</a>
            </li>
            <li class="<?php echo (isset($page) && $page=="term_override")?'active':''; ?>"><a
                href="<?php echo base_url(); ?>grading/term_override"
                style="margin-left: 10px;"><i class="ion ion-android-list"></i> GS Override</a></li>

          </ul>

        </li>


        <?php endif; ?>
        <?php if($user['intUserLevel'] == 2 || $user['intUserLevel'] == 7 ): ?>
        <li
          class="treeview <?php echo (isset($opentree) && $opentree=="scholarship")?'active':''; ?>">
          <a href="#">
            <i class="fa fa-circle text-green"></i> <span>Scholarship/Discount</span>
            <i class="fa pull-right fa-angle-left"></i>
          </a>
          <ul class="treeview-menu">
            <li class="<?php echo (isset($page) && $page=="assign_scholarship")?'active':''; ?>"><a
                href="<?php echo base_url(); ?>scholarship/select_student"
                style="margin-left: 10px;"><i class="fa fa-user"></i> Assign
                Scholarship/Discount</a></li>
            <li class="<?php echo (isset($page) && $page=="add_scholarship")?'active':''; ?>"><a
                href="<?php echo base_url(); ?>scholarship/view/0"
                style="margin-left: 10px;"><i class="ion ion-ios-plus-empty"></i> Add
                Scholarship/Discount</a></li>
            <li class="<?php echo (isset($page) && $page=="scholarships")?'active':''; ?>"><a
                href="<?php echo base_url(); ?>scholarship/scholarships"
                style="margin-left: 10px;"><i class="ion ion-android-list"></i>
                Scholarships/Discount</a></li>
          </ul>
        </li>
        <?php endif; ?>


      </ul>
    </section>
    <!-- /.sidebar -->
  </aside>