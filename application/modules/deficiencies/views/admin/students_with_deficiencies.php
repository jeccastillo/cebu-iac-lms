<aside class="right-side" id="registration-container">    
    <section class="content-header">
        <h1>
            Student Deficiencies
            <small>          
                <a class="btn btn-app" :href="base_url + 'excel/deficiency_report_data/' + sem"><i class="fa fa-file"></i>Download Excel</a>                       
            </small>
        </h1>     
    </section>
        <hr />
    <div class="content">  
        <div class="box box-primary">
            <div class="box-header">
                <h4>Students with Deficiencies</h4>                
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
                <hr />
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Student Number</th>                                                                          
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="students.length == 0">
                            <td colspan='8'>No Students with deficiencies for this term</td>
                        </tr>
                        <tr v-else v-for="(item,index) in students">
                            <td>{{ index+1 }}</td>
                            <td><a :href="base_url+'deficiencies/student_deficiencies/'+item.student_id+'/'+sem">{{ item.strLastname + " " + item.strFirstname }}</a></td>
                            <td>{{ item.strStudentNumber.replace(/-/g, "")  }}</td>                          
                        </tr>
                    </tbody>
                </table>                              
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
        active_sem: undefined,      
        students:[],              
        terms: [],            
        
      
        
    },

    mounted() {

        let url_string = window.location.href;        
        if(this.id != 0){            
            //this.loader_spinner = true;
            axios.get(this.base_url + 'deficiencies/deficiency_report_data/'+this.sem)
                .then((data) => {                                      
                    this.terms = data.data.sy;                                        
                    this.sem = data.data.active_sem.intID;                     
                    this.active_sem = data.data.active_sem;
                    this.students = data.data.students;                    
                })
            .catch((error) => {
                console.log(error);
                
            });
        }

    },

    methods: {      
        selectTerm: function(event){
            document.location = base_url + 'deficiencies/deficiency_report/'+event.target.value;

        },       
                                              
    }

})
</script>

