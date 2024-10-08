<aside class="right-side" id="registration-container">    
    <section class="content-header">
        <h1>
            <small>
                <a class="btn btn-app" :href="base_url + 'unity/view_classlist/' + sem"><i class="ion ion-arrow-left-a"></i>All Classes</a>                                     
            </small>
        </h1>
    </section>        
    <div class="content">        
        <div class="box box-primary">
            <div class="box-header">
                <h3>{{ section.name }}</h3>
            </div>
            <div class="box-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Last Name</th>
                            <th>First Name</th>
                            <th>Middle Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="student in students">
                            <td>{{ student.strLastname }}</td>
                            <td>{{ student.strFirstname }}</td>
                            <td>{{ student.strMiddlename }}</td>
                            <td>
                                <a @click="loadAttendance(student)" class="btn btn-primary" data-toggle="modal" data-target="#attendance-modal">
                                    View Attendance
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- modal start -->
    <div class="modal fade"
      id="attendance-modal"
      tabindex="-1"
      role="dialog">
        <div class="modal-dialog"
        role="document">
            <div class="modal-content">            
                <div class="modal-header">
                    <h3>Attendance</h3>
                    <p>{{ selected_student.strLastname +  " " + selected_student.strFirstname + ", " + selected_student.strMiddlename }}</p>
                </div>

                <div v-if="!loading_attendance" class="modal-body">
                    <a class="btn btn-primary"
                        @click="resetForm"
                        data-toggle="modal"
                        data-target="#add-attendance-modal">
                            Add Attendance Record
                    </a>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Days</th>
                                <th>Abscences</th>
                                <th>Tardies</th>
                                <th>Actions</th>
                            </tr>                            
                        </thead>
                        <tbody>
                            <tr v-for="ad in attendance_data">
                                <td>{{ ad.month }}</td>
                                <td>{{ ad.school_days }}</td>
                                <td>{{ ad.abscences }}</td>
                                <td>{{ ad.tardy }}</td>
                                <td>
                                    <button class="btn btn-danger" @click="deleteAttendance(ad.id)">Delete</button>
                                    <button class="btn btn-primary" data-toggle="modal"
                                    data-target="#add-attendance-modal" @click="setForEdit(ad)">Edit</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="modal-body">
                    <h3>Loading Data</h3>
                </div>
            </div>
        </div>
    </div>
    <!-- modal end -->
     <!-- modal start -->
    <div class="modal fade"
      id="add-attendance-modal"
      tabindex="-1"
      role="dialog">
        <div class="modal-dialog"
        role="document">
            <div class="modal-content">
                <div class="modal-header">
                <button type="button"
                    class="close"
                    data-dismiss="modal"
                    aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"
                    id="modalLabel">Add Attendance Record</h4>
                </div>
                <div class="modal-body">
                <form @submit.prevent="submitAttendance(for_update)">
                  <div>
                    Student: 
                    {{ selected_student.strLastname.toUpperCase() }}, {{ selected_student.strFirstname.toUpperCase() }}
                    {{ selected_student.strMiddlename?selected_student.strMiddlename.toUpperCase():'' }}
                  </div>
                  <div>
                    Term: {{ active_sem.enumSem + " " + active_sem.term_label + " " + active_sem.strYearStart + " - " + active_sem.strYearEnd }}
                  </div>
                  <hr />
                  <div class="form-group">
                    <label>Select Month</label>
                    <select v-model="add_attendance.month_id"                  
                      class="form-control">
                      <option v-for="m in term_months"
                        :value="m.id">
                        {{ m.month }}
                      </option>
                    </select>
                  </div> 
                  <div class="form-group">
                    <label>School Days</label>
                    <input type="number" min="0" placeholder="Enter number" v-model="add_attendance.school_days"                  
                      class="form-control" />                                          
                  </div> 
                  <div class="form-group">
                    <label>Number of Days Abscent</label>
                    <input type="number" min="0" placeholder="Enter number" v-model="add_attendance.abscences"                  
                      class="form-control" />                                          
                  </div> 
                  <div class="form-group">
                    <label>Number of Days Tardy</label>
                    <input type="number" min="0" placeholder="Enter number" v-model="add_attendance.tardy"                  
                      class="form-control" />                                          
                  </div> 
                  <div class="form-group">
                    <div>
                        <button type="submit"
                        class="btn btn-default">Submit</button>
                    </div>
                  </div>
                </form>
                </div>
                <div class="modal-footer"
                style="margin-top:0">
                               
                 
                <button type="button"
                    class="btn btn-secondary"
                    data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- modal end -->
    
