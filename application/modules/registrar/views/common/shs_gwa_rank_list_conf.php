<script type="text/javascript">
$(document).ready(function() {

    $('#shs-gwa-rank-table').DataTable({
        "aLengthMenu": [10, 20, 50, 100, 250, 500, 750, 1000],
        "bProcessing": true,
        "serverSide": false,
        "ordering": false,
        "paging": true,
        ajax: {
            url: "<?php echo base_url(); ?>registrar/shs_gwa_rank_data/<?php echo $current_sem; ?>/<?php echo $postyear; ?>",
            dataSrc: ''
        },
        columns: [{
                data: 'student_number',
                title: 'Student Number'
            },
            {
                data: 'first_name',
                title: 'First Name'
            },

            {
                data: 'middle_name',
                title: 'Middle Name'
            },
            {
                data: 'last_name',
                title: 'Last Name'
            },
            {
                data: 'track',
                title: 'Track'
            },
            {
                data: 'gwa',
                title: 'GWA'
            },
            {
                data: 'year_level',
                title: 'Year Level'
            }

        ]
    });



});


$("#select-term-leads").on('change', function(e) {
    let campus = "<?php echo $campus;?>";
    const term = $(this).val();
    document.location = "<?php echo base_url()."registrar/shs_gwa_rank/"; ?>" +
        term + '/' + $("#int-year-level").val();
});

$("#int-year-level").on('change', function(e) {
    let campus = "<?php echo $campus;?>";
    const level = $(this).val();
    document.location = "<?php echo base_url()."registrar/shs_gwa_rank/"; ?>" + $(
        "#select-term-leads").val() + '/' + level;
});


$(document).ready(function() {
    $("#shs_gwa_rank_list_excel").click(function(e) {
        var campus = "<?php echo $campus;?>";
        var base_url = "<?php echo base_url(); ?>";
        var url = base_url + 'excel/shs_gwa_rank/' + $("#select-term-leads")
            .val() + '/' + $("#int-year-level").val();
        window.open(url, '_blank');
    })

    $("#shs_gwa_rank_list_pdf").click(function(e) {
        var campus = "<?php echo $campus;?>";
        var base_url = "<?php echo base_url(); ?>";
        var url = base_url + 'pdf/shs_gwa_rank/' + $("#select-term-leads")
            .val() + '/' + $("#int-year-level").val();
        window.open(url, '_blank');
    })
});
</script>