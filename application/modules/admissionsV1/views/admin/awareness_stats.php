<aside class="right-side" id="registration-container">    
    <section class="content-header">
        <h1>
            Admissions Report - Awareness
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
            <select id="select-term-leads" class="form-control" >
                <?php foreach($sy as $s): ?>
                    <option <?php echo ($current_sem == $s['intID'])?'selected':''; ?> value="<?php echo $s['intID']; ?>"><?php echo $s['term_student_type']." ".$s['enumSem']." ".$s['term_label']." ".$s['strYearStart']."-".$s['strYearEnd']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="row">
            <div class="col-md-6">
                <h4>Awareness Stats</h4>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Source</th>
                            <th>Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in stats">
                            <td>{{ item.source }}</td>
                            <td>{{ item.count }}</td>
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
        current_sem: '<?php echo $current_sem; ?>',
        stats: [],                      
    },

    mounted() {

        let url_string = window.location.href;        
        if(this.id != 0){            
            //this.loader_spinner = true;
            
            axios.get(api_url + 'admissions/applications/awareness?current_sem=<?php echo $current_sem; ?>&campus=<?php echo $campus; ?>')
            .then((data) => {       
                // console.log(data);           
                this.stats = data.data.applications;                       
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
        document.location = base_url + 'admissionsV1/admissions_report/<?php echo $current_sem; ?>/'+start.format('YYYY-MM-DD')+'/'+end.add('days', 1).format('YYYY-MM-DD');
        
    }
    );
    
    $("#select-term-leads").on('change', function(e){
        const term = $(this).val();
        document.location = "<?php echo base_url()."admissionsV1/awareness_stats/"; ?>"+term;
    });
});
</script>

