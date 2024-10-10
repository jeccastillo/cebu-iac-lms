<aside class="right-side" id="registration-container">    
    <section class="content-header">
        <h1>
           Advisers per Section                              
        </h1>
        <div class="box-tools pull-right">
            <label>Term</label>
            <select v-model="term" @change="changeTermSelected" class="form-control" >
                <option v-for="s in sy" :value="s.intID">{{s.term_student_type + ' ' + s.enumSem + ' ' + s.term_label + ' ' + s.strYearStart + '-' + s.strYearEnd}}</option>                      
            </select>   
        </div>        
        <hr />
    </section>
        <hr />
    <div v-if="!loading" class="content">
           <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Section</th>
                        <th>Adviser</th>
                        <th>Year/Grade (1 = grade 11, 2 = Grade 12)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="section in faculty_sections">
                        <td>{{ section.name }}</td>
                        <td>{{ section.strLastname + ", " + section.strFirstname }}</td>
                        <td>{{ section.year }}</td>
                    </tr>
                </tbody>
           </table>
    </div>    
    <div v-else class="content">             
        <h4>Loading Data Please Wait...</h4>
    </div>
</aside>

<script src="<?php echo base_url(); ?>assets/themes/default/js/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script src="<?php echo base_url(); ?>assets/themes/default/js/vue.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
    integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="<?php echo base_url(); ?>assets/themes/default/js/axios.min.js"></script>

<script>
new Vue({
    el: '#registration-container',
    data: {        
        base_url: '<?php echo base_url(); ?>',
        term: '<?php echo $term; ?>',        
        sy: [],
        loading: true,      
        faculty_sections: [],  
    },    
    mounted() {

        let url_string = window.location.href;        
        if(this.id != 0){            
            //this.loader_spinner = true;
            axios.get(this.base_url + 'academics/advisers_data/' + this.term)
                .then((data) => {                                          
                    this.loading = false; 
                    this.sy = data.data.sy;
                    this.faculty_sections = data.data.faculty_sections;
                         
                })
                .catch((error) => {
                    console.log(error);
                })
        }

    },

    methods: {   
        changeTermSelected: function(){
            document.location = this.base_url + "academics/faculty_advisers/" + 
            this.term;
        },

        deansListPdf:function(){
            window.open(this.base_url + 'pdf/deans_list/' + this.term + '/' + this.period, 'target= "_blank"');
        },

        deansListExcel:function(){
            window.open(base_url + 'excel/deans_list/' + this.term + '/' + this.period, 'target= "_blank"');
        },
    }
})
</script>

