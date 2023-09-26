
<aside class="right-side">
    <div id="vue-container">
        <section class="content-header">
            <h1>
                Shifting Courses
                <small>                
                </small>
            </h1>


        </section>
        <hr />
        <div class="content">
            <div v-if="student" class="box box-widget widget-user-2">
                <!-- Add the bg color to the header using any of the bg-* classes -->                
                <div class="box-footer no-padding">
                    <ul class="nav nav-stacked">
                    <li><a href="#" style="font-size:13px;">Student Name <span class="pull-right text-blue">{{ student.strFirstname + ' ' + student.strLastname + ' ' + student.strMiddlename }}</span></a></li>
                        <li><a href="#" style="font-size:13px;">Student Number <span class="pull-right text-blue">{{ student.strStudentNumber.replace(/-/g, '') }}</span></a></li>
                        <li><a href="#" style="font-size:13px;">Current Program <span class="pull-right text-blue">{{ registration.strProgramCode }}</span></a></li>                            
                        <li><a href="#" style="font-size:13px;">Current Curriculum <span class="pull-right text-blue">{{ registration.strName }}</span></a></li>                            
                        <li v-if="shifted"><a href="#" style="font-size:13px;">Current Program <span class="pull-right text-blue">{{ shifted.strProgramCode }}</span></a></li>                            
                        <li v-if="shifted"><a href="#" style="font-size:13px;">Current Curriculum <span class="pull-right text-blue">{{ shifted.strName }}</span></a></li>                            
                    </ul>
                </div>
            </div>
            <div class="box box-primary">
                <div class="box-header">
                    <h4>Shift Course</h4>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="form-group col-sm-6">
                            <label>Select Program to Shift to</label>
                            <select @change="getCurriculum($event)" v-model="program_selected" class="form-control" required>
                                <option v-for="item in programs" :value="item.intProgramID">{{ item.strProgramCode }}</option>
                            </select>
                        </div>
                        <div v-if="curriculum.length > 0" class="form-group col-sm-6">
                            <label>Select Program to Shift to</label>
                            <select v-model="curriculum_selected" class="form-control" required>
                                <option v-for="item in curriculum" :value="item.intID">{{ item.strName }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div><!---content container--->
    </div><!---vue container--->
</aside>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@2.6.12"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
    integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>

<script>
new Vue({
    el: '#vue-container',
    data: {
        sem:'<?php echo $sem; ?>',
        id:'<?php echo $id; ?>',
        active_sem: undefined,
        student: undefined,
        registration: undefined,
        shifted: undefined,
        programs: [],
        curriculum: [],
        program_selected: undefined,
        curriculum_selected: undefined,
       
        
    },

    mounted() {

        let url_string = window.location.href;
        let url = new URL(url_string);

        this.loader_spinner = true;

        axios.get('<?php echo base_url(); ?>registrar/shifting_data/'+this.id+'/'+this.sem)
        .then((data) => {
            this.active_sem = data.data.active_sem;
            this.student = data.data.student;
            this.registration = data.data.registration;
            this.shifted = data.data.shifted;
            this.programs = data.data.programs;
                        
        })
        .catch((error) => {
            console.log(error);
        })



    },

    methods: {        
       
        getCurriculum: function(event){
            axios.get('<?php echo base_url(); ?>registrar/get_curriculum/'+event.target.value)
            .then((data) => {
                this.curriculum = data.data.curriculum;                                            
            })
            .catch((error) => {
                console.log(error);
            })
            
        }

    }

})
</script>