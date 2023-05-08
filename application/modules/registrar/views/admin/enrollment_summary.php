<aside class="right-side" id="registration-container">    
    <section class="content-header">
        <h1>
            Enrollment Summary
        </h1>     
    </section>
        <hr />
    <div class="content">        
        <h4>Reserved</h4>
        <div>
            <table v-if="reserved" class="table table-bordered">
                <tr>
                    <th>Program</th>
                    <th>Freshman</th>
                    <th>Transferee</th>
                    <th>Foreign</th>
                    <th>Second Degree</th>
                </tr>
                <tr v-for="prog in reserved">
                    <td>{{ prog[0].program }}</td>
                    <td v-for="type in prog" v-if="type.student_type == 'freshman'">
                        {{ type.reserved_count }}
                    </td>
                    <td v-for="type in prog" v-if="type.student_type == 'transferee'">
                        {{ type.reserved_count }}
                    </td>
                    <td v-for="type in prog" v-if="type.student_type == 'foreign'">
                        {{ type.reserved_count }}
                    </td>
                    <td v-for="type in prog" v-if="type.student_type == 'second degree'">
                        {{ type.reserved_count }}
                    </td>
                </tr>
            </table>            
        </div>
        <!-- <h4>Enrolled</h4>
        <div v-for="prog in reserved" class="row">
            <div class="col-md-6">
                {{ prog.program }}
            </div>
            <div class="col-md-6">
                {{ prog.reserved_count }}
            </div>
        </div> -->
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
        current_sem: '<?php echo $active_sem['intID']; ?>',
        reserved:undefined,
        programs: undefined,
                      
    },

    mounted() {

        let url_string = window.location.href;        
        if(this.id != 0){            
            //this.loader_spinner = true;
            axios.get(this.base_url + 'registrar/enrollment_summary_data/')
                .then((data) => {  
                   console.log(data);
                   this.programs = data.data.programs;
                   axios.get(api_url + 'admissions/applications/stats?current_sem='+this.current_sem)
                    .then((data) => {  
                        this.reserved = data.data; 
                        console.log(this.reserved);                   
                    })
                    .catch((error) => {
                        console.log(error);
                    });
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

