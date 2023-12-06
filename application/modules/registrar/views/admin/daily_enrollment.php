<aside class="right-side" id="registration-container">    
    <section class="content-header">
        <h1>
            Total Daily Enrollment
            <small>
                <a class="btn btn-app" href="<?php echo base_url(); ?>registrar/registrar_reports" >
                    <i class="ion ion-arrow-left-a"></i>
                    Enrollment
                </a> 
                <form style="display: inline;" ref="pdfform" target="_blank" method="post" action="<?php echo $pdf_link; ?>">
                    <input type="hidden" name="dates" v-model="data_post" />
                    <input type="hidden" name="totals" v-model="totals_post" />
                    <input type="hidden" name="full_total" v-model="full_total_post" />
                    <a class="btn btn-app" target="_blank" href="#" @click.prevent.stop="submitForm('pdf')" ><i class="fa fa-book"></i>Generate PDF</a> 
                </form>
                <form style="display: inline;" ref="excelform" target="_blank" method="post" action="<?php echo $excel_link; ?>">                     
                    <input type="hidden" name="dates" v-model="data_post" />
                    <input type="hidden" name="totals" v-model="totals_post" />
                    <input type="hidden" name="full_total" v-model="full_total_post" />
                    <a class="btn btn-app" target="_blank" href="#" @click.prevent.stop="submitForm('excel')" ><i class="fa fa-book"></i>Generate Excel</a> 
                </form>
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
        <div class="form-group pull-right">
                <label>Term Select</label>
                <select v-model="current_sem" @change="changeTermSelected($event)" class="form-control" >
                    <option v-for="s in sy" :value="s.intID">{{s.term_student_type + ' ' + s.enumSem + ' ' + s.term_label + ' ' + s.strYearStart + '-' + s.strYearEnd}}</option>                      
                </select>   
        </div>
        <table class="table table-bordered table-striped">
            <thead style="position: sticky;top: 0" class="thead-dark">
                <tr>
                    <th>Date</th>
                    <th>Freshman</th>
                    <th>Transferee</th>
                    <th>Second Degree</th>                    
                    <th>Total Enrollment</th>
                </tr>                
            </thead>
            <tbody>
                <tr v-if="dates" v-for="date in dates">
                    <td>{{ date.date }}</td>
                    <td v-if="date.freshman > 0"><b>{{ date.freshman }}</b></td>
                    <td v-else>{{ date.freshman }}</td>
                    <td v-if="date.transferee > 0"><b>{{ date.transferee }}</b></td>
                    <td v-else>{{ date.transferee }}</td>
                    <td v-if="date.second > 0"><b>{{ date.second }}</b></td>
                    <td v-else>{{ date.second }}</td>                    
                    <td v-if="date.total > 0"><b>{{ date.total }}</b></td>
                    <td v-else>{{ date.total }}</td>                    
                </tr>
                <tr v-if="totals">
                    <td>Total:</td>
                    <td><strong>{{ totals.freshman }}</strong></td>
                    <td><strong>{{ totals.transferee }}</strong></td>                    
                    <td><strong>{{ totals.second }}</strong></td>
                    <td><strong>{{ full_total }}</strong></td>
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
var current_sem  = <?php echo $active_sem['intID']; ?>;
new Vue({
    el: '#registration-container',
    data: {                    
        base_url: '<?php echo base_url(); ?>',
        current_sem: '<?php echo $active_sem['intID']; ?>',        
        dates: undefined,
        data_post: [],
        full_total_post: 0,
        totals_post: undefined,
        full_total: 0,
        totals: undefined,
        sy: [],
                      
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
                        this.totals = data.data.totals;
                        this.sy = data.data.sy;
                        for(i in this.dates){
                            this.full_total += this.dates[i].total;
                            this.data_post.push(this.dates[i]);
                        }
                        this.data_post = JSON.stringify(this.data_post);
                        this.full_total_post = JSON.stringify(this.full_total);
                        this.totals_post = JSON.stringify(this.totals);
                    
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
        submitForm: function(type){
            if(type == 'pdf')
                this.$refs.pdfform.submit();
            else
                this.$refs.excelform.submit();
        },
        changeTermSelected: function(event){
            document.location = this.base_url + "registrar/daily_enrollment_report/<?php echo ($start!=0)?$start:date("Y-m-d"); ?>/<?php echo ($end!=0)?$end:date("Y-m-d", strtotime('tomorrow')); ?>/" + event.target.value;
        },
                                       
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
        document.location = base_url + 'registrar/daily_enrollment_report/'+start.format('YYYY-MM-DD')+'/'+end.add('days', 1).format('YYYY-MM-DD')+'/' + current_sem;
        
    }
    );  
});
</script>

