<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
<title><?php 
    
        if(isset($student)){

       // echo ($title=="")?"":$title . "-"; 
    $middleInitial = substr($student['strMiddlename'], 0,1);
                        echo $student['strLastname'] . ", " . $student['strFirstname'] . " " . $middleInitial . ".";
            
        }
    
        else{
            
            echo "CCTUnity - Report";
        }
        
    ?>
    
    
    </title>
<!-----CSS----------------------------------------------->
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/lib/foundation-icons/foundation-icons.css">
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/lib/adminlte/css/jQueryUI/jquery-ui-1.10.3.custom.min.css">
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/lib/adminlte/css/bootstrap.min.css">    
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/lib/adminlte/css/datatables/dataTables.bootstrap.css">
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/lib/adminlte/css/font-awesome.min.css">   
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/lib/adminlte/css/datepicker/datepicker3.css">  
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/lib/adminlte/css/daterangepicker/daterangepicker-bs3.css">
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/lib/adminlte/css/ionicons.min.css">  
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/lib/adminlte/css/AdminLTE.css">
    


<link rel="stylesheet" href="<?php echo $css_dir; ?>style.css">
<!-----END CSS------------------------------------------->
 <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
          <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->

</head>
    <body class="skin-blue">
        
        
        
        
    </body>
</html>


        