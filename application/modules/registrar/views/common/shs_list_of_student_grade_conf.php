<script type="text/javascript">
$(document).ready(function() {

    $('#shs-student-grade-table').DataTable({
        "aLengthMenu": [10, 20, 50, 100, 250, 500, 750, 1000],
        "bProcessing": true,
        "bServerSide": false,
        "ordering": false,
        "paging": true,
        ajax: {
            url: "<?php echo base_url(); ?>registrar/shs_student_grades_data/<?php echo $current_sem; ?>/<?php echo $postyear; ?>",
            dataSrc: ''
        },
        columns: [{
                data: 'student_number',
                title: 'Student Number'
            },
            {
                data: 'name',
                title: 'Student Name'
            },

            {
                data: 'date_registered',
                title: 'Date Enrolled'
            },
            {
                data: 'subjects.section',
                title: 'Section'
            },

            {
                data: 'subjects.subject',
                title: 'Subject'
            },

            {
                data: 'subjects.day',
                title: 'Day'
            },
            {
                data: 'subjects.time',
                title: 'Time'
            },
            {
                data: 'subjects.grade',
                title: 'Grade'
            },
            {
                data: 'subjects.professor',
                title: 'Professor'
            }


        ]
    });


});


$("#select-term-leads").on('change', function(e) {
    let campus = "<?php echo $campus;?>";
    const term = $(this).val();
    document.location = "<?php echo base_url()."registrar/shs_student_grades/"; ?>" +
        term + '/' + $("#int-year-level").val();
});

$("#int-year-level").on('change', function(e) {
    let campus = "<?php echo $campus;?>";
    const level = $(this).val();
    document.location = "<?php echo base_url()."registrar/shs_student_grades/"; ?>" + $(
        "#select-term-leads").val() + '/' + level;
});


$(document).ready(function() {
    $("#shs_list_of_student_grade_excel").click(function(e) {
        var campus = "<?php echo $campus;?>";
        var base_url = "<?php echo base_url(); ?>";
        var url = base_url + 'excel/shs_student_grades/' + $("#select-term-leads")
            .val() + '/' + $("#int-year-level").val() + '/' + campus;
        window.open(url, '_blank');
    })

    // $("#shs_by_grade_level_list_pdf").click(function(e) {
    //     var campus = "<?php echo $campus;?>";
    //     var base_url = "<?php echo base_url(); ?>";
    //     var url = base_url + 'pdf/shs_by_grade_level/' + $("#select-term-leads")
    //         .val() + '/' + $("#int-year-level").val();
    //     window.open(url, '_blank');
    // })
});
</script>