<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8" />
  <meta name="viewport"
    content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet"
    href="<?php echo base_url(); ?>assets/themes/site/css/style.css?v=1.0.1" />
  <link rel="stylesheet"
    href="<?php echo base_url(); ?>assets/themes/site/css/owl.carousel.min.css" />
  <link rel="stylesheet"
    href="<?php echo base_url(); ?>assets/themes/site/css/owl.theme.default.css" />
  <link rel="icon"
    type="image/png"
    href="<?php echo base_url() ?>assets/themes/site/images/fav.png">
  <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.6.2/sweetalert2.min.css"
    integrity="sha512-5aabpGaXyIfdaHgByM7ZCtgSoqg51OAt8XWR2FHr/wZpTCea7ByokXbMX2WSvosioKvCfAGDQLlGDzuU6Nm37Q=="
    crossorigin="anonymous"
    referrerpolicy="no-referrer" />

  <script type="text/javascript"
    src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
  <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/vue@2.6.12"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/vue-the-mask/0.11.1/vue-the-mask.min.js">
  </script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
    integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
    crossorigin="anonymous"
    referrerpolicy="no-referrer"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>



  <title>iACADEMY </title>
</head>

<body>

  <div class="body-container"
    id="bodycontainer"></div>

  <!-- Use any element to open the sidenav -->

  <div
    class="horizontal-nav h-[80px] flex items-center fixed w-full top-0 px-14 py-2 justify-between z-40">

    <a href="<?php echo base_url(); ?>"> <img src=" <?php echo $img_dir; ?>iac-cebu.png"
        class="h-[50px] hidden md:block"
        alt="" /></a>

  </div>