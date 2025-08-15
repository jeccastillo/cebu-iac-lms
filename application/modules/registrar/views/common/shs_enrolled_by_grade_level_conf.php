<script type="text/javascript">
let noYearLevel = 0;
$(document).ready(function() {
    $('#shs-enrolled-table').DataTable({
        "aLengthMenu": [10, 20, 50, 100, 250, 500, 750, 1000],
        "bProcessing": true,
        "bServerSide": false,
        "ordering": false,
        "paging": false,
        "searching": false,
        "info": false,
        ajax: {
            url: "<?php echo base_url(); ?>registrar/shs_enrolled_by_grade_level_data/<?php echo $current_sem; ?>",
            dataSrc: function(json) {
                noYearLevel = json.no_grade_level;
                return json.enrollment;
            }
        },
        columns: [{
            data: 'strProgramDescription',
            title: 'Course'
        }, {
            data: 'grade11',
            title: 'Grade 11'
        }, {
            data: 'grade12',
            title: 'Grade 12'
        }, {
            data: null,
            title: 'Total',
            render: function(data, type, row) {
                let g11 = parseInt(row.grade11) || 0;
                let g12 = parseInt(row.grade12) || 0;
                return g11 + g12;
            }
        }],
        drawCallback: function() {
            let api = this.api();
            $('#custom-total-row').remove();
            let totalGrade11 = api.column(1).data().reduce((a, b) => a + (
                parseInt(b) || 0), 0);
            let totalGrade12 = api.column(2).data().reduce((a, b) => a + (
                parseInt(b) || 0), 0);
            let totalOverall = totalGrade11 + totalGrade12;
            $(api.table().body()).append(`
            <tr id="custom-total-row" style="font-weight:bold; background:#f5f5f5;">
                <th>TOTAL</th>
                <td>${totalGrade11}</td>
                <td>${totalGrade12}</td>
                <td>${totalOverall}</td>
            </tr>
            <tr id="custom-total-row" style="font-weight:bold; background:#f5f5f5;">
                <th>GRAND TOTAL</th>
                <td>${totalGrade11}</td>
                <td>${totalGrade12}</td>
                <td>${totalOverall}</td>
            </tr>
            <tr id="custom-total-row" style="font-weight:bold; background:#f5f5f5;">                
                <td>NO YEAR LEVEL</td>
                <td></td>
                <td></td>
                <td>${noYearLevel}</td>
            </tr>
        `);
        }
    });
});
$("#select-term-leads").on('change', function(e) {
    let campus = "<?php echo $campus;?>";
    const term = $(this).val();
    document.location =
        "<?php echo base_url()."registrar/shs_enrolled_by_grade_level/"; ?>" + term;
});
$("#int-year-level").on('change', function(e) {
    let campus = "<?php echo $campus;?>";
    const level = $(this).val();
    document.location = "<?php echo base_url()."registrar/shs_student_grades/"; ?>" + $(
        "#select-term-leads").val() + '/' + level;
});
$(document).ready(function() {
    $("#shs_enrolled_excel").click(function(e) {
        var campus = "<?php echo $campus;?>";
        var base_url = "<?php echo base_url(); ?>";
        var url = base_url + 'excel/shs_enrolled_by_grade_level/' + $(
            "#select-term-leads").val();
        window.open(url, '_blank');
    })
    $("#shs_enrolled_pdf").click(function(e) {
        var campus = "<?php echo $campus;?>";
        var base_url = "<?php echo base_url(); ?>";
        var url = base_url + 'pdf/registrar_shs_enrolled_by_grade_level/' + $(
            "#select-term-leads").val();
        window.open(url, '_blank');
    })
});
</script>