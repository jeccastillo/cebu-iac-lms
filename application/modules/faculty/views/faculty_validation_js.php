<script type="text/javascript">
    $(document).ready(function(){
        
        //FORM VALIDATION FOR STUDENT
        $("input[name='strFirstname']").blur(function(){
            if($(this).val() == "" )
            {
                $(this).parent().addClass('has-error');
                $("label[for='strFirstname']").html("<i class='fa fa-times-circle-o'></i> Firstname field is required");
            }
            else
            {
                 $(this).parent().removeClass('has-error');
                 $("label[for='strFirstname']").html("Firstname*");
            }
        
        });
        $("input[name='strLastname']").blur(function(){
            if($(this).val() == "" )
            {
                $(this).parent().addClass('has-error');
                $("label[for='strLastname']").html("<i class='fa fa-times-circle-o'></i> Lastname field is required");
            }
            else
            {
                 $(this).parent().removeClass('has-error');
                 $("label[for='strLastname']").html("Lastname*");
            }
        });
        
        // $("input[name='strPass']").blur(function(){
        //     if($(this).val() == "" )
        //     {
        //         $(this).parent().addClass('has-error');
        //         $("label[for='strPass']").html("<i class='fa fa-times-circle-o'></i> Password field is required");
        //     }
        //     else
        //     {
        //          $(this).parent().removeClass('has-error');
        //          $("label[for='strPass']").html("Password*");
        //     }
        // });
        
        $("input[name='strUsername']").blur(function(){
            if($(this).val() == "" )
            {
                $(this).parent().addClass('has-error');
                $("label[for='strUsername']").html("<i class='fa fa-times-circle-o'></i> Username field is required");
            }
            else
            {
                 $(this).parent().removeClass('has-error');
                 $("label[for='strUsername']").html("Username*");
            }
        });
        $("#validate-faculty").submit(function(e){
            
            
            if($("input[name='strFirstname']").val() == ""){
                $("input[name='strFirstname']").parent().addClass('has-error');
                $("label[for='strFirstname']").html("<i class='fa fa-times-circle-o'></i> Firstname field is required");
                e.preventDefault();
            }
            else
            {
                $("input[name='strFirstname']").parent().removeClass('has-error');
                $("label[for='strFirstname']").html("Firstname*");
            }
            if($("input[name='strLastname']").val() == ""){
                $("input[name='strLastname']").parent().addClass('has-error');
                 $("label[for='strLastname']").html("<i class='fa fa-times-circle-o'></i> Lastname field is required");
                e.preventDefault();
            }
            else
            {
                $("input[name='strLastname']").parent().removeClass('has-error');
                $("label[for='strLastname']").html("Lastname*");
                
            }
            if($("input[name='strUsername']").val() == ""){
                $("input[name='strUsername']").parent().addClass('has-error');
                 $("label[for='strUsername']").html("<i class='fa fa-times-circle-o'></i> Username field is required");
                e.preventDefault();
            }
            else
            {
                $("input[name='strUsername']").parent().removeClass('has-error');
                $("label[for='strUsername']").html("Username*");
                
            }
            
            // if($("input[name='strPass']").val() == ""){
            //     $("input[name='strPass']").parent().addClass('has-error');
            //      $("label[for='strPass']").html("<i class='fa fa-times-circle-o'></i> Password field is required");
            //     e.preventDefault();
            // }
            // else
            // {
            //     $("input[name='strPass']").parent().removeClass('has-error');
            //     $("label[for='strPass']").html("Password*");
                
            // }
            
            return;
        });
    });
</script>