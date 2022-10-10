<script type="text/javascript">
    $(document).ready(function(){
        
        $(".trash-credited").click(function(e){
                    conf = confirm("Are you sure you want to delete?");
                    if(conf)
                    {
                        $(".loading-img").show();
                        $(".overlay").show();
                        var id = $(this).attr('rel');
                        var parent = $(this).parent().parent();
                        var data = {'id':id};
                        $.ajax({
                            'url':'<?php echo base_url(); ?>index.php/department/delete_credited',
                            'method':'post',
                            'data':data,
                            'dataType':'json',
                            'success':function(ret){
                                if(ret.message == "failed"){
                                    $("#alert-text").html('<b>Alert! '+code+'</b> cannot be deleted')
                                    $(".alert").show();
                                    setTimeout(function() {
                                        $(".alert").hide('fade', {}, 500)
                                    }, 3000);
                                }
                                else
                                    document.location="<?php echo base_url();?>department/crediting/<?php echo $student['intID']; ?>";

                                $(".loading-img").hide();
                                $(".overlay").hide();
                        }
                    });
                    }
                });
    });
</script>