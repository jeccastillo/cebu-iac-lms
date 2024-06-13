<style>
    .dataTables_filter {
        display: none;
    }
</style>
<script type="text/javascript">
    $(document).ready(function() {

        var table = $('#regular-list-table').DataTable({
            "aLengthMenu": [10, 20, 50, 100, 250, 500, 750, 1000],
            "bProcessing": true,
            "bServerSide": false,
            "ordering": false,
            "paging": true,
            "sAjaxSource": "<?php echo base_url(); ?>unity/regular_list_data/" + "<?php echo $current_sem; ?>",
            "columns": [
                {
                    data: 'studentNumber',
                    title: 'Student Number'
                },
                {
                    data: 'name',
                    title: 'Name'
                },{
                    data: 'course',
                    title: 'Course'
                },                
            ]
        });
    

    });

    $("#select-term-leads").on('change', function(e) {
        const term = $(this).val();
        document.location = "<?php echo base_url() . "unity/regular_list/"; ?>" + term;
    });

    $(document).ready(function() {
        $("#regular_list_excel").click(function(e) {
            var campus = "<?php echo $campus; ?>";
            var base_url = "<?php echo base_url(); ?>";
            var url = base_url + 'excel/regular_list/' + $("#select-term-leads").val();
            window.open(url, '_blank');
        })

        $("#regular_list_pdf").click(function(e) {
            var campus = "<?php echo $campus; ?>";
            var base_url = "<?php echo base_url(); ?>";
            var url = base_url + 'pdf/regular_list/' + $("#select-term-leads").val();
            window.open(url, '_blank');
        })
    });
</script>