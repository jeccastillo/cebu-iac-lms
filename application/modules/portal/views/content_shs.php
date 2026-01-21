<aside class="right-side" id="grades-container">
    <section class="content-header">
        <h1> My Grades <small>view your grades information</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo base_url() ?>portal/dashboard"><i class="fa fa-home"></i>
                    Home</a></li>
            <li class="active">My Grades</li>
        </ol>
        <div class="box-tools pull-right">
            <form action="#" method="get" class="sidebar-form">
                <select class="form-control" v-model="selectedTerm">
                    <option v-for="term in terms" :value="term.intID"
                        @click='getStudentRecords(term.intID)'>
                        {{ `${term.enumSem} term ${term.strYearStart} ${term.strYearEnd}` }}
                    </option>
                </select>
            </form>
        </div>
        <div style="clear:both"></div>
    </section>
    <div class="content">
        <input type="hidden" id="regStat" value="<?php echo $reg_status;?>" />
        <?php if ($reg_status =="For Subject Enlistment"):  { ?>
        <!-- <div class="alert alert-info alert-dismissible"> -->
        <div class="callout callout-warning">
            <!-- <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button> -->
            <h4> <i class="fa fa-warning"></i> iACADEMY Student Portal Advisory</h4>
            <p> No courses/subjects advised. Please contact your department chairman for the
                advising of courses/subjects.
            <p>
        </div> <?php } ?> <?php elseif ($reg_status =="For Registration"):  { ?> <div
            class="callout callout-info">
            <h4> <i class="fa fa-info"></i> iACADEMY Student Portal Advisory</h4>
            <p>Your courses have been advised. Please wait for the registrar to register your
                courses.
            <p>
        </div> <?php } ?> <?php elseif ($reg_status =="Registered"):  { ?> <div
            class="callout callout-success">
            <h4> <i class="fa fa-check"></i> iACADEMY Student Portal Advisory</h4>
            <p>Your courses / subjects have been registered. To view your courses / subjects, please
                wait for the accounting office to tag you as enrolled.
            <p>
        </div> <?php } endif; ?> <input type="hidden" value="<?php echo $student['intID'] ?>"
            id="student-id" />
        <input type="hidden" value="<?php echo $active_sem['intID']; ?>" id="active-sem-id" />
        <!-- <div class="box box-solid box-success"> -->
        <div class="box box-warning">
            <div class="box-body">
                <div class="alert alert-danger" style="display:none;">
                    <i class="fa fa-ban"></i>
                    <b>Alert!</b> Only admins can delete student records.
                </div>
                <div class="col-xs-8 col-md-8">
                    <h3 class="student-name" style="margin-top: 5px;">
                        <?php 
                        $middleInitial = substr($student['strMiddlename'], 0,1);
                        echo $student['strLastname'].", ". $student['strFirstname'] . " " .  $middleInitial . "."; ?></h3>
                    <?php echo $student['strProgramDescription']; ?> <p>
                        <?php  echo 'major in '. $student['strMajor']; ?></p>
                </div>
                <div class="col-xs-4 col-md-4">
                    <p><strong>Student Number:
                        </strong><?php echo preg_replace("/[^a-zA-Z0-9]+/", "", $student['strStudentNumber']); ?>
                    </p>
                    <p><strong>Year Level: </strong><?php echo $academic_standing['year']; ?></p>
                    <p><strong>Academic Status: </strong><?php echo $academic_standing['status']; ?>
                    </p>
                    <p><strong>Enrollment Status: </strong><?php echo $reg_status; ?></p>
                </div>
                <div style="clear:both"></div>
            </div>
        </div>
        <!-- <div class="box box-solid box-warning"> -->
        <div class="box box-warning">
            <div class="box-header">
                <h3 class="box-title"> Grades - A.Y. {{ showTerm  }}
                </h3>
            </div>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th width="13%">Section</th>
                            <th width="10%"> Course Code</th>
                            <th>Course Title</th>
                            <th style="text-align: left;">Units</th>
                            <th>Midterm</th>
                            <th>Final Grade</th>
                            <th>Remarks</th>
                            <th>Faculty</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-for="item in getRecordsWithCombined()">
                            <tr v-if="item.type == 'combined'" style="font-size: 13px;">
                                <td></td>
                                <td v-if="!item.data.elective_subject">
                                    {{ item.data.combineCode }}
                                </td>
                                <td v-else>
                                    ({{ item.data.elective_subject.strCode + ' - ' + item.data.combineCode }})
                                </td>
                                <td v-if="item.data.include_gwa == 1">
                                    {{ getTotalUnits(term, item.data.intSubjectID) }}
                                </td>
                                <td v-else> ({{ getTotalUnits(term, item.data.intSubjectID) }})
                                </td>
                                <td>{{ getAverageMidterm(term, item.data.intSubjectID) }}
                                </td>
                                <td>{{ getAverageFinal(term, item.data.intSubjectID) }}
                                </td>
                                <td>Combined</td>
                                <td>---</td>
                            </tr>
                            <tr v-else style="font-size: 13px;">
                                <td>{{ item.data.strClassName + item.data.year + item.data.strSection + (item.data.sub_section?item.data.sub_section:'') }}
                                </td>
                                <td v-if="!item.data.elective_subject">
                                    {{ item.data.strCode }}
                                </td>
                                <td v-else>
                                    ({{ item.data.elective_subject.strCode + ' - ' + item.data.strCode }})
                                </td>
                                <td>{{ item.data.strDescription }}</td>
                                <td v-if="item.data.include_gwa == 1">
                                    {{ item.data.strUnits }}
                                </td>
                                <td v-else>({{ item.data.strUnits }})</td>
                                <td v-if="item.data.v2 != 'OW'"
                                    :style="(item.data.intFinalized == 2)?'font-weight:bold;':''">
                                    {{ item.data.intFinalized >=1?item.data.v2:'NGS' }}
                                </td>
                                <td v-else style="font-weight:bold"> OW </td>
                                <td v-if="item.data.v3 != 'OW'"
                                    :style="(item.data.intFinalized == 2)?'font-weight:bold;':''">
                                    <span v-if="item.data.intFinalized >=2">
                                        {{ item.data.v3 }}
                                    </span>
                                    <span v-else> NGS </span>
                                </td>
                                <td v-else style="font-weight:bold"> OW </td>
                                <td
                                    :style="(item.data.strRemarks != 'Failed')?'color:#333;':'color:#990000;'">
                                    {{ item.data.intFinalized >=1?item.data.strRemarks:'---' }}
                                </td>
                                <td>{{ item.data.strFirstname+" "+item.data.strLastname }}
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <!-- remove modal -->
        </div>
        <div class="modal fade" id="modal-default" style="display:none;" data-backdrop="static"
            data-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content"> <?php if ($reg_status == "For Subject Enlistment"): ?>
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">x</button>
                        <h3 class="modal-title"><i class="fa fa-warning"></i> iACADEMY Student
                            Portal</h3>
                    </div>
                    <div class="modal-body">
                        <p>No courses / subjects advised. Please contact your department chairman
                            for the advising of courses / subjects.</p>
                    </div> <?php elseif($reg_status == "For Registration"):?> <div
                        class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">x</button>
                        <h3 class="modal-title"><i class="fa fa-info"></i> iACADEMY Student Portal
                        </h3>
                    </div>
                    <div class="modal-body">
                        <p>Your courses / subjects have been advised. Please wait for the registrar
                            to register your courses / subjects.
                    </div> <?php elseif($reg_status == "Registered"):?> <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">x</button>
                        <h3 class="modal-title"><i class="fa fa-check"></i> iACADEMY Student Portal
                        </h3>
                    </div>
                    <div class="modal-body">
                        <p>Your courses / subjects have been registered. To view your courses /
                            subjects, please wait for the accounting office to tag you as enrolled.
                    </div> <?php endif; ?> <div class="modal-footer">
                        <button type="button" class="btn btn-primary pull-right"
                            data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <script>
        new Vue({
            el: '#grades-container',
            data: {
                base_url: '<?php echo base_url(); ?>',
                terms: <?php echo json_encode($sy); ?>,
                selectedTerm: '<?php echo $selected_ay; ?>',
                schoolYear: [],
                records: [],
                combined_subjects: [],
                student: '',
            },
            mounted() {
                this.getStudentRecords(this.selectedTerm)
            },
            computed: {
                showTerm() {
                    const term = this.terms.find(term => term.intID == this.selectedTerm);
                    return `${term.strYearEnd}-${term.strYearStart} ${term.enumSem} Sem`
                },
            },
            methods: {
                getStudentRecords(term) {
                    axios.get(this.base_url + 'portal/student_grades/' + term + '/').then((
                        data) => {
                        this.records = data.data.records
                        this.combined_subjects = data.data.combined_subjects
                        this.student = data.data.student;
                    })
                },
                getRecordsWithCombined: function() {
                    if (!Array.isArray(this.combined_subjects)) {
                        let flattened = [];
                        for (let key in this.combined_subjects) {
                            if (Array.isArray(this.combined_subjects[key])) {
                                flattened = flattened.concat(this.combined_subjects[
                                    key]);
                            }
                        }
                        this.combined_subjects = flattened;
                    }
                    if (!Array.isArray(this.combined_subjects) || this.combined_subjects
                        .length === 0) {
                        return this.records.map(record => ({
                            type: 'record',
                            data: record
                        }));
                    }
                    let displayedCombined = new Set();
                    let result = [];
                    for (let record of this.records) {
                        let combined = this.combined_subjects.find(c => c
                            .intSubjectID == record.intSubjectID);
                        if (combined && !displayedCombined.has(combined.combineCode +
                                '|' + combined.combineDesc)) {
                            result.push({
                                type: 'combined',
                                data: combined
                            });
                            displayedCombined.add(combined.combineCode + '|' + combined
                                .combineDesc);
                        }
                        result.push({
                            type: 'record',
                            data: record
                        });
                    }
                    return result;
                },
                getTotalUnits: function(term, subjectID) {
                    let flattenedCombined = this.combined_subjects;
                    if (!Array.isArray(flattenedCombined)) {
                        flattenedCombined = [];
                        for (let key in this.combined_subjects) {
                            if (Array.isArray(this.combined_subjects[key])) {
                                flattenedCombined = flattenedCombined.concat(this
                                    .combined_subjects[key]);
                            }
                        }
                    }
                    let combined = flattenedCombined.find(c => c.intSubjectID ==
                        subjectID);
                    let subjectIDs = [subjectID];
                    if (combined) {
                        subjectIDs = flattenedCombined.filter(c => c.combineCode ==
                            combined.combineCode).map(c => c.intSubjectID);
                    }
                    let total = 0;
                    for (let record of this.records) {
                        if (subjectIDs.includes(record.intSubjectID)) {
                            total += parseFloat(record.strUnits) || 0;
                        }
                    }
                    return total.toFixed(1);
                },
                getAverageMidterm: function(term, subjectID) {
                    let flattenedCombined = this.combined_subjects;
                    if (!Array.isArray(flattenedCombined)) {
                        flattenedCombined = [];
                        for (let key in this.combined_subjects) {
                            if (Array.isArray(this.combined_subjects[key])) {
                                flattenedCombined = flattenedCombined.concat(this
                                    .combined_subjects[key]);
                            }
                        }
                    }
                    let combined = flattenedCombined.find(c => c.intSubjectID ==
                        subjectID);
                    let subjectIDs = [subjectID];
                    if (combined) {
                        subjectIDs = flattenedCombined.filter(c => c.combineCode ==
                            combined.combineCode).map(c => c.intSubjectID);
                    }
                    let grades = [];
                    for (let record of this.records) {
                        if (subjectIDs.includes(record.intSubjectID) && record.v2 &&
                            record.v2 != 'OW' && record.intFinalized >= 1) {
                            grades.push(parseFloat(record.v2) || 0);
                        }
                    }
                    if (grades.length === 0) return '---';
                    let avg = grades.reduce((a, b) => a + b, 0) / grades.length;
                    return Math.round(avg);
                },
                getAverageFinal: function(term, subjectID) {
                    let flattenedCombined = this.combined_subjects;
                    if (!Array.isArray(flattenedCombined)) {
                        flattenedCombined = [];
                        for (let key in this.combined_subjects) {
                            if (Array.isArray(this.combined_subjects[key])) {
                                flattenedCombined = flattenedCombined.concat(this
                                    .combined_subjects[key]);
                            }
                        }
                    }
                    let combined = flattenedCombined.find(c => c.intSubjectID ==
                        subjectID);
                    let subjectIDs = [subjectID];
                    if (combined) {
                        subjectIDs = flattenedCombined.filter(c => c.combineCode ==
                            combined.combineCode).map(c => c.intSubjectID);
                    }
                    let grades = [];
                    for (let record of term.records) {
                        if (subjectIDs.includes(record.intSubjectID) && record.v3 &&
                            record.v3 != 'OW' && record.intFinalized >= 2) {
                            grades.push(parseFloat(record.v3) || 0);
                        }
                    }
                    if (grades.length === 0) return '---';
                    let avg = grades.reduce((a, b) => a + b, 0) / grades.length;
                    return Math.round(avg);
                },
            },
        })
        </script>