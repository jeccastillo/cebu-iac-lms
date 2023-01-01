<script type="text/javascript">
    $(document).ready(function(){
        
        //FORM VALIDATION FOR STUDENT
        $("input[name='strRoomCode']").blur(function(){
            if($(this).val() == "" )
            {
                $(this).parent().addClass('has-error');
                $("label[for='strRoomCode']").html("<i class='fa fa-times-circle-o'></i> Classroom Code field is required");
            }
            else
            {
                 $(this).parent().removeClass('has-error');
                 $("label[for='strRoomCode']").html("Classroom Code*");
            }
        
        });
       
        $("#validate-classroom").submit(function(e){
            
            
            if($("input[name='strRoomCode']").val() == ""){
                $("input[name='strRoomCode']").parent().addClass('has-error');
                $("label[for='strRoomCode']").html("<i class='fa fa-times-circle-o'></i> Classroom Code field is required");
                e.preventDefault();
            }
            else
            {
                $("input[name='strRoomCode']").parent().removeClass('has-error');
                $("label[for='strRoomCode']").html("Classroom Code*");
            }
            
            
            return;
        });
    });
</script>