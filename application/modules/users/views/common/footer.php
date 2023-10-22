<!--Javascript-->
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script type="text/javascript" src="<?php echo base_url(); ?>assets/lib/adminlte/js/jquery.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/lib/adminlte/js/jquery-ui-1.10.3.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/lib/adminlte/js/bootstrap.min.js"></script>
<script>
    
	var loader = "<img src='<?php echo base_url() ?>assets/img/ajax-loader.gif' >";
	var base_url = "<?php echo base_url() ?>";
    var referer = "<?php echo $referer; ?>";
	
	$(document).ready(function(){
			$("#signin").click(function(e){
				e.preventDefault;
				login($("#strUsername").val(), $("#strPass").val()); 
			});
            $("#signin-student").click(function(e){
				e.preventDefault;
				login_student($("#strUsername").val(), $("#strPass").val()); 
			});
			$(".textinput").keydown(function(e)
			{
				if(e.keyCode=="13")
				{
					login($("#strUsername").val(), $("#strPass").val()); 
				}
			});
			$("#forgot-btn").click(function(){
				$("#login-modal").fadeOut("fast");
			});
		
			$("#forgot-password").click(function(){
				$("#forgot-password-form").validate();
					if($("#forgot-password-form").valid()){
						$("#forgot-password").hide();
						show_loading_gif("#forgot-password-form .button-set");
						forgot_password($("#email-forgot-address").val());
					}else{
						$("#email-forgot-address").val('');
					}
			});
		});
		
		function show_loading_gif(target) {
			$('.ajax-loader-small').remove();
			$(target).append('<img class="ajax-loader-small" src="' + base_url + 'images/ajax_loader_small.gif" />');
			setTimeout('hide_loading_gif()', '5000');
		}
		
		function hide_loading_gif() {
			$('.ajax-loader-small').fadeOut('slow').remove();
		}
		
		function forgot_password(_email)
		{
			var success = 0;
			$.post(base_url + "users/forgot/", {
				email: _email
			}, function(response){
				if(response){
					var modal_message = "An email with the reset link has been sent to your email address.";
                    hide_loading_gif();
					var success = 1;
				}else{
					var modal_message = "That email address does not exist on our databases";
                    hide_loading_gif();
				}
				$("#forgot-error").empty().append(modal_message).fadeIn('fast');
				setTimeout('$("#forgot-error").fadeOut("fast")', 2000);
				if(success)
					document.location = base_url+'login';
				
			});
		}
		
		function login(_email, _password)
		{
           
			$.ajax({
                url:"<?php echo base_url(); ?>users/auth/", 
                dataType:'json',
                data:{
                    strUser: _email,
                    strPass: _password,
                    loginType:$("#login-type").val(),
                    referer: '<?php echo $referer; ?>'
			         },
                type:'post',
                success: function(response){
				if(response.success)
                    document.location="<?php echo base_url()?>unity";
                else
                    Swal.fire({
						title: "error",
						text: response.message,
						icon: "error"
					}).then(function() {
						
					});
                
			}
                  
            });
			
			return false;
		}
    
        function login_student(_email, _password)
		{
           
			$.ajax({
                url:"<?php echo base_url(); ?>users/auth_student/", 
                dataType:'json',
                data:{
                    strUser: _email,
                    strPass: _password
			         },
                type:'post',
                success: function(response){
				if(response.success)
                    document.location="<?php echo base_url()?>portal";
                else
					Swal.fire({
						title: "error",
						text: response.message,
						icon: "error"
					}).then(function() {
						
					});
                
			}
                  
            });
			
			return false;
		}
	
</script>

		
	
	