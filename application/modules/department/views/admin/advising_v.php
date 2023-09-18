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
        <div class="box box-primary">
            <div class="box-header">
                <div class="row">
                    <div class="col-sm-4">
                        <select class="form-control" required @change="selectTerm($event)" v-model="sem">
                            <option v-for="term in sy" :value="term.intID">{{ term.enumSem + " " + term.term_label + " SY " + term.strYearStart + "-" + term.strYearEnd }}</option>
                        </select>
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
        id: <?php echo $id; ?>,    
        sem: <?php echo $sem; ?>,
        base_url: '<?php echo base_url(); ?>',
        sy: [],
    },

    mounted() {

        let url_string = window.location.href;        
        if(this.id != 0){            
            //this.loader_spinner = true;
            axios.get(this.base_url + 'department/load_subjects_data/' + this.id + '/' + this.sem)
                .then((data) => {                                          
                    this.sy = data.data.sy;     
                    this.sem =  data.data.active_sem.intID;
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

