<script type="text/javascript">
    $(document).ready(function(){
        
        $(".remove-subject-curriculum").click(function(e){
            e.preventDefault();
             conf = confirm("Are you sure you want to delete?");
                    if(conf)
                    {
                        $(".loading-img").show();
                        $(".overlay").show();
                        var id = $(this).attr('rel');
                        var parent = $(this).parent().parent();
                        var code = parent.children(':first-child').html();
                        var data = {'id':id,'code':code};
                        $.ajax({
                            'url':'<?php echo base_url(); ?>index.php/unity/delete_subject_curriculum',
                            'method':'post',
                            'data':data,
                            'dataType':'json',
                            'success':function(ret){
                                if(ret.message == "failed"){
                                    $("#alert-text").html('<b>Alert! '+code+'</b> cannot be deleted it is connected to classlist.')
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

        $(".remove-subject-second").click(function(e){
            e.preventDefault();
             conf = confirm("Are you sure you want to delete?");
                    if(conf)
                    {
                        $(".loading-img").show();
                        $(".overlay").show();
                        var id = $(this).attr('rel');
                        var parent = $(this).parent().parent();
                        var code = parent.children(':first-child').html();
                        var data = {'id':id,'code':code};
                        $.ajax({
                            'url':'<?php echo base_url(); ?>index.php/unity/delete_subject_curriculum/1',
                            'method':'post',
                            'data':data,
                            'dataType':'json',
                            'success':function(ret){
                                if(ret.message == "failed"){
                                    $("#alert-text").html('<b>Alert! '+code+'</b> cannot be deleted it is connected to classlist.')
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
        
        //FORM VALIDATION FOR SUBJECT
        $("input[name='strName']").blur(function(){
            if($(this).val() == "" )
            {
                $(this).parent().addClass('has-error');
                $("label[for='strName']").html("<i class='fa fa-times-circle-o'></i> Name is required");
            }
            else
            {
                 $(this).parent().removeClass('has-error');
                 $("label[for='strName']").html("Name*");
            }
        
        });
        
        $("#validate-curriculum").submit(function(e){
            
            
            if($("input[name='strName']").val() == ""){
                $("input[name='strName']").parent().addClass('has-error');
                $("label[for='strName']").html("<i class='fa fa-times-circle-o'></i> Name is required");
                e.preventDefault();
            }
            else
            {
                $("input[name='strName']").parent().removeClass('has-error');
                $("label[for='strName']").html("Name*");
            }
            
            return;
        });
    });
</script>