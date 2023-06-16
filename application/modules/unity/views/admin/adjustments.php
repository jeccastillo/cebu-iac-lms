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
                                Report of Grades
                            </a>
                        </li>
                        <li v-if="advanced_privilages">
                            <a :href="base_url + 'unity/student_viewer/' + student.intID + '/' + sem + '/tab_3'">                            
                                Assessment
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
                        <li><a :href="base_url + 'unity/registration_viewer/' + student.intID + '/' + sem">Finance</a></li>
                        <!-- <li>
                            <a :href="base_url + 'unity/accounting/' + student.intID">                                
                                Accounting Summary
                            </a>
                        </li> -->
                    </ul>
                    <div class="tab-content">
                        <button class="btn btn-danger btn-lg">
                                Withdraw
                        </button>
                        <hr />
                        <table class="table table-condensed table-bordered">
                            <thead>
                                <tr>
                                    <th>Section Code</th>
                                    <th>Course Code</th>
                                    <th>Units</th>                                                
                                    <th>Faculty</th>
                                    <th>Status</th>
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
                                    <td><span v-if="record.adjustments">{{ record.adjustments.adjustment_type }}</span></td>
                                    <td>    
                                    <button                                                
                                            @click="dropSubject(record.classlistID)"  class="btn btn-danger">
                                            Drop
                                    </button>                                                                                                    
                                    </td>
                                </tr>                                            
                            </tbody>
                        </table>
                        <hr />
                        <button data-toggle="modal"                                                
                                @click="loadAvailableSubjects()" 
                                data-target="#addSubjectModal" class="btn btn-primary">
                                Add Subject/Change Section
                        </button>
                    </div>
                </div>
            </div>  
        </div>
    </div>
    <div class="modal fade" id="addSubjectModal" role="dialog">
        <form @submit.prevent="addSubject" class="modal-dialog modal-lg">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <!-- modal header  -->
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Add Subject/Change Section</h4>
                </div>
                <div class="modal-body">                    
                    <h4>Select Subject</h4>
                    <div v-if="subjects_available" class="input-group">
                        <select required @change="getSections($event)" class="form-control" v-model="subject_to_add">
                            <option v-for="s in subjects_available" :value="s.intSubjectID">{{ s.strCode + ' ' + s.strDescription }}</option>                                                                          
                        </select>                        
                    </div>    
                    <h4>Select Section</h4>
                    <div v-if="sections" class="input-group">
                        <select required class="form-control" v-model="section_to_add">
                            <option v-for="sec in sections" :value="sec.intID">{{ sec.strClassName + ' ' + sec.year + ' ' + sec.strSection }} {{ sec.sub_section?sec.sub_section:'' }}</option>                                                                          
                        </select>                        
                    </div>                                                                         
                </div>
                <div class=" modal-footer">
                    <!-- modal footer  -->
                    <button type="submit" class="btn btn-primary">Submit</button>
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
new Vue({
    el: '#registration-container',
    data: {
        id: '<?php echo $id; ?>',    
        sem: '<?php echo $selected_ay; ?>',
        base_url: '<?php echo base_url(); ?>',
        slug: undefined,
        student:{},            
        reg_status: undefined, 
        records:[],  
        registration: undefined,     
        registration_status: 0,
        loader_spinner: true,      
        advanced_privilages: false,
        subject_to_add: undefined,
        subjects_available: undefined,   
        sections: undefined,       
        section_to_add: undefined,          
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
                    // this.subjects_available = data.data.subjects_available;
                    this.records  = data.data.records;
                    this.slug = this.student.slug;
                    this.advanced_privilages = data.data.advanced_privilages;           
                })
                .catch((error) => {
                    console.log(error);
                })
        }

    },

    methods: {      
        loadAvailableSubjects(){
            axios.get(this.base_url + 'registrar/available_subjects/' + this.id + '/' + this.sem)
                .then((data) => {     
                    this.sections = undefined;
                    this.subject_to_add = undefined;                                                         
                    this.section_to_add = undefined;
                    this.subjects_available = data.data.data;           
                })
                .catch((error) => {
                    console.log(error);
                })
        },
        getSections(event){            
            axios.get(this.base_url + 'registrar/get_sections/' + event.target.value + '/' + this.sem)
                .then((data) => {   
                    this.sections = undefined;      
                    this.section_to_add = undefined;                                                     
                    this.sections = data.data.data;                               
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
        dropSubject: function(section){
            let url = base_url + 'registrar/drop_subject';
            let slug = this.slug;      
            this.loader_spinner = true;
            
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
                                formdata.append('section_to_delete',section);                                
                                formdata.append('student',this.id);
                                formdata.append('sem',this.sem);
                                formdata.append('date',inputValue);
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
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                    }).then((result) => {
                        
                    })

        },
        addSubject: function(){
            let url = base_url + 'registrar/add_subject_student';
            let slug = this.slug;      
            this.loader_spinner = true;
            
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

