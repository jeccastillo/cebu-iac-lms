<script type="text/javascript">
$(document).ready(function() {
    $('#miscellaneous-table').DataTable({
        "aLengthMenu": [10, 20, 50, 100, 250, 500, 750, 1000],
        "bProcessing": true,
        "serverSide": false,
        "ordering": false,
        "paging": true,
        ajax: {
            url: "<?php echo base_url(); ?>finance/miscellaneous_fee_report_data/<?php echo $current_sem; ?>/<?php echo $particular_id; ?>/<?php echo $date; ?>",
            dataSrc: 'data'
        },
        columns: [{
            data: 'index',
            title: 'No'
        }, {
            data: 'student_number',
            title: 'Student Number'
        }, {
            data: 'student_name',
            title: 'Student Name'
        }, {
            data: 'course',
            title: 'Course'
        }, {
            data: 'date_enlisted',
            title: 'Date Enrolled'
        }, {
            data: 'misc_type',
            title: 'Type'
        }, {
            data: 'amount',
            title: 'Amount'
        }]
    });
});
$("#select-term-leads").on('change', function(e) {
    let term = $(this).val();
    let particularId = "<?php echo $particular_id; ?>";
    let currentDate = "<?php echo $date; ?>";
    document.location = "<?php echo base_url()."finance/miscellaneous_report/"; ?>" + term +
        '/' + particularId + '/' + currentDate;
});
$("#date-picker").on('change', function(e) {
    let term = "<?php echo $current_sem; ?>";
    let particularId = "<?php echo $particular_id; ?>";
    const currentDate = $(this).val();
    document.location = "<?php echo base_url()."finance/miscellaneous_report/"; ?>" + term +
        '/' + particularId + '/' + currentDate;
});
$("#select-misc-fee").on('change', function(e) {
    let term = "<?php echo $current_sem; ?>";
    let currentDate = "<?php echo $date; ?>";
    let particularId = $(this).val();
    document.location = "<?php echo base_url()."finance/miscellaneous_report/"; ?>" + term +
        '/' + particularId + '/' + currentDate;
});
$(document).ready(function() {
    $("#miscellaneous_fee_list_excel").click(function(e) {
        var campus = "<?php echo $campus;?>";
        var base_url = "<?php echo base_url(); ?>";
        var url = base_url + 'excel/miscellaneous_fee_report/' + $(
            "#select-term-leads").val() + '/' + campus + '/' + $(
            "#select-misc-fee").val() + '/' + $("#date-picker").val();
        window.open(url, '_blank');
    })
});
</script>