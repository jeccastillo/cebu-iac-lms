<script type="text/javascript">
    $(document).ready(function(){
        
        
        $("#select-sem-subject").change(function(e){
            document.location = "<?php echo base_url(); ?>subject/subject_viewer/"+$("#subject-id").val()+"/"+$(this).val();
        
        });
       
        $(".trash-subject2").click(function(e){
            conf = confirm("Are you sure you want to delete?");
            if(conf)
            {
                $(".loading-img").show();
                $(".overlay").show();
                var id = $(this).attr('rel');
                var code = $("#subject-code-viewer").val();
                var parent = $(this).parent().parent().parent().parent().parent();
                var data = {'id':id,'code':code};
                $.ajax({
                    'url':'<?php echo base_url(); ?>subject/delete_subject',
                    'method':'post',
                    'data':data,
                    'dataType':'json',
                    'success':function(ret){
                        if(ret.message == "failed"){
                            $(".alert").show();
                            setTimeout(function() {
                                $(".alert").hide('fade', {}, 500)
                            }, 3000);
                        }
                        else
                            document.location="<?php echo base_url(); ?>subject/view_all_subjects";
                        
                        
                }
            });
            }
        });
    });
</script>