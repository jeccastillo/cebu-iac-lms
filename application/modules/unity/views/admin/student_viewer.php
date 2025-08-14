<style>
  :root {
    --primary-color: #4361ee;
    --secondary-color: #3f37c9;
    --light-color: #f8f9fa;
    --dark-color: #343a40;
    --success-color: #4cc9f0;
    --danger-color: #ef233c;
  }
  
  body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f5f7fa;
  }
  
  .header-bar {
    background-color: var(--primary-color);
    color: white;
    padding: 15px 20px;
    margin-bottom: 20px;
    border-radius: 5px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  }
  
  .header-bar h2 {
    margin: 0;
    font-weight: 600;
  }
  
  .action-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 20px;
  }
  
  .btn-action {
    background-color: var(--primary-color);
    color: white;
    border: none;
    border-radius: 5px;
    padding: 8px 15px;
    font-size: 14px;
    transition: all 0.2s;
  }
  
  .btn-action:hover {
    background-color: var(--secondary-color);
    transform: translateY(-1px);
  }
  
  .btn-action i {
    margin-right: 5px;
  }
  
  .student-profile {
    background-color: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    margin-bottom: 20px;
  }
  
  .profile-header {
    background-color: var(--primary-color);
    color: white;
    padding: 15px;
    border-radius: 5px 5px 0 0;
    margin-bottom: 20px;
  }
  
  .student-photo {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 20px;
  }
  
  .info-card {
    background-color: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    margin-bottom: 20px;
  }
  
  .info-item {
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
  }
  
  .info-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
  }
  
  .info-label {
    font-weight: 600;
    color: var(--dark-color);
    margin-bottom: 5px;
  }
  
  .info-value {
    color: #555;
  }
  
  .nav-tabs {
    border-bottom: 2px solid #eee;
  }
  
  .nav-tabs .nav-link {
    border: none;
    color: #555;
    font-weight: 500;
    border-bottom: 2px solid transparent;
  }
  
  .nav-tabs .nav-link.active {
    color: var(--primary-color);
    border-bottom: 2px solid var(--primary-color);
    background-color: transparent;
  }
  
  .table-responsive {
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
  }
  
  .table {
    margin-bottom: 0;
  }
  
  .table th {
    background-color: var(--primary-color);
    color: white;
    font-weight: 500;
  }
  
  .alert-warning {
    background-color: #fff3cd;
    border-left: 4px solid #ffc107;
  }
  
  .form-control, .form-select {
    border-radius: 5px;
    padding: 8px 12px;
  }
  
  .form-control:focus, .form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
  }
  
  /* Responsive adjustments */
  @media (max-width: 768px) {
    .action-buttons {
      justify-content: center;
    }
    
    .student-photo {
      width: 100px;
      height: 100px;
    }
    
    .info-item {
      margin-bottom: 10px;
      padding-bottom: 10px;
    }
  }
