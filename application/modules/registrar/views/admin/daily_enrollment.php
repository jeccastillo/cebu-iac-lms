<aside class="right-side" id="registration-container">    
    <section class="content-header">
        <h1>
            Total Daily Enrollment
            <small>
                <a class="btn btn-app" href="<?php echo base_url(); ?>registrar/registrar_reports" >
                    <i class="ion ion-arrow-left-a"></i>
                    Enrollment
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
        </div>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Regular</th>
                    <th>Online</th>
                    <th>Hybrid</th>
                    <th>Hyflex</th>
                    <th>Total Enrollment</th>
                </tr>                
            </thead>
            <tbody>
                <tr v-if="dates" v-for="date in dates">
                    <td>{{ date.date }}</td>
                    <td v-if="date.regular > 0"><b>{{ date.regular }}</b></td>
                    <td v-else>{{ date.regular }}</td>
                    <td v-if="date.online > 0"><b>{{ date.online }}</b></td>
                    <td v-else>{{ date.online }}</td>
                    <td v-if="date.hybrid > 0"><b>{{ date.hybrid }}</b></td>
                    <td v-else>{{ date.hybrid }}</td>
                    <td v-if="date.hyflex > 0"><b>{{ date.hyflex }}</b></td>
                    <td v-else>{{ date.hyflex }}</td>
                    <td v-if="date.total > 0"><b>{{ date.total }}</b></td>
                    <td v-else>{{ date.total }}</td>                    
                </tr>
                <tr v-if="dates">
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>Total:</td>
                    <td>{{ full_total }}</td>
                </tr>                
            </tbody>
        </table>
                         
      
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
    var query_str = 'admissions/applications/enapps?current_sem=<?php echo $active_sem['intID']; ?>&start=<?php echo $start; ?>&end=<?php echo $end; ?>';
<?php else: ?>
    var query_str = 'admissions/applications/enapps?current_sem=<?php echo $active_sem['intID']; ?>';
<?php endif; ?>
new Vue({
    el: '#registration-container',
    data: {                    
        base_url: '<?php echo base_url(); ?>',
        current_sem: '<?php echo $active_sem['intID']; ?>',        
        dates: undefined,
        full_total: 0,
                      
    },

    mounted() {

        axios.get(api_url + query_str)
            .then((data) => {       
                var formdata= new FormData();
                formdata.append('applicant_data', JSON.stringify(data.data.data)); 
                formdata.append('start','<?php echo ($start!=0)?$start:date("Y-m-d"); ?>');                       
                formdata.append('end','<?php echo ($end!=0)?$end:date("Y-m-d", strtotime('tomorrow')); ?>');
                axios.post(this.base_url + 'registrar/daily_enrollment_report_data/',formdata, {
                    headers: {
                        Authorization: `Bearer ${window.token}`
                    }
                    })
                    .then((data) => {  

                        this.dates = data.data.data;
                        for(i in this.dates){
                            this.full_total += this.dates[i].total;
                        }
                        console.log(this.dates);
                    
                    })
                .catch((error) => {
                    console.log(error);
                    
                });
            })
            .catch((error) => {
                console.log(error);
            });
        

    },

    methods: {      
       
                                       
    }

});

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
        document.location = base_url + 'registrar/daily_enrollment_report/'+start.format('YYYY-MM-DD')+'/'+end.add('days', 1).format('YYYY-MM-DD');
        
    }
    );  
});
</script>

