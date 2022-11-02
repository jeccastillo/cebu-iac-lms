   <section id="hero" class="section section_port relative">
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

           <div class="custom-container relative h-full pt-[200px] mb-[100px] md:mb-[10px]">

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
                                   <input
                                       class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                       type="file" required v-model="request.email">
                               </div>

                           </div>

                           <div class="md-w-1/3">
                               <img src="<?php echo $img_dir; ?>admissions/form/nso.png"
                                   class="max-w-full h-auto mx-auto block">

                               <div class="w-[200px] my-3">
                                   <input
                                       class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                       type="file" required v-model="request.email">
                               </div>


                           </div>

                           <div class="md-w-1/3">
                               <img src="<?php echo $img_dir; ?>admissions/form/2x2.png"
                                   class="max-w-full h-auto mx-auto block">

                               <div class="w-[200px] my-3">
                                   <input
                                       class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                       type="file" required v-model="request.email">
                               </div>
                           </div>
                       </div>
                   </div>
               </div>

               <div class="text-center">
                   <!-- <a href=""> <img src="<?php echo $img_dir; ?>admissions/form/2x2.png"
                           class="max-w-full h-auto mx-auto block"></a> -->
               </div>


           </div>
       </div>
   </section>