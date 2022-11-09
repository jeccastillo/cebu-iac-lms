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
        <form action="" @submit.prevent="updateStatus">
            <div class="box ">
                <div class="box-header with-border font-weight-bold" style="text-align:left; font-weight:bold">
                    <h3 class="box-title text-left">Juan Dela Cruz - Details </h3>
                </div>

                <div class="box-body" style="padding:2rem">
                    <div>
                        <strong><i class="fa fa-sitemap margin-r-5"></i> Current Status</strong>
                        <p>
                            <span class="label label-danger">New</span>
                            <span class="label label-primary">For Interview</span>
                            <span class="label label-warning">Scheduled</span>
                            <span class="label label-info">For Reservation</span>
                            <span class="label label-success">Reserved</span>
                            <span class="label label-danger">Rejected</span>
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
                            {{request.birthday}}
                        </p>
                        <hr>
                    </div>

                    <div>
                        <strong><i class="fa fa-home margin-r-5"></i> Previous School</strong>
                        <p class="text-muted">
                            {{request.previous_school}}
                        </p>
                        <hr>
                    </div>

                    <div>
                        <strong><i class="fa fa-user margin-r-5"></i>Student Type</strong>
                        <p class="text-muted">
                            {{request.student_type_title}}
                        </p>
                        <hr>
                    </div>

                    <div class="hidden">
                        <strong><i class="fa fa-bookmark margin-r-5"></i>Desired Program</strong>
                        <p class="text-muted">
                            {{request.student_type_title}}
                        </p>
                        <hr>
                    </div>

                    <div>
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
                    </div>

                    <div class="text-right">
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>

                </div>

            </div>


            <!-- for status Update -->
            <div class="row">

                <!-- for interview -->
                <div class="col-lg-4">
                    <div class="box box-primary">
                        <div class="box-header with-border  font-weight-bold" style="text-align:left; font-weight:bold">
                            <h3 class="box-title text-left text-primary">For Interview</h3>
                        </div>

                        <div class="box-body" style="padding:2rem">
                            <div>
                                <strong><i class="fa fa-calendar margin-r-5"></i>Schedule</strong>
                                <p class="text-muted">
                                    December 10, 2022 10:-00 AM
                                </p>
                                <hr>
                            </div>

                            <div>
                                <strong><i class="fa fa-user margin-r-5"></i>AO Officer</strong>
                                <p class="text-muted">
                                    Pedro Biglang Awa
                                </p>
                                <hr>
                            </div>

                            <div>
                                <strong><i class="fa  fa-file margin-r-5"></i>Remarks</strong>
                                <p class="text-muted">
                                    Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed
                                    quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt.
                                </p>
                                <hr>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- end -->

                <!-- for scheduled -->
                <div class="col-lg-4">
                    <div class="box box-warning">
                        <div class="box-header with-border font-weight-bold" style="text-align:left; font-weight:bold">
                            <h3 class="box-title text-left text-warning">Scheduled</h3>
                        </div>

                        <div class="box-body" style="padding:2rem">
                            <div>
                                <strong><i class="fa fa-calendar margin-r-5"></i>Date</strong>
                                <p class="text-muted">
                                    December 10, 2022 10:-00 AM
                                </p>
                                <hr>
                            </div>

                            <div>
                                <strong><i class="fa fa-user margin-r-5"></i>AO Officer</strong>
                                <p class="text-muted">
                                    Pedro Biglang Awa
                                </p>
                                <hr>
                            </div>

                            <div>
                                <strong><i class="fa  fa-file margin-r-5"></i>Remarks</strong>
                                <p class="text-muted">
                                    Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed
                                    quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt.
                                </p>
                                <hr>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- end -->

                <!-- for reservation -->
                <div class="col-lg-4">
                    <div class="box box-info">
                        <div class="box-header with-border font-weight-bold" style="text-align:left; font-weight:bold">
                            <h3 class="box-title text-left text-info">For Reservation</h3>
                        </div>

                        <div class="box-body" style="padding:2rem">
                            <div>
                                <strong><i class="fa fa-money margin-r-5"></i>Reservation Fee</strong>
                                <p class="text-muted">
                                    ₱700
                                </p>
                                <hr>
                            </div>

                            <div>
                                <strong><i class="fa fa-user margin-r-5"></i>AO Officer</strong>
                                <p class="text-muted">
                                    Pedro Biglang Awa
                                </p>
                                <hr>
                            </div>

                            <div>
                                <strong><i class="fa  fa-file margin-r-5"></i>Remarks</strong>
                                <p class="text-muted">
                                    Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed
                                    quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt.
                                </p>
                                <hr>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- end -->

                <!-- reserved -->
                <div class="col-lg-4">
                    <div class="box box-success">
                        <div class="box-header with-border font-weight-bold" style="text-align:left; font-weight:bold">
                            <h3 class="box-title text-left text-success">Reserved</h3>
                        </div>

                        <div class="box-body" style="padding:2rem">
                            <div>
                                <strong><i class="fa fa-calendar margin-r-5"></i>Date Reserved</strong>
                                <p class="text-muted">
                                    December 15, 2022 10:-00 AM
                                </p>
                                <hr>
                            </div>

                            <div>
                                <strong><i class="fa fa-user margin-r-5"></i>AO Officer</strong>
                                <p class="text-muted">
                                    Pedro Biglang Awa
                                </p>
                                <hr>
                            </div>

                            <div>
                                <strong><i class="fa  fa-file margin-r-5"></i>Remarks</strong>
                                <p class="text-muted">
                                    Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed
                                    quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt.
                                </p>
                                <hr>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end -->

                <!-- rejected -->
                <div class="col-lg-4">
                    <div class="box box-danger">
                        <div class="box-header with-border font-weight-bold" style="text-align:left; font-weight:bold">
                            <h3 class="box-title text-left text-danger">Rejected</h3>
                        </div>

                        <div class="box-body" style="padding:2rem">
                            <div>
                                <strong><i class="fa fa-calendar margin-r-5"></i>Date Reserved</strong>
                                <p class="text-muted">
                                    December 15, 2022 10:-00 AM
                                </p>
                                <hr>
                            </div>

                            <div>
                                <strong><i class="fa fa-user margin-r-5"></i>AO Officer</strong>
                                <p class="text-muted">
                                    Pedro Biglang Awa
                                </p>
                                <hr>
                            </div>

                            <div>
                                <strong><i class="fa  fa-file margin-r-5"></i>Remarks</strong>
                                <p class="text-muted">
                                    Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed
                                    quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt.
                                </p>
                                <hr>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end -->

            </div>
            <!-- end -->
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
        request: {},
        loader_spinner: true,
        type: '',
        slug: "<?php echo $this->uri->segment('3'); ?>",
    },

    mounted() {

        let url_string = window.location.href;
        let url = new URL(url_string);
        // let slug = url.searchParams.get("slug");
        console.log(this.slug);



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
                title: 'Update Details',
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
                        .post(api_url + 'admissions/student-info/' + this.slug, {
                            status: $("#select-update-status").val()
                        }, {
                            headers: {
                                Authorization: `Bearer ${window.token}`
                            }
                        })
                        .then(data => {
                            if (data.data.success) {
                                // this.successMessageApi(data.data.message);
                                location.reload();
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