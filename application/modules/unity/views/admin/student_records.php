<aside class="right-side" id="registration-container">    
    <section class="content-header">
        <h1>
            <small>
                <a class="btn btn-app" :href="base_url + 'unity/student_viewer/' + student.intID"><i class="ion ion-arrow-left-a"></i>All Details</a>                                       
            </small>
        </h1>
        <hr />
    </section>
        <hr />
    <div class="content">        
    <div class="row">
        <div class="col-sm-12">
            <div v-if="student" class="box box-widget widget-user-2">
                <!-- Add the bg color to the header using any of the bg-* classes -->
                <div class="widget-user-header bg-red">
                    <!-- /.widget-user-image -->
                    <h3 class="widget-user-username" style="text-transform:capitalize;margin-left:0;font-size:1.3em;">{{ student.strLastname.toUpperCase() }}, {{ student.strFirstname.toUpperCase() }} {{ student.strMiddlename?student.strMiddlename.toUpperCase():'' }}</h3>
                    <h5 class="widget-user-desc" style="margin-left:0;">{{ student.strProgramDescription }} {{ (student.strMajor != 'None')?'Major in '+student.strMajor:'' }}</h5>
                </div>
                <div class="box-footer no-padding">
                    <ul class="nav nav-stacked">
                        <li><a href="#" style="font-size:13px;">Student Number <span class="pull-right text-blue">{{ student.strStudentNumber.replace(/-/g, '') }}</span></a></li>
                    </ul>
                </div>
            </div>                
        </div> 
        <div class="col-sm-12">
            <div v-for="term in records" class="box box-success">
                <div class="box-header">
                    <h4>{{ term.reg.enumSem + " " + term.reg.term_label + " SY" + term.reg.strYearStart + "-" + term.reg.strYearEnd }}</h4>
                </div>
                <div class="box-body">
                    <table class="table table-condensed table-bordered">
                        <thead>
                            <tr>
                                <th>Section Code</th>
                                <th>Course Code</th>
                                <th>Units</th>
                                <th>Midterm</th>
                                <th>Final</th>
                                <th>Remarks</th>
                                <th>Faculty</th>                                      
                            </tr>
                        </thead>
                        <tbody>                                          
                            <tr v-for="record in term.records" style="font-size: 13px;">
                                <td>{{ record.strClassName + record.year + record.strSection + (record.sub_section?record.sub_section:'') }}</td>
                                <td>{{ record.strCode }}</td>
                                <td v-if="record.include_gwa">{{ record.strUnits }}</td>
                                <td v-else>({{ record.strUnits }})</td>
                                <td>{{ record.intFinalized >=1?record.v2:'NGS' }}</td>
                                <td>{{ record.intFinalized >=2?record.v3:'NGS' }}</td>
                                <td>{{ record.intFinalized >=1?record.strRemarks:'---' }}</td>     
                                <td>{{ record.facultyName }}</td>
                                                                               
                            </tr>
                            <tr style="font-size: 13px;">
                                <td></td>
                                <td align="right"><strong>TOTAL UNITS CREDITED:</strong></td>
                                <td>{{ term.records.units_earned }}</td>
                                <td colspan="3"></td>
                            </tr>
                            <tr style="font-size: 11px;">
                                <td></td>
                                <td align="right"><strong>GWA:</strong></td>
                                <td>{{ term.records.gwa }}</td>
                                <td colspan="3"></td>
                            </tr>

                        </tbody>
                    </table>
                </div>
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
function inArray(needle, haystack) {
    var length = haystack.length;
    for(var i = 0; i < length; i++) {
        if(haystack[i] == needle) return true;
    }
    return false;
}
new Vue({
    el: '#registration-container',
    data: {
        id: '<?php echo $id; ?>',    
        base_url: '<?php echo base_url(); ?>',
        slug: undefined,
        student:undefined,         
        records: [],         
    },

    mounted() {

        let url_string = window.location.href;        
        if(this.id != 0){            
            //this.loader_spinner = true;
            axios.get(this.base_url + 'unity/student_records_data/' + this.id + '/')
                .then((data) => {                                          
                    this.student = data.data.student;
                    this.records = data.data.data;            
                })
                .catch((error) => {
                    console.log(error);
                })
        }

    },

    methods: {      
        
    }

})
</script>

