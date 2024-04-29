<?php $d_open = '<div class="btn-group"><button type="button" class="btn btn-default">Actions</button><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span><span class="sr-only">Toggle Dropdown</span></button><ul class="dropdown-menu" role="menu">';
?>
<script type="text/javascript">
$(document).ready(function() {
    let table = $('#users-table').DataTable({
        searching: false,
        info: false,
        ordering: false,
        paging: false,
        columns: [{
                data: 'studentNo',
                title: 'Student  No.'
            },
            {
                data: 'lastName',
                title: 'Last Name'
            },
            {
                data: 'firstName',
                title: 'First Name',

            },
            {
                data: 'middleName',
                title: 'Middle Name',

            },
            {
                data: 'gender',
                title: 'Gender',

            },
            {
                data: 'course',
                title: 'Course',

            },
            {
                data: 'studentYear',
                title: 'year',

            },
            {
                data: 'subjects',
                title: 'Subjects',
            },
            {
                data: 'subjectDesc',
                title: 'Subject Description',

            },
            {
                data: 'unit',
                title: 'Unit',

            },
            {
                data: 'mg',
                title: 'MG',

            },
            {
                data: 'fg',
                title: 'FG',

            }


        ],
    });

    $.get('<?php echo base_url(); ?>' + 'registrar/ched_report/' + '<?php echo $current_sem; ?>', function(
        json) {
        let chedData = JSON.parse(json).data
        chedData.forEach((student, index) => {
            table.row.add({
                studentNo: student.strStudentNumber,
                lastName: student.strLastname,
                firstName: student.strFirstname,
                middleName: student.strMiddlename,
                gender: student.enumGender,
                course: student.course,
                studentYear: student.intStudentYear,
                subjects: student.subjects[0].strCode,
                subjectDesc: student.subjects[0].strDescription,
                unit: student.subjects[0].strUnits,
                mg: student.subjects[0].floatMidtermGrade,
                fg: student.subjects[0].floatFinalGrade
            }).draw();
            if (student.subjects.length > 1) {
                for (let i = 1; i < student.subjects.length; i++) {
                    table.row.add({
                        studentNo: '',
                        lastName: '',
                        firstName: '',
                        middleName: '',
                        gender: '',
                        course: '',
                        studentYear: '',
                        subjects: student.subjects[i].strCode,
                        subjectDesc: student.subjects[i].strDescription,
                        unit: student.subjects[i].strUnits,
                        mg: student.subjects[i].floatMidtermGrade,
                        fg: student.subjects[i].floatFinalGrade
                    }).draw();
                }
            }
        });
    });
});

$("#select-term-leads").on('change', function(e) {
    const term = $(this).val();
    document.location = "<?php echo base_url()."registrar/promotional_report/"; ?>" + term;
});
</script>