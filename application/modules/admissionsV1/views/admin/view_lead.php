
<link rel="stylesheet" href="https://unpkg.com/vue2-datepicker/index.css">
<script src="http://cdnjs.cloudflare.com/ajax/libs/moment.js/2.7.0/moment.min.js"></script>
<script src="https://unpkg.com/vue2-datepicker/index.min.js"></script>
<script src="https://unpkg.com/vue2-datepicker/locale/zh-cn.js"></script>

<div class="content-wrapper " id="applicant-container">
    <section class="content-header container ">
        <h1>
            Student Applicants
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i>Student Applicant Details </a></li>
            <li class="active">Details</li>
        </ol>
    </section>
    <div class="content  container">
        <div action="">
            <div class="box ">
                <div class="box-header with-border font-weight-bold py-5" style="text-align:left; font-weight:bold">
                    <h3 class="box-title text-left text-primary " style="font-size:2rem">
                        Applicant Details
                    </h3>
                </div>

                <div class="box-body" style="padding:2rem">
                    <div>
                        <strong><i class="fa fa-sitemap margin-r-5"></i>Status</strong>
                        <p>
                            <span class="label label-danger" v-if="request.status ==  'New'">New</span>
                            <span class="label label-primary" v-if="request.status ==  'For Interview'">For
                                Interview</span>
                            <span class="label label-warning" v-if="request.status ==  'Waiting For Interview'">Waiting
                                For
                                Interview</span>
                            <!-- <span class="label label-warning">Scheduled</span> -->
                            <span class="label label-info" v-if="request.status ==  'For Reservation'">For
                                Reservation</span>
                            <span class="label label-success" v-if="request.status ==  'Reserved'">Reserved</span>
                            <span class="label label-success" v-if="request.status ==  'Confirmed'">Confirmed</span>
                            <span class="label label-success" v-if="request.status ==  'Game Changer'">Game Changer</span>
                            <span class="label label-success" v-if="request.status ==  'For Enrollment'">For Enrollment</span>
                            <span class="label label-success" v-if="request.status ==  'Enrolled'">Enrolled</span>
                            <span class="label label-danger" v-if="request.status ==  'Rejected'">Rejected</span>
                        </p>
                        <hr>
                    </div>

                    <div>
                        <strong><i class="fa fa-book margin-r-5"></i> Name</strong>
                        <p class="text-muted">
                            {{request.first_name + ' ' + request.last_name}}
                        </p>
                        <hr>
                    </div>

                    <div>
                        <strong><i class="fa fa-envelope margin-r-5"></i> Email</strong>
                        <p class="text-muted">
                            {{ request.email }}
                        </p>
                        <hr>
                    </div>

                    <div>
                        <strong><i class="fa fa-phone margin-r-5"></i> Mobile Number</strong>
                        <p class="text-muted">
                            {{request.mobile_number}}
                        </p>
                        <hr>
                    </div>

                    <div>
                        <strong><i class="fa fa-phone-square margin-r-5"></i> Telephone Number</strong>
                        <p class="text-muted">
                            {{request.tel_number}}
                        </p>
                        <hr>
                    </div>

                    <div>
                        <strong><i class="fa fa-location-arrow margin-r-5"></i> Address</strong>
                        <p class="text-muted">
                            {{request.address}}
                        </p>
                        <hr>
                    </div>

                    <div>
                        <strong><i class="fa fa-calendar margin-r-5"></i> Birthday</strong>
                        <p class="text-muted">
                            {{request.date_of_birth}}
                        </p>
                        <hr>
                    </div>

                    <div>
                        <strong><i class="fa fa-home margin-r-5"></i> Previous School</strong>
                        <p class="text-muted">
                            {{request.school}}
                        </p>
                        <hr>
                    </div>

                    <div>
                        <strong><i class="fa fa-user margin-r-5"></i>Student Type</strong>
                        <p class="text-muted">
                            {{request.program}}
                        </p>
                        <hr>
                    </div>
                    <div>
                        <strong><i class="fa fa-user margin-r-5"></i>Citizenship</strong>
                        <p class="text-muted">
                            {{request.citizenship}}
                        </p>
                        <hr>
                    </div>
                    <div>
                        <strong><i class="fa fa-user margin-r-5"></i>Holds a good moral standing in previous school</strong>
                        <p :class="request.good_moral=='No'?'text-red':'text-muted'">
                            {{request.good_moral}}
                        </p>
                        <hr>
                    </div>

                    <div>
                        <strong><i class="fa fa-user margin-r-5"></i>Has been involved of any illegal activities</strong>
                        <p :class="request.crime=='Yes'?'text-red':'text-muted'">
                            {{request.crime}}
                        </p>
                        <hr>
                    </div>
                    <?php if($userlevel == "2" || $userlevel == "5"): ?>
                        <div class="" v-if="request.uploaded_requirements.length > 0">
                            <strong><i class="fa  margin-r-5"></i> <span style="font-size:2rem"
                                    class=" text-primary">Initial
                                    Requirements</span>
                            </strong>

                            <hr>
                        </div>

                        <div v-for="requirement in request.uploaded_requirements">
                            <strong><i class="fa fa-user margin-r-5"></i>{{ requirement.type }}</strong>
                            <p class="text-muted">
                                <a :href="requirement.path" target="_blank">
                                    {{requirement.filename}}</a>
                            </p>
                            <hr>
                        </div>
                    <?php endif; ?>

                    <div v-if="request.schedule_date">
                        <div class="">
                            <strong><i class="fa  margin-r-5"></i> <span style="font-size:2rem"
                                    class=" text-primary">Interview Schedule
                                </span>
                            </strong>

                            <hr>
                        </div>

                        <div>
                            <strong><i class="fa fa-calendar margin-r-5"></i> Date</strong>
                            <p class="text-muted">
                                {{request.schedule_date}}
                            </p>
                            <hr>
                        </div>

                        <div>
                            <strong><i class="fa fa-clock-o margin-r-5"></i> Time</strong>
                            <p class="text-muted">
                                {{request.schedule_time_from}} - {{request.schedule_time_to}}
                            </p>
                            <hr>
                        </div>
                    </div>

                    <div>
                        <div class="">
                            <strong><i class="fa  margin-r-5"></i> <span style="font-size:2rem"
                                    class=" text-primary">Health Declaration
                                </span>
                            </strong>

                            <hr>
                        </div>

                        <div>
                            <strong>Hospitalized?</strong>
                            <p class="text-muted">
                                {{request.hospitalized}}
                            </p>
                            <hr>
                        </div>

                        <div>
                            <strong>Hospitalized Reason</strong>
                            <p class="text-muted">
                                {{request.hospitalized_reason}}
                            </p>
                            <hr>
                        </div>
                        <div>
                            <strong>Health Concerns</strong>
                            <p class="text-red">
                                {{request.health_concern}}
                            </p>
                            <hr>
                        </div>
                        <div>
                            <strong>Other Health Concerns</strong>
                            <p class="text-muted">
                                {{request.other_health_concern}}
                            </p>
                            <hr>
                        </div>
                    </div>
                    <!-- <div>
                        <strong><i class="fa fa-sitemap margin-r-5"></i>Update Status</strong>
                        <div class="row">
                            <div class="text-muted mt-1 col-sm-5">
                                <select name="" class="form-control" required id="select-update-status">
                                    <option value="" disabled selected>--select--</option>
                                    <option value="new">New</option>
                                    <option value="for_interview">For Interview</option>
                                    <option value="scheduled">Scheduled</option>
                                    <option value="For Reservation">For Reservation</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                        </div>
                        <hr>
                    </div> -->
                    <?php if($userlevel == "2" || $userlevel == "5"): ?>
                    <div class="text-right">
                        <button type="button"  data-toggle="modal" data-target="#setFISchedule"
                            class=" btn btn-info">Update/Set FI</button>
                        <button type="button" v-if="request.status == 'New' || request.status == 'Waiting For Interview'"  @click="deleteApplicant"
                            class=" btn btn-danger">Delete applicant</button>
                        <button type="button" v-if="request.status == 'Waiting For Interview'" data-toggle="modal"
                            @click="update_status = 'For Interview';" data-target="#myModal" class=" btn
                            btn-primary">For
                            Interview</button>
                        <button type="button" v-if="request.status == 'For Interview'"
                            @click="update_status = 'For Reservation'" data-toggle="modal" data-target="#myModal"
                            class=" btn btn-info">For
                            Reservation</button>
                        <button type="button" v-if="request.status == 'Reserved'"
                            @click="update_status = 'For Enrollment'" data-toggle="modal" data-target="#myModal"
                            class=" btn btn-info">For
                            Enrollment</button>
                        <button type="button" v-if="request.status != 'Reserved' && request.status != 'For Enrollment' && request.status != 'Enrolled'" data-toggle="modal"
                            @click="update_status = 'Rejected'" data-target="#myModal" class=" btn
                            btn-danger">Reject</button>                        
                    </div>
                    <?php endif; ?>
                </div>

            </div>

        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title text-left text-primary">Update Program</h3>
                    </div>
                    <div class="box-body" style="padding:2rem">
                        <form @submit.prevent="confirmProgram" method="post">
                            <table class="table table-bordered table-striped">
                                <tbody>
                                    <tr>
                                        <th>Select Program</th>                                
                                        <td>                                    
                                            <select v-model="program_update" @change="changeProgram($event)" required class="form-control">
                                                <option v-for="program in programs" :value="program.intProgramID">{{ program.strProgramDescription }}</option>
                                            </select>
                                        </td>                                        
                                    </tr>                                    
                                </tbody>
                            </table>
                            <hr />
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">Update Program</button>                        
                            </div> 
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">

        <div class="row">

            <!-- for interview -->
            <div class="col-lg-12">
                <div class="box box-primary">
                    <div class="box-header with-border  font-weight-bold" style="text-align:left; font-weight:bold">
                        <h3 class="box-title text-left text-primary">Payments Made</h3>
                    </div>

                    <div class="box-body" style="padding:2rem">
                        <table class="table table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th>Request ID</th>
                                    <th>Total Payment</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="payment in request.payments">
                                    <td>{{payment.description}}</td>
                                    <td>{{payment.request_id}}</td>
                                    <td>₱ {{payment.total_amount_due}}</td>
                                    <td>{{payment.status}}</td>
                                    <td>{{payment.status == 'Paid' ? payment.date_paid : payment.date_expired  }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">

            <!-- for interview -->
            <div class="col-lg-12">
                <div class="box box-primary">
                    <div class="box-header with-border  font-weight-bold" style="text-align:left; font-weight:bold">
                        <h3 class="box-title text-left text-primary">Status Logs</h3>
                    </div>

                    <div class="box-body" style="padding:2rem">
                        <table class="table table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Admissions Officer</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="log in request.logs">
                                    <td>{{log.date_change}}</td>
                                    <td>{{log.status}}</td>
                                    <td>{{log.admissions_officer}}</td>
                                    <td>
                                        {{log.remarks}}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="setFISchedule" role="dialog">
        <form @submit.prevent="submitSchedule" class="modal-dialog modal-lg">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <!-- modal header  -->
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Update FI Schedule</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="inline-full-name">
                            Select Date
                        </label>
                        <date-picker v-model="request_sched.date"  :input-attr="{
                                        required: true,
                                        id: 'date'
                                    }"                                              
                            format="YYYY-MM-DD"
                            lang="en"
                            type="date"
                            placeholder="Select date"
                        ></date-picker>
                </div>
                <div class="form-group">
                        <label for="inline-full-name">
                            Select Time
                        </label>
                        <date-picker :time-picker-options="
                                            reserve_time_picker_options
                                        "  v-model="request_sched.from" type="time" lang="en" format="hh:mm A"
                            @change="checkTime" placeholder="HH:MM AM" :input-attr="{
                                        required: true,
                                        id: 'time_from'
                                    }"
                            input-class="form-control">
                        </date-picker>
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

    <div class="modal fade" id="myModal" role="dialog">
        <form @submit.prevent="updateStatus" class="modal-dialog modal-lg">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <!-- modal header  -->
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">{{update_status}}</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Remarks <span class="text-danger">*</span> </label>
                        <textarea class="form-control" v-model="status_remarks" rows="5" required></textarea>
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
</div>




