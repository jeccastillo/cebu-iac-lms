<!DOCTYPE HTML>
<!--
	Telephasic by HTML5 UP
	html5up.net | @n33co
	Free for personal and commercial use under the CCA 3.0 license (html5up.net/license)
-->
<html>
	<head>
		<title><?php echo $title; ?></title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<meta name="description" content="" />
		<meta name="keywords" content="" />
		<!--[if lte IE 8]><script src="css/ie/html5shiv.js"></script><![endif]-->
		<script src="<?php echo $js_dir; ?>jquery.min.js"></script>
		<script src="<?php echo $js_dir; ?>jquery.dropotron.min.js"></script>
		<script src="<?php echo $js_dir; ?>skel.min.js"></script>
		<script src="<?php echo $js_dir; ?>skel-layers.min.js"></script>
        <script type="text/javascript">
            js_dir = <?php echo $js_dir; ?>
        </script>
		<script src="<?php echo $js_dir; ?>init.js"></script>
		
        <link rel="stylesheet" href="<?php echo $css_dir; ?>skel.css" />
        <link rel="stylesheet" href="<?php echo $css_dir; ?>style.css" />
		
		<!--[if lte IE 8]><link rel="stylesheet" href="css/ie/v8.css" /><![endif]-->
	</head>
    
    <body class="<?php echo $body_class; ?>">
<!-- Header -->
			<div id="header-wrapper">
				<div id="header" class="container">
					
					<!-- Logo -->
						<h1 id="logo"><a href="index.html"><img src="<?php echo $img_dir; ?>cctLogo_new.png" /></a></h1>
					
					<!-- Nav -->
						<nav id="nav">
							<ul>
								<li>
									<a href="">Offered Courses</a>
									<ul>
										<li><a href="#">Lorem ipsum dolor</a></li>
										<li><a href="#">Magna phasellus</a></li>
										<li><a href="#">Etiam dolore nisl</a></li>
										<li>
											<a href="">Phasellus consequat</a>
											<ul>
												<li><a href="#">Lorem ipsum dolor</a></li>
												<li><a href="#">Phasellus consequat</a></li>
												<li><a href="#">Magna phasellus</a></li>
												<li><a href="#">Etiam dolore nisl</a></li>
											</ul>
										</li>
										<li><a href="#">Veroeros feugiat</a></li>
									</ul>
								</li>
								<li><a href="left-sidebar.html">Facilities</a></li>
								<li class="break"><a href="right-sidebar.html">Faculty</a></li>
								<li><a href="no-sidebar.html">Administration</a></li>
							</ul>
						</nav>

				</div>
                <?php if($home): ?>
                <!-- Hero -->
					<section id="hero" class="container">
						<header>
							<h2>City College of Tagaytay
							<br />
							Student Portal</h2>
						</header>
						<p>Character Knowledge</p>
						<ul class="actions">
							<li><a href="<?php echo base_url(); ?>portal/student_login" class="button">Login</a></li>
						</ul>
					</section>
                <?php endif; ?>

			
            </div>