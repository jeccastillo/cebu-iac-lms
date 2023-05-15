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
        <div class="input-group pull-right">
            <a href="<?php echo base_url(); ?>admissionsV1/admissions_report" class="btn btn-primary">
                Quick Stats
            </a>
        </div>   
        <div class="row">
            <div class="col-md-6">
                <h4>Quick Stats</h4>
                <table v-if="stats" class="table table-bordered table-striped">
                    <tr>
                        <th>Applicants</th>
                        <td>{{ total }}</td>
                        <td></td>
                    </tr>                    
                    <tr>
                        <th>Interviewed</th>
                        <td>{{ stats.for_reservation + stats.reserved + stats.confirmed + stats.enlisted + stats.for_enrollment + stats.enrolled }}</td>
                        <td>{{ (((stats.for_reservation + stats.reserved + stats.confirmed + stats.enlisted + stats.for_enrollment + stats.enrolled)/total)*100).toFixed(2) }}%</td>
                    </tr>
                    <tr>
                        <th>Reserved</th>
                        <td>{{ stats.reserved + stats.confirmed + stats.enlisted + stats.for_enrollment + stats.enrolled }}</td>
                        <td>{{ (((stats.reserved  + stats.confirmed + stats.enlisted + stats.for_enrollment + stats.enrolled)/(stats.for_reservation + stats.reserved + stats.confirmed + stats.for_enrollment + stats.enlisted + stats.enrolled))*100).toFixed(2) }}%</td>
                    </tr>                    
                    <tr>
                        <th>Enrolled</th>
                        <td>{{ stats.enrolled }}</td>
                        <td>{{ ((stats.enrolled/(stats.reserved + stats.confirmed + stats.enlisted + stats.for_enrollment + stats.enrolled))*100).toFixed(2) }}%</td>
                    </tr>
                    <tr>
                        <th>Rejected</th>
                        <td>{{ stats.rejected }}</td>
                        <td>{{ ((stats.rejected/total)*100).toFixed(2) }}%</td>
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
<?php if($start!=0): ?>
    var query_str = 'admissions/applications/adstats?current_sem='+this.current_sem+'&start=<?php echo $start; ?>&end=<?php echo $end; ?>';
<?php else: ?>
    var query_str = 'admissions/applications/adstats?current_sem='+this.current_sem;
<?php endif; ?>
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
            
            axios.get(api_url + query_str)
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

$(document).ready(function(){
    $('#daterange-btn').daterangepicker(
    {
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract('days', 1), moment().subtract('days', 1)],
            'Last 7 Days': [moment().subtract('days', 6), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract('month', 1).startOf('month'), moment().subtract('month',1).endOf('month')]
        },
        startDate: moment().subtract('days', 29),
        endDate: moment()
    },
    function(start, end) {
        document.location = base_url + 'admissionsV1/admissions_report/'+start.format('YYYY-MM-DD')+'/'+end.format('YYYY-MM-DD');
        
    }
    );  
});
</script>

