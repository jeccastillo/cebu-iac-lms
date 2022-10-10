<script>

    $(".filter-select").change(function(e){
           
           document.location = "<?php echo base_url(); ?>unity/edit_classlist/<?php echo $classlist['intID']; ?>/"+$(this).val();
        });
    

</script>