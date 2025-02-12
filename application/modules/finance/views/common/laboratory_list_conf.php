<script type="text/javascript">
$(document).ready(function() {
    $('#laboratory-table').DataTable({
        "aLengthMenu": [10, 20, 50, 100, 250, 500, 750, 1000],
        "bProcessing": true,
        "serverSide": false,
        "ordering": false,
        "paging": true,
        ajax: {
            url: "<?php echo base_url(); ?>finance/finance_lab_fee_report_data/<?php echo $current_sem; ?>/<?php echo $lab_type_id; ?>/<?php echo $date; ?>",
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
            data: 'lab_fee_amount',
            title: 'Amount'
        }, {
            data: 'mode_of_payment',
            title: 'Mode of Payment'
        }]
    });
});
$("#select-term-leads").on('change', function(e) {
    let term = $(this).val();
    let labType = "<?php echo $lab_type_id; ?>";
    let currentDate = "<?php echo $date; ?>";
    document.location = "<?php echo base_url()."finance/laboratory/"; ?>" + term + '/' +
        labType + '/' + currentDate;
});
$("#date-picker").on('change', function(e) {
    let term = "<?php echo $current_sem; ?>";
    let labType = "<?php echo $lab_type_id; ?>";
    const currentDate = $(this).val();
    document.location = "<?php echo base_url()."finance/laboratory/"; ?>" + term + '/' +
        labType + '/' + currentDate;
});
$("#select-lab-fee").on('change', function(e) {
    let term = "<?php echo $current_sem; ?>";
    let currentDate = "<?php echo $date; ?>";
    let labType = $(this).val();
    document.location = "<?php echo base_url()."finance/laboratory/"; ?>" + term + '/' +
        labType + '/' + currentDate;
});
$(document).ready(function() {
    $("#laboratory_list_excel").click(function(e) {
        var campus = "<?php echo $campus;?>";
        var base_url = "<?php echo base_url(); ?>";
        var url = base_url + 'excel/finance_lab_fee_report/' + $(
            "#select-term-leads").val() + '/' + campus + '/' + $(
            "#select-lab-fee").val() + '/' + $("#date-picker").val();
        window.open(url, '_blank');
    })
});
</script>