<div id="registration-container">    
    <div class="container">       
        <div class="content">                        
            <div class="box">
                <div class="box-header">
                    <h3>Name :{{ student.strFirstname }} {{ student.strLastname }} <br />
                        Stud No :{{ student.strStudentNumber }}
                    </h3>
                </div>
                <div class="box-body">
                    <table class="table table-bordered table-striped">
                        <tbody>
                            <tr>
                                <th>Select Program</th>                                
                                <td>                                    
                                    <select v-model="request.intProgramID" @change="changeProgram" class="form-control">
                                        <option v-for="program in programs" :value="program.intProgramID">{{ program.strProgramDescription }}</option>
                                    </select>
                                </td>
                                <td></td>
                            </tr>
                            <tr>
                                <th>Select Section/Schedule</th>                                
                                <td>                                    
                                    <select v-model="request.preferedSection" @change="changeSection" class="form-control">
                                        <option v-for="section in sections" :value="section.intID">{{ section.name }}</option>
                                    </select>
                                </td>
                                <td>
                                    <a class="btn btn-primary" :href="base_url + 'unity/schedule_viewer/' + section.intID" target="_blank">View Schedule</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="box box-primary">
                        <div class="box-header">
                            <h3>Additional Information</h3>
                        </div>
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label>Mother's Maiden Name</label>
                                    <input type="text" required class="form-control" v-model="">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Contact Number</label>
                                    <input type="number" required class="form-control" v-model="">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label>Email Address</label>
                                    <input type="email" required class="form-control" v-model="">
                                </div>                                
                            </div>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label>Father's Name</label>
                                    <input type="text" required class="form-control" v-model="">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Contact Number</label>
                                    <input type="number" required class="form-control" v-model="">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label>Email Address</label>
                                    <input type="email" required class="form-control" v-model="">
                                </div>                                
                            </div>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label>Name of Guardian</label>
                                    <input type="text" required class="form-control" v-model="">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Contact Number</label>
                                    <input type="number" required class="form-control" v-model="">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label>Email Address</label>
                                    <input type="email" required class="form-control" v-model="">
                                </div>                                
                            </div>
                        </div>
                    </div>
                    <div class="box box-primary">
                        <div class="box-header">
                            <h3>Educational Background</h3>
                        </div>
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-4 form-group">
                                    <label>High School</label>
                                    <input type="text" required class="form-control" v-model="">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label>School Address</label>
                                    <textarea required class="form-control" v-model=""></textarea>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label>Years Attended (month-day-year)</label>
                                    <the-mask
                                        class="form-control"
                                        :mask="['( ##-##-####']" type="text" v-model="" required masked="true" placeholder="mm-dd-yyyy"></the-mask>
                                </div>
                            </div> 
                            <div class="row">
                                <div class="col-md-4 form-group">
                                    <label>College</label>
                                    <input type="text" required class="form-control" v-model="">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label>School Address</label>
                                    <textarea required class="form-control" v-model=""></textarea>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label>Years Attended From (month-day-year)</label>
                                    <the-mask
                                        class="form-control"
                                        :mask="['( ##-##-####']" type="text" v-model="" required masked="true" placeholder="mm-dd-yyyy"></the-mask>
                                </div>
                            </div>                           
                            <div class="row">
                                <div class="col-md-4 form-group">                                   
                                </div>
                                <div class="col-md-4 form-group">                                    
                                </div>
                                <div class="col-md-4 form-group">
                                    <label>Years Attended To (month-day-year)</label>
                                    <the-mask
                                        class="form-control"
                                        :mask="['( ##-##-####']" type="text" v-model="" required masked="true" placeholder="mm-dd-yyyy"></the-mask>
                                </div>
                            </div> 
                            <div class="row">
                                <div class="col-md-4 form-group">
                                    <label>Señor High School</label>
                                    <input type="text" required class="form-control" v-model="">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label>School Address</label>
                                    <textarea required class="form-control" v-model=""></textarea>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label>Years Attended From (month-day-year)</label>
                                    <the-mask
                                        class="form-control"
                                        :mask="['( ##-##-####']" type="text" v-model="" required masked="true" placeholder="mm-dd-yyyy"></the-mask>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 form-group">
                                    <label>Strand</label>
                                    <input type="text" required class="form-control" v-model="">
                                </div>
                                <div class="col-md-8 form-group">
                                    <label>Type of Student</label>
                                    <select required class="form-control" v-model="">
                                        <option value="Freshman">Freshman</option>
                                        <option value="Transferee">Transferee</option>
                                        <option value="Foreign">Foreign</option>
                                    </select>                                    
                                </div>                                
                            </div>      
                        </div>
                    </div>
                    <hr />    
                    <div class="text-center">
                        <button class="btn btn-primary" v-if="loaded" @click="confirmProgram">Confirm Selected Program and Section</button>                        
                    </div>                
                </div>
            </div>
        </div>        
    </div>  
