<aside class="right-side" id="registration-container">    
    <section class="content-header">
        <h1>
            Enrollment Summary
            <small>
                <a class="btn btn-app" href="<?php echo base_url(); ?>registrar/registrar_reports" >
                    <i class="ion ion-arrow-left-a"></i>
                    All Reports
                </a> 
                <a class="btn btn-app" target="_blank" href="<?php echo $pdf_link; ?>" ><i class="fa fa-book"></i>Generate PDF</a> 
                <a class="btn btn-app" target="_blank" href="<?php echo $excel_link; ?>" ><i class="fa fa-book"></i>Generate Excel</a> 
            </small>
        </h1>     
    </section>
        <hr />
    <div class="content">                
        <h4>Officially Enrolled</h4>
        <div>
            <div class="form-group pull-right">
                <label>Term Select</label>
                <select v-model="current_sem" @change="changeTermSelected($event)" class="form-control" >
                    <option v-for="s in sy" :value="s.intID">{{s.term_student_type + ' ' + s.enumSem + ' ' + s.term_label + ' ' + s.strYearStart + '-' + s.strYearEnd}}</option>                      
                </select>   
            </div>
            <table v-if="enrolled" class="table table-bordered table-striped">
                <tr>
                    <th>Program</th>
                    <th>Freshman</th>
                    <th>Transferee</th>
                    <th>Foreign</th>
                    <th>Second Degree</th>
                    <th>Continuing</th>
                    <th>Total</th>
                </tr>
                <tr v-for="item in enrolled">
                    <td>
                        {{ item.strProgramDescription }} {{ (item.strMajor != "None" && item.strMajor != "")?'Major in '+item.strMajor:'' }}
                    </td>
                    <td>
                        {{ item.enrolled_freshman }}
                    </td>
                    <td>
                        {{ item.enrolled_transferee }}
                    </td>
                    <td>
                        {{ item.enrolled_foreign }}
                    </td>
                    <td>
                        {{ item.enrolled_second }}
                    </td>
                    <td>
                        {{ item.enrolled_continuing }}
                    </td>
                    <td>
                        {{ item.enrolled_freshman + item.enrolled_transferee + item.enrolled_foreign + item.enrolled_second + item.enrolled_continuing }}
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td><strong>{{ all_enrolled }}</strong></td>
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
        current_sem: '<?php echo $sem; ?>',        
        enrolled: undefined,        
        programs: undefined,        
        all_enrolled: 0,
        sy: [],
                      
    },

    mounted() {

        let url_string = window.location.href;        
        if(this.id != 0){            
            //this.loader_spinner = true;
            axios.get(this.base_url + 'registrar/enrollment_summary_data/' + this.current_sem)
                .then((data) => {  
                    this.sy = data.data.sy;
                    this.enrolled = data.data.data;
                    for(i in this.enrolled){
                        this.all_enrolled +=  this.enrolled[i].enrolled_freshman + this.enrolled[i].enrolled_foreign + this.enrolled[i].enrolled_second + this.enrolled[i].enrolled_transferee;
                    }
                   
                })
            .catch((error) => {
                console.log(error);
                
            });
        }

    },

    methods: {      
        changeTermSelected: function(event){
            document.location = this.base_url + "registrar/enrollment_summary/" + event.target.value;
        }, 
                                       
    }

})
</script>

