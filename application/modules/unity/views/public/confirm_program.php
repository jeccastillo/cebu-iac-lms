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
                            </tr>
                            <tr>
                                <th>Select Section/Schedule</th>                                
                                <td>                                    
                                    <select v-model="request.preferedSection" @change="changeSection" class="form-control">
                                        <option v-for="section in sections" :value="section.intID">{{ section.name }}</option>
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <hr />                    
                </div>
            </div>
        </div>        
    </div>
    <div class="content container">               
        <div class="box box-primary">
            <div class="box-header">
                <h4>Schedule</h4>
            </div>
            <div class="box-body">
                <?php echo $sched_table; ?>
            </div>
        </div>
    </div>
    <div class="content container">
        <div class="text-center">
            <button class="btn btn-primary" v-if="loaded" @click="confirmProgram">Confirm Selected Program and Schedule</button>                        
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@2.6.12"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
    integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
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
                        load_schedule(data.data.sections[0].schedule);
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
                load_schedule(data.data.section.schedule);
            });
        },
        changeProgram: function(){
            axios.get(this.base_url + 'unity/program_confirmation_sub_data/' + this.request.intProgramID)
            .then((data) => {
                if(data.data.sections.length > 0){ 
                    this.sections = data.data.sections;
                    console.log(data.data.sections[0].schedule);
                    load_schedule(data.data.sections[0].schedule);
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

