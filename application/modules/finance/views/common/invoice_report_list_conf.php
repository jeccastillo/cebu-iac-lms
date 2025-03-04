<script type="text/javascript">
$(document).ready(function() {
    $('#invoice-report-table').DataTable({
        "aLengthMenu": [10, 20, 50, 100, 250, 500, 750, 1000],
        "bProcessing": true,
        "serverSide": false,
        "ordering": false,
        "paging": true,
        ajax: {
            url: "<?php echo base_url(); ?>finance/finance_invoice_report_data/<?php echo $current_sem; ?>/<?php echo $date; ?>",
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
            data: 'paymentFor',
            title: 'Payment For'
        }, {
            data: 'particular]',
            title: 'Particulars'
        }, {
            data: 'remarks',
            title: 'Payment Type'
        }, {
            data: 'isCash',
            title: 'MOP'
        }, {
            data: 'invoiceDate',
            title: 'Invoice Date'
        }, {
            data: 'invoiceNumber',
            title: 'Invoice Number'
        }, {
            data: 'invoiceAmount',
            title: 'Variable Amount'
        }, {
            data: 'vatExempt',
            title: 'Vat Exempt'
        }, {
            data: 'zeroRated',
            title: 'Zero Rated'
        }, {
            data: 'totalSales',
            title: 'Total Sales'
        }, {
            data: 'vat',
            title: 'VAT'
        }, {
            data: 'ewtRate',
            title: 'EWT Rate'
        }, {
            data: 'ewtAmount',
            title: 'EWT Amount'
        }, {
            data: 'netAmount',
            title: 'Net Amount Due'
        }, {
            data: 'paymentReceived',
            title: 'Payment Received'
        }]
    });
});
$("#select-term-leads").on('change', function(e) {
    let term = $(this).val();
    let currentDate = "<?php echo $date; ?>";
    document.location = "<?php echo base_url()."finance/invoice_report/"; ?>" + term + '/' +
        currentDate;
});
$("#date-picker").on('change', function(e) {
    let term = "<?php echo $current_sem; ?>";
    const currentDate = $(this).val();
    document.location = "<?php echo base_url()."finance/invoice_report/"; ?>" + term + '/' +
        currentDate;
});
$(document).ready(function() {
    $("#scholarship_report_list_excel").click(function(e) {
        var campus = "<?php echo $campus;?>";
        var base_url = "<?php echo base_url(); ?>";
        var url = base_url + 'excel/finance_invoice_report/' + $(
                "#select-term-leads").val() + '/' + campus + '/' + $("#date-picker")
            .val();
        window.open(url, '_blank');
    })
});
</script>