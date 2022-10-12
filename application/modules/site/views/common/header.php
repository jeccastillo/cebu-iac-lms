<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/themes/site/css/style.css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/themes/site/css/owl.carousel.min.css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/themes/site/css/owl.theme.default.css" />
  </head>
  <body>

	<div class="body-container hidden" id="bodycontainer"></div>

	<div id="mySidenav" class="sidenav hidden">
	<a href="javascript:void(0)" class="closebtn" onclick="closeNav()"
		>&times;</a
	>
	<ul>
		<li>
		<a class="hover-line inline-block" href="#">Home</a>
		</li>
		<li>
		<a class="hover-line inline-block" href="#">About</a>
		</li>
		<li>
		<a class="hover-line inline-block" href="#">Academics</a>
		</li>
		<li>
		<a class="hover-line inline-block" href="#">News</a>
		</li>
		<li>
		<a class="hover-line inline-block" href="#">Linkages</a>
		</li>
		<li>
		<a class="hover-line inline-block" href="#">Top Works</a>
		</li>
		<li>
		<a class="hover-line inline-block" href="#">Virtual Tool</a>
		</li>
		<li>
		<a class="hover-line inline-block" href="#">Student</a>
		</li>
		<li>
		<a class="hover-line inline-block" href="#">Alumni</a>
		</li>
	</ul>
	</div>

	<!-- Use any element to open the sidenav -->

	<div
	class="horizontal-nav h-[80px] flex items-center fixed w-full top-0 px-14 py-2 justify-between z-40"
	>
	<span onclick="openNav()" class="md:w-[170px]">
		<img src="<?php echo $img_dir; ?>menu.svg" />
	</span>
	<!-- <a href="#" class="btn uppercase bg-pink">Apply Now</a>
		-->
	<a href="">
		<img
		src="<?php echo $img_dir; ?>btn-apply.png"
		class="w-[170px] img-btn"
		alt=""
		/>
	</a>
	<img
		src="<?php echo $img_dir; ?> logo.png"
		class="h-[50px] hidden md:block"
		alt=""
	/>
	</div>