<script type="text/javascript">
    $(document).ready(function(){
        
        //FORM VALIDATION FOR SUBJECT
        $("input[name='strProgramCode']").blur(function(){
            if($(this).val() == "" )
            {
                $(this).parent().addClass('has-error');
                $("label[for='strProgramCode']").html("<i class='fa fa-times-circle-o'></i> Program Code is required");
            }
            else
            {
                 $(this).parent().removeClass('has-error');
                 $("label[for='strProgramCode']").html("Program Code*");
            }
        
        });
        $("textarea[name='strProgramDescription']").blur(function(){
            if($(this).val() == "" )
            {
                $(this).parent().addClass('has-error');
                $("label[for='strProgramDescription']").html("<i class='fa fa-times-circle-o'></i> Description field is required");
            }
            else
            {
                 $(this).parent().removeClass('has-error');
                 $("label[for='strProgramDescription']").html("Program Description*");
            }
        
        });
        $("#validate-program").submit(function(e){
            
            
            if($("input[name='strProgramCode']").val() == ""){
                $("input[name='strProgramCode']").parent().addClass('has-error');
                $("label[for='strProgramCode']").html("<i class='fa fa-times-circle-o'></i> Program Code is required");
                e.preventDefault();
            }
            else
            {
                $("textarea[name='strProgramCode']").parent().removeClass('has-error');
                $("label[for='strProgramCode']").html("Program Code*");
            }
            if($("textarea[name='strProgramDescription']").val() == ""){
                $("textarea[name='strProgramDescription']").parent().addClass('has-error');
                 $("label[for='strProgramDescription']").html("<i class='fa fa-times-circle-o'></i> Description field is required");
                e.preventDefault();
            }
            else
            {
                $("textarea[name='strProgramDescription']").parent().removeClass('has-error');
                $("label[for='strProgramDescription']").html("Program Description*");
                
            }
            
            return;
        });
    });
</script>