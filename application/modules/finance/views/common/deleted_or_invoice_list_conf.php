<script type="text/javascript">
$(document).ready(function() {
    $('#deleted-or-invoice-table').DataTable({
        "aLengthMenu": [10, 20, 50, 100, 250, 500, 750, 1000],
        "bProcessing": true,
        "serverSide": false,
        "ordering": false,
        "paging": true,
        ajax: {
            url: "<?php echo base_url(); ?>finance/finance_deleted_or_invoice_data/<?php echo $current_sem; ?>/<?php echo $report_type; ?>/<?php echo $date; ?>",
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
    document.location = "<?php echo base_url()."finance/deleted_or_invoice/"; ?>" + term +
        '/' + reportType + '/' + currentDate;
});
$("#date-picker").on('change', function(e) {
    let term = "<?php echo $current_sem; ?>";
    let reportType = "<?php echo $report_type; ?>";
    const currentDate = $(this).val();
    document.location = "<?php echo base_url()."finance/deleted_or_invoice/"; ?>" + term +
        '/' + reportType + '/' + currentDate;
});
$("#select-report-type").on('change', function(e) {
    let term = "<?php echo $current_sem; ?>";
    let currentDate = "<?php echo $date; ?>";
    let reportType = $(this).val();
    document.location = "<?php echo base_url()."finance/deleted_or_invoice/"; ?>" + term +
        '/' + reportType + '/' + currentDate;
});
$(document).ready(function() {

    $("#deleted_or_invoice_list_excel").click(function(e) {
        let api_url = "<?php echo $api_url ?>";
        axios.get(api_url + 'sms/finance/deleted-payment/' + $("#select-term-leads").val())
                .then((data) => {
                    let payments = data.data.data;
                    var campus = "<?php echo $campus;?>";
                    var base_url = "<?php echo base_url(); ?>";
                    let url = base_url + 'excel/finance_deleted_or_invoice/' + $(
                "#select-term-leads").val() + '/' + $("#select-report-type").val() +
            '/' + campus + '/' + $("#date-picker").val();
                    
                    var f = $("<form target='_blank' method='POST' style='display:none;'></form>").attr({
                        action: url
                    }).appendTo(document.body);
                        $('<input type="hidden" />').attr({
                            name: 'deleted_payments',
                            value: JSON.stringify(payments)
                        }).appendTo(f);
                    f.submit();
                    f.remove();
                })
                .catch((error) => {
                    console.log(error);
                })
                
        // var url = base_url + 'excel/finance_deleted_or_invoice/' + $(
        //         "#select-term-leads").val() + '/' + $("#select-report-type").val() +
        //     '/' + campus + '/' + $("#date-picker").val();
        // window.open(url, '_blank');
    })
});
</script>