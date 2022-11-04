<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/themes/site/css/style.css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/themes/site/css/owl.carousel.min.css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/themes/site/css/owl.theme.default.css" />
    <link rel="icon" type="image/png" href="<?php echo base_url() ?>assets/themes/site/images/fav.png">
    <title>iACADEMY Cebu</title>
</head>

<body>

    <div class="body-container" id="bodycontainer"></div>



    <div id="mySidenav" class="sidenav">
        <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
        <ul>
            <li>
                <a class="hover-line white inline-block" href="<?php echo base_url(); ?>site">Home</a>
            </li>

            <li>
                <a class="hover-line inline-block" target="_blank"
                    href="http://localhost:3310/iacademynew/homev4/about">About
                    iACADEMY</a>
            </li>
        </ul>
    </div>

    <!-- Use any element to open the sidenav -->

    <div class="horizontal-nav h-[80px] flex items-center fixed w-full top-0 px-14 py-2 justify-between z-40">
        <a onclick="openNav()" class="md:w-[170px] cursor-pointer">
            <img src="<?php echo $img_dir; ?>menu.svg" />
        </a>
        <a href="<?php echo base_url(); ?>site/student_application">
            <img src="<?php echo $img_dir; ?>btn-apply.png" class="w-[170px] img-btn" alt="" />
        </a>
        <img src="<?php echo $img_dir; ?> logo.png" class="h-[50px] hidden md:block" alt="" />
    </div>