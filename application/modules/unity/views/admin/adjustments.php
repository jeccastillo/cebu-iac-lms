<aside class="right-side" id="registration-container">    
    <section class="content-header">
        <h1>
            <small>
                <a class="btn btn-app" :href="base_url + 'student/view_all_students'"><i class="ion ion-arrow-left-a"></i>All Students</a>                     
                <a class="btn btn-app" :href="base_url + 'student/edit_student/' + student.intID"><i class="ion ion-edit"></i> Edit</a> 
                <a class="btn btn-app" :href="base_url + 'finance/student_ledger/' + student.intID"><i class="ion ion-edit"></i> Ledger</a>                                         
            </small>
        </h1>
        <div v-if="registration" class="pull-right">
            
            <label style="font-size:.6em;"> Registration Status</label>
                
            <select v-model="registration_status" @change="changeRegStatus" class="form-control">
                <option value="0">Registered</option>
                <option value="1">Enrolled</option>
                <option value="2">Cleared</option>
            </select>
            
        </div>
        <hr />
    </section>
        <hr />
    <div class="content">        
        <div class="row">
            <div class="col-sm-12">
                <div v-if="student" class="box box-widget widget-user-2">
                    <!-- Add the bg color to the header using any of the bg-* classes -->
                    <div class="widget-user-header bg-red">
                        <!-- /.widget-user-image -->
                        <h3 class="widget-user-username" style="text-transform:capitalize;margin-left:0;font-size:1.3em;">{{ student.strLastname }}, {{ student.strFirstname }} {{ student.strMiddlename }}</h3>
                        <h5 class="widget-user-desc" style="margin-left:0;">{{ student.strProgramDescription }}  {{ (student.strMajor != 'None')?'Major in '+student.strMajor:'' }}</h5>
                    </div>
                    <div class="box-footer no-padding">
                        <ul class="nav nav-stacked">
                        <li><a href="#" style="font-size:13px;">Student Number <span class="pull-right text-blue">{{ student.strStudentNumber.replace(/-/g, '') }}</span></a></li>
                        <li><a href="#" style="font-size:13px;">Curriculum <span class="pull-right text-blue">{{ student.strName }}</span></a></li>
                        <li v-if="registration"><a style="font-size:13px;" href="#">Registration Status <span class="pull-right">{{ reg_status }}</span></a></li>
                        <li v-if="registration"><a style="font-size:13px;" href="#">Class Type <span class="pull-right">{{ registration.type_of_class }}</span></a></li>
                        <li>
                            <a style="font-size:13px;" href="#">Date Registered <span class="pull-right">
                                <span style="color:#009000" v-if="registration" >{{ registration.date_enlisted }}</span>
                                <span style="color:#900000;" v-else>N/A</span>                                
                            </a>
                        </li>
                        <li v-if="registration"><a style="font-size:13px;" href="#">Scholarship Type <span class="pull-right">{{ registration.scholarshipName }}</span></a></li>
                            
                        </ul>
                    </div>
                </div>                
            </div>
            <div class="col-sm-12">
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li v-if="advanced_privilages">
                            <a :href="base_url + 'unity/student_viewer/' + student.intID + '/' + sem + '/tab_1'">
                                Personal Information
                            </a>
                        </li>
                        
                        <li v-if="advanced_privilages">
                            <a :href="base_url + 'unity/student_viewer/' + student.intID + '/' + sem + '/tab_2'">                            
                                Subjects
                            </a>
                        </li>
                        <li v-if="advanced_privilages">
                            <a :href="base_url + 'unity/student_viewer/' + student.intID + '/' + sem + '/tab_3'">                            
                                Grade Changes
                            </a>
                        </li>
                        
                        <li v-if="advanced_privilages">
                            <a :href="base_url + 'unity/student_viewer/' + student.intID + '/' + sem + '/tab_5'">                            
                                Schedule
                            </a>
                        </li>
                        <li class="active" v-if="advanced_privilages">
                            <a href="#tab_1" data-toggle="tab">                            
                                Adjustments
                            </a>
                        </li>                        
                        <!-- <li>
                            <a :href="base_url + 'unity/accounting/' + student.intID">                                
                                Accounting Summary
                            </a>
                        </li> -->
                    </ul>
                    <div class="tab-content">
                        <button v-if="reg_status == 'Enrolled' || reg_status == 'Registered'" data-toggle="modal" data-target="#withdrawStudentModal" class="btn btn-danger btn-lg">
                                Withdraw
                        </button>
                        <a target="_blank" :href="base_url + 'pdf/adjustments/'+ id + '/' + sem" v-if="reg_status == 'Enrolled'" class="btn btn-primary btn-lg">
                                Print PDF
                        </a>
                        <a target="_blank" :href="base_url + 'excel/adjustments/'+ id + '/' + sem" v-if="reg_status == 'Enrolled'" class="btn btn-primary btn-lg">
                                Export Excel
                        </a>
                        <hr />
                        <table class="table table-condensed table-bordered">
                            <thead>
                                <tr>
                                    <th>Section Code</th>
                                    <th>Course Code</th>
                                    <th>Units</th>                                                
                                    <th>Faculty</th>
                                    <th>Status</th>
                                    <th>Schedule</th>
                                    <th>Adjustments</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>                                          
                                <tr v-for="record in records" style="font-size: 13px;">
                                    <td>{{ record.strClassName + record.year + record.strSection + " "}} {{ record.sub_section?record.sub_section:'' }}</td>
                                    <td>{{ record.strCode }}</td>
                                    <td>{{ record.strUnits }}</td>                                                                                                
                                    <td>{{ record.facultyName }}</td>
                                    <td>{{ record.recStatus }}</td>
                                    <td v-if="record.schedule.schedString != ''">                                                    
                                        {{ record.schedule.schedString }}                                                       
                                    </td>
                                    <td><span v-if="record.adjustments">{{ record.adjustments.adjustment_type }}</span></td>
                                    <td>    
                                    <button v-if="reg_status == 'Enrolled' || reg_status == 'Enlisted'"                                                
                                            @click="dropSubject(record.classlistID,false)"  class="btn btn-danger">
                                            Drop
                                    </button>                                                                                                    
                                    </td>
                                </tr>                                            
                            </tbody>
                        </table>
                        <hr />
                        <div v-if="reg_status == 'Enrolled' || reg_status == 'Enlisted'">
                            <button data-toggle="modal"            
                                    value = 0                                    
                                    @click="loadAvailableSubjects($event,'add-subject')" 
                                    data-target="#addSubjectModal" class="btn btn-primary">
                                    Add/Replace Subject
                            </button>
                            <button data-toggle="modal"            
                                    value = 0                                    
                                    @click="loadAvailableSubjects($event,'change-section')" 
                                    data-target="#addSubjectModal" class="btn btn-primary">
                                    Change Section
                            </button>
                        </div>
                        <hr />
                        <h4>Adjustments</h4>
                        <table class="table table-condensed table-bordered">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>Adjustment</th>
                                    <th>Removed</th>                                                
                                    <th>Added</th>  
                                    <th>Adjusted By</th> 
                                    <th>Remarks</th>                                 
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>                                          
                                <tr v-for="adj in adjustments" style="font-size: 13px;">
                                    <td>{{ adj.strCode }}</td>
                                    <td>{{ adj.adjustment_type }}</td>
                                    <td>{{ adj.from_subject }}</td>                                                                                                
                                    <td>{{ adj.to_subject }}</td>
                                    <td>{{ adj.strLastname + ", " + adj.strFirstname }}</td>
                                    <td>{{ adj.remarks }}</td>
                                    <td>{{ adj.date }}</td>                                                                                                                                                                                                  
                                </tr>                                            
                            </tbody>
                        </table>
                        
                    </div>
                </div>
            </div>  
        </div>
    </div>
    <div class="modal fade" id="withdrawStudentModal" role="dialog">
        <form @submit.prevent="withdrawStudent()" class="modal-dialog modal-lg">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <!-- modal header  -->
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Withdraw Student Enrollment</h4>
                </div>
                <div class="modal-body">     
                    <div class="row">
                        <div class="col-sm-6">
                            <label>Withdrawal Period</label>
                            <select class="form-control" required  v-model="withdrawal.period">
                                <option selected value="before">Before Start of Term</option>
                                <option value="after">After Start of Term</option>
                                <option value="end">End of Term</option>
                            </select> 
                        </div>    
                    </div>                                                  
                </div>
                <div class=" modal-footer">
                    <!-- modal footer  -->
                    <button type="submit" class="btn btn-primary">Submit</button>                    
                </div>
            </div>

        </form>
    </div>
    <div class="modal fade" id="addSubjectModal" role="dialog">
        <form @submit.prevent="addSubject(0)" class="modal-dialog modal-lg">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <!-- modal header  -->
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">{{ modal_title }}</h4>
                </div>
                <div class="modal-body">     
                    <div v-if="replace">
                        <label>Subject From (set to none if you want to add a new subject)</label>
                        <div v-if="records" class="input-group">
                            <select required @change="loadAvailableSubjects($event,'add-subject')" class="form-control" v-model="subject_to_replace">
                                <option selected value="0">None</option>
                                <option v-for="record in records" :value="record.classlistID">{{ record.strCode + ' ' + record.strDescription +' '+ record.strClassName + record.year + record.strSection + " "}} {{ record.sub_section?record.sub_section:'' }}</option>                                                                          
                            </select>                        
                        </div>      
                        <hr /> 
                    </div>        
                    
                    <label>{{ subject_to_label }}</label>
                    <div v-if="subjects_available" class="input-group">
                        <select required @change="getSections($event)" class="form-control" v-model="subject_to_add">
                            <option v-for="s in subjects_available" v-if="!hide_subjects || !inArray(s.strCode,subjects_loaded)" :value="s.intSubjectID">{{ s.strCode + ' ' + s.strDescription }}</option>                                                                          
                        </select>                        
                    </div>    
                    <div v-if="sections">
                    <hr />
                    <label>Select Section</label>
                        <div class="input-group">
                            <select required class="form-control" v-model="section_to_add">
                                <option v-for="sec in sections" :value="sec.intID">
                                    {{ sec.strClassName + ' ' + sec.year + ' ' + sec.strSection }} {{ sec.sub_section?sec.sub_section:'' }} {{ schedules[sec.intID]?schedules[sec.intID]:"" }} ({{ sec.slots_available }})
                                </option>                                                                          
                            </select>                        
                        </div>               
                    </div>                                                          
                </div>
                <div class=" modal-footer">
                    <!-- modal footer  -->
                    <button v-if="subject_to_replace == 0" type="submit" class="btn btn-primary">Submit</button>
                    <button v-else type="button" @click="dropSubject(subject_to_replace,true)" class="btn btn-primary">Replace</button>
                    
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>

        </form>
    </div>
    
