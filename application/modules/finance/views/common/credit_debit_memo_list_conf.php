<script type="text/javascript">
$(document).ready(function() {
    $('#credit-debit-memo-table').DataTable({
        "aLengthMenu": [10, 20, 50, 100, 250, 500, 750, 1000],
        "bProcessing": true,
        "serverSide": false,
        "ordering": false,
        "paging": true,
        ajax: {
            url: "<?php echo base_url(); ?>finance/finance_credit_debit_memo_data/<?php echo $current_sem; ?>/<?php echo $date; ?>",
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
            data: 'date',
            title: 'Date'
        }, {
            data: 'added_by',
            title: 'Entered by.'
        }, {
            data: 'particular',
            title: 'OR/Invoice No.'
        }, {
            data: 'debit_memo',
            title: 'Debit Memo'
        }, {
            data: 'credit_memo',
            title: 'Credit Memo'
        }]
    });
});
$("#select-term-leads").on('change', function(e) {
    let term = $(this).val();
    let currentDate = "<?php echo $date; ?>";
    document.location = "<?php echo base_url()."finance/credit_debit_memo/"; ?>" + term +
        '/' + currentDate;
});
$("#date-picker").on('change', function(e) {
    let term = "<?php echo $current_sem; ?>";
    const currentDate = $(this).val();
    document.location = "<?php echo base_url()."finance/credit_debit_memo/"; ?>" + term +
        '/' + currentDate;
});
$(document).ready(function() {
    $("#deleted_or_invoice_list_excel").click(function(e) {
        var campus = "<?php echo $campus;?>";
        var base_url = "<?php echo base_url(); ?>";
        var url = base_url + 'excel/finance_credit_debit_memo_report/' + $(
                "#select-term-leads").val() + '/' + campus + '/' + $("#date-picker")
            .val();
        window.open(url, '_blank');
    })
});
</script>