<script type="text/javascript">
$(document).ready(function(){
    $("#sem-select-edit-ay").on('change',function(e){
        var id = $(this).val();
        document.location = "<?php echo base_url(); ?>finance/edit_ay/" + id;
    });
});

</script>