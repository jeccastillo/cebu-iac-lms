<div class="login-box" style="max-width: 400px; margin: 60px auto;">
    <div class="login-logo" style="margin-bottom: 18px;">
        <a href="#" style="font-size: 2.2rem; color: #222; letter-spacing: 1px;"><b>iACADEMY</b> <?php echo $campus; ?></a>
    </div>
    <div class="login-box-body" style="background: #fff; border-radius: 14px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); padding: 36px 32px 28px 32px;">
        <p class="login-box-msg" style="font-size: 1.1rem; color: #555; margin-bottom: 28px;">Sign in to start your session</p>
        <form id="login-form" action="#" onsubmit="return false;" method="post">
            <div class="form-group has-feedback" style="margin-bottom: 22px;">
                <input type="text" id="strUsername" name="strUsername" class="form-control reg-box" style="height: 48px; font-size: 1.1rem; border-radius: 8px;" placeholder="User Name">
            </div>
            <div class="form-group has-feedback" style="margin-bottom: 22px;">
                <input type="password" id="strPass" name="strPass" class="form-control reg-box" style="height: 48px; font-size: 1.1rem; border-radius: 8px;" placeholder="Password">
            </div>
            <input type="hidden" value="faculty" id="login-type">
            <div class="footer">
                <button id="signin" class="btn btn-primary btn-block signin" style="height: 48px; font-size: 1.1rem; border-radius: 8px; background: linear-gradient(90deg, #1e90ff 0%, #38b6ff 100%); border: none; color: #fff; font-weight: 600; box-shadow: 0 2px 8px rgba(30,144,255,0.08);">Sign me in</button>
            </div>
        </form>
    </div>
</div>