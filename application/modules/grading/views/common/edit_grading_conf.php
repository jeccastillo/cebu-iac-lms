<script type="text/javascript">
    $(document).ready(function(){
        $("#add-grade-line").click(function(e){
            e.preventDefault();
            $("#item-container").append('<div class="row mt-5"><div class="col-sm-4"><input type="text" name="item[]" class="form-control" placeholder="Enter Value" /></div></div>');
        })
    });
</script>
<input type="text" name="item[]" class="form-control" placeholder="Enter Value" />