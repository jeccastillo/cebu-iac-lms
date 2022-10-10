<div id="login-form-container">	
	<div id="forgot-modal" class="window">				
			<?php if(isset($message)): ?>
				<div class="wrap">
					<h2><?php echo $message; ?></h2>
				</div>
				<br/>
				<a class="login-button" href="<?php echo base_url(); ?>">Back to Pinoytuner</a>&nbsp;&nbsp;
				<a class="login-button" href="<?php echo base_url(); ?>login">Login</a>
			<?php else: ?>
				<form id="reset-form" action="<?php echo base_url(); ?>users/password_reset/<?php echo $hash ?>" method="POST">
					<div class="status error"></div>
					<div class="wrap">
						<fieldset>
							<p>								
								<input type="password" placeholder="Password" id="password" name="password" class="textinput" minlength="6"/>
							</p>
							<p>								
								<input type="password" placeholder="Confirm Password" id="confirm-password" name="confirm-password" class="textinput" minlength="6"/>
							</p>
						</fieldset>
					</div>
					<br />
					<input type="submit" id="reset-password" class="btn btn-inverse" value="Reset Password" />
				</form>
				
				
				<script type="text/javascript">
					$(document).ready(function(){
						$("#reset-password").click(function(){		

							$("#reset-form").validate();
							
						});
					});
				</script>
			
			<?php endif; ?>
		</div>
	</div>