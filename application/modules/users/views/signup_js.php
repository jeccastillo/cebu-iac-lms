<?php //jQuery Validation ?>
<script type="text/javascript">
	$(document).ready(function(){
	     //Get the screen height and width
        var maskHeight = $(document).height();
        var maskWidth = $(window).width();
     
        //Set height and width to mask to fill up the whole screen
        $('#loading').css({'width':maskWidth,'height':maskHeight,'position':'absolute','top':'0','left':'0','z-index':'1000'});

        $("#signup").click(function(e){
					$("#signup-form").validate();
					e.preventDefault;
					if($("#signup-form").valid()){ //register if form is valid
					    $("#signup").hide();
					    register($("#strUsername").val(),$("#strFirstname").val(),$("#strLastname").val(),$("#strEmail").val(), $("#strPass").val(),$("#con-password").val()); 
					
					}
		});
        
	});
		
		
	function register(_username,_firstname,_lastname,_email, _password,_conpassword)
	{
		if(_password!=_conpassword)
		{
			$(".modal-error").html("passwords do not match");
            $("#loading").hide();
			$("#signup").show();
			
		}
        else if(_username=='')
        {
            $(".modal-error").html("username cannot be empty");
            $("#signup").show();
        }
        else if(_firstname=='')
        {
            $(".modal-error").html("firstname cannot be empty");
            $("#signup").show();
        }
        else if(_lastname=='')
        {
            $(".modal-error").html("lastname cannot be empty");
            $("#signup").show();
        }
        else if(_email=='')
        {
            $(".modal-error").html("email cannot be empty");
            $("#signup").show();
        }
        else if(_password=='')
        {
            $(".modal-error").html("password cannot be empty");
            $("#signup").show();
        }
		else
		{
			$.post(base_url + "users/register/", {
				strUsername: _username,
				strFirstname: _firstname,
				strLastname: _lastname,
				strEmail: _email,
				strPass: _password
				
			}, function(response){

			        	$("#loading").hide();
        				$("#signup").show();
					
					var res = $.parseJSON(response);
					if(res.success==0 || res.success==2)
					{
						$(".modal-error").html(res.message);
                        $("#signup").show();
					}
					else
					{
						$(".modal-success").html(res.message);
                        $("#signup").show();
					}	

				

			});

		}
		setTimeout("clear_html()","5000");
		return false;
	}		
	
	function clear_html()
	{
		$('.modal-error').html('');
	}
</script>