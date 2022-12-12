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



                    <div class="" v-if="request.uploaded_requirements.length > 0">
                        <strong><i class="fa  margin-r-5"></i> <span style="font-size:2rem"
                                class=" text-primary">Initial
                                Requirements</span>
                        </strong>

                        <hr>
                    </div>

                    <div v-if="request.uploaded_requirements.length > 0">
                        <strong><i class="fa fa-user margin-r-5"></i>School ID</strong>
                        <p class="text-muted">
                            <a :href="request.uploaded_requirements[0].path" target="_blank">
                                {{request.uploaded_requirements[0].filename}}</a>
                        </p>
                        <hr>
                    </div>

                    <div v-if="request.uploaded_requirements.length > 0">
                        <strong><i class="fa fa-file margin-r-5"></i>PSA / NSO</strong>
                        <p class="text-muted">
                            <a :href="request.uploaded_requirements[1].path" target="_blank">
                                {{request.uploaded_requirements[1].filename}}</a>
                        </p>
                        <hr>
                    </div>

                    <div v-if="request.uploaded_requirements.length > 0">
                        <strong><i class="fa fa-camera margin-r-5"></i>2x2 Picture</strong>
                        <p class="text-muted">
                            <a :href="request.uploaded_requirements[2].path" target="_blank">
                                {{request.uploaded_requirements[2].filename}}</a>
                        </p>
                        <hr>
                    </div>

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

                    <div class="text-right">
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
                                    <th>Total Payment</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="payment in request.payments">
                                    <td>{{payment.description}}</td>
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
    data: {
        request: {
            uploaded_requirements: []
        },
        loader_spinner: true,
        type: "",
        slug: "<?php echo $this->uri->segment('3'); ?>",
        update_status: "",
        status_remarks: "",
    },

    mounted() {

        let url_string = window.location.href;
        let url = new URL(url_string);



        this.loader_spinner = true;
        axios.get(api_url + 'admissions/student-info/' + this.slug)
            .then((data) => {
                this.request = data.data.data;
                this.loader_spinner = false;
            })
            .catch((error) => {
                console.log(error);
            })



    },

    methods: {

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