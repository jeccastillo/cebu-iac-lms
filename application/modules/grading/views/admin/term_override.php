<aside class="right-side" id="registration-container">    
    <section class="content-header">
        <h1>
            Term-Grading System Override
            <small>                
            </small>
        </h1>     
    </section>
        <hr />
    <div class="content">  
        <div class="box box-primary">
            <div class="box-header">
                <h4>Override Grading System</h4>
            </div>
            <div class="box-body">
                <div class="row" style="margin-bottom:10px">                    
                    <div class="col-sm-4">
                        <label>Select Term</label>
                        <select class="form-control" @change="selectTerm($event)" v-model="sem">
                            <option v-for="term in terms" :value="term.intID">{{ term.enumSem + " " + term.term_label + " SY " + term.strYearStart + "-" + term.strYearEnd }}</option>
                        </select>
                    </div>
                </div>
                <h4>Add Override</h4>
                <form method="post" @submit.prevent="addOverride">
                    <div class="row" style="margin-bottom:10px">                    
                        <div class="col-sm-4">
                            <label>Select Subject</label>
                            <select class="form-control" required v-model="request.subject_id">
                                <option v-for="subject in subjects" :value="subject.intID">{{ subject.strCode + " " + subject.strDescription  }}</option>
                            </select>
                        </div>
                        <div class="col-sm-4">
                            <label>Grading System</label>
                            <select class="form-control" required v-model="request.grading_system_id">
                                <option v-for="item in grading_systems" :value="item.id">{{ item.name  }}</option>
                            </select>
                        </div>
                        <div class="col-sm-4">
                            <label>Period</label>
                            <select class="form-control" required v-model="request.period">
                                <option value="midterm">Midterm</option>
                                <option value="final">Final</option>
                            </select>
                        </div>
                    </div>
                </form>
                <hr />                                
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
        base_url: '<?php echo base_url(); ?>',
        sem: '<?php echo $sem; ?>',                    
        terms: [],      
        grading_systems:[],        
        subjects: [],
        overrides:[],
        request:{
            subject_id: undefined,
            period: undefined,
            syid: '<?php echo $sem; ?>',
            grading_system_id: undefined,
        }
    },

    mounted() {

        let url_string = window.location.href;        
        if(this.id != 0){            
            //this.loader_spinner = true;
            axios.get(this.base_url + 'grading/term_override_data/'+this.sem)
                .then((data) => {                                      
                  this.terms = data.data.sy;                       
                  this.grading_systems = data.data.grading_systems;  
                  this.subjects = data.data.subjects;
                  this.overrides = data.data.overrides;   
                  this.sem = data.data.active_sem.intID;     
                })
            .catch((error) => {
                console.log(error);
                
            });
        }

    },

    methods: {      
        selectTerm: function(event){
            document.location = base_url + 'grading/term_override/'+event.target.value;

        },
        addOverride: function(){
            var formdata= new FormData();
            for (const [key, value] of Object.entries(this.request)) {
                formdata.append(key,value);
            }  
        },
                                       
    }

})
</script>

