   <section id="adminssions-form" class="section section_port relative">
       <div class="custom-container  relative z-1">
           <!-- <img src="<?php echo $img_dir; ?>home-poly/blue-poly.png"
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
               data-aos="zoom-in" /> -->

           <form @submit.prevent="submitPost"
               class="custom-container relative h-full pt-[200px] mb-[100px] md:mb-[10px]">

               <div class="md:flex  md:mt-0 h-full items-center justify-center">
                   <div class="md:w-12/12 py-3">
                       <p class="max-w-[800px] color-primary mt-[60px]  text-2xl">
                            <span class="font-bold"> Great! </span> Next, is to upload your initial requirements:
                            <br />
                            <span class="small">
                                PS: We also sent you an <span class="font-bold">email</span> with the link to this page if you want to continue later.
                            </span>
                       </p>

                        <div class="md:flex md:space-x-10 md:items-center justify-between my-[90px]"
                           v-if="request.email && request.citizenship == 'Philippines'">                            
                                <div class="md-w-1/3">
                                    <img src="<?php echo $img_dir; ?>admissions/form/2x2.png"
                                        class="max-w-full h-auto mx-auto block">

                                    <div class="w-[200px] my-3 block mx-auto">
                                        <input ref="file_2x2" @change="uploadReq('2x2')"
                                            class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                            type="file" required>
                                    </div>
                                </div>
                                <div class="md-w-1/3">
                                    <img src="<?php echo $img_dir; ?>admissions/form/nso.png"
                                        class="max-w-full h-auto mx-auto block">

                                    <div class="w-[200px] my-3 block mx-auto">
                                        <input ref="file_nso" @change="uploadReq('psa')"
                                            class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                            type="file" required>
                                    </div>
                                </div>

                                <div class="md-w-1/3">
                                    <img src="<?php echo $img_dir; ?>admissions/form/id.png"
                                        class="max-w-full h-auto mx-auto block">

                                    <div class="w-[200px] my-3 block mx-auto">
                                        <input ref="file_id" @change="uploadReq('school_id')"
                                            class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                            type="file" required>
                                    </div>

                                </div>
                        </div>
                        <div class="md:flex md:space-x-10 md:items-center justify-between my-[90px]"
                           v-if="request.email && request.citizenship == 'Foreign'">
                                <div class="md-w-1/4">
                                    <img src="<?php echo $img_dir; ?>admissions/form/upload1.png"
                                        class="max-w-full h-auto mx-auto block" title='Photocopy of the valid (unexpired) passport pages bearing the bio-page, 
                                        the latest admission/arrival in the Philippines with "valid authorized stay" date and the Bureau of Quarantine (BOQ) stamp.
                                        Note: Present the original passport for verification'>                                        
                                    <input ref="file_passport" @change="uploadReq('passport')"
                                        class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                        type="file" required>
                                </div>
                                
                                <div class="md-w-1/4">
                                    <img src="<?php echo $img_dir; ?>admissions/form/upload2.png"
                                        class="max-w-full h-auto mx-auto block" title='Copy of Alien Certificate of Registration (i-CARD) if any'>                                    
                                    <input ref="file_icard" @change="uploadReq('icard')"
                                        class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                        type="file">
                                </div>                                                        
                                <div class="md-w-1/4">
                                    <img src="<?php echo $img_dir; ?>admissions/form/upload3.png"
                                        class="max-w-full h-auto mx-auto block" title='Quarantine Medical Examination by the Bureau of Quarantine (BOQ)'>                                    
                                    <input ref="file_quarantine" @change="uploadReq('quarantine_med_exam')"
                                        class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                        type="file" required>
                                </div>
                                <div class="md-w-1/4">
                                    <img src="<?php echo $img_dir; ?>admissions/form/upload4.png"
                                        class="max-w-full h-auto mx-auto block" title='Copy of Birth Certificate.'>
                                    <input ref="file_birthcert" @change="uploadReq('birthcert')"
                                        class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                        type="file" required>
                                </div>                                                  
                        </div>
                        <div class="md:flex md:space-x-10 md:items-center justify-between my-[90px]"
                           v-if="request.email && request.citizenship == 'Foreign'">                                
                                <div class="md-w-1/4">
                                    <img src="<?php echo $img_dir; ?>admissions/form/upload5.png"
                                        class="max-w-full h-auto mx-auto block" title='Original copy of Scholastic Records'>                                    
                                    <input ref="file_schrecords" @change="uploadReq('schrecords')"
                                        class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                        type="file">
                                </div>              
                                <div class="md-w-1/4">
                                    <img src="<?php echo $img_dir; ?>admissions/form/upload6.png"
                                        class="max-w-full h-auto mx-auto block" title='Recommendation letter from the Principal/Guidance Counselor/Class Adviser'>                                    
                                    <input ref="file_recommendation" @change="uploadReq('recommendation')"
                                        class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                        type="file" required>
                                </div>
                                <div class="md-w-1/4">
                                    <img src="<?php echo $img_dir; ?>admissions/form/upload7.png"
                                        class="max-w-full h-auto mx-auto block" title="Proof of adequate financial support to cover expenses for the student's accommodation and subsistence, as well as school dues and other incidental expenses (to be notarized by a lawyer in the country of residence) and to be authenticated by the Philippine Embassy.">                                    
                                    <input ref="file_financial_support" @change="uploadReq('financial_support')"
                                        class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                        type="file">
                                </div>                                
                                <div class="md-w-1/4">
                                    <img src="<?php echo $img_dir; ?>admissions/form/upload8.png"
                                        class="max-w-full h-auto mx-auto block" title='2x2 ID picture (white background with name tag below)'>                                    
                                    <input ref="file_2x2_foreign" @change="uploadReq('2x2_foreign')"
                                        class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                        type="file" required>
                                </div>
                            </span>
                       </div>
                   </div>
               </div>


               <div class="text-center" v-if="request.email">
                    <div v-if="loading_spinner" class="lds-ring"><div></div><div></div><div></div><div></div></div> 
                    <div v-else>
                        <button  type="submit"> <img src="<?php echo $img_dir; ?>admissions/form/proceed_payment.png"
                            class="max-w-full h-auto mx-auto block img-btn"></button>
                    </div>
               </div>


           </form>
       </div>
   </section>

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
        loading_spinner: false,
        types: [],
        uploads: {
            requirements: [
                {
                    "file_id": ""
                },
                {
                    "file_id": ""
                },
                {
                    "file_id": ""
                },
            ]
        },
        slug: '<?php echo $this->uri->segment('3'); ?>'
    },
    mounted() {

        axios.get(api_url + 'admissions/student-info/' + this.slug)
            .then((data) => {
                this.request = data.data.data;
                if(this.request.citizenship != "Philippines")
                    this.uploads.requirements = [
                        {
                            "file_id": ""
                        },
                        {
                            "file_id": ""
                        },
                        {
                            "file_id": ""
                        },
                        {
                            "file_id": ""
                        },
                        {
                            "file_id": ""
                        },
                        {
                            "file_id": ""
                        },
                        {
                            "file_id": ""
                        },
                        {
                            "file_id": ""
                        },
                    ]
            })
            .catch((error) => {
                console.log(error);
            })

    },

    methods: {


        submitPost: function() {

            this.loading_spinner = true;

            // Swal.fire({
            //     title: "Submit Requirements",
            //     text: "Are you sure you want to submit?",
            //     showCancelButton: true,
            //     confirmButtonText: "Yes",
            //     imageWidth: 100,
            //     icon: "question",
            //     cancelButtonText: "No, cancel!",
            //     showCloseButton: true,
            //     showLoaderOnConfirm: true,
            //     preConfirm: (login) => {
            //         return 
            //     },
            //     allowOutsideClick: () => !Swal.isLoading()
            // }).then((result) => {
            //     if (result.isConfirmed) {}
            // })

            axios
                .post(api_url + 'admissions/student-info/requirements', this.uploads, {
                    headers: {
                        Authorization: `Bearer ${window.token}`
                    }
                })
                .then(data => {
                    this.is_done = true;

                    if (data.data.success) {
                        this.loading_spinner = false;
                        Swal.fire({
                            title: "Success!",
                            text: data.data.message,
                            icon: "success"
                        }).then(d => {
                            window.location =
                                "<?php echo base_url();?>site/admissions_student_payment/" +
                                this.slug;
                        });

                    } else {
                        this.loading_spinner = false;
                        Swal.fire(
                            'Failed!',
                            data.data.message,
                            'error'
                        )
                        // window.location =
                        // "<?php echo base_url();?>site/admissions_student_payment/" +
                        // this.slug;
                    }
                });

        },

        uploadReq: function(type) {

            let formDataUp = "";
            formDataUp = new FormData();

            let file = '';

            if (type == 'school_id') {
                file = this.$refs.file_id.files[0];
            } else if (type == 'psa') {
                file = this.$refs.file_nso.files[0];
            } else if (type == '2x2') {
                file = this.$refs.file_2x2.files[0];
            } else if (type == 'passport') {
                file = this.$refs.file_passport.files[0];
            } else if (type == 'icard') {
                file = this.$refs.file_icard.files[0];
            } else if (type == 'quarantine_med_exam') {
                file = this.$refs.file_quarantine.files[0];
            } else if (type == 'birthcert') {
                file = this.$refs.file_birthcert.files[0];
            } else if (type == 'schrecords') {
                file = this.$refs.file_schrecords.files[0];
            } else if (type == 'recommendation') {
                file = this.$refs.file_recommendation.files[0];
            } else if (type == 'financial_support') {
                file = this.$refs.file_financial_support.files[0];
            } else if (type == '2x2_foreign') {
                file = this.$refs.file_2x2_foreign.files[0];                
            }else {
                file = '';
            }

            formDataUp.append("file", file);
            formDataUp.append("type", type);
            formDataUp.append("slug", this.slug);

            console.log(formDataUp);

            axios
                .post(api_url + 'admissions/student-info/upload',
                    formDataUp, {
                        headers: {
                            Authorization: `Bearer ${window.token}`
                        }
                    })
                .then(data => {
                    if (data.data.success) {
                        // this.successMessageApi(data.data.message);
                        // location.reload();
                        Swal.fire(
                            'Success!',
                            data.data.message,
                            'success'
                        )
                        if(this.request.citizenship == "Philippines"){
                            if (type == 'school_id')
                                this.uploads.requirements[0].file_id = data.data.data.id;
                            if (type == 'psa')
                                this.uploads.requirements[1].file_id = data.data.data.id;
                            if (type == '2x2')
                                this.uploads.requirements[2].file_id = data.data.data.id;
                        }
                        else{
                            if (type == 'passport')
                                this.uploads.requirements[0].file_id = data.data.data.id;
                            if (type == 'icard')
                                this.uploads.requirements[1].file_id = data.data.data.id;
                            if (type == 'quarantine_med_exam')
                                this.uploads.requirements[2].file_id = data.data.data.id;
                            if (type == 'birthcert')
                                this.uploads.requirements[3].file_id = data.data.data.id;
                            if (type == 'schrecords')
                                this.uploads.requirements[4].file_id = data.data.data.id;
                            if (type == 'recommendation')
                                this.uploads.requirements[5].file_id = data.data.data.id;                            
                            if (type == 'financial_support')
                                this.uploads.requirements[6].file_id = data.data.data.id;
                            if (type == '2x2_foreign')
                                this.uploads.requirements[7].file_id = data.data.data.id;
                        }
                        
                        this.uploads.slug = this.slug;


                    } else {
                        Swal.fire(
                            'Failed!',
                            data.data.message,
                            'error'
                        )
                    }
                });



        }
    }
});
   </script>