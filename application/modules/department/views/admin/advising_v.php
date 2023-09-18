<aside class="right-side" id="registration-container">    
    <section class="content-header">
        <h1>
            <small>
                <a class="btn btn-app" :href="base_url + 'unity/student_viewer/' + id"><i class="ion ion-arrow-left-a"></i>All Details</a> 
                <!-- <a class="btn btn-app" :href="base_url + 'pdf/transcript/' + student.intID"><i class="fa fa-print"></i>Print Transcript</a>                                        -->
            </small>
        </h1>
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
                        <div class="pull-right">
                            <label>Select Term</label>
                            <select class="form-control" required @change="selectTerm($event)" v-model="sem">
                                <option v-for="term in sy" :value="term.intID">{{ term.enumSem + " " + term.term_label + " SY " + term.strYearStart + "-" + term.strYearEnd }}</option>
                            </select>
                        </div>
                        <h3 class="widget-user-username" style="text-transform:capitalize;margin-left:0;font-size:1.3em;">{{ student.strLastname.toUpperCase() }}, {{ student.strFirstname.toUpperCase() }} {{ student.strMiddlename?student.strMiddlename.toUpperCase():'' }}</h3>
                        <h5 class="widget-user-desc" style="margin-left:0;">{{ student.strProgramDescription }} {{ (student.strMajor != 'None')?'Major in '+student.strMajor:'' }}</h5>
                        
                    </div>
                    <div class="box-footer no-padding">
                        <ul class="nav nav-stacked">
                            <li><a href="#" style="font-size:13px;">Student Number <span class="pull-right text-blue">{{ student.strStudentNumber.replace(/-/g, '') }}</span></a></li>
                        </ul>
                    </div>
                </div>                
            </div>
        </div> 
        </div>
        <div class="box box-primary">
            <div class="box-header">
                
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
        id: <?php echo $id; ?>,    
        sem: <?php echo $sem; ?>,
        base_url: '<?php echo base_url(); ?>',
        sy: [],
        student: undefined,
    },

    mounted() {

        let url_string = window.location.href;        
        if(this.id != 0){            
            //this.loader_spinner = true;
            axios.get(this.base_url + 'department/load_subjects_data/' + this.id + '/' + this.sem)
                .then((data) => {                                          
                    this.sy = data.data.sy;     
                    this.sem =  data.data.active_sem.intID;
                    this.student  = data.data.student;
                })
                .catch((error) => {
                    console.log(error);
                })
        }

    },

    methods: {      
        selectTerm($event){
            document.location = base_url + 'department/load_subjects/' + this.id +'/'+ event.target.value;
        }
    }

})
</script>

