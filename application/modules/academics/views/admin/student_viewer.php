
<aside class="right-side">
    <div id="student-viewer-container">        
        <section class="content-header">
            <h1>
                <small>                    
                    <a class="btn btn-app" :href="base_url + 'academics/view_all_students'" ><i class="ion ion-arrow-left-a"></i>All Students</a>                                         
                    <a class="btn btn-app" :href="base_url + 'academics/student_records/' + student.intID"><i class="fa fa-user"></i>Records</a> 
                    <!-- <a v-if="user_level == 2 || user_level == 3" target="_blank" v-if="registration" class="btn btn-app" :href="base_url + 'pdf/student_viewer_registration_print/' + student.intID +'/'+ applicant_data.id +'/'+ active_sem.intID">
                        <i class="ion ion-printer"></i>RF Print
                    </a>                      -->                   
                    <a  class="btn btn-app" :href="base_url + 'deficiencies/student_deficiencies/' + student.intID">
                        <i class="fa fa-user"></i>Deficiencies
                    </a> 
                    
                                                         
                </small>
                
                <div class="box-tools pull-right">
                    <select v-model="sem_student" @change="changeTermSelected" class="form-control" >
                        <option v-for="s in sy" :value="s.intID">{{s.term_student_type + ' ' + s.enumSem + ' ' + s.term_label + ' ' + s.strYearStart + '-' + s.strYearEnd}}</option>                      
                    </select>                   
                </div>
                <div style="clear:both"></div>
            </h1>
        </section>
        <hr />
        <div class="content">
            <div class="row">
                <div class="col-sm-12">
                    <div v-if="student" class="box box-widget widget-user-2">
                        <!-- Add the bg color to the header using any of the bg-* classes -->
                        <div class="widget-user-header bg-red">
                            <!-- /.widget-user-image -->
                            <h3 class="widget-user-username" style="text-transform:capitalize;margin-left:0;font-size:1.3em;">{{ student.strLastname.toUpperCase() }}, {{ student.strFirstname.toUpperCase() }} {{ student.strMiddlename?student.strMiddlename.toUpperCase():'' }}</h3>
                            <h5 class="widget-user-desc" style="margin-left:0;">{{ student.strProgramDescription }} {{ (student.strMajor != 'None')?'Major in '+student.strMajor:'' }}</h5>
                        </div>
                        <div class="box-footer no-padding">
                            <ul class="nav nav-stacked">
                            <li><a href="#" style="font-size:13px;">Student Number <span class="pull-right text-blue">{{ student.strStudentNumber.replace(/-/g, '') }}</span></a></li>
                            <li><a href="#" style="font-size:13px;">Status <span class="pull-right text-blue">{{ student.student_status.toUpperCase() }}</span></a></li>                            
                            <li><a href="#" style="font-size:13px;">Curriculum <span class="pull-right text-blue">{{ student.strName }}</span></a></li>                            
                            <li><a style="font-size:13px;" href="#">Registration Status <span class="pull-right">{{ reg_status }}</span></a></li>
                            <li><a @click.prevent="resetStatus()" href="#"><i class="ion ion-android-close"></i> Reset Status</a> </li>
                            <li>
                                <a style="font-size:13px;" href="#">Date Registered <span class="pull-right">
                                    <span style="color:#009000" v-if="registration" >{{ registration.date_enlisted }}</span>
                                    <span style="color:#900000;" v-else>N/A</span>                                
                                </a>
                            </li>                                                
                            <li v-if="registration"><a style="font-size:13px;" href="#">Scholarship <span class="pull-right">{{ scholarship.name }}</span></a></li>
                            <li v-if="registration"><a style="font-size:13px;" href="#">Discount <span class="pull-right">{{ discount.name }}</span></a></li>
                                
                            </ul>
                        </div>
                    </div>                
                </div>                                                            
            </div>
        </div>
    </div>
</div>
</aside>

<style>
    .green-bg
    {
        background-color:#77cc77;
    }
    .red-bg
    {
        background-color:#cc7777;
    }
    .select2-container
    {
        display: block !important;
    }
