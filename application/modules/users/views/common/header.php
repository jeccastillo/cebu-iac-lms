<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title><?php echo ($title=="")?"":$title; ?></title>
<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
<!-----CSS----------------------------------------------->
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/lib/foundation-icons/foundation-icons.css">
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/lib/adminlte/css/bootstrap.min.css">    
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/lib/adminlte/css/AdminLTE.min.css">
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/lib/adminlte/css/font-awesome.min.css">   
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.6.2/sweetalert2.min.css"
        integrity="sha512-5aabpGaXyIfdaHgByM7ZCtgSoqg51OAt8XWR2FHr/wZpTCea7ByokXbMX2WSvosioKvCfAGDQLlGDzuU6Nm37Q=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="<?php echo $css_dir; ?>style.css">
<!-----END CSS------------------------------------------->
<!--[if IE]>
	<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
    
<body class="hold-transition login-page">