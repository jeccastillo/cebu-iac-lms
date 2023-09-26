
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
                        <li><a href="#" style="font-size:13px;">Student Number <span class="pull-right text-blue">{{ student.strStudentNumber.replace(/-/g, '') }}</span></a></li>
                        <li><a href="#" style="font-size:13px;">Curriculum <span class="pull-right text-blue">{{ student.strName }}</span></a></li>                            
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