</aside>

<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@2.6.12"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
    integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>

<script>
function inArray(needle, haystack) {
    var length = haystack.length;
    for(var i = 0; i < length; i++) {
        if(haystack[i] == needle) return true;
    }
    return false;
}
new Vue({
    el: '#registration-container',
    data: {
        id: '<?php echo $id; ?>',    
        sem: '<?php echo $selected_ay; ?>',
        base_url: '<?php echo base_url(); ?>',
        slug: undefined,
        student:{},            
        reg_status: undefined, 
        modal_title: "Add/Replace Subject",
        records:[],  
        replace: false,
        registration: undefined,     
        withdrawal:{
            period:'before',
        },
        registration_status: 0,     
        hide_subjects: false,   
        subject_to_label: 'Subject To',
        loader_spinner: true,      
        advanced_privilages: false,
        subject_to_add: undefined,
        subjects_loaded: [],
        subject_to_replace: 0,
        subjects_available: undefined,   
        sections: undefined,       
        schedules: undefined,
        section_to_add: undefined,    
        adjustments:[],      
    },

    mounted() {

        let url_string = window.location.href;        
        if(this.id != 0){            
            //this.loader_spinner = true;
            axios.get(this.base_url + 'unity/adjustments_data/' + this.id + '/' + this.sem)
                .then((data) => {                                          
                    this.registration = data.data.registration;                       
                    this.reg_status = data.data.reg_status;
                    this.student = data.data.student;
                    this.adjustments = data.data.adjustments;
                    // this.subjects_available = data.data.subjects_available;
                    this.records  = data.data.records;     
                    for(i in this.records){
                        this.subjects_loaded.push(this.records[i].strCode);
                    }               
                    this.slug = this.student.slug;
                    this.advanced_privilages = data.data.advanced_privilages;           
                })
                .catch((error) => {
                    console.log(error);
                })
        }

    },

    methods: {      
        loadAvailableSubjects(event,type){
            
                                
            this.sections = undefined;
            this.subject_to_add = undefined;                                                         
            this.section_to_add = undefined;
            if(type == 'change-section'){
                this.subject_to_label = "Select From Enlisted Subjects";
                this.replace = false;
                this.subjects_available = this.records;
                this.modal_title = "Change Section";
                this.hide_subjects = false;
            }
            else{
            axios.get(this.base_url + 'registrar/available_subjects/' + this.id + '/' + this.sem)
                .then((data) => {      
                    this.subject_to_label = "Subject To";
                    this.hide_subjects = true;                    
                    this.replace = true;                                       
                    this.modal_title = "Add/Replace Subject";                    
                    this.subjects_available = data.data.data;
                    //this.subject_to_replace = all;
                })
                .catch((error) => {
                    console.log(error);
                })
            }
        },        
        getSections(event){            
            axios.get(this.base_url + 'registrar/get_sections/' + event.target.value + '/' + this.sem)
                .then((data) => {                       
                    this.sections = undefined;      
                    this.section_to_add = undefined;                                                     
                    this.sections = data.data.data;           
                    this.schedules = data.data.schedules;                                             
                })
                .catch((error) => {
                    console.log(error);
                })
        },
        changeRegStatus: function(){
            let url = this.base_url + 'unity/update_rog_status';
            var formdata= new FormData();
            formdata.append("intRegistrationID",this.registration.intRegistrationID);
            formdata.append("intROG",this.registration_status);            
            var missing_fields = false;
            this.loader_spinner = true;
            
            //validate description
                      
            axios.post(url, formdata, {
                headers: {
                    Authorization: `Bearer ${window.token}`
                }
            })
            .then(data => {
                this.loader_spinner = false;
                if(data.data.success)
                    Swal.fire({
                        title: "Success",
                        text: data.data.message,
                        icon: "success"
                    }).then(function() {
                        location.reload();
                    });
                else
                    Swal.fire({
                        title: "Failed",
                        text: data.data.message,
                        icon: "error"
                    }).then(function() {
                        //location.reload();
                    });
            });
           
            
            
        },        
        withdrawStudent: function(){
            let url = base_url + 'registrar/withdraw_student';            
            this.loader_spinner = true;            
                          
            Swal.fire({
                title: 'Continue deleting Subject',
                text: "Are you sure you want withdraw this student's enrollment?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                    preConfirm: (login) => {       
                        Swal.fire({
                            title: 'Continue deleting Subject',
                            text: "Are you absolutely sure you want withdraw this student's enrollment? Enter your password.",                            
                            showCancelButton: true,
                            input:"password",
                            confirmButtonText: "Yes",
                            imageWidth: 100,
                            icon: "question",
                            cancelButtonText: "No, cancel!",
                            showCloseButton: true,
                            showLoaderOnConfirm: true,
                            preConfirm: (inputValue) => {                                                                       
                                var formdata= new FormData();
                                formdata.append('period',this.withdrawal.period);     
                                formdata.append('id',this.student.intID);
                                formdata.append('password',inputValue);                           
                                formdata.append('sem',this.sem);
                                return axios.post(url, formdata, {
                                    headers: {
                                        Authorization: `Bearer ${window.token}`
                                    }
                                })
                                .then(data => {
                                    this.loader_spinner = false;                                      
                                    if(data.data.registration.enumStudentType == "new"){                                 
                                        if(data.data.success){   
                                            var update_status = 'Withdrawn Before';
                                            switch(this.withdrawal.period){
                                                case 'before':
                                                    update_status = 'Withdrawn Before';
                                                break;
                                                case 'after':
                                                    update_status = 'Withdrawn After';
                                                break;
                                                case 'end':
                                                    update_status = 'Withdrawn End';
                                                break;
                                            }
                                            axios.post(api_url + 'admissions/student-info/' + this.slug +
                                                '/update-status', {
                                                    status: update_status,
                                                    remarks: "Student Withdrawn",
                                                    admissions_officer: "<?php echo $user['strFirstname'] . '  ' . $user['strLastname'] ; ?>"
                                                }, {
                                                    headers: {
                                                        Authorization: `Bearer ${window.token}`
                                                    }
                                                })
                                                .then(data => {
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

                                        }                                        
                                        else
                                            Swal.fire({
                                                title: "Failed",
                                                text: data.data.message,
                                                icon: "error"
                                            }).then(function() {
                                                //location.reload();
                                            });                                        
                                    }
                                    else
                                        location.reload();
                                });                                                                                                                                                  
                            },
                            allowOutsideClick: () => !Swal.isLoading()
                        }).then((result) => {
                        
                        })
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                    }).then((result) => {
                        
                    })
        },
        dropSubject: function(section,swap){
            let url = base_url + 'registrar/drop_subject';
            let slug = this.slug;      
            this.loader_spinner = true;            
               
            if(swap && (!this.subject_to_add || !this.section_to_add)){
                Swal.fire({
                    title: "Failed",
                    text: "Please Select Subject and Section",
                    icon: "error"
                }).then(function() {
                    //location.reload();
                });   
            }
            else
            Swal.fire({
                title: 'Continue deleting Subject',
                text: "Are you sure you want drop this subject?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                    preConfirm: (login) => {       
                        Swal.fire({
                            title: 'Continue deleting Subject',
                            text: "Are you absolutely sure you want drop this subject? Enter today's date in yyyy-mm-dd format.",                            
                            showCancelButton: true,
                            input:"text",
                            confirmButtonText: "Yes",
                            imageWidth: 100,
                            icon: "question",
                            cancelButtonText: "No, cancel!",
                            showCloseButton: true,
                            showLoaderOnConfirm: true,
                            preConfirm: (inputValue) => {                                                                       
                                var formdata= new FormData();
                                formdata.append('swap',swap);
                                formdata.append('section_to_delete',section);                                
                                formdata.append('student',this.id);                                
                                formdata.append('sem',this.sem);
                                formdata.append('date',inputValue);
                                formdata.append('section_to_add',this.section_to_add);
                                formdata.append('subject_to_add',this.subject_to_add);
                                return axios.post(url, formdata, {
                                    headers: {
                                        Authorization: `Bearer ${window.token}`
                                    }
                                })
                                .then(data => {
                                    this.loader_spinner = false;                                    
                                    if(data.data.success){   
                                        if(swap){
                                            this.addSubject(1);
                                        }
                                        else                                         
                                            Swal.fire({
                                                title: "Success",
                                                text: data.data.message,
                                                icon: "success"
                                            }).then(function() {
                                                location.reload();
                                            });                                                                                                                              

                                    }                                        
                                    else
                                        Swal.fire({
                                            title: "Failed",
                                            text: data.data.message,
                                            icon: "error"
                                        }).then(function() {
                                            //location.reload();
                                        });                                        
                                    });                                        
                                                                        
                            },
                            allowOutsideClick: () => !Swal.isLoading()
                        }).then((result) => {
                        
                        })
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                    }).then((result) => {
                        
                    })

        },
        addSubject: function(cf){            
            let url = base_url + 'registrar/add_subject_student';
            let slug = this.slug;      
            this.loader_spinner = true;
            
            
            if(cf){
                Swal.fire({
                    showCancelButton: false,
                    showCloseButton: false,
                    allowEscapeKey: false,
                    title: 'Loading',
                    text: 'Updating Data do not leave page',
                    icon: 'info',
                })
                Swal.showLoading();
                var formdata= new FormData();
                formdata.append('section_to_add',this.section_to_add);
                formdata.append('subject_to_add',this.subject_to_add);
                formdata.append('subject_to_replace',this.subject_to_replace);                        
                formdata.append('student',this.id);
                formdata.append('sem',this.sem);
                return axios.post(url, formdata, {
                    headers: {
                        Authorization: `Bearer ${window.token}`
                    }
                })
                .then(data => {
                    this.loader_spinner = false;                                    
                    if(data.data.success){                                            
                        Swal.fire({
                                title: "Success",
                                text: data.data.message,
                                icon: "success"
                            }).then(function() {
                                location.reload();
                            });                                                                                                                              

                        }                                        
                        else
                            Swal.fire({
                                title: "Failed",
                                text: data.data.message,
                                icon: "error"
                            }).then(function() {
                                //location.reload();
                            });                                        
                    });   
            }
            else
                Swal.fire({
                    title: 'Continue adding Subject',
                    text: "Are you sure you want add this subject?",
                    showCancelButton: true,
                    confirmButtonText: "Yes",
                    imageWidth: 100,
                    icon: "question",
                    cancelButtonText: "No, cancel!",
                    showCloseButton: true,
                    showLoaderOnConfirm: true,
                        preConfirm: (login) => {                                                
                            var formdata= new FormData();
                            formdata.append('section_to_add',this.section_to_add);
                            formdata.append('subject_to_add',this.subject_to_add);
                            formdata.append('subject_to_replace',this.subject_to_replace);                        
                            formdata.append('student',this.id);
                            formdata.append('sem',this.sem);
                            return axios.post(url, formdata, {
                                headers: {
                                    Authorization: `Bearer ${window.token}`
                                }
                            })
                            .then(data => {
                                this.loader_spinner = false;                                    
                                if(data.data.success){                                            
                                    Swal.fire({
                                            title: "Success",
                                            text: data.data.message,
                                            icon: "success"
                                        }).then(function() {
                                            location.reload();
                                        });                                                                                                                              

                                    }                                        
                                    else
                                        Swal.fire({
                                            title: "Failed",
                                            text: data.data.message,
                                            icon: "error"
                                        }).then(function() {
                                            //location.reload();
                                        });                                        
                                });                                        
                                                                    
                        },
                        allowOutsideClick: () => !Swal.isLoading()
                    }).then((result) => {
                    
                    })

        }
    }

})
</script>

