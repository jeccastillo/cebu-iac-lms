<?php 
    error_reporting(0);
?>

<aside class="right-side" id="registration-container">
<section class="content-header">
            <h1>
                        My Grades
                        <small>view your grades information</small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="<?php echo base_url() ?>portal/dashboard"><i class="fa fa-home"></i> Home</a></li>
                        <li class="active">My Grades</li>
                    </ol>
                    <div class="box-tools pull-right">
                        <form action="#" method="get" class="sidebar-form">
                      
                            <select id="select-sem" class="form-control" >
                                <?php foreach($sy as $s): ?>
                                    <option rel='<?php echo $page ?>' <?php echo ($selected_ay == $s['intID'])?'selected':''; ?> value="<?php echo $s['intID']; ?>"><?php echo $s['enumSem']." ".$term_type." ".$s['strYearStart']."-".$s['strYearEnd']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                     </div>
                     <div style="clear:both"></div>
</section>
<div class="content">
<input type="hidden" id="regStat" value="<?php echo $reg_status;?>"/>
<?php if ($reg_status =="For Subject Enlistment"):  { ?>
    <!-- <div class="alert alert-info alert-dismissible"> -->
    <div class="callout callout-warning">
    <!-- <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button> -->
        <h4> <i class="fa fa-warning"></i> iACADEMY Student Portal Advisory</h4>
        <p> No courses/subjects advised. Please contact your department chairman for the advising of courses/subjects.<p>
    </div>
<?php } ?>

<?php elseif ($reg_status =="For Registration"):  { ?>
    <div class="callout callout-info">
        <h4> <i class="fa fa-info"></i> iACADEMY Student Portal Advisory</h4>
        <p>Your courses have been advised. Please wait for the registrar to register your courses.<p>
    </div>
<?php } ?>
    <?php elseif ($reg_status =="Registered"):  { ?>
        <div class="callout callout-success">
         <h4> <i class="fa fa-check"></i> iACADEMY Student Portal Advisory</h4>
        <p>Your courses / subjects have been registered. To view your courses / subjects, please wait for the accounting office to tag you as enrolled.<p>
    </div>
<?php } endif; ?>
    <input type="hidden" value="<?php echo $student['intID'] ?>" id="student-id" />
    <input type="hidden" value="<?php echo $active_sem['intID']; ?>" id="active-sem-id" />
    <!-- <div class="box box-solid box-success"> -->
   
    <div class="box box-warning">
        <div class="box-body">
            <div class="alert alert-danger" style="display:none;">
                <i class="fa fa-ban"></i>
                <b>Alert!</b> Only admins can delete student records.
            </div>
           
            <div class="col-xs-8 col-md-8">
              <h3 class="student-name" style="margin-top: 5px;"><?php 
                        $middleInitial = substr($student['strMiddlename'], 0,1);
                        echo $student['strLastname'].", ". $student['strFirstname'] . " " .  $middleInitial . "."; ?></h3>
              <?php echo $student['strProgramDescription']; ?>
              <p> <?php  echo 'major in '. $student['strMajor']; ?></p>
            </div>
            <div class="col-xs-4 col-md-4">
              <p><strong>Student Number: </strong><?php echo preg_replace("/[^a-zA-Z0-9]+/", "", $student['strStudentNumber']); ?></p>
              <p><strong>Year Level: </strong><?php echo $academic_standing['year']; ?></p>
              <p><strong>Academic Status: </strong><?php echo $academic_standing['status']; ?></p>
              <p><strong>Enrollment Status: </strong><?php echo $reg_status; ?></p>
            </div>
            <div style="clear:both"></div>
        </div>
       
    </div>    
    <!-- <div class="box box-solid box-warning"> -->
    <div class="box box-warning">
          <div class="box-header">
            <h3 class="box-title" ><?php echo 'Grades - A.Y. ' . $sem_selected->strYearStart."-".$sem_selected->strYearEnd . " " .  $sem_selected->enumSem." ".$sem_selected->term_label." "; ?></h3>            
         </div>
        
        <?php if ($reg_status =="For Subject Enlistment"):  { ?>
            <div class="box-body table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr> 
                        <td style="text-align:center;font-style:italic;">No data available</td>
                    </tr>
                </thead>
            </table>
        </div>
        <?php } ?>
        <?php elseif ($reg_status =="For Registration"):  { ?>
        <div class="box-body table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr> 
                        <td style="text-align:center;font-style:italic;">No data available</td>
                    </tr>
                </thead>
            </table>
        </div>
        <?php } ?>
        <?php elseif ($registration['intROG'] ==0):  { ?>
        <div class="box-body table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr> 
                        <td style="text-align:center;font-style:italic;">No data available</td>
                    </tr>
                </thead>
            </table>
        </div>
        <?php } ?>

        <?php else:  ?>
        <div class="table-responsive" id="grades-container">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th width="13%">Section</th>
                        <th width="10%"> Course Code</th>
                        <th >Course Title</th>
                        <th style="text-align: center;">Units</th>
                        <th>Midterm</th>
                        <th>Final Grade</th>
						<th>Remarks</th>
                        <th>Faculty</th>
                    </tr>
                </thead>
                <tbody>
                    <template v-for="item in combined_subjects">
                        <tr style="font-size: 13px;">
                            <td></td>
                            <td>{{ item.combineCode }}</td>
                            <td>{{ item.combineDesc }}</td>
                            <td>{{ item.intSubjectID }}</td>
                            <td>---</td>
                            <td>---</td>
                            <td>Combined</td>
                            <td>---</td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    

