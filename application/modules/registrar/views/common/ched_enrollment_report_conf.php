<script type="text/javascript">
$(document).ready(function() {
    let table = $('#enrollment-report-table').DataTable({
        searching: false,
        info: false,
        ordering: false,
        paging: false,
        columns: [{
                data: 'index',
                title: 'No.'
            },
            {
                data: 'program',
                title: 'Program',

            },
            {
                data: 'major',
                title: 'Major',

            },
            {
                data: 'strStudentNumber',
                title: 'Student Number',

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
                data: 'strLastname',
                title: 'SurName'
            },

            {
                data: 'nameExtension',
                title: 'Name Extension',

            },
            {
                data: 'strCitizenship',
                title: 'Citizenship',

            },
            {
                data: 'enumGender',
                title: 'Gender',

            },
            {
                data: 'intStudentYear',
                title: 'Year Level',

            },

            {
                data: 'subjectsEnrolled',
                title: 'SUBJECTS ENROLLED FOLLOWED BY UNITS',

            },
            {
                data: 'totalUnits',
                title: 'NO. OF UNITS',

            },
            {
                data: 'remarks',
                title: 'REMARKS (if any)',
            }


        ],
    });

    $.get('<?php echo base_url(); ?>' + 'registrar/ched_enrollment_report_data/' +
        '<?php echo $current_sem; ?>',
        function(
            json) {
            let chedData = JSON.parse(json).data
            chedData.forEach((student, index) => {
                table.row.add({
                    index: student.index,
                    program: student.course.strProgramDescription,
                    major: '',
                    strStudentNumber: student.strStudentNumber,
                    strFirstname: student.strFirstname,
                    strMiddlename: student.strMiddlename,
                    strLastname: student.strLastname,
                    nameExtension: student.nameExtension,
                    strCitizenship: student.strCitizenship,
                    enumGender: student.enumGender,
                    intStudentYear: student.intStudentYear,
                    subjectsEnrolled: student.subjectsEnrolled,
                    totalUnits: student.totalUnits,
                    remarks: ''
                }).draw();

            });
        });

});

$("#select-term-leads").on('change', function(e) {
    const term = $(this).val();
    document.location = "<?php echo base_url()."registrar/ched_enrollment_report/"; ?>" + term;
});

$(document).ready(function() {
    $("#ched_enrollment_report_excel").click(function(e) {
        var campus = "<?php echo $campus;?>";
        var base_url = "<?php echo base_url(); ?>";
        var url = base_url + 'excel/ched_enrollment_report/' + $("#select-term-leads").val() + '/' +
            campus;
        window.open(url, '_blank');
    })

    $("#ched_enrollment_report_pdf").click(function(e) {
        var campus = "<?php echo $campus;?>";
        var base_url = "<?php echo base_url(); ?>";
        var url = base_url + 'pdf/ched_enrollment_report/' + $("#select-term-leads").val() + '/' +
            campus;
        window.open(url, '_blank');
    })
});
</script>