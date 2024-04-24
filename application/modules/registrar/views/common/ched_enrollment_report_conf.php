
<script type="text/javascript">

$(document).ready(function() {
    $("#ched_enrollment_report_excel").click(function(e){
            var campus = "<?php echo $campus;?>";
            var base_url = "<?php echo base_url(); ?>";
            var url = base_url + 'excel/ched_enrollment_report/' + $("#select-term-leads").val() + '/' + campus;
            window.open(url, '_blank');
        })

        $("#ched_enrollment_report_pdf").click(function(e){
            var campus = "<?php echo $campus;?>";
            var base_url = "<?php echo base_url(); ?>";
            var url = base_url + 'pdf/ched_enrollment_report/' + $("#select-term-leads").val() + '/' + campus;
            window.open(url, '_blank');
        })
});
</script>