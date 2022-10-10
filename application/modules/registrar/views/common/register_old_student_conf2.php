<script type="text/javascript">
    $(document).ready(function(){
       
        $('#enumRegistrationStatus').change(function() {
            $("#subject-list").html('');
            if ($(this).val() == 'regular') {
              $("#load-subjects").removeAttr("disabled");
              total_units = 0;
            }
            else if ($(this).val() == 'irregular') {
                $("#load-subjects").attr("disabled","disabled");
                total_units = 0;
            }

            $("#total-units").val(parseInt(total_units)*parseInt(<?php echo $unit_fee; ?>));
            $("#tuition-fee").html(parseInt(total_units)*parseInt(<?php echo $unit_fee; ?>));
        });
    });
</script>