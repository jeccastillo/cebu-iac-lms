<script>
$(document).ready(function(){     
    
        //FORM VALIDATION FOR SUBJECT
        $("input[name='strName']").blur(function(){
            if($(this).val() == "" )
            {
                $(this).parent().addClass('has-error');
                $("label[for='strName']").html("<i class='fa fa-times-circle-o'></i> Name is required");
            }
            else
            {
                 $(this).parent().removeClass('has-error');
                 $("label[for='strName']").html("Name*");
            }
        
        });
        
        $("#cp-form").submit(function(e){
            
            
            if($("input[name='password']").val() == ""){
                $("input[name='password']").parent().addClass('has-error');
                $("label[for='password']").html("<i class='fa fa-times-circle-o'></i> Password is Required");
                e.preventDefault();
            }
            else if($("input[name='password']").val().length < 6){
                $("input[name='password']").parent().addClass('has-error');
                $("label[for='password']").html("<i class='fa fa-times-circle-o'></i> Password Must be at least 6 characters");
                e.preventDefault();
            }
            else if($("input[name='password']").val() != $("input[name='repeat_password']").val()){
                $("input[name='password']").parent().addClass('has-error');
                $("input[name='repeat_password']").parent().addClass('has-error');
                $("label[for='password']").html("<i class='fa fa-times-circle-o'></i> Passwords do not match");
                e.preventDefault();
            }
            else
            {
                $("input[name='password']").parent().removeClass('has-error');
                $("input[name='repeat_password']").parent().removeClass('has-error');
                $("label[for='password']").html("Password*");
            }
            
            return;
        });
    });


</script>