</aside>

<script src="<?php echo base_url(); ?>assets/themes/default/js/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script src="<?php echo base_url(); ?>assets/themes/default/js/vue.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
    integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="<?php echo base_url(); ?>assets/themes/default/js/axios.min.js"></script>

<script>
new Vue({
    el: '#registration-container',
    data: {
        id: '<?php echo $id; ?>',            
        sem: '<?php echo $sem; ?>',       
        section: undefined,
        active_sem: undefined,
        term_months: [],
        students: [],
        loading_attendance: false,
        attendance_data: [],     
        selected_student: {
            strFirstname: "",
            strLastname: "",
            strMiddlename: "",
        },   
        add_attendance: {            
            student_id: undefined,
            month_id: undefined,
            school_days: undefined,
            abscences: undefined,
            tardy: undefined,
        },
        for_update: 0,
    },

    mounted() {

        let url_string = window.location.href;        
        if(this.id != 0){            
            //this.loader_spinner = true;
            axios.get(base_url + 'unity/advising_section_data/' + this.id + "/" + this.sem)
                .then((data) => {                                          
                    this.section = data.data.section;
                    this.students = data.data.students;
                    this.active_sem = data.data.active_sem;
                    this.term_months = data.data.term_months;
                })
                .catch((error) => {
                    console.log(error);
                })
        }

    },

    methods: {      
        loadAttendance: function(student){
            this.loading = true;
            axios.get(base_url + 'unity/attendance_data/' + student.intID + '/' + this.active_sem.intID)
            .then((data) => {
                this.attendance_data = data.data.attendance;
                this.loading = false;
                this.selected_student = student;
                this.add_attendance.student_id = student.intID;
            }
            )
            .catch((error) => {
            console.log(error);
            })
        },
        submitAttendance: function(update = 0){
            Swal.fire({
                title: 'Submit Attendance Record',
                text: "Are you sure you want to proceed?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                preConfirm: (login) => {
                
                var formdata = new FormData();
                for (const [key, value] of Object.entries(this.add_attendance)) {
                formdata.append(key, value);
                }          
                if(update != 0)
                    formdata.append('id', update);
                            
                return axios.post(base_url + 'unity/add_attendance_record', formdata, {
                    headers: {
                        Authorization: `Bearer ${window.token}`
                    },

                })
                .then(data => {
                    if(data.data.success)
                        Swal.fire({
                            title: "Success",
                            text: data.data.message,
                            icon: "success"
                        }).then(function() {
                            location.reload();
                        });
                    else
                        Swal.fire({
                            title: "Error",
                            text: data.data.message,
                            icon: "error"
                        })
                })
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {

            })
        },
        setForEdit: function(attendance){
            this.for_update = attendance.id;
            this.add_attendance.month_id = attendance.month_id;
            this.add_attendance.school_days = attendance.school_days;
            this.add_attendance.abscences = attendance.abscences;
            this.add_attendance.tardy = attendance.tardy;
        },
        resetForm: function(attendance){
            this.for_update = 0;
            this.add_attendance.month_id = attendance.month_id;
            this.add_attendance.school_days = attendance.school_days;
            this.add_attendance.abscences = attendance.abscences;
            this.add_attendance.tardy = attendance.tardy;
        },
        deleteAttendance(id){
            Swal.fire({
                title: 'Delete Attendance?',
                text: "Continue deleting attendance record?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                preConfirm: (login) => {
                var formdata = new FormData();
                formdata.append("id", id);
                return axios
                    .post('<?php echo base_url(); ?>unity/delete_attendance', formdata, {
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
    }

})
</script>