<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@2.6.12"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
    integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>

<script>
new Vue({
    el: '#applicant-container',
    components: {        
        'date-picker': DatePicker
    },
    data: {
        request: {
            uploaded_requirements: []
        },
        request_sched: {
            from: "",
            to: "",
        },
        loader_spinner: true,
        type: "",
        slug: "<?php echo $this->uri->segment('3'); ?>",
        update_status: "",
        status_remarks: "",
        sched:"",
        date_selected: "",        
        date_selected_formatted: "",
        programs: [],
        program_update: undefined,
        program_text: undefined,
        reserve_time_picker_options: {
            start: "08:00",
            step: "00:30",
            end: "16:00"
        },
        payload:{
            field: undefined,            
        },
        delete_applicant:{

        }
    },

    mounted() {

        let url_string = window.location.href;
        let url = new URL(url_string);



        this.loader_spinner = true;
        axios.get(api_url + 'admissions/student-info/' + this.slug)
            .then((data) => {
                this.request = data.data.data;
                this.loader_spinner = false;
                //this.program_update = this.request.type_id;
                axios.get(base_url + 'program/programs')
                .then((data) => {
                    this.programs = data.data.programs;                     
                })
                .catch((error) => {
                    console.log(error);
                })
                
            })
            .catch((error) => {
                console.log(error);
            })



    },

    methods: {
        checkTime: function() {

            if (this.request.from && this.request.to) {
                if (this.request.from >= this.request.to) {
                    Swal.fire(
                        'Failed!',
                        "Invalid time, please select valid time.",
                        'error'
                    )

                    this.request.to = "";

                }
            }

        },
        changeProgram: function(event){
            //console.log(event.target[event.target.selectedIndex].text);
            this.program_text = event.target[event.target.selectedIndex].text;
        },
        confirmProgram: function(){    
           
                this.loading_spinner = true;
                Swal.fire({
                    showCancelButton: false,
                    showCloseButton: false,
                    allowEscapeKey: false,
                    title: 'Please wait',
                    text: 'Processing confirmation',
                    icon: 'info',
                })
                
                Swal.showLoading();
                this.payload = {
                    field: 'type_id',
                    value: this.program_update,
                    program: this.program_text
                };

                axios
                    .post(api_url + 'admissions/student-info/update-field/custom/' + this.slug , this.payload, {
                        headers: {
                            Authorization: `Bearer ${window.token}`
                        }
                    })
                    .then(data => {     
            
                        Swal.hideLoading();
                        document.location = base_url+'admissionsV1/view_lead/'+this.slug;
        
                        
                    });
            
            
        },

        deleteApplicant: function(){
            this.loading_spinner = true;
            Swal.fire({
                title: "Delete Applicant",
                text: "Are you sure you want to delete?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "warning",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                preConfirm: (login) => {
                    return axios.delete(api_url + 'admissions/student-info/'+ this.slug, this.delete_applicant, {
                            headers: {
                                Authorization: `Bearer ${window.token}`
                            }
                        })
                        .then(data => {
                            this.is_done = true;

                            if (data.data.success) {

                                Swal.fire({
                                    title: "SUCCESS",
                                    text: data.data.message,
                                    icon: "success"
                                }).then(res => {
                                    document.location = base_url+"admissionsV1/view_all_leads";
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
            }).then((result) => {
                if (result.isConfirmed) {}
            })
        },       
        submitSchedule: function() {

            let time_from = moment(this.request_sched.from).format('LT');
            let time_to = moment(this.request_sched.from).add(30, 'minutes').format('LT');
            
            this.request_sched.date = moment(this.request_sched.date).format("YYYY-MM-DD");
            
            this.request_sched.slug = this.slug;
            this.request_sched.time_from = moment(time_from, ["h:mm A"]).format("HH:mm")
            this.request_sched.time_to = moment(time_to, ["h:mm A"]).format("HH:mm")



            Swal.fire({
                title: "Submit Schedule",
                text: "Are you sure you want to submit?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                preConfirm: (login) => {
                    return axios
                        .post(api_url + 'interview-schedules/admin/set_date', this.request_sched, {
                            headers: {
                                Authorization: `Bearer ${window.token}`
                            }
                        })
                        .then(data => {
                            this.is_done = true;

                            if (data.data.success) {

                                Swal.fire({
                                    title: "SUCCESS",
                                    text: data.data.message,
                                    icon: "success"
                                }).then(res => {
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
            }).then((result) => {
                if (result.isConfirmed) {}
            })
        },
        updateStatus: function() {


            Swal.fire({
                title: 'Update Status',
                text: "Are you sure you want to update?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                preConfirm: (login) => {

                    return axios
                        .post(api_url + 'admissions/student-info/' + this.slug +
                            '/update-status', {
                                status: this.update_status,
                                remarks: this.status_remarks,
                                admissions_officer: "<?php echo $user['strFirstname'] . '  ' . $user['strLastname'] ; ?>"
                            }, {
                                headers: {
                                    Authorization: `Bearer ${window.token}`
                                }
                            })
                        .then(data => {
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
            }).then((result) => {
                // if (result.isConfirmed) {
                //     Swal.fire({
                //         icon: result?.value.data.success ? "success" : "error",
                //         html: result?.value.data.message,
                //         allowOutsideClick: false,
                //     }).then(() => {
                //         if (reload && result?.value.data.success) {
                //             if (reload == "reload") {
                //                 location.reload();
                //             } else {
                //                 window.location.href = reload;
                //             }
                //         }
                //     });
                // }
            })
        }


    }

})
</script>