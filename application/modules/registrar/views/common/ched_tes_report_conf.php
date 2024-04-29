<script type="text/javascript">
$(document).ready(function() {

    let table = $('#tes-report-table').DataTable({
        searching: false,
        info: false,
        ordering: false,
        paging: false,
        scrollX: true,
        columns: [{
                data: 'index',
            },
            {
                data: 'strStudentNumber',
            },
            {
                data: 'strLastname',
            },
            {
                data: 'strFirstname',
            },
            {
                data: 'nameExtension',
            },
            {
                data: 'strMiddlename',
            },

            {
                data: 'enumGender',

            },
            {
                data: 'dteBirthDate',
                render: (data, type, row) => {
                    return new Date(data).toLocaleDateString('en-US', {
                        month: '2-digit',
                        day: '2-digit',
                        year: 'numeric'
                    });
                },
                targets: 0

            },
            {
                data: 'course',

            },
            {
                data: 'intStudentYear',

            },

            {
                data: 'fatherLastName',

            },
            {
                data: 'fatherFirstName',

            },
            {
                data: 'fatherMiddleName',
            },

            {
                data: 'motherLastName',

            },
            {
                data: 'motherFirstName',

            },
            {
                data: 'motherMiddleName',
            },
            {
                data: 'strAddress',
            },
            {
                data: 'strZipCode',
            },
            {
                data: 'disability',
            },
            {
                data: 'strMobileNumber',
            },
            {
                data: 'strEmail',
            },
            {
                data: 'group',
            }
        ],
    });

    $.get('<?php echo base_url(); ?>' + 'registrar/ched_tes_report_data/' +
        '<?php echo $current_sem; ?>',
        function(
            json) {
            let chedData = JSON.parse(json).data
            chedData.forEach((student, index) => {
                table.row.add({
                    index: student.index,
                    strStudentNumber: student.strStudentNumber,
                    strFirstname: student.strFirstname,
                    strMiddlename: student.strMiddlename,
                    strLastname: student.strLastname,
                    nameExtension: student.nameExtension,
                    enumGender: student.enumGender,
                    dteBirthDate: student.dteBirthDate,
                    course: student.course.strProgramDescription,
                    intStudentYear: student.intStudentYear,
                    fatherLastName: student.fatherLastName,
                    fatherFirstName: student.fatherFirstName,
                    fatherMiddleName: student.fatherMiddleName,
                    motherLastName: student.motherLastName,
                    motherFirstName: student.motherFirstName,
                    motherMiddleName: student.motherMiddleName,
                    strAddress: student.strAddress,
                    strZipCode: student.strZipCode,
                    disability: '',
                    strMobileNumber: student.strMobileNumber,
                    strEmail: student.strEmail,
                    group: '',

                }).draw();

            });
        });

    $("#select-term-leads").on('change', function(e) {
        const term = $(this).val();
        document.location = "<?php echo base_url()."registrar/ched_tes_report/"; ?>" + term;
    });


    $("#ched_tes_report_excel").click(function(e) {
        var campus = "<?php echo $campus;?>";
        var base_url = "<?php echo base_url(); ?>";
        var url = base_url + 'excel/ched_tes_report/' + $("#select-term-leads").val() + '/' + campus;
        window.open(url, '_blank');
    })

    $("#ched_tes_report_pdf").click(function(e) {
        var campus = "<?php echo $campus;?>";
        var base_url = "<?php echo base_url(); ?>";
        var url = base_url + 'pdf/ched_tes_report/' + $("#select-term-leads").val() + '/' + campus;
        window.open(url, '_blank');
    })
});
</script>