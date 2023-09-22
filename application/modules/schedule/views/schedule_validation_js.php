<script type="text/javascript">
    $(document).ready(function(){
       
        $("#dteStart").change(function(){
            var idx = this.selectedIndex;    
            if(idx >= $("#dteEnd").prop('selectedIndex'))
                $("#dteEnd").prop('selectedIndex', idx+1);   
        });
        $("#validate-schedule").submit(function(e){
            if(parseInt($("#dteStart").val()) >= parseInt($("#dteEnd").val()))
            {
                alert("start time can't be greater than end time");
                e.preventDefault();
            }
        });
        
        $("#strSchema").change(function(){
            var schema = $(this).val();
            if(schema != 0)
                $("#strDay").attr('disabled','disabled');
            else
                $("#strDay").removeAttr('disabled');
        });

        $("#select-term-schedule").change(function(e){
            var sem = $(this).val();
            document.location = "<?php echo base_url(); ?>schedule/add_schedule/"+sem;
        });
    });
</script>