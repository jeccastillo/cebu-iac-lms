<script type="text/javascript">
$(document).ready(function() {
    $('#deleted-or-invoice-table').DataTable({
        "aLengthMenu": [10, 20, 50, 100, 250, 500, 750, 1000],
        "bProcessing": true,
        "serverSide": false,
        "ordering": false,
        "paging": true,
        ajax: {
            url: "<?php echo $api_url ?>sms/finance/deleted-payment/<?php echo $current_sem;?>/<?php echo $campus;?>/<?php echo $date_start;?>/<?php echo $date_end;?>",
            dataSrc: 'data'
        },
        columns: [{
            data: 'id',
            title: 'No'
        }, {
            data: 'student_number',
            title: 'Student Number'
        }, {
            data: null,
            title: 'Student Name',
            render: function(data, type, row, meta) {
                return `${data.last_name}, ${data.first_name} ${data.middle_name}`;
            }
        }, {
            data: 'course',
            title: 'Course'
        }, {
            data: 'or_date',
            title: 'Date'
        }, {
            data: 'or_number',
            title: 'OR No.'
        }, {
            data: 'invoice_number',
            title: 'Invoice No.'
        }, {
            data: 'total_amount_due',
            title: 'Amount'
        }, {
            data: 'deleted_at',
            title: 'Date Deleted'
        }, {
            data: 'deleted_by',
            title: 'Deleted By'
        }, {
            data: 'remarks',
            title: 'Remarks'
        }]
    });
});
$("#select-term-leads").on('change', function(e) {
    let term = $(this).val();
    let campus = "<?php echo $campus;?>"
    document.location = "<?php echo base_url()."finance/deleted_or_invoice/"; ?>" + term +
        '/' + $("#date-picker-start").val() + '/' + $("#date-picker-end").val();
});
$("#date-picker-start").on('change', function(e) {
    const dateFrom = $(this).val();
    const campus = "<?php echo $campus;?>"
    document.location = "<?php echo base_url()."finance/deleted_or_invoice/"; ?>" + $(
        "#select-term-leads").val() + '/' + dateFrom + '/' + $("#date-picker-end").val();
});
$("#date-picker-end").on('change', function(e) {
    const dateFrom = new Date($("#date-picker-start").val());
    const campus = "<?php echo $campus;?>"
    const dateTo = new Date($(this).val());
    if (dateFrom > dateTo) {
        alert(
            "The start date cannot be later than the end date. Please select a valid date range.");
        return;
    }
    document.location = "<?php echo base_url()."finance/deleted_or_invoice/"; ?>" + $(
        "#select-term-leads").val() + '/' + $("#date-picker-start").val() + '/' + $(
        "#date-picker-end").val();
});
$(document).ready(function() {
    $("#deleted_or_invoice_list_excel").click(function(e) {
        let api_url = "<?php echo $api_url ?>";
        var campus = "<?php echo $campus;?>"
        axios.get(`${api_url}sms/finance/deleted-payment/${$("#select-term-leads").val()}/${campus}/${$(
        "#date-picker-start").val()}/${$("#date-picker-end").val()}`).then((data) => {
            let payments = data.data.data;
            var campus = "<?php echo $campus;?>";
            var base_url = "<?php echo base_url(); ?>";
            let url = base_url + `excel/finance_deleted_or_invoice/${$("#select-term-leads").val()}/${campus}/${$(
        "#date-picker-start").val()}/${$("#date-picker-end").val()}`;
            var f = $(
                "<form target='_blank' method='POST' style='display:none;'></form>"
                ).attr({
                action: url
            }).appendTo(document.body);
            $('<input type="hidden" />').attr({
                name: 'deleted_payments',
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