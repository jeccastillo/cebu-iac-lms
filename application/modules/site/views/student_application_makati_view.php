<div class="custom-container">
    <a href="https://iacademy.edu.ph/"
        class="flex mt-10 items-center gap-x-2 text-[#666666] cursor-pointer">
        <svg xmlns="http://www.w3.org/2000/svg"
            width="8"
            height="15"
            viewBox="0 0 8 15"
            fill="none">
            <path d="M7 1L1 7.5L7 14"
                stroke="#666666"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round" />
        </svg>
        BACK
    </a>
</div>

<div class=" block mx-auto mt-[60px]"
    data-aos="fade-up">
    <h1 class="text-4xl font-[900] text-center color-primary">
        iACADEMY
    </h1>
</div>
<!--  Application Form for {{ term.term_student_type.toUpperCase() }} -->
<div class="custom-container max-w-[1080px]"
    id="adminssions-form"
    style="margin-top:10px;">
    <div class="color-primary text-center">
        <h4 class="font-medium text-2xl mb-5">
            Application Form for
            <strong>(Makati Campus)</strong><br />
        </h4>
        <p>Hello future Game Changers! Kindly fill out your information sheet. If you have any
            questions, feel free
            to email us at <strong><u>admissions@iacademy.edu.ph</u></strong> </p>

        <p style="margin-top:15px;">
            Note: You are applying for iACADEMY Makati Campus, if you want to apply to iACADEMY Cebu
            click
            <a style="text-decoration: underline;"
                href="http://cebu.iacademy.edu.ph/site/student_application">here</a>.
        </p>
    </div>

    <form @submit.prevent="
            customSubmit(
                'submit',
                'Submit Details',
                'form',
                request,
                'admissions/student-info'
            )
        "
        method="post"
        class="">

        <div class="flex flex-wrap md:space-x-5 mb-6 mt-10 justify-center ">
            <div id="select-term"
                class=" px-4 flex-[1_0_188px]">
                <div class="mb-5">
                    <label class="block t color-primary font-bold mb-3 pr-4"
                        for="inline-full-name">
                        Select Term <span class="text-red-500">*</span>
                    </label>

                    <select
                        class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                        type="text"
                        required
                        v-model="request.syid">
                        <option v-for="s in sy"
                            :value="s.intID">
                            {{ s.enumSem+" "+s.term_label+" SY "+s.strYearStart+"-"+s.strYearEnd }}
                        </option>
                    </select>
                </div>
                <div id="applicant-type"
                    class="border-[1px] border-neutral-100 p-2.5 rounded-lg">
                    <label class="block t color-primary font-bold  mb-3  pr-4"
                        for="inline-full-name">
                        Applicant Type
                    </label>
                    <label v-for="type of applicantTypeObj"
                        class="block color-primary mb-1 ml-1.5">
                        <input type="radio"
                            id="one"
                            :value="type.value"
                            class="mr-1">
                        {{type.type}}
                    </label>
                </div>
            </div>
            <div id=applying-for
                class=" flex-[4_1_auto]">
                <div class="md:w-5/5">
                    <label class="block t color-primary font-bold  mb-3  pr-4"
                        for="inline-full-name">
                        Applying for
                    </label>
                    <div class="border-[1px] border-neutral-100 p-2.5 rounded-lg">
                        <div id="first-choice"
                            class="mb-3">
                            <label class="block t color-primary font-bold  mb-3  pr-4"
                                for="inline-full-name">
                                First Choice
                            </label>
                            <select
                                class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                type="text"
                                required
                                v-model="request.syid">
                                <option v-for="s in sy"
                                    :value="s.intID">

                                </option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="block t color-primary font-bold  mb-3  pr-4"
                                for="inline-full-name">
                                Second Choice
                            </label>
                            <select
                                class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                type="text"
                                required
                                v-model="request.syid">
                                <option v-for="s in sy"
                                    :value="s.intID">

                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block t color-primary font-bold  mb-3  pr-4"
                                for="inline-full-name">
                                Third Choice
                            </label>
                            <select
                                class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                type="text"
                                required
                                v-model="request.syid">
                                <option v-for="s in sy"
                                    :value="s.intID">

                                </option>
                            </select>
                        </div>

                    </div>


                </div>
            </div>

        </div>
        <div class=" mb-6 mt-10">
            <h4 class="color-primary font-bold text-xl">BASIC INFORMATION</h4>
            <hr class="mb-5 bg-[#10326f] h-1 w-3/5" />
            <div class="border-[1px] border-neutral-100 rounded-lg mt-5 py-5 px-2.5">
                <div class="flex flex-wrap gap-x-2 gap-y-2 mb-4">
                    <div id="first-name"
                        class="flex-grow">
                        <label class="block color-primary font-bold  mb-3  pr-4">
                            First Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text"
                            required>

                        </input>
                    </div>
                    <div id="middle-name"
                        class="flex-grow">
                        <label class="block t color-primary font-bold  mb-3  pr-4">
                            Middle Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text"
                            required>

                        </input>
                    </div>
                    <div id="last-name"
                        class="flex-grow">
                        <label class="block t color-primary font-bold  mb-3  pr-4">
                            Last Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text"
                            required>

                        </input>
                    </div>
                    <div id="suffix"
                        class="basis-[100px]">
                        <label class="block t color-primary font-bold  mb-3  pr-4">
                            Suffix <span class="text-red-500">*</span>
                        </label>
                        <input
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text"
                            required>

                        </input>
                    </div>
                </div>
                <div class="flex flex-wrap gap-x-2 gap-y-2 mb-4">
                    <div id="date-birth"
                        class="basis-[300px]">
                        <label class="block t color-primary font-bold  mb-3  pr-4">
                            Date of Birth <span class="text-red-500">*</span>
                        </label>
                        <input
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text"
                            required>

                    </div>
                    <div id="place-birth"
                        class="basis-[300px]">
                        <label class="block t color-primary font-bold  mb-3  pr-4">
                            Place of Birth <span class="text-red-500">*</span>
                        </label>
                        <input
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text"
                            required>

                    </div>

                    <div id="gender"
                        class="basis-[100px]">
                        <label class="block t color-primary font-bold  mb-3  pr-4">
                            Gender <span class="text-red-500">*</span>
                        </label>
                        <select
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500">
                            <option disabled
                                value="">--options--</option>
                        </select>
                    </div>
                </div>
                <div class="flex flex-wrap gap-x-2 gap-y-2 ">
                    <div id="citizenship-base"
                        class="basis-[300px]">
                        <label class="block t color-primary font-bold  mb-3  pr-4">
                            Citizenship <span class="text-red-500">*</span>
                        </label>
                        <select
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500">
                            <option disabled
                                value="">--Select options--</option>
                        </select>

                    </div>
                    <div id="citizenship-dual"
                        class="basis-[300px] self-end">

                        <select
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500">
                            <option disabled
                                value="">--Select options--</option>
                        </select>

                    </div>

                    <div id="citizenship-radio"
                        class="self-end">
                        <label class="block color-primary mb-1 ml-1.5">
                            <input type="radio"
                                id="one"
                                class="mr-1">
                            I'm a dual citizen
                        </label>
                    </div>
                </div>
            </div>

        </div>
        <div class=" mb-6 mt-10">
            <h4 class="color-primary font-bold text-xl">CONTACT INFORMATION</h4>
            <hr class="mb-5 bg-[#10326f] h-1 w-3/5" />
            <div class="border-[1px] border-neutral-100  rounded-lg mt-5 py-2.5 pl-2.5 pr-2.5">
                <h5 class="color-primary mb-2.5">CONTACT DETAILS</h5>
                <div
                    class="grid gap-x-16 grid-cols-[repeat(auto-fit,_minmax(0,420px))] gap-y-2 mb-4 ">
                    <div id="email"
                        class="">
                        <label class="block  color-primary font-bold mb-3 pr-4">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <input
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text"
                            required>

                        </input>
                    </div>
                    <div id="email-confirm">
                        <label class="block t color-primary font-bold  mb-3  pr-4">
                            Confirm Email Address <span class="text-red-500">*</span>
                        </label>
                        <input
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text"
                            required>
                        </input>
                    </div>

                </div>
                <div
                    class="grid grid-cols-[repeat(auto-fit,_minmax(0,420px))] gap-x-16 gap-y-2 mb-4">
                    <div id="email"
                        class="">
                        <label class="block  color-primary font-bold mb-3 pr-4">
                            Mobile Number <span class="text-red-500">*</span>
                        </label>

                        <div class="flex gap-x-2.5">
                            <select
                                class="w-1/5 bg-neutral-100 border border-neutral-100 rounded-lg  py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500">
                                <option disabled
                                    value="">--options--</option>
                            </select>
                            <input
                                class="w-4/5 bg-neutral-100 border border-neutral-100 rounded-lg  py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                type="text"
                                required>

                            </input>
                        </div>
                    </div>
                    <div id="email-confirm">
                        <label class="block t color-primary font-bold  mb-3  pr-4">
                            Confirm Mobile Number <span class="text-red-500">*</span>
                        </label>
                        <div class="flex gap-x-2.5">
                            <select
                                class="w-1/5 bg-neutral-100 border border-neutral-100 rounded-lg  py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500">
                                <option disabled
                                    value="">--options--</option>
                            </select>
                            <input
                                class="w-4/5 bg-neutral-100 border border-neutral-100 rounded-lg  py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                type="text"
                                required>

                            </input>
                        </div>
                    </div>

                </div>
            </div>
            <div class="border-[1px] border-neutral-100  rounded-lg mt-5 py-2.5 pl-2.5 pr-2.5">
                <h5 class="color-primary mb-2.5">ADDRESS</h5>
                <div class="grid grid-cols-[repeat(3,_minmax(0,1fr))] gap-2.5 mb-4 ">
                    <div id="home"
                        class="">
                        <label class="block  color-primary font-bold mb-3 pr-4">
                            Home Number/Street/Subdivision <span class="text-red-500">*</span>
                        </label>
                        <input
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text"
                            required>

                        </input>
                    </div>
                    <div id="barangay">
                        <label class="block t color-primary font-bold  mb-3  pr-4">
                            Barangay
                        </label>
                        <input
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text"
                            required>
                        </input>
                    </div>
                    <div id="barangay">
                        <label class="block t color-primary font-bold  mb-3  pr-4">
                            City <span class="text-red-500">*</span>
                        </label>
                        <input
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text"
                            required>
                        </input>
                    </div>


                </div>
                <div class="grid grid-cols-[repeat(3,_minmax(0,1fr))] gap-2.5 mb-4 ">
                    <div id="home"
                        class="">
                        <label class="block  color-primary font-bold mb-3 pr-4">
                            State/Province <span class="text-red-500">*</span>
                        </label>
                        <input
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text"
                            required>

                        </input>
                    </div>

                    <div id="barangay">
                        <label class="block t color-primary font-bold  mb-3  pr-4">
                            Country <span class="text-red-500">*</span>
                        </label>
                        <input
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text"
                            required>
                        </input>
                    </div>


                </div>

            </div>

        </div>


        <div class="text-center color-primary mt-[50px]"
            v-if="false">
            iACADEMY shall retain in confidence all confidential information concerning and
            involving every
            student and the school.
            <a href=" https://iacademy.edu.ph/privacypolicy.htm"
                target="_blank"
                class="underline font-bold">
                https://iacademy.edu.ph/privacypolicy.htm</a>

            <div class="mt-4">
                <input type="checkbox"
                    required
                    id="agreement"> <label for="agreement"
                    class="italic">I have read and
                    I
                    agree to the
                    said
                    policy.</label>
            </div>
        </div>

        <hr class="my-5 bg-gray-400 h-[3px]" />


        <div class=" text-right"
            v-if="false">
            <div v-if="loading_spinner"
                class="lds-ring">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
            <div v-else>
                <button type="submit">
                    <img src="<?php echo $img_dir; ?>admissions/form/Asset 10.png">
                </button>

                <button type="button">
                    <img src="<?php echo $img_dir; ?>admissions/form/Asset 9.png">
                </button>
            </div>
        </div>

    </form>
