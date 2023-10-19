<aside class="right-side" id="registration-container">    
    <section class="content-header">
        <h1>
            Records
            <small>
                <a class="btn btn-app" :href="base_url + 'unity/student_viewer/' + student.intID"><i class="ion ion-arrow-left-a"></i>All Details</a> 
                <a class="btn btn-app" href="#" data-toggle="modal" data-target="#printTranscript" ><i class="fa fa-print"></i>Print TOR</a>                                       
                <a class="btn btn-app" href="#" data-toggle="modal" data-target="#creditSubjects" ><i class="fa fa-plus"></i>Add Credits</a>                
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
                    <li><a href="#tab_4" data-toggle="tab">Generated Transcripts</a></li>
                    <li><a href="#tab_5" data-toggle="tab">Change of Grades</a></li>
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
                                    <div class="col-sm-3 text-right">
                                        Cummulative Units Earned:
                                    </div>
                                    <div class="col-sm-3">
                                        {{ units }}
                                    </div>
                                    <div class="col-sm-3 text-right">
                                        Cummulative GWA
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
                                            <tr :style="(item.rec || item.equivalent)?'background-color:'+item.rec.bg+';color:#f2f2f2':''" v-for="item in term.records" style="font-size: 13px;">                                                
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
                                            <th>Course Code</th>
                                            <th>Course Description</th>
                                            <th>Units</th>                                            
                                            <th>Grade</th>
                                            <th>Date Added</th>
                                            <th>Added By</th>
                                            <th>Equivalent Subject</th>                                      
                                            <th>Actions</th>
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
                                            <td><button class="btn btn-danger" @click="deleteCredited(record.id)">Delete</button></td>
                                        </tr>                                        
                                    </tbody>
                                </table>
                            </div>
                        </div>                          
                    </div>
                    <div class="tab-pane" id="tab_4">
                        <div class="box box-success">
                            <div class="box-header">
                                <h4>Transcripts Generated</h4>
                            </div>
                            <div class="box-body">
                                <table class="table table-condensed table-bordered">
                                    <thead>
                                        <tr>                                            
                                            <th>Generated By</th>
                                            <th>Date Issued</th>
                                            <th>Prepared By</th>                                            
                                            <th>Verified By</th>                                            
                                            <th>Registrar</th>
                                            <th>Terms (ID)</th>                                      
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>                                          
                                        <tr v-for="item in generated_tor" style="font-size: 13px;">
                                            <td>{{ item.generated_by  }}</td>
                                            <td>{{ item.date_generated  }}</td>
                                            <td>{{ item.prepared_by  }}</td>
                                            <td>{{ item.verified_by  }}</td>
                                            <td>{{ item.registrar  }}</td>
                                            <td>{{ item.included_terms  }}</td>                                            
                                            <td><a :href="base_url +'pdf/reprint_tor/' + item.id +'?picture=' + tor.picture + '&admission_date=' + tor.admission_date" target="_blank" class="btn btn-success">Re-print</a></td>
                                        </tr>                                        
                                    </tbody>
                                </table>
                            </div>
                        </div>                          
                    </div>
                    <div class="tab-pane" id="tab_5">
                    <div class="box box-primary">
                            <div class="box-body">                                    
                                <table class="table table-condensed table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Term/Sem</th>
                                            <th>SY</th>
                                            <th>Subject</th>
                                            <th>Section</th>
                                            <th>From</th>
                                            <th>To</th>
                                            <th>Date Changed</th>
                                            <th>Changed By</th>                                                
                                        </tr>
                                    </thead>
                                    <tbody>                                          
                                        <tr v-for="record in change_grades" style="font-size: 13px;">
                                            <td>{{ record.enumSem }}</td>
                                            <td>{{ record.strYearStart + '-' + record.strYearEnd }}</td>
                                            <td>{{ record.strCode }}</td>
                                            <td>{{ record.strClassName + record.year + record.strSection + (record.sub_section?record.sub_section:'') }}</td>                                                
                                            <td>{{ record.from_grade }}</td>
                                            <td>{{ record.to_grade }}</td>
                                            <td>{{ record.date }}</td>                                                
                                            <td>{{ record.changed_by }}</td>                                                     
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
    <div class="modal fade" id="printTranscript" role="dialog">
        <form target="_blank" ref="generate_tor" @submit.prevent="printTOR" method="post" :action="base_url + 'pdf/generate_tor'" class="modal-dialog modal-lg">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <!-- modal header  -->
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Generate TOR</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <input type="hidden" name="student_id" v-model="tor.student_id" />
                        <input type="hidden" name="picture" v-model="tor.picture" />
                        <input type="hidden" name="admission_date" v-model="tor.admission_date" />
                        <div class="form-group col-sm-6">
                            <label>Date Issued</label>
                            <input required name="date_issued" v-model="tor.date_issued" type="datetime-local" class="form-control">
                        </div>
                        <div class="form-group col-sm-6">
                            <label>Remarks</label>
                            <textarea required name="remarks" v-model="tor.remarks" class="form-control"></textarea>
                        </div>
                        <div class="form-group col-sm-6">
                            <label>Prepared By</label>
                            <input required name="prepared_by" v-model="tor.prepared_by" type="text" class="form-control">
                        </div>
                        <div class="form-group col-sm-6">
                            <label>Verified By</label>
                            <input required name="verified_by" v-model="tor.verified_by" type="text" class="form-control">
                        </div>
                        <div class="form-group col-sm-6">
                            <label>Registrar</label>
                            <input required name="registrar" v-model="tor.registrar" type="text" class="form-control">
                        </div>
                        <div class="form-group col-sm-6">
                            <label>Included Terms</label>
                            <select name="included_terms[]" required multiple v-model="tor.included_terms" class="form-control">
                                <option v-for="term in records" :value="term.reg.term_id">
                                {{ term.reg.enumSem + " " + term.reg.term_label + " SY" + term.reg.strYearStart + "-" + term.reg.strYearEnd }}
                                </option>
                            </select>
                        </div>

                    </div>
                </div>
                <div class=" modal-footer">
                    <!-- modal footer  -->
                    <button type="submit" class="btn btn-primary">Generate</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>

        </form>
    </div>
    <div class="modal fade" id="creditSubjects" role="dialog">
        <form ref="credit_subjects" @submit.prevent="creditSubject" method="post"  class="modal-dialog modal-lg">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <!-- modal header  -->
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Credit Subject</h4>
                </div>
                <div class="modal-body">
                    <div class="row">                        
                        <div class="form-group col-sm-6">
                            <label>Course Code *</label>
                            <input required v-model="add_credits.course_code" type="text" class="form-control">
                        </div>                       
                        <div class="form-group col-sm-6">
                            <label>Descriptive Title *</label>
                            <textarea required  v-model="add_credits.descriptive_title" class="form-control"></textarea>
                        </div>
                        <div class="form-group col-sm-6">
                            <label>Units *</label>
                            <input required v-model="add_credits.units" type="number" step="0.25" class="form-control">
                        </div> 
                        <div class="form-group col-sm-6">
                            <label>Grade *</label>
                            <input required v-model="add_credits.grade" type="text" max="25" class="form-control">
                        </div>
                        <div class="form-group col-sm-6">
                            <label>School *</label>
                            <input v-model="add_credits.completion" type="text" max="50" class="form-control">
                        </div>
                        <div class="form-group col-sm-6">
                            <label>Term</label>
                            <input placeholder="Ex. First Trimester" v-model="add_credits.term" type="text" max="50" class="form-control">
                        </div>
                        <div class="form-group col-sm-6">
                            <label>School Year</label>
                            <input placeholder="Ex. 2023-2024" v-model="add_credits.school_year" type="text" max="50" class="form-control">
                        </div>
                        <div class="form-group col-sm-6">
                            <label>Equivalent Subject</label>
                            <select v-model="add_credits.equivalent_subject" class="form-control">
                                <option v-for="item in subjects" :value="item.intSubjectID">
                                    {{ item.strCode + " "  + item.strDescription }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class=" modal-footer">
                    <!-- modal footer  -->
                    <button type="submit" class="btn btn-primary">Submit</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>

        </form>
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
        deficiencies: [],
        subjects: [],
        units: undefined,
        assessment_gwa: undefined,  
        assessment_units: undefined, 
        applicant_data: undefined,  
        credited_subjects: [],
        change_grades: [],
        generated_tor:[],
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
                    this.generated_tor =  data.data.generated_tor;
                    this.change_grades = data.data.change_grades;
                    this.credited_subjects =  data.data.credited_subjects;
                    this.records = data.data.data;        
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
                title: 'Generate TOR?',
                text: "Continue genrating TOR?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                preConfirm: (login) => {
                        if(this.deficiencies.length > 0){                                
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
                                footer: '<a href="'+base_url + 'deficiencies/student_deficiencies/' + this.student.intID+'">View Deficiencies</a>',
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

