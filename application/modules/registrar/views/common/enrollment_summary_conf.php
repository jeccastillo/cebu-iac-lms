<script type="text/javascript">
let noYearLevel = 0;
$(document).ready(function() {
    $.getJSON(
        "<?php echo base_url(); ?>registrar/enrollment_summary_by_student_number_data/<?php echo $current_sem; ?>",
        function(json) {
            console.log(json);
            let columns = [{
                data: 'strProgramDescription',
                title: 'Program Name'
            }];
            json.student_years.forEach(year => {
                columns.push({
                    data: `years.${year}`,
                    title: year
                });
            });
            columns.push({
                data: null,
                title: 'Total',
                render: function(data, type, row) {
                    let sum = 0;
                    json.student_years.forEach(year => {
                        sum += parseInt(row.years[year]) || 0;
                    });
                    return sum;
                }
            });
            $('#enrollment-summary-table').DataTable({
                "aLengthMenu": [10, 20, 50, 100, 250, 500, 750, 1000],
                "bProcessing": true,
                "bServerSide": false,
                "ordering": false,
                "paging": false,
                "searching": false,
                "info": false,
                "data": json.enrollment,
                "columns": columns,
                "drawCallback": function(settings) {
                    let api = this.api();
                    $('#enrollment-summary-table tbody tr.total-row')
                        .remove();
                    let totals = [];
                    columns.forEach((col, index) => {
                        if (index === 0) {
                            totals.push('<strong>Total</strong>');
                        } else if (index < columns.length - 1) {
                            let total = api.column(index, {
                                page: 'current'
                            }).data().reduce((a, b) => (
                                parseInt(a) || 0) + (
                                parseInt(b) || 0), 0);
                            totals.push('<strong>' + total +
                                '</strong>');
                        } else {
                            let grandTotal = 0;
                            json.student_years.forEach(year => {
                                let colIndex = columns
                                    .findIndex(c => c
                                        .title == year);
                                let yearTotal = api.column(
                                    colIndex, {
                                        page: 'current'
                                    }).data().reduce((a,
                                    b) => (parseInt(
                                    a) || 0) + (
                                    parseInt(b) || 0
                                    ), 0);
                                grandTotal += yearTotal;
                            });
                            totals.push('<strong>' + grandTotal +
                                '</strong>');
                        }
                    });
                    $('#enrollment-summary-table tbody').append(
                        '<tr class="total-row bg-gray-100">' + totals
                        .map(val => `<td>${val}</td>`).join('') +
                        '</tr>');
                }
            });
        });
});
$("#select-term-leads").on('change', function(e) {
    let campus = "<?php echo $campus;?>";
    const term = $(this).val();
    document.location =
        "<?php echo base_url()."registrar/enrollment_summary_by_student_number/"; ?>" +
        term;
});
// $("#int-year-level").on('change', function(e) {
//     let campus = "<?php echo $campus;?>";
//     const level = $(this).val();
//     document.location = "<?php echo base_url()."registrar/shs_student_grades/"; ?>" + $(
//         "#select-term-leads").val() + '/' + level;
// });
$(document).ready(function() {
    $("#enrollment_summary_excel").click(function(e) {
        var campus = "<?php echo $campus;?>";
        var base_url = "<?php echo base_url(); ?>";
        var url = base_url + 'excel/enrollment_summary_by_student_number/' + $(
            "#select-term-leads").val();
        window.open(url, '_blank');
    })
    $("#enrollment_summary_pdf").click(function(e) {
        var campus = "<?php echo $campus;?>";
        var base_url = "<?php echo base_url(); ?>";
        var url = base_url + 'pdf/enrollment_summary_by_student_number/' + $(
            "#select-term-leads").val();
        window.open(url, '_blank');
    })
});
</script>