<div class="modal fade" id="modal-default" style="display:none;" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content"> 
           
                <?php if ($reg_status == "For Subject Enlistment"): ?>
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">x</button>
                        <h3 class="modal-title"><i class="fa fa-warning"></i> iACADEMY Student Portal</h3>
                    </div>
                    <div class="modal-body">
                        <p>No courses / subjects advised. Please contact your department chairman for the advising of courses / subjects.</p>
                    </div>
                <?php elseif($reg_status == "For Registration"):?>
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">x</button>
                        <h3 class="modal-title"><i class="fa fa-info"></i> iACADEMY Student Portal</h3>
                    </div>
                    <div class="modal-body">
                        <p>Your courses / subjects have been advised. Please wait for the registrar to register your courses / subjects.
                    </div>
                    <?php elseif($reg_status == "Registered"):?>
                        <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">x</button>
                        <h3 class="modal-title"><i class="fa fa-check"></i> iACADEMY Student Portal</h3>
                    </div>
                    <div class="modal-body">
                        <p>Your courses / subjects have been registered. To view your courses / subjects, please wait for the accounting office to tag you as enrolled.
                    </div>
                <?php endif; ?>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-primary pull-right" data-dismiss="modal">Close</button>
                
            </div>
        </div>
    </div>

</div>


