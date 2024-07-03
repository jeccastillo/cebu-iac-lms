<div class="container py-4" id="adminssions-form" style="margin-top:100px;">
    <h4 class="font-bold"><strong>Student Information Sheet</strong></h4>
    <p>Hello future Game Changers! Kindly fill out your information sheet. If you have any questions, feel free to email
        us at <strong><u>admissionscebu@iacademy.edu.ph</u></strong> </p>
    <hr>

    <form class="row" @submit.prevent="
                        customSubmit(
                            'submit',
                            'Submit Details',
                            'form',
                            request,
                            'admissions/student-info'
                        )
                    " method="post">
        <div class="col-md-6">
            <div class="card-body">
                <div class="form-group">
                    <label for="exampleInputEmail1">Email address</label>
                    <input type="email" v-model="request.email" class="form-control" id="exampleInputEmail1" required>
                </div>
                <div class="form-group">
                    <label for="exampleInputEmail2">Re-type Email</label>
                    <input type="email" v-model="request.email_confirmation" class="form-control"
                        id="exampleInputEmail2" required>
                    <small>( You won't receive our Admissions Letter if this is incorrect )</small>
                </div>
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" v-model="request.first_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Middle Name</label>
                    <input type="text" v-model="request.middle_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" v-model="request.last_name" class="form-control" required>
                </div>

            </div>

        </div>

        <div class="col-md-6">
            <div class="card-body">
                <div class="form-group">
                    <label for="exampleInputEmail1">Mobile Number</label>
                    <input type="number" v-model="request.number" class="form-control" id="" required>
                </div>
                <div class="form-group">
                    <label for="exampleInputEmail1">Telephone Number</label>
                    <input type="number" v-model="request.telephone_number" class="form-control" id="">
                    <span>&nbsp</span>
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <input type="text" class="form-control" id="" v-model="request.address">
                </div>
                <div class="form-group">
                    <label>Birthday</label>
                    <input type="date" class="form-control" required v-model="request.birtdate">
                </div>
                <div class="form-group">
                    <label>Previous School</label>
                    <input type="text" class="form-control" required v-model="request.previous_school">
                </div>
                <div class="form-group" v-if="types && programs">
                    <label for="">Applying for <span class="text-danger">*</span>
                    </label>
                    <div class="d-flex align-items-center font-14" v-for="t in types" :key="t.id">
                        <input type="checkbox" class="mr-2 admissions_submission_cb" @click="filterProgram(t.type)"
                            name="" :value="t.id" required />
                        {{ t.title }}
                    </div>
                </div>
                <div class="form-group" v-if="request.type_id">
                    <label for="">Desired Program
                        <span class="text-danger">*</span>
                    </label>
                    <div class="d-flex align-items-center font-14" v-for="t in programs_group" :key="t.id">
                        <input type="checkbox" class="mr-2 admissions_submission_pg" name="" :value="t.id" required />
                        {{ t.title }}
                    </div>
                </div>

            </div>
        </div>

        <div class="col-sm-12 mb-5" style="margin-bottom: 3rem">
            <hr />
            <div class=" text-right">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </div>

    </form>
</div>

<style>
.swal2-popup {
    font-size: 1.6rem !important;
}
</style>

<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.12.1/sweetalert2.min.js" integrity="sha512-TV1UlDAJWH0asrDpaia2S8380GMp6kQ4S6756j3Vv2IwglqZc3w2oR6TxN/fOYfAzNpj2WQJUiuel9a7lbH8rA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.6.2/sweetalert2.min.css"
    integrity="sha512-5aabpGaXyIfdaHgByM7ZCtgSoqg51OAt8XWR2FHr/wZpTCea7ByokXbMX2WSvosioKvCfAGDQLlGDzuU6Nm37Q=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
<script src="<?php echo base_url(); ?>assets/themes/default/js/vue.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
    integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>
<!-- <script type="text/javascript" type="module" src="<?php echo base_url(); ?>assets/themes/default/js/mixins.js"></script> -->

<script>
new Vue({
    el: "#adminssions-form",
    data: {
        request: {
            type_id: "",
            date_of_birth: ""
        },
        programs: [],
        programs_group: [],
        types: []
    },
    mounted() {

        axios
            .get(api_url + 'admissions/student-info/programs', {
                headers: {
                    Authorization: `Bearer ${window.token}`
                },
            })

            .then((data) => {
                this.programs = data.data.data;
            })
            .catch((e) => {
                console.log("error");
            });

        axios
            .get(api_url + 'admissions/student-informations/types', {
                headers: {
                    Authorization: `Bearer ${window.token}`
                },
            })
            .then((data) => {
                this.types = data.data.data;
                setTimeout(() => {
                    $(".admissions_submission_cb").on("click", e => {
                        $(".admissions_submission_cb")
                            .not(e.currentTarget)
                            .prop("checked", false);
                        if ($(e.currentTarget).is(":checked")) {
                            this.request.type_id = e.currentTarget.value;
                            $(".admissions_submission_cb").removeAttr(
                                "required"
                            );
                        } else {
                            $(".admissions_submission_cb").attr(
                                "required",
                                true
                            );
                        }
                    });
                }, 500);
            })
            .catch((e) => {
                console.log("error");
            });

    },

    methods: {
        submitForm: function() {
            alert(1);
            console.log(this.request);
        },

        filterProgram: function(type) {
            var group = _.filter(this.programs, function(o) {
                return o.type == type;
            });
            var others = _.filter(this.programs, function(o) {
                return o.type == "others";
            });
            this.programs_group = _.concat(group, others);

            setTimeout(() => {
                $(".admissions_submission_pg").on("click", e => {
                    $(".admissions_submission_pg")
                        .not(e.currentTarget)
                        .prop("checked", false);
                    if ($(e.currentTarget).is(":checked")) {
                        this.request.program_id = e.currentTarget.value;
                        $(".admissions_submission_pg").removeAttr("required");
                    } else {
                        $(".admissions_submission_pg").attr("required", true);
                    }
                });
            }, 500);
        },

        customSubmit: function(type, title, text, data, url, redirect) {
            Swal.fire({
                title: title,
                text: "Are you sure you want to " + type + " this " + text + "?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true
            }).then(result => {
                if (result.value) {
                    this.is_done = false;
                    $(".modal").modal("hide");

                    axios
                        .post(api_url + url, data, {
                            headers: {
                                Authorization: `Bearer ${window.token}`
                            }
                        })
                        .then(data => {
                            this.is_done = true;

                            if (data.data.success) {
                                // this.successMessageApi(data.data.message);

                                if (redirect) {
                                    window.location.href = "#/" + redirect;
                                } else {
                                    location.reload();
                                }
                            } else {
                                this.failedMessageApi(data.data.message);
                            }
                        });
                } else {
                    this.noChangesApi();
                }
            });
        },
    },
});
</script>