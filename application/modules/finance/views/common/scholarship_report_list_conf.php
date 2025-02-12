<script type="text/javascript">
$(document).ready(function() {
    $('#scholarship-report-table').DataTable({
        "aLengthMenu": [10, 20, 50, 100, 250, 500, 750, 1000],
        "bProcessing": true,
        "serverSide": false,
        "ordering": false,
        "paging": true,
        ajax: {
            url: "<?php echo base_url(); ?>finance/finance_scholarship_report_data/<?php echo $current_sem; ?>/<?php echo $scholar_type_id; ?>/<?php echo $date; ?>",
            dataSrc: 'data'
        },
        columns: [{
            data: 'index',
            title: 'No'
        }, {
            data: 'studentNumber',
            title: 'Student Number'
        }, {
            data: 'studentName',
            title: 'Student Name'
        }, {
            data: 'course',
            title: 'Course'
        }, {
            data: 'dateEnrolled',
            title: 'Date Enrolled'
        }, {
            data: 'amount',
            title: 'Amount'
        }]
    });
});
$("#select-term-leads").on('change', function(e) {
    let term = $(this).val();
    let scholarType = "<?php echo $scholar_type_id; ?>";
    let currentDate = "<?php echo $date; ?>";
    document.location = "<?php echo base_url()."finance/scholarship_report/"; ?>" + term +
        '/' + scholarType + '/' + currentDate;
});
$("#date-picker").on('change', function(e) {
    let term = "<?php echo $current_sem; ?>";
    let scholarType = "<?php echo $scholar_type_id; ?>";
    const currentDate = $(this).val();
    document.location = "<?php echo base_url()."finance/scholarship_report/"; ?>" + term +
        '/' + scholarType + '/' + currentDate;
});
$("#select-scholar-type").on('change', function(e) {
    let term = "<?php echo $current_sem; ?>";
    let currentDate = "<?php echo $date; ?>";
    let scholarType = $(this).val();
    document.location = "<?php echo base_url()."finance/scholarship_report/"; ?>" + term +
        '/' + scholarType + '/' + currentDate;
});
$(document).ready(function() {
    $("#scholarship_report_list_excel").click(function(e) {
        var campus = "<?php echo $campus;?>";
        var base_url = "<?php echo base_url(); ?>";
        var url = base_url + 'excel/finance_scholarship_report/' + $(
                "#select-term-leads").val() + '/' + $("#select-scholar-type")
        .val() + '/' + campus + '/' + $("#date-picker").val();
        window.open(url, '_blank');
    })
});
</script>