</style>
<div class="container-fluid py-4" id="student-viewer-container">
    <!-- Header Section -->
    <div class="header-bar d-flex justify-content-between align-items-center">
      <h2>
        <i class="fas fa-user-graduate me-2"></i>Student Profile
      </h2>
      <select v-model="sem_student" @change="changeTermSelected" class="form-control w-auto">
        <option v-for="s in sy" :value="s.intID">
          {{s.term_student_type + ' ' + s.enumSem + ' ' + s.term_label + ' ' + s.strYearStart + '-' + s.strYearEnd}}
        </option>
      </select>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons">
      <a class="btn btn-action" :href="base_url + 'student/view_all_students'">
        <i class="fas fa-arrow-left"></i>All Students
      </a>
      <a class="btn btn-action" target="_blank" :href="base_url + 'admissionsV1/view_lead_new/' + student.slug">
        <i class="fas fa-list"></i>Applicant Data
      </a>
      <template v-if="user_level == 2 || user_level == 3">
        <a class="btn btn-action" :href="base_url + 'student/edit_student/' + student.intID">
          <i class="fas fa-edit"></i>Edit
        </a>
        <a class="btn btn-action" :href="base_url + 'unity/student_records/' + student.intID">
          <i class="fas fa-file-alt"></i>Records
        </a>
      </template>
    </div>

    <!-- Alert Box for Balances -->
    <div class="alert alert-warning mb-4" v-if="show_alert" role="alert">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <h5 class="alert-heading mb-1"><i class="fas fa-exclamation-circle me-2"></i>Outstanding Balances</h5>
          <p class="mb-0">This student has unpaid balances from previous terms.</p>
        </div>
        <a :href="base_url + 'deficiencies/student_deficiencies/' + student.intID" class="btn btn-sm btn-outline-danger">
          View Details
        </a>
      </div>
    </div>

    <!-- Student Profile Section -->
    <div class="student-profile">
      <div class="profile-header">
        <div class="row align-items-center">
          <div class="col-md-2 text-center">
            <img v-if="!picture" src="https://placehold.co/200x200/4361ee/FFFFFF/?text=Photo" class="student-photo" alt="Student profile photo placeholder" />
            <img v-else :src="picture" class="student-photo" :alt="'Photo of ' + student.strFirstname + ' ' + student.strLastname" />
          </div>
          <div class="col-md-10">
            <h3 class="mb-1" style="text-transform: capitalize;">
              {{ student.strLastname.toUpperCase() }}, {{ student.strFirstname.toUpperCase() }}
              {{ student.strMiddlename ? student.strMiddlename.toUpperCase() : '' }}
            </h3>
            <h5 class="mb-3">{{ student.strProgramDescription }}
              {{ (student.strMajor != 'None') ? 'Major in '+student.strMajor : '' }}
            </h5>
            <div class="d-flex flex-wrap gap-3">
              <div>
                <small class="text-light">Student No.</small>
                <div class="fw-bold">{{ student.strStudentNumber.replace(/-/g, '') }}</div>
              </div>
              <div>
                <small class="text-light">Status</small>
                <div class="fw-bold">{{ student.student_status ? student.student_status.toUpperCase() : '' }}</div>
              </div>
              <div>
                <small class="text-light">Curriculum</small>
                <div class="fw-bold">{{ student.strName }}</div>
              </div>
              <div>
                <small class="text-light">Registration Status</small>
                <div :class="{
                  'text-warning': reg_status === 'For Registration',
                  'text-success': reg_status === 'Enrolled',
                  'text-danger': reg_status === 'Withdrawn' || reg_status === 'LOA' || reg_status === 'AWOL'
                }" class="fw-bold">
                  {{ reg_status }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row mt-4">
        <!-- Personal Information -->
        <div class="col-md-6">
          <div class="info-card">
            <h5 class="mb-4"><i class="fas fa-id-card me-2"></i>Personal Information</h5>
            
            <div class="info-item">
              <div class="info-label">Gender</div>
              <div class="info-value">{{ student.enumGender }}</div>
            </div>
            
            <div class="info-item">
              <div class="info-label">Address</div>
              <div class="info-value">{{ student.strAddress }}</div>
            </div>
            
            <div class="info-item">
              <div class="info-label">Contact Number</div>
              <div class="info-value">{{ student.strMobileNumber }}</div>
            </div>
            
            <div class="info-item">
              <div class="info-label">Personal Email</div>
              <div class="info-value">{{ student.strEmail }}</div>
            </div>
            
            <div class="info-item">
              <div class="info-label">Birthdate</div>
              <div class="info-value">{{ student.dteBirthDate }}</div>
            </div>
            
            <div class="info-item">
              <div class="info-label">Date Created</div>
              <div class="info-value">{{ student.dteCreated }}</div>
            </div>
          </div>
        </div>

        <!-- Academic Information -->
        <div class="col-md-6">
          <div class="info-card">
            <h5 class="mb-4"><i class="fas fa-graduation-cap me-2"></i>Academic Information</h5>
            
            <div v-if="registration">
              <div class="info-item">
                <div class="info-label">Enrollment Type</div>
                <select class="form-control form-control-sm" @change="updateStudentType($event)"
                  v-model="registration.enumStudentType">
                  <option value="new">New</option>
                  <option value="freshman">Freshman</option>
                  <option value="continuing">Continuing</option>
                  <option value="shiftee">Shiftee</option>
                  <option value="2nd Degree">2nd Degree</option>
                  <option value="2nd Degree iAC">2nd Degree iAC</option>
                  <option value="returning">Returnee</option>
                  <option value="transferee">Transferee</option>
                </select>
              </div>
              
              <div class="info-item">
                <div class="info-label">Year Level</div>
                <select class="form-control form-control-sm" @change="updateStudentYearLevel($event)"
                  v-model="registration.intYearLevel">
                  <option value=1>1</option>
                  <option value=2>2</option>
                  <option value=3>3</option>
                  <option value=4>4</option>
                </select>
              </div>
              
              <div class="info-item">
                <div class="info-label">Academic Status</div>
                <select class="form-control form-control-sm" @change="updateAcademicStatus($event)"
                  v-model="registration.enumRegistrationStatus">
                  <option value="regular">Regular</option>
                  <option value="irregular">Irregular</option>
                </select>
              </div>
              
              <div class="info-item">
                <div class="info-label">Date Enrolled</div>
                <input class="form-control form-control-sm" type="datetime-local" @blur="updateDateEnrolled($event)"
                  v-model="registration.dteRegistered">
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabs Navigation -->
      <ul class="nav nav-tabs mt-4" id="studentTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button" role="tab" aria-controls="personal" aria-selected="true">
            <i class="fas fa-user me-1"></i> Personal
          </button>
        </li>
        <li v-if="advanced_privilages1" class="nav-item" role="presentation">
          <button class="nav-link" id="subjects-tab" data-bs-toggle="tab" data-bs-target="#subjects" type="button" role="tab" aria-controls="subjects" aria-selected="false">
            <i class="fas fa-book me-1"></i> Subjects
          </button>
        </li>
        <li v-if="reg_status == 'Enrolled'" class="nav-item" role="presentation">
          <button class="nav-link" id="grades-tab" data-bs-toggle="tab" data-bs-target="#grades" type="button" role="tab" aria-controls="grades" aria-selected="false">
            <i class="fas fa-chart-line me-1"></i> Grades
          </button>
        </li>
        <li v-if="enlistment" class="nav-item" role="presentation">
          <button class="nav-link" id="advising-tab" data-bs-toggle="tab" data-bs-target="#advising" type="button" role="tab" aria-controls="advising" aria-selected="false">
            <i class="fas fa-clipboard-check me-1"></i> Advising
          </button>
        </li>
        <li v-if="registration && advanced_privilages2" class="nav-item" role="presentation">
          <button class="nav-link" id="schedule-tab" data-bs-toggle="tab" data-bs-target="#schedule" type="button" role="tab" aria-controls="schedule" aria-selected="false">
            <i class="fas fa-calendar-alt me-1"></i> Schedule
          </button>
        </li>
      </ul>

      <!-- Tabs Content -->
      <div class="tab-content p-3 bg-white rounded-bottom" id="studentTabsContent">
        <!-- Personal Info Tab -->
        <div class="tab-pane fade show active" id="personal" role="tabpanel" aria-labelledby="personal-tab">
          <div class="row">
            <div class="col-md-6">
              <h6 class="mb-3"><i class="fas fa-users me-2"></i>Family Information</h6>
              <table class="table table-sm">
                <thead>
                  <tr>
                    <th>Relation</th>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Email</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>Mother</td>
                    <td>{{ student.mother || 'N/A' }}</td>
                    <td>{{ student.mother_contact || 'N/A' }}</td>
                    <td>{{ student.mother_email || 'N/A' }}</td>
                  </tr>
                  <tr>
                    <td>Father</td>
                    <td>{{ student.father || 'N/A' }}</td>
                    <td>{{ student.father_contact || 'N/A' }}</td>
                    <td>{{ student.father_email || 'N/A' }}</td>
                  </tr>
                  <tr>
                    <td>Guardian</td>
                    <td>{{ student.guardian || 'N/A' }}</td>
                    <td>{{ student.guardian_contact || 'N/A' }}</td>
                    <td>{{ student.guardian_email || 'N/A' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="col-md-6">
              <h6 class="mb-3"><i class="fas fa-school me-2"></i>Educational Background</h6>
              <table class="table table-sm">
                <thead>
                  <tr>
                    <th>Level</th>
                    <th>School</th>
                    <th>Year(s)</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>High School</td>
                    <td>{{ student.high_school || 'N/A' }}</td>
                    <td>{{ student.high_school_attended || 'N/A' }}</td>
                  </tr>
                  <tr>
                    <td>Senior High</td>
                    <td>{{ student.senior_high || 'N/A' }}</td>
                    <td>{{ student.senior_high_attended || 'N/A' }}</td>
                  </tr>
                  <tr>
                    <td>College</td>
                    <td>{{ student.college || 'N/A' }}</td>
                    <td>{{ student.college_attended_from || 'N/A' }} - {{ student.college_attended_to || 'N/A' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Subjects Tab -->
        <div v-if="advanced_privilages1" class="tab-pane fade" id="subjects" role="tabpanel" aria-labelledby="subjects-tab">
          <div v-if="registration" class="table-responsive">
            <table class="table table-sm table-hover">
              <thead>
                <tr>
                  <th>Section</th>
                  <th>Course</th>
                  <th>Units</th>
                  <th>Midterm</th>
                  <th>Final</th>
                  <th>Remarks</th>
                  <th>Faculty</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="record in records" :class="{'table-secondary': record.intFinalized == 2}">
                  <td>
                    {{ record.strClassName + record.year + record.strSection + (record.sub_section?record.sub_section:'') }}
                  </td>
                  <td>
                    <a :href="base_url + 'unity/classlist_viewer/' + record.classlistID + '/0/' + id">{{ record.strCode }}</a>
                    <span v-if="record.elective_classlist_id">&nbsp;( {{ record.elective_subject.strCode }} )</span>
                  </td>
                  <td>{{ record.strUnits }}</td>
                  <td :class="{'fw-bold': record.intFinalized >=1}">
                    {{ record.v2 != 'OW' ? (record.intFinalized >=1 ? record.v2 : 'NGS') : 'OW' }}
                  </td>
                  <td :class="{'fw-bold': record.intFinalized >=2, 'text-danger': record.strRemarks == 'Failed'}">
                    {{ record.v3 != 'OW' ? (record.intFinalized >=2 ? record.v3 : 'NGS') : 'OW' }}
                  </td>
                  <td :class="{'text-danger': record.strRemarks == 'Failed'}">
                    {{ record.intFinalized >=1 ? record.strRemarks : '---' }}
                  </td>
                  <td>{{ record.facultyName }}</td>
                </tr>
              </tbody>
              <tfoot v-if="electives.length > 0">
                <tr>
                  <td colspan="7">
                    <h6 class="mt-3">Assign/Unassign Elective Subjects</h6>
                    <form @submit.prevent='assignElective' class="row g-3">
                      <div class="col-md-5">
                        <select v-model="elective_classlist" required class="form-control form-control-sm">
                          <option value="" disabled>Select Subject</option>
                          <option v-for="record in records" :value='record.classlistID'>{{ record.strCode }}</option>
                        </select>
                      </div>
                      <div class="col-md-5">
                        <select v-model="elective_subj" required class="form-control form-control-sm">
                          <option value="" disabled>Select Elective</option>
                          <option v-for="elective in electives" :value='elective.intSubjectID'>{{ elective.strCode }}</option>
                        </select>
                      </div>
                      <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100">Update</button>
                      </div>
                    </form>
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>
          
          <div class="mt-4">
            <button type="button" class="btn btn-outline-secondary me-2">
              <i class="fas fa-print me-1"></i> Print ROG
            </button>
            <button type="button" class="btn btn-outline-secondary">
              <i class="fas fa-print me-1"></i> Print Data
            </button>
          </div>
        </div>

        <!-- Grades Tab -->
        <div v-if="reg_status == 'Enrolled'" class="tab-pane fade" id="grades" role="tabpanel" aria-labelledby="grades-tab">
          <div class="table-responsive">
            <table class="table table-sm table-hover">
              <thead>
                <tr>
                  <th>Subject</th>
                  <th>Section</th>
                  <th>From</th>
                  <th>To</th>
                  <th>Date Changed</th>
                  <th>Changed By</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="record in change_grade">
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

        <!-- Advising Tab -->
        <div v-if="enlistment" class="tab-pane fade" id="advising" role="tabpanel" aria-labelledby="advising-tab">
          <h6 class="mb-3">Approved Subjects for Enlistment</h6>
          <div class="table-responsive">
            <table class="table table-sm table-hover">
              <thead>
                <tr>
                  <th>Subject</th>
                  <th>Section</th>
                  <th>Schedule</th>
                  <th>Units</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="subject in enlisted_subjects">
                  <td>{{ subject.strCode }}</td>
                  <td>
                    <a target="_blank" :href="base_url + 'unity/classlist_viewer/'+subject.intID">
                      {{ subject.strClassName + subject.year + subject.strSection + subject.sub_section }}
                    </a>
                  </td>
                  <td>{{ subject.sched_room + " " + subject.sched_day + " " + subject.sched_time }}</td>
                  <td>{{ subject.strUnits }}</td>
                </tr>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="4" class="text-end">
                    <button @click="enlistStudent" class="btn btn-primary">
                      <i class="fas fa-save me-1"></i> Enlist Advised Subjects
                    </button>
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        <!-- Schedule Tab -->
        <div v-if="registration && advanced_privilages2" class="tab-pane fade" id="schedule" role="tabpanel" aria-labelledby="schedule-tab">
          <div class="table-responsive">
            <table class="table table-sm table-hover">
              <thead>
                <tr>
                  <th>Section</th>
                  <th>Sub Section</th>
                  <th>Course Code</th>
                  <th>Description</th>
                  <th>Units</th>
                  <th>Schedule</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="record in records">
                  <td>{{ record.strClassName + ' ' + record.year + record.strSection }}</td>
                  <td>{{ record.sub_section!=null?record.sub_section:'' }}</td>
                  <td>{{ record.strCode }}</td>
                  <td>{{ record.strDescription }}</td>
                  <td>
                    {{ record.strUnits == 0 ? '(' + record.intLectHours + ')' : record.strUnits }}
                  </td>
                  <td>{{ record.schedule.schedString || 'Not scheduled' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="mt-3">
            <button type="button" class="btn btn-outline-primary me-2">
              <i class="fas fa-print me-1"></i> Print Schedule
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- LOA Modal -->
  <div class="modal fade" id="loa-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Tag Student for Leave of Absence</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="submitLoa()">
            <div class="mb-3">
              <label class="form-label">Tag for AWOL?</label>
              <select v-model="awol" class="form-select">
                <option value="4">No</option>
                <option value="5">Yes</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Reason for leave of absence</label>
              <textarea v-model.trim="loaDetails.loa_remarks" class="form-control" rows="3" required></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label">Date</label>
              <input type="date" v-model="loaDetails.loa_date" class="form-control" required>
            </div>
            <div class="d-flex justify-content-end">
              <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-primary">Submit</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <script>
    const { createApp, ref } = Vue;
    
createApp({
  data() {
    return {
      test: 'dasd',
      campus: '',
      id: '',
      tab: '',
      sem: '',
      student: {
        strFirstname: 'Firstname',
        strLastname: 'Lastname',
        strMiddlename: 'Middlename',
        strStudentNumber: '0',
      },
      scholarship: { name: 'none' },
      discount: { name: 'none' },
      user_level: undefined,
      registration: undefined,
      applicant_data: {},
      programs: [],
      tuition_years: [],
      enlistment: undefined,
      enlisted_subjects: [],
      active_sem: {},
      sections: [],
      balance: 0,
      records: [],
      other_data: undefined,
      reg_status: '',
      deficiencies: [],
      block_sections: [],
      sy: undefined,
      term_type: undefined,
      sem_student: undefined,
      prev_year_sem: 0,
      add_attendance: {
        student_id: undefined,
        month_id: undefined,
        school_days: undefined,
        abscences: undefined,
        tardy: undefined,
      },
      add_subject: {
        code: undefined,
        section: undefined,
        studentID: undefined,
        activeSem: undefined,
      },
      advanced_privilages1: false,
      advanced_privilages2: false,
      registrar_privilages: false,
      photo_dir: undefined,
      img_dir: undefined,
      grad_status: 0,
      selected_ay: undefined,
      term_months: [],
      base_url: '',
      registration_status: 0,
      loader_spinner: true,
      change_grade: [],
      attendance: [],
      awol: 4,
      total_units: 0,
      tuition_payment_link: undefined,
      notif_message: undefined,
      picture: undefined,
      electives: undefined,
      elective_subj: undefined,
      elective_classlist: undefined,
      lab_units: 0,
      gpa: 0,
      assessment: '',
      deficency_msg: '',
      term_balances: [],
      show_alert: false,
      sync_data: {
        updated_at: '',
        campus: '',
      },
      loaDetails: {
        loa_remarks: '',
        loa_date: ''
      }
    };
  },
  
  mounted() {
    // Initialize base URL and other variables        
    let url_string = window.location.href;
    if (this.id != 0) {
      this.loadStudentData();
    }
  },
  
  methods: {
    loadStudentData() {
      Swal.fire({
        title: 'Loading',
        html: 'Syncing data...',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      // First sync payment data
      axios.post(api_url + 'finance/sync_payments', this.sync_data, {
        headers: {
          Authorization: `Bearer ${window.token}`
        }
      })
      .then(() => {
        // Then load student data
        axios.get(this.base_url + 'unity/student_viewer_data/' + this.id + '/' + this.sem)
          .then((response) => {
            Swal.close();
            if (response.data.success) {
              this.processStudentData(response.data);
            } else {
              window.location = this.base_url + 'users/login';
            }
          })
          .catch(error => {
            Swal.close();
            console.error("Error loading student data:", error);
            Swal.fire('Error', 'Failed to load student data', 'error');
          });
      })
      .catch(error => {
        Swal.close();
        console.error("Error syncing payments:", error);
        Swal.fire('Error', 'Failed to sync payment data', 'error');
      });
    },

    processStudentData(data) {
      this.student = data.student;
      this.tuition_years = data.tuition_years;
      this.term_balances = data.term_balances;
      this.programs = data.programs;
      this.electives = data.electives;
      this.change_grade = data.change_grade;
      this.deficiencies = data.deficiencies;
      this.balance = data.balancel;
      this.user_level = data.user_level;
      this.registration = data.registration;
      this.registration_status = data.registration?.intROG || 0;
      this.active_sem = data.active_sem;
      this.reg_status = data.reg_status;
      this.selected_ay = data.selected_ay;
      this.attendance = data.attendance;
      this.block_sections = data.block_sections;
      this.term_months = data.term_months;
      this.sections = data.sections;
      this.tuition_payment_link = data.tuition_payment_link;
      this.notif_message = data.notif_message;
      this.enlistment = data.enlistment;
      this.enlisted_subjects = data.enlisted_subjects;

      // Check for outstanding balances
      this.show_alert = this.term_balances.some(item => item.balance > 0);

      // Process scholarship and discount
      if (data.scholarship?.length > 0) {
        this.scholarship.name = data.scholarship.map(s => s.name).join(" ");
      }
      if (data.discount?.length > 0) {
        this.discount.name = data.discount.map(d => d.name).join(" ");
      }

      // Initialize form fields
      if (this.registration) {
        this.add_attendance.student_id = this.student.intID;
        this.add_subject.section = this.sections?.[0]?.intID || null;
        this.sem_student = this.selected_ay;
      }

      this.advanced_privilages1 = data.advanced_privilages1;
      this.advanced_privilages2 = data.advanced_privilages2;
      this.registrar_privilages = data.registrar_privilages;
      this.grad_status = this.student.isGraduate;
      this.records = data.records;
      this.total_units = data.total_units;
      this.lab_units = data.lab_units;
      this.gpa = data.gpa;
      this.other_data = data.other_data;

      // Load applicant data
      if (this.student.slug) {
        this.loadApplicantData();
      }
    },

    loadApplicantData() {
      axios.get(api_url + 'admissions/student-info/' + this.student.slug)
        .then(response => {
          this.applicant_data = response.data.data;
          this.applicant_data.tos = this.capitalizeFirstLetter(this.applicant_data.tos);

          // Find student photo
          const photoRequirement = this.applicant_data.uploaded_requirements?.find(req => 
            req.type === "2x2" || req.type === "2x2_foreign"
          );
          if (photoRequirement) {
            this.picture = photoRequirement.path.replace("116.50.237.244", "smsapi.iacademy.edu.ph");
          }
        })
        .catch(error => {
          console.error("Error loading applicant data:", error);
        });
    },

    capitalizeFirstLetter(string) {
      return string.charAt(0).toUpperCase() + string.slice(1);
    },

    updateStudentType(event) {
      this.updateField('enumStudentType', event.target.value);
    },

    updateStudentYearLevel(event) {
      this.updateField('intYearLevel', event.target.value);
    },

    updateAcademicStatus(event) {
      this.updateField('enumRegistrationStatus', event.target.value);
    },

    updateEnrollmentStatus(event) {
      this.updateField('intROG', event.target.value);
    },

    updateWithdrawalPeriod(event) {
      this.updateField('withdrawal_period', event.target.value);
    },

    updateField(field, value) {
      if (!this.registration) return;

      const formData = new FormData();
      formData.append('intRegistrationID', this.registration.intRegistrationID);
      formData.append(field, value);

      this.showLoading();
      axios.post(this.base_url + 'unity/update_academic_status', formData, {
        headers: {
          Authorization: `Bearer ${window.token}`
        }
      })
      .then(response => {
        this.hideLoading();
        if (response.data.success) {
          this.showSuccess(response.data.message);
          // Update local registration data
          this.registration[field] = value;
        } else {
          this.showError(response.data.message);
        }
      })
      .catch(error => {
        this.hideLoading();
        this.showError('Failed to update information');
        console.error(error);
      });
    },

    updateBlock(event) {
      this.updateField('block_section', event.target.value);
    },

    updateDateEnrolled(event) {
      this.updateField('dteRegistered', event.target.value);
    },

    updateDateEnlisted(event) {
      this.updateField('date_enlisted', event.target.value);
    },

    updateInternshipStatus(event) {
      this.updateField('internship', event.target.value);
    },

    updateCurrentProgram(event) {
      this.updateField('current_program', event.target.value);
    },

    selectTuitionYear(event) {
      this.updateField('tuition_year', event.target.value);
    },

    resetStatus() {
      Swal.fire({
        title: 'Confirm Reset',
        text: 'Are you sure you want to reset this student\'s registration?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, reset it!',
        cancelButtonText: 'No, cancel'
      }).then((result) => {
        if (result.isConfirmed) {
          window.location = this.base_url + 'unity/delete_registration/' + 
            this.student.intID + '/' + this.active_sem.intID;
        }
      });
    },

    assignElective() {
      if (!this.elective_classlist || !this.elective_subj) {
        this.showError('Please select both subject and elective');
        return;
      }

      Swal.fire({
        title: 'Confirm Assignment',
        text: 'Are you sure you want to update this elective assignment?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, update',
        cancelButtonText: 'Cancel'
      }).then((result) => {
        if (result.isConfirmed) {
          const formData = new FormData();
          formData.append('elective_classlist_id', this.elective_subj);
          formData.append('subject_classlist_id', this.elective_classlist);
          formData.append('student_id', this.student.intID);

          this.showLoading();
          axios.post(this.base_url + 'unity/assign_elective', formData, {
            headers: {
              Authorization: `Bearer ${window.token}`
            }
          })
          .then(response => {
            if (response.data.success) {
              this.showSuccess(response.data.message).then(() => {
                location.reload();
              });
            } else {
              this.showError(response.data.message);
            }
          })
          .catch(error => {
            this.showError('Failed to assign elective');
            console.error(error);
          });
        }
      });
    },

    enlistStudent() {
      if (!this.enlisted_subjects || this.enlisted_subjects.length === 0) {
        this.showError('No subjects selected for enlistment');
        return;
      }

      Swal.fire({
        title: 'Confirm Enlistment',
        text: 'Are you sure you want to enlist these subjects?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, enlist',
        cancelButtonText: 'Cancel'
      }).then((result) => {
        if (result.isConfirmed) {
          const formData = new FormData();
          formData.append('subjects', JSON.stringify(this.enlisted_subjects));
          formData.append('studentID', this.student.intID);
          formData.append('strAcademicYear', this.active_sem.intID);

          this.showLoading();
          axios.post(this.base_url + 'unity/enlist_from_advising', formData, {
            headers: {
              Authorization: `Bearer ${window.token}`
            }
          })
          .then(response => {
            if (response.data.success) {
              if (response.data.sid) {
                window.location = this.base_url + 'registrar/register_old_student/' + 
                  response.data.sid + '/' + this.active_sem.intID;
              } else {
                location.reload();
              }
            } else {
              this.showError(response.data.message);
            }
          })
          .catch(error => {
            this.showError('Failed to enlist subjects');
            console.error(error);
          });
        }
      });
    },

    sendEnlistedNotification() {
      if (!this.notif_message) {
        this.showError('Notification message is required');
        return;
      }

      this.showLoading();
      axios.post(api_url + 'registrar/send_notif_registered/' + this.student.slug, {
        message: this.notif_message,
        payment_link: this.tuition_payment_link
      }, {
        headers: {
          Authorization: `Bearer ${window.token}`
        }
      })
      .then(response => {
        this.showSuccess(response.data.message);
      })
      .catch(error => {
        this.showError('Failed to send notification');
        console.error(error);
      });
    },

    changeTermSelected() {
      window.location = this.base_url + "unity/student_viewer/" +
        this.student.intID + "/" + this.sem_student + "/" + this.tab;
    },

    printRF() {
      const marginTop = this.campus == 'Makati' ? 12 : 35;
      const url = this.base_url + 'pdf/student_viewer_registration_print/' + 
        this.student.intID + '/' + this.applicant_data.id + '/' + 
        this.active_sem.intID + '/' + marginTop;

      if (this.deficiencies.length > 0 || this.balance > 0) {
        Swal.fire({
          title: 'Warning',
          html: 'This student has active deficiencies.<br>' +
            '<a href="' + this.base_url + 'deficiencies/student_deficiencies/' + 
            this.student.intID + '" target="_blank">View Deficiencies</a>',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Continue Anyway',
          cancelButtonText: 'Cancel'
        }).then((result) => {
          if (result.isConfirmed) {
            window.open(url, '_blank');
          }
        });
      } else {
        window.open(url, '_blank');
      }
    },

    updateGradStatus() {
      const formData = new FormData();
      formData.append('intID', this.student.intID);
      formData.append('isGraduate', this.grad_status);

      this.showLoading();
      axios.post(this.base_url + 'unity/update_graduate_status', formData, {
        headers: {
          Authorization: `Bearer ${window.token}`
        }
      })
      .then(response => {
        if (response.data.success) {
          this.showSuccess(response.data.message);
        } else {
          this.showError(response.data.message);
        }
      })
      .catch(error => {
        this.showError('Failed to update graduation status');
        console.error(error);
      });
    },

    submitLoa() {
      if (!this.loaDetails.loa_remarks || !this.loaDetails.loa_date) {
        this.showError('Please provide both reason and date for the leave of absence');
        return;
      }

      Swal.fire({
        title: 'Confirm LOA',
        text: 'Are you sure you want to tag this student for Leave of Absence?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, continue',
        cancelButtonText: 'Cancel',
        input: 'password',
        inputAttributes: {
          placeholder: 'Enter your password to confirm',
          required: 'true'
        }
      }).then((result) => {
        if (result.isConfirmed) {
          const password = result.value;
          if (!password) {
            this.showError('Password is required');
            return;
          }

          const formData = new FormData();
          formData.append('loa_remarks', this.loaDetails.loa_remarks);
          formData.append('loa_date', this.loaDetails.loa_date);
          formData.append('term_id', this.sem_student);
          formData.append('student_id', this.student.intID);
          formData.append('awol', this.awol);
          formData.append('password', password);
          formData.append('student_name', `${this.student.strLastname} ${this.student.strFirstname}`);

          this.showLoading();
          axios.post(this.base_url + 'unity/tag_loa', formData, {
            headers: {
              Authorization: `Bearer ${window.token}`
            }
          })
          .then(response => {
            if (response.data.success) {
              this.showSuccess(response.data.message).then(() => {
                location.reload();
              });
            } else {
              this.showError(response.data.message);
            }
          })
          .catch(error => {
            this.showError('Failed to submit LOA request');
            console.error(error);
          });

          // Reset form and close modal
          this.loaDetails.loa_remarks = '';
          this.loaDetails.loa_date = '';
          const modal = bootstrap.Modal.getInstance(document.getElementById('loa-modal'));
          if (modal) modal.hide();
        }
      });
    },

    // Utility methods
    showLoading() {
      this.loader_spinner = true;
      Swal.showLoading();
    },

    hideLoading() {
      this.loader_spinner = false;
      Swal.hideLoading();
    },

    showSuccess(message) {
      return Swal.fire('Success', message, 'success');
    },

    showError(message) {
      return Swal.fire('Error', message, 'error');
    }
  }
}).mount('#student-viewer-container');

</script>