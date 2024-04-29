<script type="text/javascript">
$(document).ready(function() {
    let table = $('#nstp-report-table').DataTable({
        searching: false,
        info: false,
        ordering: false,
        paging: false,
        columns: [{
                data: 'index',
                title: 'No.'
            },
            {
                data: 'strStudentNumber',
                title: 'Student No.',

            },
            {
                data: 'strLastname',
                title: 'Surname',

            },
            {
                data: 'strFirstname',
                title: 'First Name',

            },

            {
                data: 'strMiddlename',
                title: 'Middle Name',

            },
            {
                data: 'course',
                title: 'Course/Program',

            },
            {
                data: 'enumGender',
                title: 'Gender'
            },
            {
                data: 'dteBirthDate',
                title: 'Birthdate',
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
                data: 'strAddress',
                title: 'Street/Barangay Address',

            },
            {
                data: 'city',
                title: 'Town/City Address',

            },
            {
                data: 'province',
                title: 'Provincial Address',

            },
            {
                data: 'strMobileNumber',
                title: 'Contact Number Telephone/Mobile',

            },
            {
                data: 'strEmail',
                title: 'Email address',
            }
        ],
    });

    $.get('<?php echo base_url(); ?>' + 'registrar/ched_nstp_report_data/' +
        '<?php echo $current_sem; ?>',
        function(
            json) {
            let chedData = JSON.parse(json).data
            chedData.forEach((student, index) => {
                table.row.add({
                    index: student.index,
                    strStudentNumber: student.strStudentNumber,
                    strFirstname: student.strFirstname,
                    strLastname: student.strLastname,
                    strMiddlename: student.strMiddlename,
                    course: student.course.strProgramDescription,
                    enumGender: student.enumGender,
                    dteBirthDate: student.dteBirthDate,
                    strAddress: student.strAddress,
                    city: student.city,
                    province: student.province,
                    strMobileNumber: student.strMobileNumber,
                    strEmail: student.strEmail
                }).draw();

            });
        });

});

$("#select-term-leads").on('change', function(e) {
    const term = $(this).val();
    document.location = "<?php echo base_url()."registrar/nstp_report/"; ?>" + term;
});

$(document).ready(function() {
    $("#ched_nstp_report_excel").click(function(e) {
        var campus = "<?php echo $campus;?>";
        var base_url = "<?php echo base_url(); ?>";
        var url = base_url + 'excel/ched_nstp_report/' + $("#select-term-leads").val() + '/' + campus;
        window.open(url, '_blank');
    })

    $("#ched_nstp_report_pdf").click(function(e) {
        var campus = "<?php echo $campus;?>";
        var base_url = "<?php echo base_url(); ?>";
        var url = base_url + 'pdf/ched_nstp_report/' + $("#select-term-leads").val() + '/' + campus;
        window.open(url, '_blank');
    })
});
</script>