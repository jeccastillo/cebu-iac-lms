   <section id="adminssions-form" class="section section_port relative">
        <div v-if="loading_spinner" wire:loading class="fixed top-0 left-0 right-0 bottom-0 w-full h-screen z-50 overflow-hidden bg-gray-700 opacity-75 flex flex-col items-center justify-center">
            <div class="loader ease-linear rounded-full border-4 border-t-4 border-gray-200 h-12 w-12 mb-4"></div>
            <h2 class="text-center text-white text-xl font-semibold">Loading...</h2>
            <p class="w-1/3 text-center text-white">This may take a few seconds, please don't close this page.</p>
        </div>
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
                                        <input ref="file_2x2" @change="uploadReq('2x2',$event)"
                                            class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                            type="file" required>
                                    </div>
                                </div>
                                <div class="md-w-1/3">
                                    <img src="<?php echo $img_dir; ?>admissions/form/nso.png"
                                        class="max-w-full h-auto mx-auto block">

                                    <div class="w-[200px] my-3 block mx-auto">
                                        <input ref="file_nso" @change="uploadReq('psa',$event)"
                                            class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                            type="file" required>
                                    </div>
                                </div>

                                <div class="md-w-1/3">
                                    <img src="<?php echo $img_dir; ?>admissions/form/id.png"
                                        class="max-w-full h-auto mx-auto block">

                                    <div class="w-[200px] my-3 block mx-auto">
                                        <input ref="file_id" @change="uploadReq('school_id',$event)"
                                            class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                            type="file" required>
                                    </div>

                                </div>
                        </div>
                        <div class="grid cols-4 gap-4 md:space-x-20 md:items-center justify-between my-[90px]"
                           v-if="request.email && request.citizenship != 'Philippines'">
                                <div class="mb-5">                                    
                                    <!-- <img src="<?php echo $img_dir; ?>admissions/form/upload1.png" style="max-width:140px"
                                        class="h-auto mx-auto block" title='Scanned copy unexpired Passport (bio page and all the pages with stamp)'>                                        
                                    -->
                                    <div class="bg-gray-700 h-48 min-h-fit text-gray-400">                                        
                                        <div class="container mx-auto md-w-full px-5 py-10">
                                            <div class="relative rounded-md border border-gray-600">
                                                <p class="p-3">Scanned copy unexpired Passport (bio page and all the pages with stamp)</p>
                                                <h2 class="absolute flex top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                                                    <span class="bg-gray-700 px-2 text-sm font-medium">Passport</span>
                                                </h2>
                                            </div>
                                        </div>                                        
                                    </div>
                                    <input ref="file_passport" @change="uploadReq('passport',$event)"
                                        class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                        type="file" required>
                                </div>                                
                                <div class="mb-5">                               
                                    <!-- <img src="<?php echo $img_dir; ?>admissions/form/upload4.png" style="max-width:140px"
                                        class="h-auto mx-auto block" title='Copy of Birth Certificate.'> -->
                                    <div class="bg-gray-700 h-48 md-w-full min-h-fit text-gray-400">                                        
                                        <div class="container mx-auto px-5 py-10">
                                            <div class="relative rounded-md border border-gray-600">
                                                <p class="p-3">Scanned Birth Certificate</p>
                                                <h2 class="absolute flex top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                                                    <span class="bg-gray-700 px-2 text-sm font-medium">Birth Certificate</span>
                                                </h2>
                                            </div>
                                        </div>                                        
                                    </div>
                                    <input ref="file_birthcert" @change="uploadReq('birthcert',$event)"
                                        class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                        type="file" required>
                                </div>         
                                <div class="mb-5">
                                    <h4>Scanned 2x2 Photo</h4>
                                    <img src="<?php echo $img_dir; ?>admissions/form/upload8.png" style="max-width:140px"
                                        class="h-auto mx-auto block" title='2x2 ID picture (white background with name tag below)'>                                    
                                    <input ref="file_2x2_foreign" @change="uploadReq('2x2_foreign',$event)"
                                        class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                        type="file" required>
                                </div>  
                                <div class="mb-5">
                                    <h4>Scanned School ID</h4>
                                    <img src="<?php echo $img_dir; ?>admissions/form/id.png"
                                        class="max-w-full h-auto mx-auto block" title='school id'>                                    
                                    <input ref="file_id" @change="uploadReq('school_id',$event)"
                                        class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                        type="file" required>
                                </div>     
                                                                
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
   <style>
    .loader {
        border-top-color: #3498db;
        -webkit-animation: spinner 1.5s linear infinite;
        animation: spinner 1.5s linear infinite;
    }

    @-webkit-keyframes spinner {
        0% {
            -webkit-transform: rotate(0deg);
        }
        100% {
            -webkit-transform: rotate(360deg);
        }
    }

    @keyframes spinner {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }
   </style>
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
                    // headers: {
                    //     Authorization: `Bearer ${window.token}`
                    // }
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

        uploadReq: function(type,event) {

            this.loading_spinner = true;
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
                            if (type == 'birthcert')
                                this.uploads.requirements[1].file_id = data.data.data.id;                            
                            if (type == '2x2_foreign')
                                this.uploads.requirements[2].file_id = data.data.data.id;
                            if (type == 'school_id')
                                this.uploads.requirements[3].file_id = data.data.data.id;
                        }
                        
                        this.uploads.slug = this.slug;
                        this.loading_spinner = false;

                    } else {
                        Swal.fire(
                            'Failed!',
                            data.data.message,
                            'error'
                        )
                        event.target.value = null;
                        this.loading_spinner = false;
                    }
                });



        }
    }
});
   </script>