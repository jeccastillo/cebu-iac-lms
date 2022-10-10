<!--script type="text/javascript" src="<?php echo base_url(); ?>assets/js/photobooth_min.js"></script-->
<script type="text/javascript">
    $(document).ready(function(){
     
        $("#studentnumber-lock").click(function(){
            rel = $(this).attr('rel');
            
            if(rel == "locked")
            {
                $(this).attr('rel','unlocked');
                $(this).find('i').removeClass('ion-locked');
                $(this).find('i').addClass('ion-unlocked');
                $("#strStudentNumber").removeAttr('disabled');
            }
            else
            {
                $(this).attr('rel','locked');
                $(this).find('i').removeClass('ion-unlocked');
                $(this).find('i').addClass('ion-locked');
                $("#strStudentNumber").attr('disabled','disabled');
            }
        });
        
        $("#generate-password").click(function(){
           
            $.ajax({
                'url':'<?php echo base_url(); ?>unity/generate_password',
                'method':'post',
                'data':{},
                'dataType':'json',
                'success':function(ret){
                    $("#strPass").val(ret.password);
                }
            
            });
        });
    });
</script>