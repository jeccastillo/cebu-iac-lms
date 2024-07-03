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
            <br />
            <?php if($start != 0): ?>
                <?php if($start == $end): ?>
                    <?php echo date('M j, Y',strtotime($start)); ?>
                <?php else: ?>
                from <?php echo date('M j, Y',strtotime($start))." to ".date('M j, Y',strtotime($end)); ?>
                <?php endif; ?>
            <?php endif; ?>
            
        </h1>     
    </section>
        <hr />
    <div class="content"> 
        <div class="input-group pull-right">
            <button class="btn btn-default pull-right" id="daterange-btn-admissions">
                <i class="fa fa-calendar"></i> Choose Date Range
                <i class="fa fa-caret-down"></i>
            </button>
            <select id="select-term-leads" class="form-control" >
                <?php foreach($sy as $s): ?>
                    <option <?php echo ($current_sem == $s['intID'])?'selected':''; ?> value="<?php echo $s['intID']; ?>"><?php echo $s['term_student_type']." ".$s['enumSem']." ".$s['term_label']." ".$s['strYearStart']."-".$s['strYearEnd']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="row">
            <div class="col-md-6">
                <h4>Quick Stats</h4>
                <table v-if="stats" class="table table-bordered table-striped">
                    <tr>
                        <th>Sign Ups</th>
                        <td>{{ total }}</td>
                        <td>Paid: {{ stats.paid }} Unpaid: {{ stats.unpaid }}</td>
                    </tr>
                    <tr>
                        <th>Will Not Proceed</th>
                        <td>{{ stats.will_not_proceed }}</td>
                        <td>{{ ((stats.will_not_proceed/total)*100).toFixed(2) }}%</td>
                    </tr>
                    <tr>
                        <th>Cancelled Applications (Paid Application fee but was not Interviewed)</th>
                        <td>{{ stats.cancelled }}</td>
                        <td>{{ ((stats.cancelled/stats.paid)*100).toFixed(2) }}%</td>
                    </tr>                    
                    <tr>
                        <th>Interviewed</th>
                        <td>{{ stats.interviewed }}</td>
                        <td>{{ ((stats.interviewed/stats.paid)*100).toFixed(2) }}%</td>
                    </tr>
                    <tr>
                        <th>For Reservation:</th>
                        <td>{{ stats.for_reservation}}</td>
                        <td></td>
                    </tr>
                    <tr>
                        <th>Rejected</th>
                        <td>{{ stats.rejected }}</td>
                        <td>{{ ((stats.rejected/(stats.for_reservation + stats.reserved + stats.confirmed + stats.enlisted + stats.for_enrollment + stats.enrolled + stats.withdrawn_before + stats.withdrawn_after + stats.withdrawn_end))*100).toFixed(2) }}%</td>
                    </tr>
                    <tr>
                        <th>Did not Reserve</th>
                        <td>{{ stats.did_not_reserve }}</td>
                        <td>{{ ((stats.did_not_reserve/(stats.for_reservation + stats.reserved + stats.confirmed + stats.enlisted + stats.for_enrollment + stats.enrolled + stats.withdrawn_before + stats.withdrawn_after + stats.withdrawn_end))*100).toFixed(2) }}%</td>
                    </tr>
                    <tr>
                        <th>Reserved</th>
                        <td>{{ stats.reserved }}</td>
                        <td>{{ (((stats.reserved)/(stats.for_reservation + stats.reserved + stats.withdrawn_before + stats.withdrawn_after + stats.withdrawn_end))*100).toFixed(2) }}%</td>
                    </tr>                    
                    <tr>
                        <th>Enrolled</th>
                        <td>{{ stats.enrolled }}</td>
                        <td>{{ ((stats.enrolled/(stats.reserved))*100).toFixed(2) }}%</td>
                    </tr>
                    <tr>
                        <th>Withdrawn Enrollment Before Opening of SY</th>
                        <td>{{ stats.withdrawn_before }}</td>
                        <td>{{ ((stats.withdrawn_before/(stats.enrolled + stats.withdrawn_before + stats.withdrawn_after + stats.withdrawn_end))*100).toFixed(2) }}%</td>
                    </tr>                    
                    <tr>
                        <th>Withdrawn Enrollment After Opening of SY</th>
                        <td>{{ stats.withdrawn_after }}</td>
                        <td>{{ ((stats.withdrawn_after/(stats.enrolled + stats.withdrawn_before + stats.withdrawn_after + stats.withdrawn_end))*100).toFixed(2) }}%</td>
                    </tr>
                    <tr>
                        <th>Withdrawn Enrollment at the End of the Term</th>
                        <td>{{ stats.withdrawn_end }}</td>
                        <td>{{ ((stats.withdrawn_end/(stats.enrolled + stats.withdrawn_before + stats.withdrawn_after + stats.withdrawn_end))*100).toFixed(2) }}%</td>
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
<script src="<?php echo base_url(); ?>assets/themes/default/js/vue.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
    integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="<?php echo base_url(); ?>assets/themes/default/js/axios.min.js"></script>

<script>
<?php if($start!=0): ?>
    var query_str = 'admissions/applications/adstats?current_sem=<?php echo $current_sem; ?>&campus=<?php echo $campus; ?>&start=<?php echo $start; ?>&end=<?php echo $end; ?>';
<?php else: ?>
    var query_str = 'admissions/applications/adstats?current_sem=<?php echo $current_sem; ?>&campus=<?php echo $campus; ?>';
<?php endif; ?>
new Vue({
    el: '#registration-container',
    data: {                    
        base_url: '<?php echo base_url(); ?>',
        current_sem: '<?php echo $current_sem; ?>',
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
                this.total =this.stats.will_not_proceed + this.stats.reserved + this.stats.floating +
                            this.stats.for_reservation + this.stats.for_interview + this.stats.waiting + this.stats.new + 
                            this.stats.did_not_reserve + this.stats.rejected + this.stats.cancelled +
                            this.stats.withdrawn_before + this.stats.withdrawn_after + this.stats.withdrawn_end;
                
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
    $('#daterange-btn-admissions').daterangepicker(
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
        document.location = base_url + 'admissionsV1/admissions_report/<?php echo $current_sem; ?>/'+start.format('YYYY-MM-DD')+'/'+end.format('YYYY-MM-DD');
        
    }
    );
    
    $("#select-term-leads").on('change', function(e){
        const term = $(this).val();
        document.location = "<?php echo base_url()."admissionsV1/admissions_report/"; ?>"+term;
    });
});
</script>

