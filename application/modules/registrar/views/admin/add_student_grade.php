<aside class="right-side" id="container">
    <section class="content-header">
        <h1>
            Student Grades
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Student</a></li>
            <li class="active">Upload Student Grades</li>
        </ol>
    </section>
    <div class="content">
        <div id="add-student" class="span10 box box-primary">
        <!-- <form action="<?php echo base_url(); ?>excel/import_student_grades" method="post" role="form" enctype="multipart/form-data">    -->
            <div class="box-body">
                <div class="row">                     
                    <div class="form-group col-sm-4">
                        <label for="sem">Select Term:</label>
                        <select id="sem" name="sem" class="form-control select2">
                            <?php foreach($sy as $s): ?>
                            <option <?php echo ($current_sem == $s['intID'])?'selected':''; ?>
                            value="<?php echo $s['intID']; ?>">
                            <?php echo $s['term_student_type']." ".$s['enumSem']." ".$s['term_label']." ".$s['strYearStart']."-".$s['strYearEnd']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-sm-4">
                        <select id="term" class="form-control select2">
                            <option value="Midterm">Midterm</option>
                            <option value="Final">Final</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-sm-4">
                        <!-- <input type="file" name="studentGradeExcel" size="20" /> -->
                        <input @change="attachFile" type="file" name="student_grade_excel" id="student_data_excel" size="20" />
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-sm-4">
                        <!-- <input type="submit" value="Import" class="btn btn-lg btn-default  btn-flat"> -->
                        <button class="btn btn-app" @click="importStudentGrade" >Import</button>
                    </div>
                </div>
            </div>
        <!-- </form> -->
        </div>
</aside>

<script>
new Vue({
    el: '#container',
    data: {
        base_url : "<?php echo base_url(); ?>",
        campus : "<?php echo $campus;?>",
        report_date: null,
        sem : null,
        term: null,
        sy_reference : null,
        students : null,
        attachment : null,
    },
    methods: {

        attachFile($event) {
            this.attachment = $event.target.files[0]
        },
        
        // async importStudentGrade() {
        //     const formData = new FormData()

        //     // formData.append('student_data_excel', this.attachment)
        //     // formData.append('student_level', $("#studentLevel").val())
        //     // formData.append('campus', this.campus)
        //     // formData.append('active_sem', this.activeSem)

        //     const {
        //         data
        //     } = await axios
        //         // .post(`${base_url}excel/import_student_grades/' + this.sem + '/' + this.term`, formData, {
        //         .post(this.base_url + 'excel/import_student_grades/' + this.sem + '/' + this.term, formData, {
        //             headers: {
        //                 Authorization: `Bearer ${window.token}`
        //             }
        //         })

        //     if (data.success) {
        //         $.ajax({
        //             'url':'<?php echo base_url(); ?>excel/import_student_data',
        //             'method':'post',
        //             'data':{
        //                 'student_level': $("#studentLevel").val(),
        //                 'data':data.data
        //             },
        //             'dataType':'json'
        //         });
                
        //         Swal.fire({
        //             showCancelButton: false,
        //             showCloseButton: true,
        //             allowEscapeKey: false,
        //             title: 'Successfully Import ',
        //             // text: 'Field Updated',
        //             icon: 'success',
        //         });
        //     } else {
        //         Swal.fire({
        //             showCancelButton: false,
        //             showCloseButton: true,
        //             allowEscapeKey: false,
        //             title: `${data.message}`,
        //             text: 'Error',
        //             icon: 'error',
        //         });
        //     }
        // },

        // importStudentGrade: function(){
        async importStudentGrade() {
            this.report_date = $("#report_date").val();
            this.sem = $("#sem").val();
            this.term = $("#term").val();

            const formData = new FormData();

            formData.append('student_grade_excel', this.attachment)

            Swal.showLoading();
            const {
                data
            } = await axios
                .post('<?php echo base_url(); ?>excel/import_student_grades/' + this.sem + '/' + this.term, formData, {
                })
                .then(data => {
                    Swal.hideLoading();
                    console.log(data);
                    if (data) {
                        Swal.fire({
                            showCancelButton: false,
                            showCloseButton: true,
                            allowEscapeKey: false,
                            title: 'Successfully Import',
                            icon: 'success',
                        });
                        $("#student_grade_excel").val('');
                    }else {
                        Swal.fire({
                            showCancelButton: false,
                            showCloseButton: true,
                            allowEscapeKey: false,
                            title: 'Import failed',
                            messge: data,
                            icon: 'error',
                        });
                    }
                });
            



            //     $.ajax({
            //         'url':'<?php echo base_url(); ?>excel/import_student_grades/' + this.sem + '/' + this.term,
            //         'method':'post',
            //         'data':{
            //             'student_grade_excel':formData
            //         },
            //         'dataType':'json'
            //     });
                
            //     Swal.fire({
            //         showCancelButton: false,
            //         showCloseButton: true,
            //         allowEscapeKey: false,
            //         title: 'Successfully Import ',
            //         text: 'Field Updated',
            //         icon: 'success',
            //     });
            //     $("#student_grade_excel").val('');
            // } else {
            //     Swal.fire({
            //         showCancelButton: false,
            //         showCloseButton: true,
            //         allowEscapeKey: false,
            //         title: `${data.message}`,
            //         text: 'Error',
            //         icon: 'error',
            //     });
            // }
        },


            // // if(this.report_date == ""){
            // //     alert("Please select report date");
            // // }else{
            //     // axios.get('https://smsapi.iacademy.edu.ph/api/v1/sms/admissions/student-info/view-students/' + this.sem)
            //     // .then((data) => {
                    
            //         // let url = this.base_url + 'excel/student_account_report/' + this.sem + '/' + this.campus + '/' + this.report_date;
            //         let url = this.base_url + 'excel/import_student_grades/' + this.sem + '/' + this.term;

            //         var f = $("<form target='_blank' method='POST' style='display:none;'></form>").attr({
            //             action: url
            //         }).appendTo(document.body);
            //             // $('<input type="hidden" />').attr({
            //             //     name: 'ar_students',
            //             //     value: JSON.stringify(this.students)
            //             // }).appendTo(f);
            //         f.submit();
            //         f.remove();
            //     // })
            //     // .catch((error) => {
            //     //     console.log(error);
            //     // })
            // // }
        // }
    }

})
</script>