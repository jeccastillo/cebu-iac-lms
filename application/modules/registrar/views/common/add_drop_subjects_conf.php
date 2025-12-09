<script type="text/javascript">
$(document).ready(function() {
    $('#shs-student-grade-table').DataTable({
        "aLengthMenu": [10, 20, 50, 100, 250, 500, 750, 1000],
        "bProcessing": true,
        "bServerSide": false,
        "ordering": false,
        "paging": true,
        ajax: {
            url: "<?php echo base_url(); ?>registrar/add_drop_subjects_data/<?php echo $current_sem; ?>",
            dataSrc: ''
        },
        columns: [{
            data: 'student_number',
            title: 'Student Number'
        }, {
            data: 'name',
            title: 'Student Name'
        }, {
            data: 'date_registered',
            title: 'Date Enrolled'
        }, {
            data: 'subjects.section',
            title: 'Section'
        }, {
            data: 'subjects.subject',
            title: 'Subject'
        }, {
            data: 'subjects.day',
            title: 'Day'
        }, {
            data: 'subjects.time',
            title: 'Time'
        }, {
            data: 'subjects.grade',
            title: 'Grade'
        }, {
            data: 'subjects.professor',
            title: 'Professor'
        }]
    });
});
$("#select-term-leads").on('change', function(e) {
    let campus = "<?php echo $campus;?>";
    const term = $(this).val();
    document.location = "<?php echo base_url()."registrar/add_drop_subjects/"; ?>" + term
});
$(document).ready(function() {
    $("#add_drop_subjects_excel").click(function(e) {
        var campus = "<?php echo $campus;?>";
        var base_url = "<?php echo base_url(); ?>";
        var url = base_url + 'excel/add_drop_report/' + $("#select-term-leads")
        .val()
        window.open(url, '_blank');
    })
});
</script>