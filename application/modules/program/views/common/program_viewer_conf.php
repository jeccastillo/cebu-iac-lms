<script type="text/javascript">
    $(document).ready(function(){
        
        
        $("#select-sem-program").change(function(e){
            document.location = "<?php echo base_url(); ?>program/program_viewer/"+$("#program-id").val()+"/"+$(this).val();
        
        });
       
        $(".trash-program2").click(function(e){
            conf = confirm("Are you sure you want to delete?");
            if(conf)
            {
                $(".loading-img").show();
                $(".overlay").show();
                var id = $(this).attr('rel');
                var code = $("#program-code-viewer").val();
                var parent = $(this).parent().parent().parent().parent().parent();
                var data = {'id':id,'code':code};
                $.ajax({
                    'url':'<?php echo base_url(); ?>program/delete_program',
                    'method':'post',////
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
                            document.location="<?php echo base_url(); ?>program/view_all_programs";
                        
                        
                }
            });
            }
        });
    });
</script>