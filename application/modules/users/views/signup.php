<div class="content" style="width:900px;margin:100px auto; background-color:#fff;opacity:0.8; border-radius: 4px 4px 4px 4px;">
	<div id="signup-form-container">		
		<div id="register-modal" class="window">	
		<div class="dialog"> 		
			<div id="ptlogo"> 		
				<div id="logo"> 		
					<a style="color:#fff;" href="<?php echo base_url();?>"><img src="<?php echo base_url();?>assets/themes/pinoytuner/images/PT-logo.png" /></a> 		
				</div> 		
			<div id="register-title" class="header-caption"><h4>REGISTER</h4></div> 		
			</div>
				<hr class="clear" />
				<span class="modal-error" style="padding:10px;color:#900;display:block;"></span>		
				<span class="modal-success" style="padding:10px;color:#333;display:block;"></span>	
				<form id="signup-form" action="#" method="POST" onsubmit="return false;">
						<div class="wrap">
							<fieldset>
								<div class="register-form">
									<p>
										<label for="strFirstname">Firstname</label>
										<input class="reg-box" type="text" placeholder="First Name" id="strFirstname" name="strFirstname" class="required"  />
									</p>
									<p>
										<label for="strLastname">Lastname</label>
										<input class="reg-box" type="text" placeholder="Last Name" id="strLastname" name="strLastname" class="required" />
									</p>
									<p>
										<label for="strUsername">Username</label>
										<input class="reg-box" type="text" placeholder="Username" id="strUsername" name="strUsername" class="required" />
									</p>
									<p>
										<label for="strEmail">Email</label>
										<input class="reg-box" type="text" placeholder="Email Address" id="strEmail" name="strEmail" class="email required" />
									</p>
									<p>
										<label for="strPass">Password</label>
										<input class="reg-box" type="password" placeholder="Password" id="strPass" name="strPass" class="required" />
									</p>
									<p>
										<label for="con-password">Confirm Password</label>
										<input class="reg-box" type="password" placeholder="Confirm Password" id="con-password" name="con-password" class="required" />
									</p>	
									<p class="button-set">
										<input type="submit" id="signup" class="btn register" value="Register" />
									</p>
									</br>							
									<p class="button-set">
										<label>&nbsp;Already a Member?</label> <a id="signin" class="btn signin" href="<?php echo base_url(); ?>users/signup" id="register-btn">Log In</a>	
									</p>
								</div>							
								<div class="clear"></div>	
							</fieldset>							
						</div>
						<div id="message-modal">
						</div>
						
					</form>
			<div class="clear"></div>
			</div>
		</div>
	</div>
</div>