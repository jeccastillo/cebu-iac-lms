<aside class="right-side" id="applicant-container">
    <section class="content-header">
            <h1>
            Student Account Report
            <small>
                <!-- <button class="btn btn-app" id="export_student_account_report" target="_blank" href="#" ><i class="fa fa-book"></i>Download Excel</button>  -->
                <button class="btn btn-app" @click="exportStudentAccountReport" ><i class="fa fa-book"></i>Download Excel</button>
            </small>
            
        </h1>                          
        <ol class="breadcrumb">
        </ol>
    </section>
    <div class="content">
        <div class="box">

            <div class="box-body" style="display: block;">
                <div class="row">                                            
                    <div class="col-sm-4">
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
                    <div class="col-sm-4">
                        <label for="report_date">As Of Date:</label>
                        <input required type="date" id="report_date" name="report_date" v-model="report_date" class="form-control" />                     
                    </div>
                </div>
            </div>
        </div>
    </div>
</aside>

<script>
new Vue({
    el: '#applicant-container',
    data: {
        base_url : "<?php echo base_url(); ?>",
        api_url : "<?php echo $this->config->item('api_url') ?>",
        campus : "<?php echo $campus;?>",
        report_date: null,
        sem : null,
        sy_reference : null,
        students : null,
    },

    mounted() {

        // let url_string = window.location.href;
        // let url = new URL(url_string);

        // this.loader_spinner = true;
        // axios.get(api_url + 'admissions/student-info/' + this.slug)
            // .then((data) => {
            //     this.request = data.data.data;
            //     this.sy_reference = this.request.sy_reference;
            //     this.loader_spinner = false;
            //     //this.program_update = this.request.type_id;
            //     axios.get(base_url + 'admissionsV1/programs/' + this.slug + '/' + this.sy_reference)    
            //         .then((data) => {
            //             this.current_term = data.data.current_term;
            //             this.request.applicant_id = "A" + this.current_term
            //                 .strYearStart + "-" + String(this.request.id).padStart(
            //                     4, '0');
            //             console.log(this.request.applicant_id);
            //             this.programs = data.data.programs;
            //             this.entrance_exam = data.data.entrance_exam;
            //             this.sections_scores = data.data.section_scores;

            //             this.status_update_manual = this.request.status;
            //             this.sy = data.data.sy;
            //             if (this.programs.length > 0)
            //                 this.filtered_programs = this.programs.filter((
            //                     prog) => {
            //                     return prog.type == this.request.type
            //                 })



            //             if (this.entrance_exam && this.entrance_exam.token) {
            //                 this.student_exam_link = this.base_url +
            //                     'unity/student_exam/' + this.slug +
            //                     '/' + this.entrance_exam.exam_id + '/' + this
            //                     .entrance_exam.token
            //             } else {
            //                 this.student_exam_link = this.base_url +
            //                     'unity/student_exam/' + this.slug +
            //                     '/' + this.entrance_exam.exam_id + '/submitted'
            //             }



            //         })
            //         .catch((error) => {
            //             console.log(error);
            //         })

            // })
            // .catch((error) => {
            //     console.log(error);
            // })

    },

    methods: {
        exportStudentAccountReport: function(){
            this.report_date = $("#report_date").val();
            this.sem = "<?php echo $current_sem; ?>";
            if(this.report_date == ""){
                alert("Please select report date");
            }else{
                axios.get(this.api_url + 'sms/admissions/student-info/view-students/' + this.sem)
                .then((data) => {
                    this.students = data.data.data;
                    let url = this.base_url + 'excel/student_account_report/' + this.sem + '/' + this.campus + '/' + this.report_date;
                    
                    var f = $("<form target='_blank' method='POST' style='display:none;'></form>").attr({
                        action: url
                    }).appendTo(document.body);
                        $('<input type="hidden" />').attr({
                            name: 'ar_students',
                            value: JSON.stringify(this.students)
                        }).appendTo(f);
                    f.submit();
                    f.remove();
                    window.open(url, '_blank');
                })
                .catch((error) => {
                    console.log(error);
                })
            }
        }
    }

})
</script>