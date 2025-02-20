<script type="text/javascript">
$(document).ready(function() {
    $('#cancelled-or-invoice-table').DataTable({
        "aLengthMenu": [10, 20, 50, 100, 250, 500, 750, 1000],
        "bProcessing": true,
        "serverSide": false,
        "ordering": false,
        "paging": true,
        ajax: {
            url: "<?php echo base_url(); ?>finance/finance_cancelled_or_invoice_data/<?php echo $current_sem; ?>/<?php echo $report_type; ?>/<?php echo $date; ?>",
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
            data: 'or_invoice_date',
            title: 'OR / Invoice Date'
        }, {
            data: 'or_invoice_number',
            title: 'OR / Invoice No.'
        }, {
            data: 'amount',
            title: 'Amount'
        }, {
            data: 'date_deleted',
            title: 'Date Deleted'
        }, {
            data: 'deleted_by',
            title: 'Deleted By'
        }, {
            data: 'remarls',
            title: 'Remarks'
        }]
    });
});
$("#select-term-leads").on('change', function(e) {
    let term = $(this).val();
    let reportType = "<?php echo $report_type; ?>";
    let currentDate = "<?php echo $date; ?>";
    document.location = "<?php echo base_url()."finance/cancelled_or_invoice/"; ?>" + term +
        '/' + reportType + '/' + currentDate;
});
$("#date-picker").on('change', function(e) {
    let term = "<?php echo $current_sem; ?>";
    let reportType = "<?php echo $report_type; ?>";
    const currentDate = $(this).val();
    document.location = "<?php echo base_url()."finance/cancelled_or_invoice/"; ?>" + term +
        '/' + reportType + '/' + currentDate;
});
$("#select-report-type").on('change', function(e) {
    let term = "<?php echo $current_sem; ?>";
    let currentDate = "<?php echo $date; ?>";
    let reportType = $(this).val();
    document.location = "<?php echo base_url()."finance/cancelled_or_invoice/"; ?>" + term +
        '/' + reportType + '/' + currentDate;
});
$(document).ready(function() {
    $("#cancelled_or_invoice_list_excel").click(function(e) {
        var campus = "<?php echo $campus;?>";
        var base_url = "<?php echo base_url(); ?>";
        var url = base_url + 'excel/finance_cancelled_or_invoice/' + $(
                "#select-term-leads").val() + '/' + $("#select-report-type").val() +
            '/' + campus + '/' + $("#date-picker").val();
        window.open(url, '_blank');
    })
});
</script>