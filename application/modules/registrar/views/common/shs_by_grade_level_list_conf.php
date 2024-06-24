<script type="text/javascript">
$(document).ready(function() {

    var table = $('#shs-grade-table').DataTable({
        "aLengthMenu": [10, 20, 50, 100, 250, 500, 750, 1000],
        "bProcessing": true,
        "bServerSide": false,
        "ordering": false,
        "paging": true,
        "sAjaxSource": "<?php echo base_url(); ?>registrar/shs_by_grade_level_data/" +
            "<?php echo $current_sem; ?>" + '/' + "<?php echo $postyear; ?>",
        "columns": [{
                data: 'studentNumber',
                title: 'Student Number'
            },
            {
                data: 'strLastname',
                title: 'Last Name'
            }, {
                data: 'strFirstname',
                title: 'Fist Name'
            }, , {
                data: 'strMiddlename',
                title: 'Middle Name'
            }, , {
                data: 'intStudentYear',
                title: 'Year Level'
            }, , {
                data: 'section',
                title: 'Section'
            }

        ]
    });


});


$("#select-term-leads").on('change', function(e) {
    const term = $(this).val();
    document.location = "<?php echo base_url()."registrar/shs_by_grade_level/"; ?>" +
        term + '/' + $("#int-year-level").val();
});

$("#int-year-level").on('change', function(e) {
    const level = $(this).val();
    document.location = "<?php echo base_url()."registrar/shs_by_grade_level/"; ?>" + $(
        "#select-term-leads").val() + '/' + level;
});


$(document).ready(function() {
    $("#shs_by_grade_level_excel").click(function(e) {
        var campus = "<?php echo $campus;?>";
        var base_url = "<?php echo base_url(); ?>";
        var url = base_url + 'excel/shs_by_grade_level/' + $("#select-term-leads")
            .val() + '/' + campus;
        window.open(url, '_blank');
    })

    $("#shs_by_grade_level_list_pdf").click(function(e) {
        var campus = "<?php echo $campus;?>";
        var base_url = "<?php echo base_url(); ?>";
        var url = base_url + 'pdf/shs_by_grade_level/' + $("#select-term-leads")
            .val() + '/' + $("#int-year-level").val();
        window.open(url, '_blank');
    })
});
</script>