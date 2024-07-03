<div id="registration-container">
    <div class="container">
        <div class="content">
            <div class="box">
                <div class="box-header">
                    <h3>Name :{{ student.strFirstname }} {{ student.strLastname }} <br />
                    </h3>
                    <h4>Please confirm your selected program and fill in additional information.</h4>
                </div>
                <div class="box-body">
                    <form @submit.prevent="confirmProgram" method="post">
                        <table class="table table-bordered table-striped">
                            <tbody>
                                <tr>
                                    <th>Selected Program</th>
                                    <td>
                                        <select id="program" disabled v-model="request.intProgramID"
                                            @change="changeProgram($event)" class="form-control">
                                            <option v-for="program in programs" :value="program.intProgramID">
                                                {{ program.strProgramDescription }}</option>
                                        </select>
                                    </td>
                                    <td></td>
                                </tr>

                                <tr v-if="sections.length > 0">
                                    <th>Select Section/Schedule</th>
                                    <td>
                                        <select v-model="request.preferedSection" @change="changeSection"
                                            class="form-control">
                                            <option v-for="section in sections" :value="section.intID">
                                                {{ section.name }}</option>
                                        </select>
                                    </td>
                                    <td>
                                        <a class="btn btn-primary"
                                            :href="base_url + 'unity/schedule_viewer/' + section.intID"
                                            target="_blank">View Schedule</a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <h3>Additional Information (type n/a if not applicable)</h3>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Mother's Maiden Name</label>
                                <input type="text" class="form-control" v-model="request.mother">
                            </div>
                            <div v-if="request.mother!='n/a'" class="col-md-6 form-group">
                                <label>Contact Number (please specify country code)</label>
                                <input type="text" class="form-control" v-model="request.mother_contact" masked="true"
                                    placeholder="Enter contact number" />
                            </div>
                        </div>
                        <div class="row">
                            <div v-if="request.mother!='n/a'" class="col-md-6 form-group">
                                <label>Email Address</label>
                                <input type="email" class="form-control" v-model="request.mother_email">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Father's Name</label>
                                <input type="text" class="form-control" v-model="request.father">
                            </div>
                            <div v-if="request.father!='n/a'" class="col-md-6 form-group">
                                <label>Contact Number (please specify country code)</label>
                                <input type="text" class="form-control" v-model="request.father_contact" masked="true"
                                    placeholder="Enter contact number" />
                            </div>
                        </div>
                        <div class="row">
                            <div v-if="request.father!='n/a'" class="col-md-6 form-group">
                                <label>Email Address</label>
                                <input type="email" class="form-control" v-model="request.father_email">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Name of Guardian</label>
                                <input type="text" class="form-control" v-model="request.guardian">
                            </div>
                            <div v-if="request.guardian!='n/a'" class="col-md-6 form-group">
                                <label>Contact Number (please specify country code)</label>
                                <input type="text" class="form-control" v-model="request.guardian_contact" masked="true"
                                    placeholder="Enter contact number" />
                            </div>
                        </div>
                        <div class="row">
                            <div v-if="request.guardian!='n/a'" class="col-md-6 form-group">
                                <label>Email Address</label>
                                <input type="text" class="form-control" v-model="request.guardian_email" masked="true"
                                    placeholder="Enter contact number" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Gender</label>
                                <select required class="form-control" v-model="request.enumGender">
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                </select>
                            </div>
                        </div>

                        <h3>Educational Background (type n/a if not applicable)</h3>

                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label>Junior High School</label>
                                <input type="text" required class="form-control" v-model="request.high_school">
                            </div>
                            <div class="col-md-4 form-group">
                                <label>School Address</label>
                                <textarea required class="form-control"
                                    v-model="request.high_school_address"></textarea>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Years Attended (month-day-year)</label>
                                <the-mask class="form-control" :mask="['##-##-####']" type="text"
                                    v-model="request.high_school_attended" required masked="true"
                                    placeholder="mm-dd-yyyy"></the-mask>
                            </div>
                        </div>

                        <!-- college  -->
                        <div class="row" v-if="api_data.type == 'college' || api_data.type == 'other'">
                            <div class="col-md-4 form-group">
                                <label>College</label>
                                <input type="text" class="form-control" v-model="request.college">
                            </div>
                            <div v-if="request.college!='n/a'" class="col-md-4 form-group">
                                <label>School Address</label>
                                <textarea class="form-control" v-model="request.college_address"></textarea>
                            </div>
                            <div v-if="request.college!='n/a'" class="col-md-4 form-group">
                                <label>Years Attended From (month-day-year)</label>
                                <the-mask class="form-control" :mask="['##-##-####']" type="text"
                                    v-model="request.college_attended_from" masked="true" placeholder="mm-dd-yyyy">
                                </the-mask>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 form-group">
                            </div>
                            <div class="col-md-4 form-group">
                            </div>
                            <div v-if="request.college!='n/a'" class="col-md-4 form-group">
                                <label>Years Attended To (month-day-year)</label>
                                <the-mask class="form-control" :mask="['##-##-####']" type="text"
                                    v-model="request.college_attended_to" masked="true" placeholder="mm-dd-yyyy">
                                </the-mask>
                            </div>
                        </div>
                        <!-- end -->



                        <!-- SHS -->

                        <div class="row" v-if="api_data.type == 'college' || api_data.type == 'other'">
                            <div class="col-md-4 form-group">
                                <label>Senior High School</label>
                                <input type="text" class="form-control" v-model="request.senior_high">
                            </div>
                            <div class="col-md-4 form-group">
                                <label>School Address</label>
                                <textarea class="form-control" v-model="request.senior_high_address"></textarea>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Years Attended From (month-day-year)</label>
                                <the-mask class="form-control" :mask="['##-##-####']" type="text"
                                    v-model="request.senior_high_attended" masked="true" placeholder="mm-dd-yyyy">
                                </the-mask>
                            </div>
                        </div>

                        <!-- END -->





                        <div class="row">
                            <div class="col-md-4 form-group"
                                v-if="api_data.type == 'college' || api_data.type == 'other'">
                                <label>Strand</label>
                                <input type="text" class="form-control" v-model="request.strand">
                            </div>
                            <!-- <div class="col-md-8 form-group">
                                <label>Type of Student</label>
                                <select v-if="api_data.campus == 'Cebu'" required class="form-control"
                                    v-model="request.student_type">
                                    <option value="freshman">Freshman</option>
                                    <option value="transferee">Transferee</option>
                                    <option value="foreign">Foreign</option>
                                </select>
                                <select v-if="api_data.campus == 'Makati'" required class="form-control"
                                    v-model="request.student_type">
                                    <option value="COLLEGE - Freshman">COLLEGE - Freshman</option>
                                    <option value="COLLEGE - Transferee">COLLEGE - Transferee</option>
                                    <option value="SHS - Freshman">SHS - Freshman</option>
                                    <option value="SHS-  Transferee">SHS - Transferee</option>
                                    <option value="SHS - DRIVE HomeSchool Program">SHS - DRIVE HomeSchool Program
                                    </option>
                                    <option value="2ND - DEGREE">2ND - DEGREE</option>
                                </select>

                            </div> -->
                        </div>

                        <!--  -->
                        <hr>
                        <h3>LRN & Voucher</h3>

                        <div class="row">
                            <div class="col-md-4 form-group"> <label>LRN</label>
                                <input type="text" maxlength="13" class="form-control" v-model="request.strLRN" />
                            </div>
                            <div class="col-md-4 form-group"> <label>Voucher</label>
                                <input type="file" accept="image/*" ref="voucher_file" class="form-control"
                                    @change="changeVoucher" />
                            </div>
                        </div>

                        <hr />
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary" v-if="loaded">Confirm Selected Program and
                                Section</button>
                        </div>
                    </form>
                </div>
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
<script src="<?php echo base_url(); ?>assets/themes/default/js/vue-the-mask.min.js"></script>
<script src="<?php echo base_url(); ?>assets/themes/default/js/axios.min.js"></script>

