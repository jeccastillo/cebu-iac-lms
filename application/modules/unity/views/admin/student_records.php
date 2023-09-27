<aside class="right-side" id="registration-container">    
    <section class="content-header">
        <h1>
            Records
            <small>
                <a class="btn btn-app" :href="base_url + 'unity/student_viewer/' + student.intID"><i class="ion ion-arrow-left-a"></i>All Details</a> 
                <a class="btn btn-app" :href="base_url + 'pdf/transcript/' + student.intID"><i class="fa fa-print"></i>Print Transcript</a>                                       
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
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#tab_1" data-toggle="tab">Grades</a></li>
                    <li><a href="#tab_2" data-toggle="tab">Assessment</a></li>                        
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="tab_1">
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
                                        <tr :style="(record.intFinalized == 2)?'background-color:#ccc;':''" v-for="record in term.records" style="font-size: 13px;">
                                            <td>{{ record.strClassName + record.year + record.strSection + (record.sub_section?record.sub_section:'') }}</td>
                                            <td>{{ record.strCode }}</td>
                                            <td v-if="record.include_gwa == 1">{{ record.strUnits }}</td>
                                            <td v-else>({{ record.strUnits }})</td>
                                            <td :style="(record.intFinalized == 2)?'font-weight:bold;':''">{{ record.intFinalized >=1?record.v2:'NGS' }}</td>
                                            <td :style="(record.intFinalized == 2)?'font-weight:bold;':''">
                                                <span v-if="record.intFinalized >=2" :style="(record.strRemarks != 'Failed')?'color:#333;':'color:#990000;'">
                                                    {{ record.v3 }}
                                                </span>
                                                <span v-else>
                                                    NGS
                                                </span>
                                            </td>
                                            <td :style="(record.strRemarks != 'Failed')?'color:#333;':'color:#990000;'">{{ record.intFinalized >=1?record.strRemarks:'---' }}</td>   
                                            <td>{{ record.facultyName }}</td>
                                                                                        
                                        </tr>
                                        <tr style="font-size: 13px;">
                                            <td></td>
                                            <td align="right"><strong>Units Earned for Term:</strong></td>
                                            <td>{{ term.units_earned }}</td>
                                            <td colspan="3"></td>
                                        </tr>
                                        <tr style="font-size: 11px;">
                                            <td></td>
                                            <td align="right"><strong>Term GWA:</strong></td>
                                            <td>{{ term.gwa }}</td>
                                            <td colspan="3"></td>
                                        </tr>

                                    </tbody>
                                </table>
                            </div>
                        </div> 
                        <div class="box box-success">                
                            <div class="box-footer">
                                <div class="row" style="font-weight:bold;">
                                    <div class="col-sm-3 text-right">
                                        Total Units Earned:
                                    </div>
                                    <div class="col-sm-3">
                                        {{ units }}
                                    </div>
                                    <div class="col-sm-3 text-right">
                                        GWA
                                    </div>
                                    <div class="col-sm-3">
                                        {{ gwa }}
                                    </div>
                                </div>
                            </div>
                        </div> 
                    </div>
                    <div class="tab-pane" id="tab_2">
                        <div class="box box-success">                            
                            <div class="box-body">
                                <div v-for="record in curriculum_subjects">
                                    <table v-for="term in record" class="table table-condensed table-bordered">
                                        <thead>
                                            <tr>
                                                <th colspan="5">{{ stringifyNumber(term.year) + ' Year ' + stringifyNumber(term.sem) + ' Term' }}</th>
                                            </tr>
                                            <tr>                                               
                                                <th>Subject Code</th>
                                                <th>Description</th> 
                                                <th>Grade</th>                                                
                                                <th>Remarks</th>                                           
                                                <th>Units Earned</th>
                                            </tr>
                                        </thead>
                                        <tbody>                                          
                                            <tr :style="item.rec?'background-color:#009000;color:#f2f2f2':''" v-for="item in term.records" style="font-size: 13px;">                                                
                                                <td>{{ item.strCode }}</td>
                                                <td>{{ item.strDescription }}</td>   
                                                <td>{{ item.rec?item.rec.floatFinalGrade:'---' }}</td>
                                                <td>{{ item.rec?item.rec.strRemarks:'---' }}</td>
                                                <td v-if="item.rec && item.rec.include_gwa == 1">{{ (item.rec && item.rec.strRemarks == 'Passed')?item.rec.strUnits:'---' }}</td>
                                                <td v-else>{{ (item.rec && item.rec.strRemarks == 'Passed')?'('+item.rec.strUnits+')':'---' }}</td>                                                                                                                                                                               
                                            </tr>                                                                                    
                                        </tbody>
                                    </table>
                                    <hr />
                                </div>
                            </div>
                        </div> 
                        <div class="box box-success">                
                            <div class="box-footer">
                                <div class="row" style="font-weight:bold;">
                                    <div class="col-sm-3 text-right">
                                        Total Units Earned:
                                    </div>
                                    <div class="col-sm-3">
                                        {{ assessment_units }}
                                    </div>
                                    <div class="col-sm-3 text-right">
                                        GWA
                                    </div>
                                    <div class="col-sm-3">
                                        {{ assessment_gwa }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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
var special = ['0th','1st', '2nd', '3rd', '4th', '5th'];

function stringifyNumber(n) {
  return special[n];
  
  
}
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
        gwa: undefined,
        curriculum_subjects: [],
        units: undefined,
        assessment_gwa: undefined,  
        assessment_units: undefined,      
    },

    mounted() {

        let url_string = window.location.href;        
        if(this.id != 0){            
            //this.loader_spinner = true;
            axios.get(this.base_url + 'unity/student_records_data/' + this.id + '/')
                .then((data) => {                                          
                    this.student = data.data.student;
                    this.records = data.data.data;        
                    this.curriculum_subjects = data.data.curriculum_subjects; 
                    this.gwa = data.data.gwa;
                    this.units = data.data.total_units_earned;  
                    this.assessment_gwa = data.data.assessment_gwa; 
                    this.assessment_units = data.data.assessment_units;
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

