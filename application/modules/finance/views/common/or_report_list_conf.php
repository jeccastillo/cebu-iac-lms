<script type="text/javascript">
$(document).ready(function() {
    $('#or-report-table').DataTable({
        "aLengthMenu": [10, 20, 50, 100, 250, 500, 750, 1000],
        "bProcessing": true,
        "serverSide": false,
        "ordering": false,
        "paging": true,
        ajax: {
            url: "<?php echo base_url(); ?>finance/finance_or_report_data/<?php echo $date_start; ?>/<?php echo $date_end; ?>",
            dataSrc: 'data'
        },
        columns: [{
            data: 'index',
            title: 'No'
        }, {
            data: 'orDate',
            title: 'OR Date'
        }, {
            data: 'orNumber',
            title: 'OR Number'
        }, {
            data: 'invoiceNumber',
            title: 'Invoice Number'
        }, {
            data: 'studentNumber',
            title: 'Student Number'
        }, {
            data: 'studentName',
            title: 'Payee Name'
        }, {
            data: 'paymentFor',
            title: 'Payment For'
        }, {
            data: 'particular',
            title: 'Particulars'
        }, {
            data: 'paymentReceived',
            title: 'Payment Received'
        }]
    });
});
$("#date-picker-from").on('change', function(e) {
    const dateFrom = $(this).val();
    document.location = "<?php echo base_url()."finance/or_report/"; ?>" + dateFrom + '/' +
        $("#date-picker-to").val();
});
$("#date-picker-to").on('change', function(e) {
    const dateFrom = new Date($("#date-picker-from").val());
    const dateTo = new Date($(this).val());
    if (dateFrom > dateTo) {
        alert(
            "The start date cannot be later than the end date. Please select a valid date range.");
        return;
    }
    document.location = "<?php echo base_url()."finance/or_report/"; ?>" + $(
        "#date-picker-from").val() + '/' + $("#date-picker-to").val();
});
$(document).ready(function() {
    $("#or_report_list_excel").click(function(e) {
        var campus = "<?php echo $campus;?>";
        var base_url = "<?php echo base_url(); ?>";
        var url = base_url + 'excel/finance_or_report/' + campus + '/' + $(
            "#date-picker-from").val() + '/' + $("#date-picker-to").val();
        window.open(url, '_blank');
    })
});
</script>