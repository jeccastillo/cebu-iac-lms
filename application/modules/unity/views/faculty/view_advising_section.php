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
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Days</th>
                                <th>Abscences</th>
                                <th>Tardies</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="ad in attendance_data">
                                <td>{{ ad.month }}</td>
                                <td>{{ ad.school_days }}</td>
                                <td>{{ ad.abscences }}</td>
                                <td>{{ ad.tardy }}</td>
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
        students: [],
        loading_attendance: false,
        attendance_data: [],     
        selected_student: undefined,   
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
            }
            )
            .catch((error) => {
            console.log(error);
            })
        },
    }

})
</script>

