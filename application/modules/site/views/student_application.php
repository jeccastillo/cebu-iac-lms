
    <section id="hero" class="section section_port relative">
       <div class="custom-container md:h-[500px] relative z-1">
           <!-- parallax object? -->
           <img src="<?php echo $img_dir; ?>home-poly/blue-poly.png"
               class="absolute top-0 md:right-[25%] hidden md:block" alt="" data-scroll-speed="4" data-aos="zoom-in" />

           <img src="<?php echo $img_dir; ?>home-poly/yellow-poly.png"
               class="absolute top-[10%] md:left-[17%] hidden md:block" alt="" data-scroll-speed="4"
               data-aos="zoom-in" />
           <img src="<?php echo $img_dir; ?>home-poly/red-poly.png"
               class="absolute top-[30%] md:left-[0%] hidden md:block" alt="" data-scroll-speed="4"
               data-aos="zoom-in" />

           <img src="<?php echo $img_dir; ?>home-poly/peach-poly.png"
               class="absolute top-[25%] md:left-[33%] hidden md:block" alt="" data-scroll-speed="4"
               data-aos="zoom-in" />

           <img src="<?php echo $img_dir; ?>home-poly/lyellow-poly.png"
               class="absolute top-[50%] md:right-[0%] hidden md:block" alt="" data-scroll-speed="4"
               data-aos="zoom-in" />

           <img src="<?php echo $img_dir; ?>home-poly/lblue-poly.png"
               class="absolute top-[20%] md:right-[10%] hidden md:block" alt="" data-scroll-speed="4"
               data-aos="zoom-in" />

           <!-- parallax object end -->
           <div class="custom-container relative h-full mb-[100px] md:mb-[10px]">
               <div class="md:flex mt-[100px] md:mt-0 h-full items-center justify-center">
                   <div class="md:w-12/12 py-3">

                       <div class=" block mx-auto mt-[60px]" data-aos="fade-up">
                           <h1 class="text-4xl font-[900] text-center color-primary">
                               iACADEMY
                           </h1>
                           <h1 class="text-4xl uppercase text-center color-primary">
                               School management system
                           </h1>
                       </div>
                   </div>
               </div>
           </div>
       </div>
   </section>

   <div class="custom-container" id="adminssions-form" style="margin-top:10px;">        
       <div class="color-primary">
           <h4 class="font-medium text-2xl mb-5">
               Student Information Sheet</h4>
           <p>Hello future Game Changers! Kindly fill out your information sheet. If you have any questions, feel free
               to email us at <strong><u>admissionscebu@iacademy.edu.ph</u></strong> </p>
       </div>

       <form @submit.prevent="
            customSubmit(
                'submit',
                'Submit Details',
                'form',
                request,
                'admissions/student-info'
            )
        " method="post">

           <div class="flex md:space-x-5 mb-6 mt-10">
               <div class="md:w-1/2 w-full">
                   <div class="mb-6">
                       <div class="md:w-4/5">
                           <label class="block t color-primary font-bold  mb-3  pr-4" for="inline-full-name">
                               Email <span class="text-red-500">*</span>
                           </label>
                           <input
                               class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                               type="email" required v-model="request.email">
                       </div>
                   </div>

                   <div class="mb-6">
                       <div class="md:w-4/5">
                           <label class="block t color-primary font-bold  mb-3  pr-4" for="inline-full-name">
                               Re-type Email <span class="text-red-500">*</span>
                           </label>
                           <input
                               class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                               type="email" required v-model="request.email_confirmation">
                       </div>
                   </div>

                   <div class="mb-6">
                       <div class="md:w-4/5">
                           <label class="block t color-primary font-bold  mb-3  pr-4" for="inline-full-name">
                               First Name <span class="text-red-500">*</span>
                           </label>
                           <input
                               class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                               type="text" required v-model="request.first_name">
                       </div>
                   </div>

                   <div class="mb-6">
                       <div class="md:w-4/5">
                           <label class="block t color-primary font-bold  mb-3  pr-4" for="inline-full-name">
                               Middle Name
                           </label>
                           <input
                               class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                               type="text" v-model="request.middle_name">
                       </div>
                   </div>

                   <div class="mb-6">
                       <div class="md:w-4/5">
                           <label class="block t color-primary font-bold  mb-3  pr-4" for="inline-full-name">
                               Last Name <span class="text-red-500">*</span>
                           </label>
                           <input
                               class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                               type="text" required v-model="request.last_name">
                       </div>
                   </div>

                   <div class="mb-6">
                       <div class="md:w-4/5">
                           <label class="block t color-primary font-bold  mb-3  pr-4" for="inline-full-name">
                               Mobile Number <span class="text-red-500">*</span>
                           </label>
                           <the-mask
                           class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                           :mask="['(+63) ###-###-####']" type="text" v-model="request.mobile_number" required masked="true" placeholder="(+63) XXX-XXX-XXXX"></the-mask>
                           <!-- <input
                               class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                               type="number" required v-model="request.mobile_number"> -->
                       </div>
                   </div>

                   <div class="mb-6">
                       <div class="md:w-4/5">
                           <label class="block t color-primary font-bold  mb-3  pr-4" for="inline-full-name">
                               Telephone Number
                           </label>
                           <the-mask
                           class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                           :mask="['(+63) ###-####']" type="text" v-model="request.tel_number" masked="true" placeholder="(+63) XXX-XXXX"></the-mask>
                           <!-- <input
                               class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                               type="number" v-model="request.tel_number"> -->
                       </div>
                   </div>

                   <div class="mb-6">
                       <div class="md:w-4/5">
                           <label class="block t color-primary font-bold  mb-3  pr-4" for="inline-full-name">
                               Address <span class="text-red-500">*</span>
                           </label>
                           <textarea required
                               class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                               name="" rows="4" v-model="request.address">></textarea>

                       </div>
                   </div>


                   <div class="mb-6">
                       <div class="md:w-4/5">
                           <label class="block t color-primary font-bold  mb-3  pr-4" for="inline-full-name">
                               Birthday <span class="text-red-500">*</span>
                           </label>
                           <input
                               class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                               type="date" required v-model="request.date_of_birth">
                       </div>
                   </div>

                   <div class="form-group mb-6">
                        <label for="">
                            Country of citizenship
                            <span class="text-danger">*</span>
                        </label>

                        <div>
                            <input
                                type="radio"
                                required
                                name="citizenship"
                                v-model="request.citizenship"
                                value="Philippines"
                            />
                            Philippines
                        </div>

                        <div>
                            <input
                                type="radio"
                                required
                                name="citizenship"
                                value="Foreign"
                                v-model="request.citizenship"
                            />
                            Foreign
                        </div>
                    </div>


                    <div class="form-group mb-6">
                        <label for=""
                            >Do you hold good moral standing in your
                            previous school?
                            <span class="text-danger">*</span>
                        </label>

                        <div>
                            <input
                                type="radio"
                                required
                                name="good_moral"
                                v-model="request.good_moral"
                                value="Yes"
                            />
                            Yes
                        </div>

                        <div>
                            <input
                                type="radio"
                                required
                                name="good_moral"
                                value="No"
                                v-model="request.good_moral"
                            />
                            No
                        </div>
                    </div>

                    <div class="form-group  mb-6">
                        <label for=""
                            >Have you been involved of any illegal
                            activities?
                            <span class="text-danger">*</span>
                        </label>

                        <div>
                            <input
                                type="radio"
                                required
                                name="crime"
                                v-model="request.crime"
                                value="Yes"
                            />
                            Yes
                        </div>

                        <div>
                            <input
                                type="radio"
                                required
                                name="crime"
                                value="No"
                                v-model="request.crime"
                            />
                            No
                        </div>
                    </div>      
                    <div class="mb-5">
                        <hr />
                        <div class="form-group">
                            <h4 class="mb-3">
                                Health Conditions
                                <span class="text-danger"></span>
                            </h4>

                            <label for=""
                                >Have you been hospitalized before?
                                <span class="text-danger">*</span>
                            </label>

                            <div>
                                <input
                                    type="radio"
                                    required
                                    name="hospitalized"
                                    v-model="request.hospitalized"
                                    value="Yes"
                                />
                                Yes
                            </div>

                            <div>
                                <input
                                    type="radio"
                                    required
                                    name="hospitalized"
                                    value="No"
                                    v-model="request.hospitalized"
                                />
                                No
                            </div>
                        </div>
                        <div
                            class="form-group"
                            v-if="request.hospitalized == 'Yes'"
                        >
                            <label for=""
                                >Reason <span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                required
                                class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                v-model="request.hospitalized_reason"
                            />
                        </div>

                        <div class="form-group">
                            <label for=""
                                >Do you have any of the following? (check
                                all that apply)                                
                            </label>

                            <div>
                                <input
                                    type="checkbox"
                                    name="health_concern"
                                    v-model="request.health_concerns"
                                    value="Diabetes"                                  
                                />
                                Diabetes
                            </div>

                            <div>
                                <input
                                    type="checkbox"
                                    name="health_concern"
                                    value="Allergies"
                                    v-model="request.health_concerns"
                                />
                                Allergies
                            </div>

                            <div>
                                <input
                                    type="checkbox"
                                    name="health_concern"
                                    value="High Blood"
                                    v-model="request.health_concerns"
                                />
                                High Blood
                            </div>
                            <div>
                                <input
                                    type="checkbox"
                                    name="health_concern"
                                    value="Anemia"
                                    v-model="request.health_concerns"
                                />
                                Anemia
                            </div>
                            <div>
                                <input
                                    type="checkbox"
                                    name="health_concern"
                                    value="Others"
                                    v-model="request.health_concerns"
                                />
                                Others (please specify)
                            </div>
                            <div
                                v-if="
                                    request.health_concerns.includes(
                                        'Others'
                                    )
                                "
                            >
                                <input                                    
                                    type="text"
                                    class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                    required
                                    value=""
                                    v-model="request.health_concern_other"
                                />
                            </div>
                        </div>

                        <div class="form-group">
                            <label for=""
                                >Other health concerns/conditions the school
                                should know about
                                <span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                required
                                class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                v-model="request.other_health_concern"
                            />
                        </div>
                    </div>
                    <div class="mb-6">
                       <div class="md:w-5/5">
                           <label class="block t color-primary font-bold  mb-3  pr-4" for="inline-full-name">
                               Applying For <span class="text-red-500">*</span>
                           </label>
                           <div class="d-flex align-items-center" v-for="t in programs" :key="t.id">
                               <input type="checkbox" class="mr-2 admissions_submission_cb" :id="'progId-' + t.id"
                                   @click="filterProgram(t.type,t.title)" name="" :value="t.id" required />
                               <label :for="'progId-' + t.id"> {{ t.title }} {{ t.strMajor != "None" ? "with Major in " + t.strMajor: '' }}</label>
                           </div>
                       </div>
                   </div>

                   <!-- <div class="mb-6 hidden">
                       <div class="md:w-4/5">
                           <label class="block t color-primary font-bold  mb-3  pr-4" for="inline-full-name">
                               Applying For <span class="text-red-500">*</span>
                           </label>
                           <div class="d-flex align-items-center">
                               <input type="checkbox" class="mr-2 admissions_submission_cb" name="" value="1"
                                   required />
                               Software Engineering
                           </div>
                           <div class="d-flex align-items-center">
                               <input type="checkbox" class="mr-2 admissions_submission_cb" name="" value="1"
                                   required />
                               Game Development
                           </div>
                           <div class="d-flex align-items-center">
                               <input type="checkbox" class="mr-2 admissions_submission_cb" name="" value="1"
                                   required />
                               Animation
                           </div>
                           <div class="d-flex align-items-center">
                               <input type="checkbox" class="mr-2 admissions_submission_cb" name="" value="1"
                                   required />
                               Multimedia Arts and Design
                           </div>
                       </div>
                   </div> -->

                   <div class="mb-6">
                       <div class="md:w-4/5">
                           <label class="block t color-primary font-bold  mb-3  pr-4" for="inline-full-name">
                               Previous School <span class="text-red-500">*</span>
                           </label>
                           <input
                               class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                               type="text" required v-model="request.school">
                       </div>
                   </div>
               </div>
               <div class="md:w-1/2 md:block hidden">
                   <div class="relative">
                       <div class="md:flex md:space-x-4 items-center">
                           <div class="md:w-1/2">
                               <img src="<?php echo $img_dir; ?>admissions/form/rocket.png" class="max-w-full h-auto">
                           </div>
                           <div class="md:w-1/2">
                               <img src="<?php echo $img_dir; ?>admissions/form/blue.png" class="max-w-full h-auto">
                           </div>
                       </div>
                   </div>

                   <div class="relative mt-[70px]">
                       <div class="md:flex md:space-x-4 items-center">
                           <div class="md:w-1/2">
                               <img src="<?php echo $img_dir; ?>admissions/form/red.png" class="max-w-full h-auto">
                           </div>
                           <div class="md:w-1/2">
                               <img src="<?php echo $img_dir; ?>admissions/form/tablet.png" class="max-w-full h-auto">
                           </div>
                       </div>
                   </div>

                   <div class="relative mt-[70px]">
                       <div class="md:flex md:space-x-4 items-center">
                           <div class="md:w-1/2">
                               <img src="<?php echo $img_dir; ?>admissions/form/gamepad.png" class="max-w-full h-auto">
                           </div>
                           <div class="md:w-1/2">
                               <img src="<?php echo $img_dir; ?>admissions/form/yel.png"
                                   class="max-w-full h-auto mx-auto">
                           </div>
                       </div>
                   </div>

                   <div class="relative mt-[70px]">
                       <div class="md:flex md:space-x-4 items-center">
                           <div class="md:w-1/2">
                               <img src="<?php echo $img_dir; ?>admissions/form/peach.png" class="max-w-full h-auto">
                           </div>
                           <div class="md:w-1/2 mt-5">
                               <img src="<?php echo $img_dir; ?>admissions/form/cam.png"
                                   class="max-w-full h-auto mx-auto">
                           </div>
                       </div>
                   </div>

                   <div class="relative mt-[70px]">
                       <div class="md:flex md:space-x-4 items-center">
                           <div class="md:w-1/2">
                               <img src="<?php echo $img_dir; ?>admissions/form/laptop.png" class="max-w-full h-auto">
                           </div>
                           <div class="md:w-1/2 mt-5">
                               <img src="<?php echo $img_dir; ?>admissions/form/dblue.png"
                                   class="max-w-full h-auto mx-auto">
                           </div>
                       </div>
                   </div>

               </div>
           </div>


           <div class="text-center color-primary mt-[50px]">
               iACADEMY shall retain in confidence all confidential information concerning and involving every
               student and the school.
               <a href=" https://iacademy.edu.ph/privacypolicy.htm" target="_blank" class="underline font-bold">
                   https://iacademy.edu.ph/privacypolicy.htm</a>

               <div class="mt-4">
                   <input type="checkbox" required id="agreement"> <label for="agreement" class="italic">I have read and
                       I
                       agree to the
                       said
                       policy.</label>
               </div>
           </div>

           <hr class="my-5 bg-gray-400 h-[3px]" />


           <div class=" text-right">
                <div v-if="loading_spinner" class="lds-ring"><div></div><div></div><div></div><div></div></div>
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
   <style>

   </style>



   <script>