</style>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script>
new Vue({
    el: '#student-viewer-container',
    data: {
        id: '<?php echo $id; ?>', 
        tab: '<?php echo $tab; ?>',                          
        sem: '<?php echo $sem; ?>',
        student: {
            strFirstname: 'Firstname',
            strLastname: 'Lastname',
            strMiddlename: 'Middlename',
            strStudentNumber: '0',
        },
        scholarship:{
            name:'none'
        },
        discount:{
            name:'none'
        },
        user_level: undefined,
        registration: undefined,
        applicant_data:{},
        active_sem: {},
        sections: [],
        balance: 0,
        records: [],
        other_data: undefined,
        reg_status: '',
        deficiencies:[],
        sy: undefined,
        term_type: undefined,
        sem_student: undefined,
        prev_year_sem: 0,
        add_subject:{
            code: undefined,
            section:undefined,
            studentID: undefined,
            activeSem: undefined,
        },        
        advanced_privilages1: false,
        advanced_privilages2: false,
        registrar_privilages: false,
        photo_dir: undefined,
        img_dir: undefined,  
        grad_status: 0,      
        selected_ay: undefined,
        base_url: '<?php echo base_url(); ?>',   
        registration_status: 0,                   
        loader_spinner: true,  
        change_grade: [],         
        total_units: 0,
        picture: undefined,
        lab_units: 0,    
        gpa: 0,        
        assessment: '',   
        deficency_msg: '',          
    },

    mounted() {

        let url_string = window.location.href;                
        if(this.id != 0){            
            //this.loader_spinner = true;
            axios.get(this.base_url + 'unity/student_viewer_data/' + this.id + '/' + this.sem )
                .then((data) => {  
                    console.log(data);
                    if(data.data.success){                                                                                                                   
                        this.student = data.data.student;                        
                        if(data.data.scholarship.length > 0){
                            var sch = "";
                            for(i in data.data.scholarship)
                                sch += data.data.scholarship[i].name+" ";
                            this.scholarship = {name:sch};
                        }
                        else{
                            this.scholarship = {name:'none'};
                        } 
                        if(data.data.discount.length > 0){
                            var sch = "";
                            for(i in data.data.discount)
                                sch += data.data.discount[i].name+" ";
                            this.discount = {name:sch};
                        }
                        else{
                            this.discount = {name:'none'};
                        }                               
                        this.change_grade = data.data.change_grade;
                        this.deficiencies = data.data.deficiencies;
                        this.balance = data.data.balancel;
                        this.user_level = data.data.user_level;
                        this.registration = data.data.registration;                        
                        this.registration_status = data.data.registration ? data.data.registration.intROG : 0;                        
                        this.active_sem = data.data.active_sem;
                        this.reg_status = data.data.reg_status;
                        this.selected_ay = data.data.selected_ay;
                        this.curriculum_subjects = data.data.curriculum_subjects;                        
                        this.sections = data.data.sections;
                        
                        if(this.sections)
                            this.add_subject.section = ( this.sections.length > 0 ) ? this.sections[0].intID : null;

                        this.add_subject.subject = ( this.curriculum_subjects.length > 0 ) ? this.curriculum_subjects[0].intSubjectID : null;
                        this.add_subject.studentID = this.id;
                        this.add_subject.activeSem = this.selected_ay;
                        this.advanced_privilages1 = data.data.advanced_privilages1;
                        this.advanced_privilages2 = data.data.advanced_privilages2;                        
                        this.sy = data.data.sy;
                        this.term_type = data.data.term_type;
                        this.photo_dir = data.data.photo_dir;
                        this.img_dir = data.data.img_dir;
                        this.sem_student = this.selected_ay;
                        this.registrar_privilages =  data.data.registrar_privilages;        
                        this.grad_status = this.student.isGraduate;     
                        this.records = data.data.records;           
                        this.total_units = data.data.total_units;
                        this.lab_units = data.data.lab_units;
                        this.gpa = data.data.gpa;
                        this.other_data = data.data.other_data;   
                        var sched = data.data.schedule;                        
                        axios.get(api_url + 'admissions/student-info/' + this.student.slug)
                        .then((data) => {
                            this.applicant_data = data.data.data;
                            for(i in this.applicant_data.uploaded_requirements){
                                 if(this.applicant_data.uploaded_requirements[i].type == "2x2" || this.applicant_data.uploaded_requirements[i].type == "2x2_foreign")
                                    this.picture = this.applicant_data.uploaded_requirements[i].path;
                            }
                        })
                        .catch((error) => {
                            console.log(error);
                        })

                        setTimeout(function() {
                            // function code goes here
                            load_schedule(sched);
                        }, 1000);
                                            
                    }
                    else{
                       //document.location = this.base_url + 'users/login';
                    }

                    this.loader_spinner = false;                    
                })
                .catch((error) => {
                    console.log(error);
                })
        }

    },

    methods: {  
        resetStatus: function(){
            let reset_url = base_url + 'unity/delete_registration/' + this.student.intID + '/' + this.active_sem.intID;
            Swal.fire({
                title: 'Reset Registration?',
                text: "Continue with reset?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                preConfirm: (login) => {
                    document.location = reset_url;
                }
            });
        },
        removeFromClasslist: function(classlistID){
            Swal.fire({
                title: 'Delete Entry?',
                text: "Continue deleting entry?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                preConfirm: (login) => {
                    var formdata= new FormData();
                    formdata.append("intCSID",classlistID);                                                            
                    return axios
                        .post('<?php echo base_url(); ?>unity/delete_student_cs',formdata, {
                                headers: {
                                    Authorization: `Bearer ${window.token}`
                                }
                            })
                        .then(data => {
                            console.log(data.data);
                            if (data.data.success) {
                                Swal.fire({
                                    title: "Success",
                                    text: data.data.message,
                                    icon: "success"
                                }).then(function() {
                                    location.reload();
                                });
                            } else {
                                Swal.fire(
                                    'Failed!',
                                    data.data.message,
                                    'error'
                                )
                            }
                        });
                },
                allowOutsideClick: () => !Swal.isLoading()
            });

        },
        submitSubject: function(){
            if(add_subject.section){            
                var formdata= new FormData();
                for (const [key, value] of Object.entries(this.add_subject)) {
                    formdata.append(key,value);
                }                                                    

                this.loader_spinner = true;
                axios.post(base_url + 'unity/add_to_classlist_ajax', formdata, {
                    headers: {
                        Authorization: `Bearer ${window.token}`
                    }
                })
                .then(data => {
                    this.loader_spinner = false;
                    Swal.fire({
                        title: "Success",
                        text: data.data.message,
                        icon: "success"
                    }).then(function() {
                        
                    });
                });
            }
            else
                Swal.fire({
                    title: "Failed",
                    text: 'Incomplete Data',
                    icon: "success"
                });

        },
        printRF: function(){
            var url = base_url + 'pdf/student_viewer_registration_print/' + this.student.intID +'/'+ this.applicant_data.id +'/'+ this.active_sem.intID + '/35';
            if(this.deficiencies.length > 0 || this.balance > 0){                                
                Swal.fire({
                    title: 'Warning',
                    text: "This student has active deficiencies",
                    showCancelButton: true,
                    confirmButtonText: "Continue Printing Anyway?",
                    imageWidth: 100,
                    icon: "question",
                    cancelButtonText: "No, cancel!",
                    showCloseButton: true,
                    showLoaderOnConfirm: true,
                    footer: '<a target="_blank" href="'+base_url + 'deficiencies/student_deficiencies/' + this.student.intID+'">View Deficiencies</a>',
                    preConfirm: (login) => {  
                        document.location = url;
                    }
                });           
            }
            else
                window.open(
                    url,
                    '_blank' // <- This is what makes it open in a new window.
                );
            
        },
        updateGradStatus: function(){
            
            var formdata= new FormData();
            formdata.append("intID",this.student.intID);
            formdata.append("isGraduate",this.grad_status);

            this.loader_spinner = true;
            axios.post(base_url + 'unity/update_graduate_status', formdata, {
                headers: {
                    Authorization: `Bearer ${window.token}`
                }
            })
            .then(data => {
                this.loader_spinner = false;
                Swal.fire({
                    title: "Success",
                    text: data.data.message,
                    icon: "success"
                }).then(function() {
                    
                });
            });
                                
        },
        changeTermSelected: function(){
            document.location = this.base_url + "unity/student_viewer/" + 
            this.student.intID + "/" + this.sem_student + "/" + this.tab;
        },          
                 
    }

})
</script>