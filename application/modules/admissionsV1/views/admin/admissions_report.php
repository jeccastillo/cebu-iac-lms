<aside class="right-side" id="registration-container">    
    <section class="content-header">
        <h1>
            Admissions Report
            <small>
                <a class="btn btn-app" href="<?php echo base_url(); ?>admissionsV1/view_all_leads" >
                    <i class="ion ion-arrow-left-a"></i>
                    View Leads
                </a> 
            </small>
        </h1>     
    </section>
        <hr />
    <div class="content">    
        <div class="row">
            <div class="col-md-6">
                <h4>Quick Stats</h4>
                <table class="table table-bordered table-striped">
                    <tr>
                        <th>New Applicants</th>
                        <td>{{ stats.new }}</td>
                        <td></td>
                    </tr>
                    <tr>
                        <th>Waiting for Interview</th>
                        <td>{{ stats.waiting }}</td>
                        <td>{{ (stats.waiting/total)*100 }}%</td>
                    </tr>
                    <tr>
                        <th>For Interview</th>
                        <td>{{ stats.for_interview }}</td>
                        <td>{{ (stats.for_interview/total)*100 }}%</td>
                    </tr>
                    <tr>
                        <th>For Reservation</th>
                        <td>{{ stats.for_reservation }}</td>
                        <td>{{ (stats.for_reservation/total)*100 }}%</td>
                    </tr>
                    <tr>
                        <th>Reserved</th>
                        <td>{{ stats.reserved }}</td>
                        <td>{{ (stats.reserved/total)*100 }}%</td>
                    </tr>
                    <tr>
                        <th>For Enrollment</th>
                        <td>{{ stats.for_enrollment }}</td>
                        <td>{{ (stats.for_enrollment/total)*100 }}%</td>
                    </tr>
                    <tr>
                        <th>Confirmed and Complete Information</th>
                        <td>{{ stats.confirmed }}</td>
                        <td>{{ (stats.confirmed/total)*100 }}%</td>
                    </tr>
                    <tr>
                        <th>Enlisted</th>
                        <td>{{ stats.enlisted }}</td>
                        <td>{{ (stats.enlisted/total)*100 }}%</td>
                    </tr>
                    <tr>
                        <th>Enrolled</th>
                        <td>{{ stats.enrolled }}</td>
                        <td>{{ (stats.enrolled/total)*100 }}%</td>
                    </tr>
                    <tr>
                        <th>Total</th>
                        <td><strong>{{ total }}</strong></td>
                        <td></td>
                    </tr>
                </table>
            </div>
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
        stats: undefined,
        enrolled: undefined,        
        programs: undefined,
        all_reserved: 0,
        all_enrolled: 0,
        total: 0,
                      
    },

    mounted() {

        let url_string = window.location.href;        
        if(this.id != 0){            
            //this.loader_spinner = true;
            
            axios.get(api_url + 'admissions/applications/adstats?current_sem='+this.current_sem)
            .then((data) => {       
                // console.log(data);           
                this.stats = data.data;  
                this.total = this.stats.new + this.stats.enrolled + this.stats.enlisted + this.stats.confirmed + 
                            this.stats.for_enrollment + this.stats.reserved + 
                            this.stats.for_reservation + this.stats.for_interview + this.stats.waiting;
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

