<script type="text/javascript">
$(document).ready(function(){

    $(".delete-extension").click(function(e){
        e.preventDefault();        
        conf = confirm("Are you sure you want to delete?");        
        if(conf)
        {
            $(".loading-img").show();
            $(".overlay").show();
            var id = $(this).attr('rel');                
            var data = {'id':id};
            $.ajax({
                'url':'<?php echo base_url(); ?>index.php/registrar/delete_extension',
                'method':'post',
                'data':data,
                'dataType':'json',
                'success':function(ret){
                    if(ret.message == "failed"){
                        alert('Error in deleting.')                        
                    }
                    else
                        location.reload();

                    $(".loading-img").hide();
                    $(".overlay").hide();
            }
        });
        }
        
    });
    
    $(".delete-month").click(function(e){
        e.preventDefault();        
        conf = confirm("Are you sure you want to delete?");        
        if(conf)
        {
            $(".loading-img").show();
            $(".overlay").show();
            var id = $(this).attr('rel');                
            var data = {'id':id};
            $.ajax({
                'url':'<?php echo base_url(); ?>index.php/registrar/delete_month',
                'method':'post',
                'data':data,
                'dataType':'json',
                'success':function(ret){
                    if(ret.message == "failed"){
                        alert('Error in deleting.')                        
                    }
                    else
                        location.reload();

                    $(".loading-img").hide();
                    $(".overlay").hide();
            }
        });
        }
        
    });
});
</script>