<script src="<?php echo base_url(); ?>assets/themes/default/js/jquery.min.js"></script>
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
        combined_subjects: [],
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
        curriculum_units_na: 0,
        units_left: 0,

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
                    this.curriculum_units_na = data.data.curriculum_units_na;                    
                    this.units_left = data.data.units_left;
                    this.generated_tor =  data.data.generated_tor;
                    this.change_grades = data.data.change_grades;
                    this.credited_subjects =  data.data.credited_subjects;
                    this.records = data.data.data;                            
                    this.balance = data.data.balance;
                    this.subjects = data.data.all_subjects;
                    this.deficiencies = data.data.deficiencies;
                    this.curriculum_subjects = data.data.curriculum_subjects; 
                    this.combined_subjects = data.data.combined_subjects;
                    this.gwa = data.data.gwa;
                    this.units = data.data.total_units_earned;  
                    this.assessment_gwa = data.data.assessment_gwa; 
                    this.assessment_units = data.data.assessment_units;
                    for(i in this.records){
                        switch(this.records[i].reg.intROG){
                            case '0': 
                                this.records[i].reg.enrollment_status = "Enlisted";
                                this.records[i].reg.color = "box-default";
                            break;
                            case '1': 
                                this.records[i].reg.enrollment_status = "Enrolled";
                                this.records[i].reg.color = "box-success";
                            break;
                            case '2': 
                                this.records[i].reg.enrollment_status = "Cleared";
                                this.records[i].reg.color = "box-success";
                            break;
                            case '3': 
                                this.records[i].reg.enrollment_status = "Officially Withdrawn";
                                this.records[i].reg.color = "box-warning";
                            break;
                            case '4': 
                                this.records[i].reg.enrollment_status = "LOA";
                                this.records[i].reg.color = "box-info";
                            break;
                            case '5': 
                                this.records[i].reg.enrollment_status = "AWOL";
                                this.records[i].reg.color = "box-danger";
                            break;
                            default:
                                this.records[i].reg.color = "box-default";
                                this.records[i].reg.enrollment_status = "None";

                        }
                    }
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
        getRecordsWithCombined: function(term) {
            if (!Array.isArray(this.combined_subjects)) {
                let flattened = [];
                for (let key in this.combined_subjects) {
                    if (Array.isArray(this.combined_subjects[key])) {
                        flattened = flattened.concat(this.combined_subjects[key]);
                    }
                }
                this.combined_subjects = flattened;
            }
            if (!Array.isArray(this.combined_subjects) || this.combined_subjects.length === 0) {
                return term.records.map(record => ({type: 'record', data: record}));
            }
            let displayedCombined = new Set();
            let result = [];
            for (let record of term.records) {
                let combined = this.combined_subjects.find(c => c.intSubjectID == record.intSubjectID);
                if (combined && !displayedCombined.has(combined.combineCode + '|' + combined.combineDesc)) {
                    result.push({type: 'combined', data: combined});
                    displayedCombined.add(combined.combineCode + '|' + combined.combineDesc);
                }
                result.push({type: 'record', data: record});
            }
            return result;
        },
        getCurriculumRecords: function(term) {
            if (!Array.isArray(this.combined_subjects)) {
                let flattened = [];
                for (let key in this.combined_subjects) {
                    if (Array.isArray(this.combined_subjects[key])) {
                        flattened = flattened.concat(this.combined_subjects[key]);
                    }
                }
                this.combined_subjects = flattened;
            }
            if (!Array.isArray(this.combined_subjects) || this.combined_subjects.length === 0) {
                return term.records.map(item => ({type: 'item', data: item}));
            }
            let displayedCombined = new Set();
            let result = [];
            for (let item of term.records) {
                let combined = this.combined_subjects.find(c => c.intSubjectID == item.intSubjectID);
                if (combined && !displayedCombined.has(combined.combineCode + '|' + combined.combineDesc)) {
                    result.push({type: 'combined', data: combined});
                    displayedCombined.add(combined.combineCode + '|' + combined.combineDesc);
                }
                result.push({type: 'item', data: item});
            }
            return result;
        },
        getTotalUnits: function(term, subjectID) {
            let total = 0;
            for (let record of term.records) {
                if (record.intSubjectID == subjectID) {
                    total += parseFloat(record.strUnits) || 0;
                }
            }
            return total.toFixed(1);
        },
        getAverageMidterm: function(term, subjectID) {
            let grades = [];
            for (let record of term.records) {
                if (record.intSubjectID == subjectID && record.v2 && record.v2 != 'OW' && record.intFinalized >= 1) {
                    grades.push(parseFloat(record.v2) || 0);
                }
            }
            if (grades.length === 0) return '---';
            let avg = grades.reduce((a, b) => a + b, 0) / grades.length;
            return Math.round(avg);
        },
        getAverageFinal: function(term, subjectID) {
            let grades = [];
            for (let record of term.records) {
                if (record.intSubjectID == subjectID && record.v3 && record.v3 != 'OW' && record.intFinalized >= 2) {
                    grades.push(parseFloat(record.v3) || 0);
                }
            }
            if (grades.length === 0) return '---';
            let avg = grades.reduce((a, b) => a + b, 0) / grades.length;
            return Math.round(avg);
        },
        getAverageSemFinal: function(term, subjectID) {
            if (this.student.type != 'shs') return '---';
            let grades = [];
            for (let record of term.records) {
                if (record.intSubjectID == subjectID && record.semFinalGrade) {
                    grades.push(parseFloat(record.semFinalGrade) || 0);
                }
            }
            if (grades.length === 0) return '---';
            let avg = grades.reduce((a, b) => a + b, 0) / grades.length;
            return Math.round(avg);
        },
        getAverageGradeCurriculum: function(term, subjectID) {
            let grades = [];
            for (let record of term.records) {
                if (record.intSubjectID == subjectID) {
                    if (record.equivalent && record.equivalent.grade && record.equivalent.grade != 'OW') {
                        grades.push(parseFloat(record.equivalent.grade) || 0);
                    } else if (record.rec && record.rec.floatFinalGrade && record.rec.floatFinalGrade != 'OW') {
                        grades.push(parseFloat(record.rec.floatFinalGrade) || 0);
                    }
                }
            }
            if (grades.length === 0) return '---';
            let avg = grades.reduce((a, b) => a + b, 0) / grades.length;
            return Math.round(avg);
        },
    }

})
</script>
