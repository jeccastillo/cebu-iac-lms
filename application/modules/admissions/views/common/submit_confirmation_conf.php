<!--Javascript-->
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script type="text/javascript" src="<?php echo base_url(); ?>assets/lib/adminlte/js/jquery.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/lib/adminlte/js/jquery-ui-1.10.3.min.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
       
        $("#submitConfirmation").click(function(e){
            var code = $("#confirmationCode").val();
            
            var data = {'code':code};
            $.ajax({
                'url':'<?php echo base_url(); ?>admissions/confirm_code',
                'method':'post',
                'data':data,
                'dataType':'json',
                'success':function(ret){
                    $("#message").html(ret.msg).fadeIn('fast');
                    setTimeout(function() {
                        $("#message").hide('fade', {}, 500);
                        if(ret.message == 1)
                            document.location = "https://citycollegeoftagaytay.edu.ph";
                    }, 3000);
                    
                }
            });
        });

        
    });

</script>