new Vue({
    el: "#adminssions-form",
    data: {
        request: {
            type_id: "",
            date_of_birth: "",
            program: "",
            health_concerns: [],
            syid: "<?php echo $current_term; ?>",
        },
        loading_spinner: false,
        programs: [],
        programs_group: [],
        types: [],
        base_url: "<?php echo base_url(); ?>",
    },
    mounted() {

        axios
            .get(this.base_url + 'program/view_active_programs', {
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
            //console.log(this.request);
        },
        unmaskedValue: function(){
            var val = this.$refs.input.clean
            console.log(val);
        },

        filterProgram: function(type,title) {
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
                        $(".admissions_submission_pg").removeAttr("required");

                    } else {
                        $(".admissions_submission_pg").attr("required", true);
                    }
                });
            }, 500);
        },

        customSubmit: function(type, title, text, data, url, redirect) {

            this.loading_spinner = true;
            if(this.request.mobile_number.length < 18){
                this.loading_spinner = false;
                Swal.fire(
                    'Failed!',
                    "Please fill in mobile number",
                    'warning'
                )
            }
            else{
                if (this.request.health_concerns.includes("Others")) {
                    const hasOther = this.request.health_concerns.indexOf("Others");
                    this.request.health_concerns.splice(
                        hasOther,
                        1,
                        this.request.health_concern_other
                    );
                }


                this.request.health_concern = this.request.health_concerns.join(
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
                                location.href = "<?php echo base_url(); ?>site/initial_requirements/" + ret.slug;
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
        },
    },
});
   </script>