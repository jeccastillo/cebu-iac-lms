<script type="text/javascript">
    $(document).ready(function(){
        $("#add-grade-line").click(function(e){
            e.preventDefault();
            $("#item-container").append('<div class="row mt-5"><div class="col-sm-4"><input type="text" required name="item[]" class="form-control" placeholder="Enter Value" /></div><div class="col-sm-4"><input type="text" required name="remarks[]" class="form-control" placeholder="Enter Remarks" /></div></div>');
        });

        $("#remove-grade-line").click(function(e){
            e.preventDefault();
            var count = $("#item-container").children().length;
            //console.log(count);
            if(count > 1)
                $("#item-container").find(".row:last").remove();
        });

        $(".delete-grade-item").click(function(e){
            e.preventDefault();
            value = $(this).attr('data-val');
            console.log(value);
        });

        $(".delete-grade-item").click(function(e){
            conf = confirm("Are you sure you want to delete?");
            if(conf)
            {
                $(".loading-img").show();
                $(".overlay").show();
                var id = $(this).attr('data-val');
                var parent = $(this).parent().parent();                
                var data = {'id':id};
                $.ajax({
                    'url':'<?php echo base_url(); ?>index.php/grading/delete_grading_item',
                    'method':'post',
                    'data':data,
                    'dataType':'json',
                    'success':function(ret){
                        // if(ret.message == "failed"){
                        //     $("#alert-text").html('<b>Alert! '+code+'</b> cannot be deleted it is connected to classlist.')
                        //     $(".alert").show();
                        //     setTimeout(function() {
                        //         $(".alert").hide('fade', {}, 500)
                        //     }, 3000);
                        // }
                        // else
                        parent.hide();

                        $(".loading-img").hide();
                        $(".overlay").hide();
                }
            });
            }
        });
    });
</script>
<input type="text" name="item[]" class="form-control" placeholder="Enter Value" />