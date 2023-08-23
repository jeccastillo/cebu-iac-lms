<?php $d_open = '<div class="btn-group"><button type="button" class="btn btn-default">Actions</button><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span><span class="sr-only">Toggle Dropdown</span></button><ul class="dropdown-menu" role="menu">';
?>
<script type="text/javascript">
$(document).ready(function() {

    $(".delete-question").on('click',function(e){
        e.preventDefault();
        var id = $(this).attr('rel');
        var conf = confirm("Are you sure you want to delete this question?");
        if(conf){
            document.location = "<?php echo base_url(); ?>examination/delete_question/"+id+"/<?php echo $exam_id; ?>";
        }
    })

});
</script>