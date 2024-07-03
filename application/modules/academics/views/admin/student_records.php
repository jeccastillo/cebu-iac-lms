<aside class="right-side" id="registration-container">    
    <section class="content-header">
        <h1>
            Records
            <small>
                <a class="btn btn-app" :href="base_url + 'academics/student_viewer/' + student.intID"><i class="ion ion-arrow-left-a"></i>All Details</a>                                 
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
                    <li><a href="#tab_2" data-toggle="tab">Curriculum Evaluation</a></li>                        
                    <li><a href="#tab_3" data-toggle="tab">Credited Subjects</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="tab_1">
                        <div v-for="term in records" :class="term.reg.intROG ? 'box box-success box-solid' : 'box box-default box-solid'">
                            <div class="box-header">
                                <div class="row">
                                    <div class="col-sm-3">School Year: <span style="font-weight:400;">{{ term.reg.strYearStart + "-" + term.reg.strYearEnd }}</span></div>
                                    <div class="col-sm-3">Term: <span style="font-weight:400;">{{term.reg.enumSem + " " + term.reg.term_label}}</span></div>
                                    <div v-if="term.reg.intROG" class="col-sm-3">Enrollment Status: {{ term.reg.intROG > 0 ? 'Enrolled' : 'Enlisted' }}</div>
                                    <div v-else class="col-sm-3">Enrollment Status:</div>                                
                                    <div class="col-sm-3">Course: <span style="font-weight:400;">{{ term.reg.strProgramCode }}</span></div>
                                </div>                                
                                <h5></h5>
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
                                            <td>{{ record.strFirstname+" "+record.strLastname }}</td>
                                                                                        
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
                                    <div class="col-sm-2 text-right">
                                        Total Units Earned:
                                    </div>
                                    <div class="col-sm-1">
                                        {{ assessment_units }}
                                    </div>
                                    <div class="col-sm-1 text-right">
                                        GWA:
                                    </div>
                                    <div class="col-sm-1">
                                        {{ assessment_gwa }}
                                    </div>
                                    <div class="col-sm-2 text-right">
                                        Total in Curriculum:
                                    </div>
                                    <div class="col-sm-1">
                                        {{ curriculum_units }}
                                    </div>
                                    <div class="col-sm-1 text-right">
                                        Units Left:
                                    </div>
                                    <div class="col-sm-1">
                                        {{ units_left }}
                                    </div>
                                    <div class="col-sm-1 text-right">
                                        Transferee Units:
                                    </div>
                                    <div class="col-sm-1">
                                        {{ credited_units }}
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
                                                <th width="10%">Subject Code</th>
                                                <th width="35%">Description</th> 
                                                <th width="10%">Grade</th>                                                
                                                <th width="35%">Remarks</th>                                           
                                                <th width="10%">Units Earned</th>
                                            </tr>
                                        </thead>
                                        <tbody>                                          
                                            <tr :style="(item.rec || item.equivalent)?'background-color:'+item.rec.bg+';color:'+item.rec.color:''" v-for="item in term.records" style="font-size: 13px;">                                                
                                                <td>{{ item.strCode }}</td>
                                                <td>{{ item.strDescription }}</td>   
                                                <td v-if="item.equivalent">{{ item.equivalent.grade }}</td>
                                                <td v-else>{{ item.rec?item.rec.floatFinalGrade:'---' }}</td>
                                                <td v-if="item.equivalent">Credited from: {{ item.equivalent.course_code }} School: {{ item.equivalent.completion }}</td>
                                                <td v-else>{{ item.rec?item.rec.strRemarks:'---' }}</td>
                                                <td v-if="item.equivalent">({{ parseInt(item.equivalent.units).toFixed(1) }})</td>
                                                <td v-else-if="item.rec && item.rec.include_gwa == 1">{{ (item.rec && item.rec.strRemarks == 'Passed')?item.rec.strUnits:'---' }}</td>
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
                    <div class="tab-pane" id="tab_3">
                        <div v-for="term in credited_subjects" class="box box-success">
                            <div class="box-header">
                                <h4>{{ term.other_data.school + " " + term.other_data.school_year + ", " + term.other_data.term }}</h4>
                            </div>
                            <div class="box-body">
                                <table class="table table-condensed table-bordered">
                                    <thead>
                                        <tr>                                            
                                            <th width="10%">Course Code</th>
                                            <th width="30%">Course Description</th>
                                            <th width="5%">Units</th>                                            
                                            <th width="5%">Grade</th>
                                            <th width="10%">Date Added</th>
                                            <th width="20%">Added By</th>
                                            <th width="10%">Equivalent Subject</th>                                                                                  
                                        </tr>
                                    </thead>
                                    <tbody>                                          
                                        <tr v-for="record in term.records" style="font-size: 13px;">
                                            <td>{{ record.course_code  }}</td>
                                            <td>{{ record.descriptive_title }}</td>                                            
                                            <td>({{ parseInt(record.units).toFixed(1) }})</td>
                                            <td>{{ record.grade }}</td> 
                                            <td>{{ record.date_added }}</td>                                                                                         
                                            <td>{{ record.added_by }}</td>
                                            <td>{{ record.strCode }}</td>                                            
                                        </tr>                                        
                                    </tbody>
                                </table>
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
<script src="<?php echo base_url(); ?>assets/themes/default/js/vue.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
    integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="<?php echo base_url(); ?>assets/themes/default/js/axios.min.js"></script>

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
        deficiencies: [],
        subjects: [],
        units: undefined,
        assessment_gwa: undefined,  
        balance: 0,
        assessment_units: undefined, 
        applicant_data: undefined,  
        credited_subjects: [],
        change_grades: [],
        generated_tor:[],
        credited_units: 0,
        curriculum_units: 0,
        units_left: 0,
        tor:{
            date_issued: undefined,
            remarks: undefined,
            prepared_by: undefined,
            verified_by: undefined,
            registrar: undefined,
            included_terms: [],
            student_id: '<?php echo $id; ?>',
            picture: undefined,            
            admission_date: undefined,
            signatory_label: undefined,
            type: 'tor',
        },
        add_credits:{
            course_code: undefined,
            descriptive_title: undefined,
            units: undefined,
            grade: undefined,
            completion: '',
            term: undefined,
            school_year: undefined,
            student_id: '<?php echo $id; ?>',
            equivalent_subject: 0,
        }
    },

    mounted() {

        let url_string = window.location.href;        
        if(this.id != 0){            
            //this.loader_spinner = true;
            axios.get(this.base_url + 'unity/student_records_data/' + this.id + '/')
                .then((data) => {                                          
                    this.student = data.data.student;
                    this.credited_units = data.data.credited_units;
                    this.curriculum_units = data.data.curriculum_units;
                    this.units_left = data.data.units_left;
                    this.generated_tor =  data.data.generated_tor;
                    this.change_grades = data.data.change_grades;
                    this.credited_subjects =  data.data.credited_subjects;
                    this.records = data.data.data;        
                    this.balance = data.data.balance;
                    this.subjects = data.data.all_subjects;
                    this.deficiencies = data.data.deficiencies;
                    this.curriculum_subjects = data.data.curriculum_subjects; 
                    this.gwa = data.data.gwa;
                    this.units = data.data.total_units_earned;  
                    this.assessment_gwa = data.data.assessment_gwa; 
                    this.assessment_units = data.data.assessment_units;
                    axios.get(api_url + 'admissions/student-info/' + this.student.slug)
                    .then((data) => {
                        this.applicant_data = data.data.data;
                        for(i in this.applicant_data.uploaded_requirements){
                                if(this.applicant_data.uploaded_requirements[i].type == "2x2" || this.applicant_data.uploaded_requirements[i].type == "2x2_foreign")
                                this.tor.picture = this.applicant_data.uploaded_requirements[i].path;
                        }
                        this.tor.admission_date = this.applicant_data.date_enrolled;
                    })
                    .catch((error) => {
                        console.log(error);
                    })
                })
                .catch((error) => {
                    console.log(error);
                })
        }

    },

    methods: {      
        printTOR: function(){   
            Swal.fire({
                title: 'Generate Document?',
                text: "Continue genrating "+this.tor.type+"?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                preConfirm: (login) => {
                        if(this.deficiencies.length > 0 || this.balance > 0){                                
                            Swal.fire({
                                title: 'Warning',
                                text: "This student has active deficiencies",
                                showCancelButton: true,
                                confirmButtonText: "Continue Printing Anyway?",
                                imageWidth: 100,
                                icon: "question",
                                cancelButtonText: "No, cancel!",
                                showCloseButton: true,
                                showLoaderOnConfirm: true,
                                footer: '<a target="_blank" href="'+base_url + 'deficiencies/student_deficiencies/' + this.student.intID+'">View Deficiencies</a>',
                                preConfirm: (login) => {  
                                    this.$refs.generate_tor.submit();  
                                }
                            })                                                         
                    }
                    else
                        this.$refs.generate_tor.submit();                                                       
                    
                },
                allowOutsideClick: () => !Swal.isLoading()
            });         
            
        },        
        creditSubject: function(){
            Swal.fire({
                title: 'Add Credits?',
                text: "Continue adding credits?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                preConfirm: (login) => {
                    var formdata= new FormData();
                    for (const [key, value] of Object.entries(this.add_credits)) {
                        formdata.append(key,value);
                    }
                    return axios
                    .post(base_url + 'unity/add_credit',formdata, {
                            headers: {
                                Authorization: `Bearer ${window.token}`
                            }
                        })
                    .then(data => {
                        console.log(data.data);
                        if (data.data.success) {
                            Swal.fire({
                                title: "Success",
                                text: data.data.message,
                                icon: "success"
                            }).then(function() {
                                location.reload();
                            });
                        } else {
                            Swal.fire(
                                'Failed!',
                                data.data.message,
                                'error'
                            )
                        }
                    });
                    
                },
                allowOutsideClick: () => !Swal.isLoading()
            });         
        },
        deleteCredited: function(id){
            Swal.fire({
                title: 'Delete Credited Subject?',
                text: "Continue Deleting This Subject?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                preConfirm: (login) => {
                    var formdata= new FormData();
                    formdata.append('id',id);
                    return axios
                    .post(base_url + 'unity/delete_credited',formdata, {
                            headers: {
                                Authorization: `Bearer ${window.token}`
                            }
                        })
                    .then(data => {
                        console.log(data.data);
                        if (data.data.success) {
                            Swal.fire({
                                title: "Success",
                                text: data.data.message,
                                icon: "success"
                            }).then(function() {
                                location.reload();
                            });
                        } else {
                            Swal.fire(
                                'Failed!',
                                data.data.message,
                                'error'
                            )
                        }
                    });
                    
                },
                allowOutsideClick: () => !Swal.isLoading()
            });
        }
    }

})
</script>