</div>

<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@2.6.12"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
    integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/vue-the-mask/0.11.1/vue-the-mask.min.js"></script>    
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>

<style scoped="">
.box_mode_payment {
    border: 1px solid #000;
    height: 41px;
    width: 57px;
    margin: 4px;
    cursor: pointer;
}

.box_mode_payment.active {
    background: #1c54a5;
}

.spinner {
    animation-name: spin;
    animation-duration: 1000ms;
    animation-iteration-count: infinite;
    animation-timing-function: linear;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }
}
</style>
<script>
new Vue({
    el: '#registration-container',
    data: {
        id: '<?php echo $id; ?>',   
        base_url: '<?php echo base_url(); ?>',            
        student: {},
        programs: [],
        loaded: false,
        sections: [],
        section: {
            intID: 0,
        },
        api_data:{},        
        request: {
            intProgramID: undefined,
            preferedSection: undefined,
            id: undefined,
        },
        payload:{

        },
        show_select: false,
    },    
    mounted() {        
        let url_string = window.location.href;           
           
        axios.get(this.base_url + 'unity/program_confirmation_data/' + this.id + '/')
                .then((data) => {  
                    this.student = data.data.student;     
                    this.request.intProgramID = this.student.intProgramID;         
                    this.programs = data.data.programs;      
                    this.request.id = this.student.intID; 
                    
                    if(data.data.sections.length > 0){ 
                        this.sections = data.data.sections;
                        this.section = data.data.sections[0];
                        this.request.preferedSection = data.data.sections[0].intID;                        
                    }                       
                    axios.get(api_url + 'admissions/student-info/' + data.data.student.slug)
                    .then((data) => {
                        this.api_data = data.data.data;                        
                        if(this.api_data.status == "Confirmed")
                            document.location = this.base_url;
                        else
                            this.loaded = true;
                        
                    })
                    .catch((error) => {
                        console.log(error);
                    })  
                })
                .catch((error) => {
                    console.log(error);
                })

        

    },

    methods: {  
        changeSection: function(){
            axios.get(this.base_url + 'unity/program_confirmation_section/' + this.request.preferedSection)
            .then((data) => {                    
                this.section = data.data.section;                 
            });
        },
        changeProgram: function(){
            axios.get(this.base_url + 'unity/program_confirmation_sub_data/' + this.request.intProgramID)
            .then((data) => {
                if(data.data.sections.length > 0){ 
                    this.sections = data.data.sections;  
                    this.section = data.data.sections[0];                    
                    this.request.preferedSection = data.data.sections[0].intID;                                        
                }  
            });

        },
        confirmProgram: function(){
            this.loading_spinner = true;
            Swal.fire({
                showCancelButton: false,
                showCloseButton: false,
                allowEscapeKey: false,
                title: 'Please wait',
                text: 'Processing confirmation',
                icon: 'info',
            })
            Swal.showLoading();

            axios
                .post(api_url + 'registrar/confirm_selected_program/' + this.student.slug , this.payload, {
                    headers: {
                        Authorization: `Bearer ${window.token}`
                    }
                })
                .then(data => {     
                    var formdata= new FormData();
                    for (const [key, value] of Object.entries(this.request)) {
                        formdata.append(key,value);
                    }                                                    
                    axios
                    .post(this.base_url + 'unity/student_confirm_program', formdata, {
                        headers: {
                            Authorization: `Bearer ${window.token}`
                        }
                    })
                    .then(data => {
                        Swal.hideLoading();
                        location.reload();
                    });               
                    
                });
            
        }
    }

})
</script>

