<script type="text/javascript">
    $(document).ready(function(){
        
        $("#enumRegistrationStatus").change(function(){
            if($(this).val() == "0" )
            {
                $(this).parent().addClass('has-error');
                $("label[for='enumRegistrationStatus']").html("<i class='fa fa-times-circle-o'></i> Academic Status field is required");
            }
            else
            {
                 $(this).parent().removeClass('has-error');
                 $("label[for='enumRegistrationStatus']").html("Academic Status");
            }
        });
        
         $("#enumScholarship").change(function(){
            if($(this).val() == "0" )
            {
                $(this).parent().addClass('has-error');
                $("label[for='enumScholarship']").html("<i class='fa fa-times-circle-o'></i> Scholarship Grant field is required");
            }
            else
            {
                 $(this).parent().removeClass('has-error');
                 $("label[for='enumScholarship']").html("Scholarship Grant");
            }
        });
        
         $("#transcrossSelect").change(function(){
            if($(this).val() == "0" )
            {
                $(this).parent().addClass('has-error');
                $("label[for='enumStudentType']").html("<i class='fa fa-times-circle-o'></i> Student Type field is required");
            }
            else
            {
                 $(this).parent().removeClass('has-error');
                 $("label[for='enumStudentType']").html("Student Type");
            }
        });
        
        
        $("#validate-student").submit(function(e){
            
            
            if($("#enumRegistrationStatus").val() == "0"){
                $("#enumRegistrationStatus").parent().addClass('has-error');
                $("label[for='enumRegistrationStatus']").html("<i class='fa fa-times-circle-o'></i> Academic Status field is required");
                e.preventDefault();
            }
            else
            {
                $("#enumRegistrationStatus").parent().removeClass('has-error');
                $("label[for='enumRegistrationStatus']").html("Academic Status*");
            }
            
            if($("#enumScholarship").val() == "0"){
                $("#enumScholarship").parent().addClass('has-error');
                $("label[for='enumScholarship']").html("<i class='fa fa-times-circle-o'></i> Scholarship Grant field is required");
                e.preventDefault();
            }
            else
            {
                $("#enumScholarship").parent().removeClass('has-error');
                $("label[for='enumScholarship'").html("Scholarship Grant*");
                
            }
            
            if($("#transcrossSelect").val() == "0"){
                $("#transcrossSelect").parent().addClass('has-error');
                 $("label[for='enumStudentType'").html("<i class='fa fa-times-circle-o'></i> Student Type field is required");
                e.preventDefault();
            }
            else
            {
                $("#transcrossSelect").parent().removeClass('has-error');
                $("label[for='enumStudentType'").html("Student Type*");
            }
                   
            return;
        });
    });
</script>