<style scoped="">
.box_mode_payment {
    border: 1px solid #000;
    height: 41px;
    width: 57px;
    margin: 4px;
    cursor: pointer;
}

.box_mode_payment.active {
    background: #1c54a5;
}

.spinner {
    animation-name: spin;
    animation-duration: 1000ms;
    animation-iteration-count: infinite;
    animation-timing-function: linear;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }
}
</style>
<script>
new Vue({
    el: '#registration-container',
    data: {
        id: '<?php echo $id; ?>',
        base_url: '<?php echo base_url(); ?>',
        student: {},
        programs: [],
        loaded: false,
        sections: [],
        section: undefined,
        api_data: {},
        program_text: undefined,
        lrn: "",
        request: {
            intProgramID: undefined,
            preferedSection: undefined,
            id: undefined,
            father: undefined,
            father_email: undefined,
            father_contact: undefined,
            mother: undefined,
            mother_email: undefined,
            mother_contact: undefined,
            guardian: undefined,
            guardian_email: undefined,
            guardian_contact: undefined,
            high_school: undefined,
            high_school_address: undefined,
            high_school_attended: undefined,
            college: 'n/a',
            college_address: undefined,
            college_attended_from: undefined,
            college_attended_to: undefined,
            senior_high: undefined,
            senior_high_address: undefined,
            senior_high_attended: undefined,
            strand: undefined,
            enumGender: undefined,
        },
        payload: {

        },
        show_select: false,
    },
    mounted() {

        let url_string = window.location.href;
        const select = document.getElementById('program');

        axios.get(this.base_url + 'unity/program_confirmation_data/' + this.id + '/')
            .then((data) => {
                this.student = data.data.student;
                this.request.father = this.student.father;
                this.request.father_contact = this.student.father_contact;
                this.request.father_email = this.student.father_email;
                this.request.mother = this.student.mother;
                this.request.mother_contact = this.student.mother_contact;
                this.request.mother_email = this.student.mother_email;
                this.request.guardian = this.student.father;
                this.request.guardian_contact = this.student.guardian_contact;
                this.request.guardian_email = this.student.guardian_email;
                this.request.intProgramID = this.student.intProgramID;
                this.program_text = data.data.selected;
                //console.log(this.program_text);     
                this.programs = data.data.programs;
                this.request.id = this.student.intID;




                if (data.data.sections.length > 0) {
                    this.sections = data.data.sections;
                    this.section = data.data.sections[0];
                    this.request.preferedSection = data.data.sections[0].intID;
                }
                axios.get(api_url + 'admissions/student-info/' + data.data.student.slug)
                    .then((data) => {
                        this.api_data = data.data.data;
                        if (this.api_data.status == "Confirmed")
                            document.location = this.base_url;
                        else
                            this.loaded = true;

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
        changeSection: function() {
            axios.get(this.base_url + 'unity/program_confirmation_section/' + this.request.preferedSection)
                .then((data) => {
                    this.section = data.data.section;
                });
        },

        changeVoucher: function() {
            var file = this.$refs.voucher_file.files[0];
            this.request.voucher = file;
        },

        unmaskedValue: function() {
            var val = this.$refs.input.clean
            console.log(val);
        },
        changeProgram: function(event) {
            this.program_text = event.target[event.target.selectedIndex].text;
            console.log(this.program_text);
            axios.get(this.base_url + 'unity/program_confirmation_sub_data/' + this.request.intProgramID)
                .then((data) => {
                    if (data.data.sections.length > 0) {
                        this.sections = data.data.sections;
                        this.section = data.data.sections[0];
                        this.request.preferedSection = data.data.sections[0].intID;
                    }
                });

        },
        confirmProgram: function() {
            if (this.request.mother == "n/a" && this.request.father == "n/a" && this.request.guardian ==
                "n/a") {
                Swal.fire(
                    'Failed!',
                    "You must have at least one guardian to contact.",
                    'warning'
                )
            } else {
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
                    type_id: this.request.intProgramID,
                    program: this.program_text,
                };

                let formPayload = new FormData();
                formPayload.append("type_id", this.request.intProgramID);
                formPayload.append("program", this.program_text);
                formPayload.append("lrn", this.request.strLRN);
                formPayload.append("voucher", this.request.voucher);


                axios
                    .post(api_url + 'registrar/confirm_selected_program/' + this.student.slug,
                        formPayload, {
                            headers: {
                                Authorization: `Bearer ${window.token}`
                            }
                        })
                    .then(data => {
                        var formdata = new FormData();
                        for (const [key, value] of Object.entries(this.request)) {
                            formdata.append(key, value);
                        }
                        axios
                            .post(this.base_url + 'unity/student_confirm_program', formdata, {
                                headers: {
                                    Authorization: `Bearer ${window.token}`
                                }
                            })
                            .then(data => {
                                Swal.hideLoading();
                                document.location = this.base_url + 'site/awesome/confirm';
                            });

                    });
            }

        }
    }

})
</script>