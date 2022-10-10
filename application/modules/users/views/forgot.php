<div class="content" style="width:900px;margin:100px auto; background-color:#fff;opacity:0.8; border-radius: 4px 4px 4px 4px;">
	<div id="forgot-modal" class="window">	
		<div class="dialog">
            <div id="ptlogo"> 		
				<div id="logo"> 		
					<a style="color:#fff;" href="<?php echo base_url();?>"><img src="<?php echo base_url();?>assets/themes/pinoytuner/images/PT-logo.png" /></a>
				</div> 
                <h3>Forgot Password</h3>
                <div class="alert" id="forgot-error" style="display:none;"></div>
                <form id="forgot-password-form" action="#" method="POST" onsubmit="return false;">
                    <div class="wrap">
                        <p class="forgot-instructions"><i><small>To reset your password, type the full email address you used to sign up with us.</small></i></p>
                        <fieldset>
                            <p>
                                <label for="email">Email Address</label>
                                <input type="text" id="email-forgot-address" name="email" placeholder="Email Address" />
                            </p>
                            <br />
                                <input type="submit" id="forgot-password" class="btn btn-default  btn-flat" value="Reset Password" />	
                        </fieldset>					
                    </div>				
                </form>
                <div class="clear"></div>
            </div>
            </div>
	</div>
</div>
