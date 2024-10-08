<script type="text/javascript">
    $(document).ready(function(){
        $("#select-sem-classlist").change(function(e){
            alert(e.val())
        });
        $(".trash-classlist").click(function(e){
                    conf = confirm("Are you sure you want to delete?");
                    if(conf)
                    {
                        $(".loading-img").show();
                        $(".overlay").show();
                        var id = $(this).attr('rel');
                        var parent = $(this).parent().parent().parent().parent().parent();
                        //alert(parent.html());
                        var data = {'id':id};

                        $.ajax({
                            'url':'<?php echo base_url(); ?>unity/delete_classlist',
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
                                    parent.hide();

                                $(".loading-img").hide();
                                $(".overlay").hide();
                        }
                    });
                    }
                });
    });
</script>