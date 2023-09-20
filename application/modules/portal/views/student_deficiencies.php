<aside class="right-side" id="registration-container">    
    <section class="content-header">
        <h1>
            My Deficiencies
            <small>                
            </small>
        </h1>     
    </section>
        <hr />
    <div class="content">  
        <div class="box box-primary">
            <div class="box-header">
                <h4>{{ student.strLastname + " " + student.strFirstname }}</h4>
                <h5>{{ student.strStudentNumber.replace(/-/g, "") }}</h5>
            </div>
            <div class="box-body">                
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Term</th>
                            <th>Deficiency</th>
                            <th>Department</th>
                            <th>Remarks</th>
                            <th>Date Added</th>
                            <th>Added By</th>
                            <th>Date Resolved</th>
                            <th>Resolved By</th>
                            <th>Status</th>                                                                                   
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="deficiencies.length == 0">
                            <td colspan='8'>No Deficiencies for this term</td>
                        </tr>
                        <tr v-else v-for="item in deficiencies">
                            <td>{{ item.enumSem + " " + item.term_label + " " + item.strYearStart + "-" + item.strYearEnd}}</td>
                            <td>{{ item.details }}</td>
                            <td>{{ item.department }}</td>
                            <td>{{ item.remarks }}</td>
                            <td>{{ item.date_added }}</td>
                            <td>{{ item.added_by }}</td>
                            <td>{{ item.date_resolved }}</td>
                            <td>{{ item.resolved_by }}</td>
                            <td>{{ item.status }}</td>                            
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
        id: '<?php echo $id; ?>',
        active_sem: undefined,      
        deficiencies:[],                      
        student: {
            strStudentNumber:'aaa-aaaa-aaa'
        },        
        
    },

    mounted() {

        let url_string = window.location.href;        
        if(this.id != 0){            
            //this.loader_spinner = true;
            axios.get(this.base_url + 'portal/student_deficiencies_data/'+this.sem+'/'+this.id)
                .then((data) => {                                                                                                  
                    this.sem = data.data.active_sem.intID;                     
                    this.active_sem = data.data.active_sem;
                    this.student = data.data.student;
                    this.deficiencies = data.data.deficiencies;
                                        
                })
            .catch((error) => {
                console.log(error);
                
            });
        }

    },

    methods: {             
       
                                       
    }

})
</script>

