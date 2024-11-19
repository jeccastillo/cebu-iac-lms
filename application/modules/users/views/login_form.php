<div class="login-box">
    <div class="login-logo">
        <a href="#"><b>iACADEMY</b><?php echo $campus; ?></a>
    </div>
    <div class="login-box-body">
        <p class="login-box-msg">Sign in to start your session</p>
        <form id="login-form" action="#" onsubmit="return false;" method="post">
            <div class="form-group has-feedback">
                <input type="text" id="strUsername" name="strUsername" class="form-control reg-box"
                    placeholder="User Name">
            </div>
            <div class="form-group has-feedback">
                <input type="password" id="strPass" name="strPass" class="form-control reg-box" placeholder="Password">
            </div>
            <input type="hidden" value="faculty" id="login-type">
            <div class="footer">
                <button id="signin" class="btn btn-default  btn-flat btn-block btn-flat signin">Sign me in</button>


            </div>
        </form>


    </div>
</div>