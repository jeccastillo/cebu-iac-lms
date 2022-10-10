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
        
        
        $("input[name='strPicture']").blur(function(){
            if($(this).val() == "" )
            {
                $(this).val() = "<?php echo $img_dir . "default_image.jpeg"; ?>";
            }
        
        
        });
        
        $("#addStudentCourse").change(function(){
            if($(this).val() == "0" )
            {
                $(this).parent().addClass('has-error');
                $("label[for='intProgramID']").html("<i class='fa fa-times-circle-o'></i> Course field is required");
            }
            else
            {
                 $(this).parent().removeClass('has-error');
                 $("label[for='intProgramID']").html("Course");
            }
        
        
        });
        
        $("#validate-student").submit(function(e){
            
            
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
            
            if($("input[name='strStudentNumber']").val() == ""){
                $("input[name='strStudentNumber']").parent().addClass('has-error');
                 $("label[for='strStudentNumber']").html("<i class='fa fa-times-circle-o'></i> Student Number field is required");
                e.preventDefault();
            }
            else
            {
                $("input[name='strStudentNumber']").parent().removeClass('has-error');
                $("label[for='strStudentNumber']").html("Student Number*");
                
            }
            
             if($("#addStudentCourse").val() == "0"){
                $("#addStudentCourse").parent().addClass('has-error');
                 $("label[for='intProgramID']").html("<i class='fa fa-times-circle-o'></i> Course field is required");
                e.preventDefault();
            }
            else
            {
                $("#addStudentCourse").parent().removeClass('has-error');
                $("label[for='intProgramID']").html("Course");
                
            }
            
            return;
        });
    });
</script>