<script type="text/javascript">

    $(document).ready(function(){
       
        $('#daterange-btn-transactions').daterangepicker(
            {
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract('days', 1), moment().subtract('days', 1)],
                    'Last 7 Days': [moment().subtract('days', 6), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract('month', 1).startOf('month'), moment().subtract('month',1).endOf('month')]
                },
                startDate: moment().subtract('days', 29),
                endDate: moment()
            },
            function(start, end) {
               var daterange = start.format('YYYY-MM-D') + '/' + end.format('YYYY-MM-D');
               document.location="<?php echo base_url(); ?>unity/transactions/"+daterange;
            }
            );
            $("#s1").change(function(e){
            var str = $(this).val();
            $(".filter-year :nth-child(2)").each(function(){
            if($(this).html().trim() != str)
                $(this).parent().hide();
            else
                $(this).parent().show();
            });
        });
        
    });
</script>