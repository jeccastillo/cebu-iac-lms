<aside class="right-side" id="registration-container">    
    <section class="content-header">
        <h1>
            <small>
                <a class="btn btn-app" :href="base_url + 'student/view_all_students'"><i class="ion ion-arrow-left-a"></i>All Students</a>                     
                <a class="btn btn-app" :href="base_url + 'student/edit_student/' + student.intID"><i class="ion ion-edit"></i> Edit</a>                                          
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
        <div class="row">
            <div class="col-sm-12">
                <div class="box box-widget widget-user-2">
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
                    </div>
                </div>
            </div>  
        </div>
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
        registration: undefined,     
        registration_status: 0,
        loader_spinner: true,      
        advanced_privilages: false,                    
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
                    this.advanced_privilages = data.data.advanced_privilages;           
                })
                .catch((error) => {
                    console.log(error);
                })
        }

    },

    methods: {      
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
           
            
            
        }
    }

})
</script>

