   <section id="adminssions-form" class="section section_port relative pb-[100px] border-b border-solid border-slate-200" >
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
                        <div class="flex items-center bg-blue-500 text-white text-sm font-bold px-4 py-3" role="alert">
                            <svg class="fill-current w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M12.432 0c1.34 0 2.01.912 2.01 1.957 0 1.305-1.164 2.512-2.679 2.512-1.269 0-2.009-.75-1.974-1.99C9.789 1.436 10.67 0 12.432 0zM8.309 20c-1.058 0-1.833-.652-1.093-3.524l1.214-5.092c.211-.814.246-1.141 0-1.141-.317 0-1.689.562-2.502 1.117l-.528-.88c2.572-2.186 5.531-3.467 6.801-3.467 1.057 0 1.233 1.273.705 3.23l-1.391 5.352c-.246.945-.141 1.271.106 1.271.317 0 1.357-.392 2.379-1.207l.6.814C12.098 19.02 9.365 20 8.309 20z"/></svg>
                            <p>Note: Documents are subject for evaluation and other supporting documents may be asked to be submitted.</p>
                        </div>   
                       <div class="grid grid-cols-2 gap-4 md:items-center justify-between mb-[50px] mt-[40px]"
                           v-if="request.citizenship != 'Philippines'">
                                <div class="mb-5">                                    
                                    <!-- <img src="<?php echo $img_dir; ?>admissions/form/upload1.png" style="max-width:140px"
                                        class="h-auto mx-auto block" title='Scanned copy unexpired Passport (bio page and all the pages with stamp)'>                                        
                                    -->
                                    <div class="bg-gray-700 h-48 min-h-fit text-gray-400">                                        
                                        <div class="container mx-auto md-w-full px-5 py-10">
                                            <div class="relative rounded-md border border-gray-600">
                                                <p class="p-3">Digital/Scanned copy unexpired Passport (bio page and all the pages with stamp)</p>
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
                                                <p class="p-3">Digital/Scanned copy of your Birth Certificate</p>
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
                                    <div class="bg-gray-700 h-48 md-w-full min-h-fit text-gray-400">                                        
                                        <div class="container mx-auto px-5 py-10">
                                            <div class="relative rounded-md border border-gray-600">
                                                <p class="p-3">Digital/Scanned 2x2 Photo</p>
                                                <h2 class="absolute flex top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                                                    <span class="bg-gray-700 px-2 text-sm font-medium">2x2 Photo</span>
                                                </h2>
                                            </div>
                                        </div>                                        
                                    </div>
                                    <input ref="file_2x2_foreign" @change="uploadReq('2x2_foreign',$event)"
                                        class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                        type="file" required>
                                </div>  
                                <div class="mb-5">                                    
                                    <div class="bg-gray-700 h-48 md-w-full min-h-fit text-gray-400">                                        
                                        <div class="container mx-auto px-5 py-10">
                                            <div class="relative rounded-md border border-gray-600">
                                                <p class="p-3">Scanned School ID</p>
                                                <h2 class="absolute flex top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                                                    <span class="bg-gray-700 px-2 text-sm font-medium">School ID</span>
                                                </h2>
                                            </div>
                                        </div>                                        
                                    </div>                                 
                                    <input ref="file_id" @change="uploadReq('school_id',$event)"
                                        class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                        type="file" required>
                                </div>     
                                                                
                        </div>
                        <div class="grid grid-cols-2 gap-4 md:items-center justify-between mb-[50px] mt-[40px]"
                           v-else-if="request.citizenship == 'Philippines' && (request.tos == 'transferee' || request.tos == 'second degree')">
                                <div class="mb-5">                                    
                                    <!-- <img src="<?php echo $img_dir; ?>admissions/form/upload1.png" style="max-width:140px"
                                        class="h-auto mx-auto block" title='Scanned copy unexpired Passport (bio page and all the pages with stamp)'>                                        
                                    -->
                                    <div class="bg-gray-700 h-48 min-h-fit text-gray-400">                                        
                                        <div class="container mx-auto md-w-full px-5 py-10">
                                            <div class="relative rounded-md border border-gray-600">
                                                <p class="p-3">Scanned copy Transcript of Records (TOR) or Copy of Grades</p>
                                                <h2 class="absolute flex top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                                                    <span class="bg-gray-700 px-2 text-sm font-medium">TOR or COG</span>
                                                </h2>
                                            </div>
                                        </div>                                        
                                    </div>
                                    <input ref="transcript" @change="uploadReq('transcript',$event)"
                                        class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                        type="file" required>
                                </div>                                
                                <div class="mb-5">                               
                                    <!-- <img src="<?php echo $img_dir; ?>admissions/form/upload4.png" style="max-width:140px"
                                        class="h-auto mx-auto block" title='Copy of Birth Certificate.'> -->
                                    <div class="bg-gray-700 h-48 md-w-full min-h-fit text-gray-400">                                        
                                        <div class="container mx-auto px-5 py-10">
                                            <div class="relative rounded-md border border-gray-600">
                                                <p class="p-3">Digital/Scanned copy of your Birth Certificate</p>
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
                                    <div class="bg-gray-700 h-48 md-w-full min-h-fit text-gray-400">                                        
                                        <div class="container mx-auto px-5 py-10">
                                            <div class="relative rounded-md border border-gray-600">
                                                <p class="p-3">Digital/Scanned 2x2 Photo</p>
                                                <h2 class="absolute flex top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                                                    <span class="bg-gray-700 px-2 text-sm font-medium">2x2 Photo</span>
                                                </h2>
                                            </div>
                                        </div>                                        
                                    </div>
                                    <input ref="file_2x2_foreign" @change="uploadReq('2x2_foreign',$event)"
                                        class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                        type="file" required>
                                </div>  
                                <div class="mb-5">                                    
                                    <div class="bg-gray-700 h-48 md-w-full min-h-fit text-gray-400">                                        
                                        <div class="container mx-auto px-5 py-10">
                                            <div class="relative rounded-md border border-gray-600">
                                                <p class="p-3">Scanned School ID</p>
                                                <h2 class="absolute flex top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                                                    <span class="bg-gray-700 px-2 text-sm font-medium">School ID</span>
                                                </h2>
                                            </div>
                                        </div>                                        
                                    </div>                                 
                                    <input ref="file_id" @change="uploadReq('school_id',$event)"
                                        class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                        type="file" required>
                                </div>     
                                                                
                        </div>
                        <div class="grid grid-cols-3 gap-4 md:items-center justify-between mb-[50px] mt-[40px]"
                           v-else>                            
                                <div class="md-w-1/3">
                                    <div class="bg-gray-700 h-48 md-w-full min-h-fit text-gray-400">                                        
                                        <div class="container mx-auto px-5 py-10">
                                            <div class="relative rounded-md border border-gray-600">
                                                <p class="p-3">Digital/Scanned Copy of 2x2 Photo</p>
                                                <h2 class="absolute flex top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                                                    <span class="bg-gray-700 px-2 text-sm font-medium">2x2 Photo</span>
                                                </h2>
                                            </div>
                                        </div>                                        
                                    </div>                                     
                                    <input ref="file_2x2" @change="uploadReq('2x2',$event)"
                                        class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                        type="file" required>
                                    
                                </div>
                                <div class="md-w-1/3">
                                    <div class="bg-gray-700 h-48 md-w-full min-h-fit text-gray-400">                                        
                                        <div class="container mx-auto px-5 py-10">
                                            <div class="relative rounded-md border border-gray-600">
                                                <p class="p-3">Digital/Scanned Copy of PSA or NSO Birth Certificate</p>
                                                <h2 class="absolute flex top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                                                    <span class="bg-gray-700 px-2 text-sm font-medium">Birth Certificate</span>
                                                </h2>
                                            </div>
                                        </div>                                        
                                    </div>    
                                    <input ref="file_nso" @change="uploadReq('psa',$event)"
                                        class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                        type="file" required>
                                
                                </div>

                                <div class="md-w-1/3">
                                    <div class="bg-gray-700 h-48 md-w-full min-h-fit text-gray-400">                                        
                                        <div class="container mx-auto px-5 py-10">
                                            <div class="relative rounded-md border border-gray-600">
                                                <p class="p-3">School ID for student applicants OR Government ID for 2nd degree applicants</p>
                                                <h2 class="absolute flex top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                                                    <span class="bg-gray-700 px-2 text-sm font-medium">Valid ID</span>
                                                </h2>
                                            </div>
                                        </div>                                        
                                    </div>
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
                    <button type="submit" class="bg-transparent hover:bg-blue-500 text-blue-700 font-semibold hover:text-white py-2 px-4 border border-blue-500 hover:border-transparent rounded">
                        Submit and Proceed to Payment
                    </button>
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
                if(this.request.citizenship != "Philippines" || this.request.tos == "second degree" || this.request.tos == "transferee")
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
            }else if (type == 'transcript') {
                file = this.$refs.transcript.files[0];                
            }
            else {
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
                        if(this.request.citizenship != 'Philippines'){
                            if (type == 'passport')
                                this.uploads.requirements[0].file_id = data.data.data.id;                            
                            if (type == 'birthcert')
                                this.uploads.requirements[1].file_id = data.data.data.id;                            
                            if (type == '2x2_foreign')
                                this.uploads.requirements[2].file_id = data.data.data.id;
                            if (type == 'school_id')
                                this.uploads.requirements[3].file_id = data.data.data.id;
                        }
                        else if(this.request.tos == "transferee" || this.request.tos == "second degree"){
                            if (type == 'transcript')
                                this.uploads.requirements[0].file_id = data.data.data.id;                            
                            if (type == 'birthcert')
                                this.uploads.requirements[1].file_id = data.data.data.id;                            
                            if (type == '2x2_foreign')
                                this.uploads.requirements[2].file_id = data.data.data.id;
                            if (type == 'school_id')
                                this.uploads.requirements[3].file_id = data.data.data.id;
                        }
                        else{
                            if (type == 'school_id')
                                this.uploads.requirements[0].file_id = data.data.data.id;
                            if (type == 'psa')
                                this.uploads.requirements[1].file_id = data.data.data.id;
                            if (type == '2x2')
                                this.uploads.requirements[2].file_id = data.data.data.id;
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