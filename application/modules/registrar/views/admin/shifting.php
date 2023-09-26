
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
            
                        
        })
        .catch((error) => {
            console.log(error);
        })



    },

    methods: {        
       
        

    }

})
</script>