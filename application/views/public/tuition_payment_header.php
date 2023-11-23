<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>iACADEMY SMS</title>
<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
<!-----CSS----------------------------------------------->
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/lib/adminlte/css/bootstrap.min.css">    
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/lib/adminlte/css/AdminLTE.min.css">
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/lib/adminlte/css/font-awesome.min.css">   
<link rel="stylesheet" href="<?php echo $css_dir; ?>style.css">
<link rel="icon" href="https://iacademy.edu.ph/assets/img/fav_new.png">
<!-----END CSS------------------------------------------->
<!--[if IE]>
<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
<script src="https://cdn.tailwindcss.com"></script>
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
    
<body class="hold-transition login-page">