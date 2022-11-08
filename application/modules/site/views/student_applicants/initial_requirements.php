   <section id="adminssions-form" class="section section_port relative">
       <div class="custom-container  relative z-1">
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

           <form @submit.prevent="submitPost"
               class="custom-container relative h-full pt-[200px] mb-[100px] md:mb-[10px]">

               <div class="md:flex  md:mt-0 h-full items-center justify-center">
                   <div class="md:w-12/12 py-3">
                       <p class="max-w-[800px] color-primary mt-[60px]  text-2xl">
                           <span class="font-bold"> Great! </span> Next, is to upload your initial requirements:
                       </p>

                       <div class="md:flex md:space-x-10 md:items-center justify-between my-[90px]">
                           <div class="md-w-1/3">
                               <img src="<?php echo $img_dir; ?>admissions/form/id.png"
                                   class="max-w-full h-auto mx-auto block">

                               <div class="w-[200px] my-3">
                                   <input ref="file_id"
                                       class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                       type="file" required>
                               </div>

                           </div>

                           <div class="md-w-1/3">
                               <img src="<?php echo $img_dir; ?>admissions/form/nso.png"
                                   class="max-w-full h-auto mx-auto block">

                               <div class="w-[200px] my-3">
                                   <input ref="file_nso"
                                       class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                       type="file" required>
                               </div>


                           </div>

                           <div class="md-w-1/3">
                               <img src="<?php echo $img_dir; ?>admissions/form/2x2.png"
                                   class="max-w-full h-auto mx-auto block">

                               <div class="w-[200px] my-3">
                                   <input ref="file_2x2"
                                       class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                       type="file" required>
                               </div>
                           </div>
                       </div>
                   </div>
               </div>


               <div class="text-center">
                   <button type="submit"> <img src="<?php echo $img_dir; ?>admissions/form/proceed_payment.png"
                           class="max-w-full h-auto mx-auto block img-btn"></button>
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
        types: [],
        slug: '<?php echo $this->uri->segment('3'); ?>'
    },
    mounted() {

    },

    methods: {


        submitPost: function() {

            let file_id = this.$refs.file_id.files[0];
            let file_nso = this.$refs.file_nso.files[0];
            let file_2x2 = this.$refs.file_2x2.files[0];

            var formData = "";
            formData = new FormData();
            formData.append("file_id", file_id);
            formData.append("file_nso", file_nso);
            formData.append("file_2x2", file_2x2);
            formData.append("student_information_id", this.slug);



            Swal.fire({
                title: "Submit Requirements",
                text: "Are you sure you want to submit?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                preConfirm: (login) => {
                    return axios
                        .post(api_url + 'admissions/student-info/requirements', formData, {
                            headers: {
                                Authorization: `Bearer ${window.token}`
                            }
                        })
                        .then(data => {
                            this.is_done = true;

                            if (data.data.success) {

                                Swal.fire({
                                    title: "SUCCESS",
                                    text: data.data.message,
                                    type: "success"
                                }).then(function() {
                                    window.location = "/awesome";
                                });

                            } else {
                                Swal.fire(
                                    'Failed!',
                                    data.data.message,
                                    'error'
                                )
                                // window.location = "<?php echo base_url();?>site/awesome/" +
                                //     this.slug;
                            }
                        });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {}
            })

        },
    },
});
   </script>