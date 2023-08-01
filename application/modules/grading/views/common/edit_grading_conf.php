<script type="text/javascript">
    $(document).ready(function(){
        $("#add-grade-line").click(function(e){
            e.preventDefault();
            $("#item-container").append('<div class="row mt-5"><div class="col-sm-4"><input type="text" required name="item[]" class="form-control" placeholder="Enter Value" /></div></div>');
        })

        $("#remove-grade-line").click(function(e){
            e.preventDefault();
            var count = $("#item-container").children().length;
            //console.log(count);
            if(count > 1)
                $("#item-container").find("div:last").remove();
        });
    });
</script>
<input type="text" name="item[]" class="form-control" placeholder="Enter Value" />