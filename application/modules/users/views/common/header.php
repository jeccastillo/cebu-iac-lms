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
<script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.12.1/sweetalert2.min.js" integrity="sha512-TV1UlDAJWH0asrDpaia2S8380GMp6kQ4S6756j3Vv2IwglqZc3w2oR6TxN/fOYfAzNpj2WQJUiuel9a7lbH8rA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.6.2/sweetalert2.min.css"
        integrity="sha512-5aabpGaXyIfdaHgByM7ZCtgSoqg51OAt8XWR2FHr/wZpTCea7ByokXbMX2WSvosioKvCfAGDQLlGDzuU6Nm37Q=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="<?php echo $css_dir; ?>style.css">
<!-----END CSS------------------------------------------->
<!--[if IE]>
	<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
    
<body class="hold-transition login-page">