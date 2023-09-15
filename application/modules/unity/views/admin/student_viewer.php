
<aside class="right-side">
    <div id="student-viewer-container">        
        <section class="content-header">
            <h1>
                <small>                    
                    <a class="btn btn-app" :href="base_url + 'student/view_all_students'" ><i class="ion ion-arrow-left-a"></i>All Students</a>                     
                    <a v-if="user_level == 2 || user_level == 3" class="btn btn-app" :href="base_url + 'student/edit_student/' + student.intID"><i class="ion ion-edit"></i> Edit</a>                     
                    <a v-if="user_level == 2 || user_level == 3" class="btn btn-app" target="_blank" :href="base_url + 'pdf/print_curriculum/' + student.intCurriculumID + '/' + student.intID"><i class="fa fa-print"></i>Curriculum Outline</a> 
                    <a v-if="user_level == 2 || user_level == 3" target="_blank" v-if="registration" class="btn btn-app" :href="base_url + 'pdf/student_viewer_registration_print/' + student.intID + '/' + active_sem.intID">
                        <i class="ion ion-printer"></i>RF Print
                    </a>                     
                    <a  target="_blank" v-if="registration && (user_level == 2 || user_level == 3)" class="btn btn-app" :href="base_url + 'pdf/student_viewer_registration_print/' + student.intID +'/'+ active_sem.intID">
                        <i class="ion ion-printer"></i>RF No Header
                    </a>                     
                    <a v-if="reg_status != 'For Subject Enlistment' && (user_level == 2 || user_level == 3)" target="_blank" class="btn btn-app" :href="base_url + 'pdf/student_viewer_advising_print/' + student.intID + '/' + active_sem.intID">
                        <i class="ion ion-printer"></i>Print Subjects
                    </a> 
                    <a v-else-if="user_level == 2 || user_level == 3" class="btn btn-app" :href="base_url + 'department/load_subjects/' + student.intID">
                        <i class="fa fa-book"></i>Subject Enlistment</a> 
                    </a>
                    <a v-if="reg_status == 'For Registration' && (user_level == 2 || user_level == 3)"  class="btn btn-app" :href="base_url + 'unity/edit_sections/' + student.intID + '/' + active_sem.intID">
                        <i class="fa fa-book"></i> Update Sections
                    </a>                        
                    <a v-if="reg_status =='For Registration' && (user_level == 2 || user_level == 3)" class="btn btn-app" :href="base_url + 'registrar/register_old_student2/' + student.intID">
                        <i class="fa fa-book"></i>Student Fee Assessment
                    </a>
                    <a v-if="user_level == 2 || user_level == 3" class="btn btn-app" :href="base_url + 'registrar/student_grade_slip/' + student.intID">
                        <i class="fa fa-book"></i>Grade Slip
                    </a>  
                    <a v-if="user_level == 2 || user_level == 7" class="btn btn-app" :href="base_url + 'scholarship/assign_scholarship/'+sem_student+'/'+ student.intID">
                        <i class="fa fa-book"></i>Scholarship/Discount
                    </a>                                       
                </small>
                
                <div class="box-tools pull-right">
                    <select v-model="sem_student" @change="changeTermSelected" class="form-control" >
                        <option v-for="s in sy" :value="s.intID">{{s.enumSem + ' ' + term_type + ' ' + s.strYearStart + '-' + s.strYearEnd}}</option>                      
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
                <div class="col-sm-12">
                    <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li :class="[(tab == 'tab_1') ? 'active' : '']"><a href="#tab_1" data-toggle="tab">Personal Information</a></li>
                        <li v-if="advanced_privilages1" :class="[(tab == 'tab_2') ? 'active' : '']"><a href="#tab_2" data-toggle="tab">Report of Grades</a></li>
                        <!-- <li v-if="advanced_privilages2" :class="[(tab == 'tab_3') ? 'active' : '']"><a href="#tab_3" data-toggle="tab">Assessment</a></li>                                         -->
                        <li v-if="registration && advanced_privilages2" :class="[(tab == 'tab_5') ? 'active' : '']"><a href="#tab_5" data-toggle="tab">Schedule</a></li>
                        <li v-if="advanced_privilages2"><a :href="base_url + 'unity/adjustments/' + student.intID + '/' + selected_ay">Adjustments</a></li>                        
                        <!-- <li v-if="registration && advanced_privilages2"><a :href="base_url + 'unity/edit_registration/' + student.intID + '/' + selected_ay">Edit Registration</a></li> -->
                        <!-- <li><a :href="base_url + 'unity/accounting/' + student.intID">Accounting Summary</a></li>                     -->
                    </ul>
                    <div class="tab-content">
                        <div :class="[(tab == 'tab_1') ? 'active' : '']" class="tab-pane" id="tab_1">
                            <div class="box box-primary">
                                <div class="box-body">
                                    <div class="row">
                                        <div class="col-lg-3 size-96">                                    
                                            <img v-if="!picture" :src="img_dir + 'default_image2.png'" class="img-responsive"/>
                                            <img v-else class="img-responsive" :src="picture" />                                    
                                        </div>
                                        <div class="col-lg-3">
                                            <p><strong>Student Number: </strong>{{ student.strStudentNumber.replace(/-/g, '') }}</p>
                                            <!-- <p><strong>Learner Reference Number(LRN): </strong>{{ student.strLRN'] }}</p> -->
                                            <p><strong>Block Section: </strong>{{ student.block ? student.block : 'Not yet selected' }}</p>
                                            <p><strong>Address: </strong>{{ student.strAddress }}</p>                                            
                                            <p><strong>Contact: </strong>{{ student.strMobileNumber }}</p>
                                            <!-- <p><strong>Institutional Email: </strong>{{ student.strGSuiteEmail' }}</p>   -->
                                            <p><strong>Personal Email: </strong>{{ student.strEmail }}</p>  
                                            <p><strong>Birthdate: </strong>{{ student.dteBirthDate }}</p>                                            
                                            <p><strong>Date Created: </strong>{{ student.dteCreated }}</p>                                                
                                            <p><strong>Admission Status: </strong>{{ applicant_data.tos }}</p>
                                            <p><strong>Country of Citizenship:</strong> {{ applicant_data.citizenship }}</li>
                                            <div v-if="registration">
                                                <p><strong>Enrollment Status: </strong>{{ registration.enumStudentType }}</p>
                                                <p><strong>Academic Status: </strong>{{ registration.enumRegistrationStatus }}</p>
                                            </div>
                                            <hr />                                        
                                        </div>                            
                                        <div class="col-lg-6">
                                            <table class="table table-bordered">
                                                <tr>
                                                    <th>Mother:</th><td>{{ student.mother }}</td><td>{{ student.mother_contact }}</td><td>{{ student.mother_email }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Father:</th><td>{{ student.father }}</td><td>{{ student.father_contact }}</td><td>{{ student.father_email }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Guardian:</th><td>{{ student.guardian }}</td><td>{{ student.guardian_contact }}</td><td>{{ student.guardian_email }}</td>
                                                </tr>
                                            </table>
                                            <table class="table table-bordered">
                                                <tr>
                                                    <th></th><th>Name</th><th>Address</th><th>Date(s) Attended</th>
                                                </tr>
                                                <tr>
                                                    <th>High School:</th><td>{{ student.high_school }}</td><td>{{ student.high_school_address }}</td><td>{{ student.high_school_attended }}</td>
                                                </tr>
                                                <tr>
                                                    <th>SHS:</th><td>{{ student.senior_high }}</td><td>{{ student.senior_high_address }}</td><td>{{ student.senior_high_attended }}</td>
                                                </tr>
                                                <tr>
                                                    <th>College:</th><td>{{ student.college }}</td><td>{{ student.college_address }}</td><td>{{ student.college_attended_from }}</td>
                                                </tr>
                                                <tr>
                                                    <th></th><td></td></td><td></td><td>{{ student.college_attended_to }}</td>
                                                </tr>
                                            </table>
                                            
                                            <hr />                                        
                                        </div>            
                                                              
                                    </div>
                                    <div>
                                        <strong>Graduated Status:</strong>                                            
                                        <select v-model="grad_status" v-if="registrar_privilages" class="form-control" @change="updateGradStatus">
                                            <option value="0">No</option>
                                            <option value="1">Yes</option>
                                        </select>
                                        <hr />                                            
                                        <div v-else>
                                            {{ student.isGraduate ? 'Grad' : 'Not Grad' }}
                                        </div>
                                    </div>    
                                </div>
                            </div>
                        </div>
                         <!-- /.tab-pane -->                    
                        <div v-if="advanced_privilages1" :class="[(tab == 'tab_2') ? 'active' : '']" class="tab-pane" id="tab_2">
                            <div class="box box-primary">
                                <div class="box-body">                                    
                                    <table v-if="registration" class="table table-condensed table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Section Code</th>
                                                <th>Course Code</th>
                                                <th>Units</th>
                                                <th>Midterm</th>
                                                <th>Final</th>
                                                <th>Remarks</th>
                                                <th>Faculty</th>
                                                <th>Status</th>                                                
                                            </tr>
                                        </thead>
                                        <tbody>                                          
                                            <tr v-for="record in records" style="font-size: 13px;">
                                                <td>{{ record.strSection }}</td>
                                                <td>{{ record.strCode }}</td>
                                                <td>{{ record.strUnits }}</td>
                                                <td>{{ record.intFinalized >=1?record.v2:'NGS' }}</td>
                                                <td>{{ record.intFinalized >=2?record.v3:'NGS' }}</td>
                                                <td>{{ record.strRemarks }}</td>
                                                <td>{{ record.facultyName }}</td>
                                                <td>{{ record.recStatus }}</td>                                                
                                            </tr>
                                            <!-- <tr style="font-size: 13px;">
                                                <td></td>
                                                <td align="right"><strong>TOTAL UNITS CREDITED:</strong></td>
                                                <td>{{ total_units }}</td>
                                                <td colspan="3"></td>
                                            </tr>
                                            <tr style="font-size: 11px;">
                                                <td></td>
                                                <td align="right"><strong>GPA:</strong></td>
                                                <td>{{ gpa }}</td>
                                                <td colspan="3"></td>
                                            </tr> -->

                                        </tbody>
                                    </table>
                                    <hr />
                                    <a target="_blank" class="btn btn-default  btn-flat" :href="base_url + 'pdf/student_viewer_rog_print/' + student.intID + '/' + active_sem.intID">
                                        <i class="ion ion-printer"></i> Print Preview
                                    </a> 
                                    <a target="_blank" class="btn btn-default  btn-flat" :href="base_url + 'pdf/student_viewer_rog_data_print/' + student.intID + '/' + active_sem.intID">
                                        <i class="ion ion-printer"></i> Print Data Preview
                                    </a>                                     
                                </div>
                            </div>
                        </div>
                        <!-- /.tab-pane -->
                        <!-- <div v-if="advanced_privilages1" :class="[(tab == 'tab_3') ? 'active' : '']" class="tab-pane" id="tab_3">
                            <div class="row">
                                <div class="col-md-10 col-md-offset-1">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Academic Standing</th>
                                                <th>CGPA</th>
                                                <th>Units Earned</th>
                                                <th>Total Units</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>{{ other_data.academic_standing.year}} Year / {{ other_data.academic_standing.status }}</td>
                                                <td>{{ other_data.gpa_curriculum }}</td>
                                                <td>{{ other_data.totalUnitsEarned }}</td>
                                                <td>{{ other_data.units_in_curriculum }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <div v-html="assessment"></div>                                    
                                </div> 
                            </div>                                
                        </div>                                         -->
                        <div v-if="registration" :class="[(tab == 'tab_5') ? 'active' : '']" class="tab-pane" id="tab_5">
                            <div class="box box-primary">
                                <div class="box-body">
                                    <table class="table table-condensed table-bordered">
                                        <thead>
                                            <tr style="font-size: 13px;">
                                                <th>Section</th>
                                                <th>Sub Section</th>
                                                <th>Course Code</th>
                                                <th>Course Description</th>
                                                <th>Units</th>
                                                <th>Schedule</th>
                                            </tr>
                                        </thead>
                                        <tbody>                                            
                                            <tr v-for="record in records"  style="font-size: 13px;">
                                                <td>{{ record.strClassName + ' ' + record.year + record.strSection }}</td>
                                                <td>{{ record.sub_section!=null?record.sub_section:'' }}</td>
                                                <td>{{ record.strCode }}</td>
                                                <td>{{ record.strDescription }}</td>
                                                <td>{{ record.strUnits == 0 ? '(' + record.intLectHours + ')' : record.strUnits }}</td>     
                                                <td v-if="record.schedule.schedString != ''">                                                    
                                                    {{ record.schedule.schedString }}                                                       
                                                </td>
                                                <td v-else></td>                                                
                                            </tr>
                                        </tbody>
                                    </table>                                    
                                </div>
                            </div>                            
                            <form method="post" target="_blank" :action="base_url + 'pdf/print_sched'">   
                                <input type="hidden" name="sched-table" id="sched-table" />                                                                                
                                <input type="hidden" :value="student.strLastname + '-' + student.strFirstname + '-' + student.strStudentNumber" name="studentInfo" id="studentInfo" />
                                <input class="btn btn-flat btn-default" type="submit" value="print preview" />
                            </form>             
                            <hr />                
                            <div class="box box-primary">
                                <div class="box-header">
                                    <h4>Schedule</h4>
                                </div>
                                <div class="box-body">
                                    <?php echo $sched_table; ?>                                
                                </div>
                            </div>
                        </div>                                        
                    </div>
                    <!-- /.tab-content -->
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
        records: [],
        other_data: undefined,
        reg_status: '',
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
        total_units: 0,
        picture: undefined,
        lab_units: 0,    
        gpa: 0,        
        assessment: '',         
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
                        
                        this.user_level = data.data.user_level;
                        this.registration = data.data.registration;                        
                        this.registration_status = data.data.registration ? data.data.registration.intROG : 0;                        
                        this.active_sem = data.data.active_sem;
                        this.reg_status = data.data.reg_status;
                        this.selected_ay = data.data.selected_ay;
                        this.curriculum_subjects = data.data.curriculum_subjects;                        
                        this.sections = data.data.sections;
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
                        this.assessment = data.data.assessment;    
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