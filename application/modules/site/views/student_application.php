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
               to email us at <strong><u>admissions@iacademy.edu.ph</u></strong> </p>
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
                               type="text" required v-model="request.email">
                       </div>
                   </div>

                   <div class="mb-6">
                       <div class="md:w-4/5">
                           <label class="block t color-primary font-bold  mb-3  pr-4" for="inline-full-name">
                               Re-type Email <span class="text-red-500">*</span>
                           </label>
                           <input
                               class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                               type="text" required v-model="request.email_confirmation">
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
                           <input
                               class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                               type="number" required v-model="request.number">
                       </div>
                   </div>

                   <div class="mb-6">
                       <div class="md:w-4/5">
                           <label class="block t color-primary font-bold  mb-3  pr-4" for="inline-full-name">
                               Telephone Number
                           </label>
                           <input
                               class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                               type="number" v-model="request.telephone_number">
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
                               type="date" required v-model="request.birthdate">
                       </div>
                   </div>

                   <div class="mb-6">
                       <div class="md:w-4/5">
                           <label class="block t color-primary font-bold  mb-3  pr-4" for="inline-full-name">
                               Applying For <span class="text-red-500">*</span>
                           </label>
                           <div class="d-flex align-items-center" v-for="t in types" :key="t.id">
                               <input type="checkbox" class="mr-2 admissions_submission_cb"
                                   @click="filterProgram(t.type)" name="" :value="t.id" required />
                               {{ t.title }}
                           </div>
                       </div>
                   </div>

                   <div class="mb-6">
                       <div class="md:w-4/5">
                           <label class="block t color-primary font-bold  mb-3  pr-4" for="inline-full-name">
                               Previous School <span class="text-red-500">*</span>
                           </label>
                           <input
                               class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                               type="text" required v-model="request.previous_school">
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
               <button type="submit">
                   <img src="<?php echo $img_dir; ?>admissions/form/Asset 10.png">
               </button>

               <button type="button">
                   <img src="<?php echo $img_dir; ?>admissions/form/Asset 9.png">
               </button>
           </div>

       </form>
   </div>

   <style>

   </style>

   <script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
   <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.6.2/sweetalert2.min.css"
       integrity="sha512-5aabpGaXyIfdaHgByM7ZCtgSoqg51OAt8XWR2FHr/wZpTCea7ByokXbMX2WSvosioKvCfAGDQLlGDzuU6Nm37Q=="
       crossorigin="anonymous" referrerpolicy="no-referrer" />
   <script src="https://cdn.jsdelivr.net/npm/vue@2.6.12"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
       integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
       crossorigin="anonymous" referrerpolicy="no-referrer"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>

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