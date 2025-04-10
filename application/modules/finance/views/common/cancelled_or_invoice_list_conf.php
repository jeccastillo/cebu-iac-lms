<script type="text/javascript">
$(document).ready(function() {
    $('#cancelled-or-invoice-table').DataTable({
        "aLengthMenu": [10, 20, 50, 100, 250, 500, 750, 1000],
        "bProcessing": true,
        "serverSide": false,
        "ordering": false,
        "paging": true,
        ajax: {
            url: "<?php echo base_url(); ?>finance/finance_cancelled_or_invoice_data",
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
$("#date-picker-from").on('change', function(e) {
    const dateFrom = $(this).val();
    document.location = "<?php echo base_url()."finance/cancelled_or_invoice/"; ?>" +
        dateFrom + '/' + $("#date-picker-to").val();
});
$("#date-picker-to").on('change', function(e) {
    const dateFrom = new Date($("#date-picker-from").val());
    const dateTo = new Date($(this).val());
    if (dateFrom > dateTo) {
        alert(
            "The start date cannot be later than the end date. Please select a valid date range.");
        return;
    }
    document.location = "<?php echo base_url()."finance/cancelled_or_invoice/"; ?>" + $(
        "#date-picker-from").val() + '/' + $("#date-picker-to").val();
});
$(document).ready(function() {
    $("#cancelled_or_invoice_list_excel").click(function(e) {
        let api_url = "<?php echo $api_url ?>";
        var campus = "<?php echo $campus;?>"
        // axios.get(api_url + 'sms/finance/voided-payment/' + $("#select-term-leads").val() + '/' + campus + '/' + $("#date-picker-from").val(); + '/' + $("#date-picker-to").val())
        axios.get(`http://cebuapi.iacademy.edu.ph/api/v1/sms/finance/voided-payment/28/${campus}/${$(
        "#date-picker-from").val()}/${$("#date-picker-to").val()}`).then((data) => {
            let payments = data.data.data;
            console.log(payments);
            var campus = "<?php echo $campus;?>";
            var base_url = "<?php echo base_url(); ?>";
            // let url = base_url + 'excel/finance_cancelled_or_invoice/' + $("#select-term-leads").val() + '/' + campus + '/' + $("#date-picker-from").val(); + '/' + $("#date-picker-to").val();
            let url = base_url + `excel/finance_cancelled_or_invoice/28/${campus}/${$(
        "#date-picker-from").val()}/${$("#date-picker-to").val()}`;
            var f = $(
                "<form target='_blank' method='POST' style='display:none;'></form>"
                ).attr({
                action: url
            }).appendTo(document.body);
            $('<input type="hidden" />').attr({
                name: 'cancelled_payments',
                value: JSON.stringify(payments)
            }).appendTo(f);
            f.submit();
            f.remove();
        }).catch((error) => {
            console.log(error);
        })
    })
});
</script>