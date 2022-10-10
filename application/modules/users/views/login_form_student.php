<div class="login-box">
  <div class="login-logo">
    <a href="#">
        <div>
            <img style="max-height:250px;margin:0 auto;" class="img-responsive" src="<?php echo base_url(); ?>assets/img/cctLogo_new.png" />
        </div>
        <b>CCT</b> Student Portal
    </a>
  </div>
  <!-- /.login-logo -->
  <div class="login-box-body">
        <p class="login-box-msg">Sign in to start your session</p>
        <form id="student-login-form" action="#" onsubmit="return false;" method="post">
                <div class="form-group has-feedback">
                    <input type="text" id="strUsername" name="strUsername" class="form-control" placeholder="Student Number" required autofocus>
                    <!-- <span class="glyphicon glyphicon-envelope form-control-feedback"></span> -->
                </div>
                <div class="form-group has-feedback">
                    <input type="password" id="strPass" name="strPass" class="form-control" placeholder="Password">
                    <!-- <span class="glyphicon glyphicon-lock form-control-feedback"></span> -->
                </div>        
        
            <div class="footer">                                                               
                <button id="signin-student" class="btn btn-danger btn-block btn-flat signin">Sign me in</button>  
            </div>
        </form>
    </div>
</div>