</div>
<!-- Start of HubSpot Embed Code -->
<!-- <script type="text/javascript"
    id="hs-script-loader"
    async
    defer
    src="//js.hs-scripts.com/45758391.js"></script> -->
<!-- End of HubSpot Embed Code -->



<script>
new Vue({
    el: "#adminssions-form",
    data: {
        syid: <?php echo $current_term; ?>,
        request: {
            type_id: "",
            date_of_birth: "",
            program: "",
            health_concerns: [],
            campus: "Makati",
            citizenship: 'Philippines',
            syid: undefined,
            student_type: '',
            type_id2: "",
            type_id3: ""
        },
        term: undefined,
        loading_spinner: false,
        programs: [],
        sy: [],
        filtered_programs: [],
        programs_group: [],
        types: [],
        base_url: "<?php echo base_url(); ?>",
        applicantTypeObj: [{
                type: 'College - Freshmen',
                value: 'freshmen'
            },
            {
                type: 'College - Transferee',
                value: 'freshmen'
            },
            {
                type: 'College - Second Degree',
                value: 'freshmen'
            },
            {
                type: 'College - iAcademy SHS Graduate',
                value: 'freshmen'
            }
        ]
    },
    mounted() {

        axios
            .get(this.base_url + 'site/view_active_programs_makati/' + this.syid, {
                headers: {
                    Authorization: `Bearer ${window.token}`
                },
            })

            .then((data) => {

                this.programs = data.data.data;
                this.sy = data.data.sy;
                this.term = data.data.term;
            })
            .catch((e) => {
                console.log("error");
            });

        axios
            .get(api_url + 'admissions/student-info/types', {
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
                            this.request.type_id = e.currentTarget
                                .value;
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

                document.querySelector('#course_first_choice').onchange = (e) => {
                    this.request.program = e.target.selectedOptions[0].getAttribute(
                        'data-title');
                };
                document.querySelector('#course_second_choice').onchange = (e) => {
                    this.request.program2 = e.target.selectedOptions[0]
                        .getAttribute('data-title');
                };
                document.querySelector('#course_third_choice').onchange = (e) => {
                    this.request.program3 = e.target.selectedOptions[0]
                        .getAttribute('data-title');
                };
            })
            .catch((e) => {
                console.log("error");
            });






    },

    methods: {
        submitForm: function() {
            //console.log(this.request);
        },
        unmaskedValue: function() {
            var val = this.$refs.input.clean
            console.log(val);
        },

        filterProgram: function(type, title) {
            var group = _.filter(this.programs, function(o) {
                return o.type == type;
            });
            var others = _.filter(this.programs, function(o) {
                return o.type == "others";
            });
            this.programs_group = _.concat(group, others);
            this.request.program = title;

            setTimeout(() => {
                $(".admissions_submission_pg").on("click", e => {
                    $(".admissions_submission_pg")
                        .not(e.currentTarget)
                        .prop("checked", false);
                    if ($(e.currentTarget).is(":checked")) {
                        this.request.program_id = e.currentTarget.value;
                        $(".admissions_submission_pg").removeAttr(
                            "required");

                    } else {
                        $(".admissions_submission_pg").attr("required",
                            true);
                    }
                });
            }, 500);
        },

        filterCourses: function(type) {
            if (type === 'shs')
                this.filtered_programs = this.programs.shs;
            else if (type === 'college')
                this.filtered_programs = this.programs.college;
            else if (type === 'drive')
                this.filtered_programs = this.programs.drive;
            else {
                this.filtered_programs = this.programs.sd;
            }

            this.request.type = type;
        },

        customSubmit: function(type, title, text, data, url, redirect) {

            Swal.fire({
                title: 'iACADEMY MAKATI CAMPUS',
                html: `
                You are applying for iACADEMY MAKATI Campus. Click <a style='color:#000099' href='https://cebu.iacademy.edu.ph'>here</a> if you are applying for iACADEMY Cebu Campus
            `,
                showCancelButton: true,
                confirmButtonText: "Submit Application",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                preConfirm: (login) => {
                    this.loading_spinner = true;
                    if (this.request.mobile_number.length < 18) {
                        this.loading_spinner = false;
                        Swal.fire(
                            'Failed!',
                            "Please fill in mobile number",
                            'warning'
                        )
                    } else {
                        if (this.request.health_concerns.includes(
                                "Others")) {
                            const hasOther = this.request.health_concerns
                                .indexOf("Others");
                            this.request.health_concerns.splice(
                                hasOther,
                                1,
                                this.request.health_concern_other
                            );
                        }


                        this.request.health_concern = this.request
                            .health_concerns.join(
                                ", "
                            );

                        axios
                            .post(api_url + url, data, {
                                headers: {
                                    Authorization: `Bearer ${window.token}`
                                }
                            })
                            .then(data => {
                                this.is_done = true;

                                if (data.data.success) {

                                    this.loading_spinner = false;
                                    var ret = data.data.data;

                                    Swal.fire({
                                        title: "SUCCESS",
                                        text: data.data.message,
                                        icon: "success"
                                    }).then(function() {
                                        location.href =
                                            "<?php echo base_url(); ?>site/initial_requirements/" +
                                            ret
                                            .slug;
                                    });

                                } else {
                                    this.loading_spinner = false;
                                    Swal.fire(
                                        'Failed!',
                                        data.data.message,
                                        'error'
                                    )
                                }
                            });
                    }
                }
            })
        },
    },
});
</script>


<style>
@import url("https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap");

* {
    font-family: "Roboto", sans-serif;
}
</style>