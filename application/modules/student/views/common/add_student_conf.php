<script type="text/javascript">
    $(document).ready(function(){
       
        $("#generate-stud-num").click(function(){
           
            $.ajax({
                'url':'<?php echo base_url(); ?>unity/generate_student_number',
                'method':'post',
                'data':{'year':$(this).attr("rel")},
                'dataType':'json',
                'success':function(ret){
                    $("#strStudentNumber").val(ret.studentNumber);
                }
            
        });
        });
        
    });
</script>