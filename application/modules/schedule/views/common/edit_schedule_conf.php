<script type="text/javascript">
    $(document).ready(function(){
       
        $("#schedcode-lock").click(function(){
            rel = $(this).attr('rel');
            
            if(rel == "locked")
            {
                $(this).attr('rel','unlocked');
                $(this).find('i').removeClass('ion-locked');
                $(this).find('i').addClass('ion-unlocked');
                $("#schedCode").removeAttr('disabled');
            }
            else
            {
                $(this).attr('rel','locked');
                $(this).find('i').removeClass('ion-unlocked');
                $(this).find('i').addClass('ion-locked');
                $("#schedCode").attr('disabled','disabled');
            }
        });
    });
</script>