<section v-if="true" class="section section_port relative" id="success-page">
    <div class="custom-container md:h-[500px] relative z-1">
        <img src="<?php echo $img_dir; ?>home-poly/blue-poly.png"
            class="absolute top-0 md:right-[25%] hidden md:block" alt="" data-scroll-speed="4"
            data-aos="zoom-in" />
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
        <div class="custom-container relative h-full mb-[100px] md:mb-[10px]">
            <div class="md:flex mt-[00px] md:mt-0 h-full items-center justify-center">
                <div class="md:w-12/12 py-3">
                    <div class="block mx-auto mt-[200px]" data-aos="fade-up">
                        <img class='m-auto my-2' :src=`${imgPath}green_check.png` alt="">
                        <h1 class="text-4xl font-[900] text-center color-primary"> {{message}} </h1>
                    </div>
                    <p class="max-w-[800px] color-primary mt-[60px]">
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
<style scoped="">
</style>
<script>
new Vue({
    el: "#success-page",
    data: {
        imgPath: '<?php echo base_url() . 'assets/img/';?>',
        status: "<?php echo $status; ?>",
    },
    computed: {
        message() {
            return this.status == 1 ? `Your scholarship
                            application has been submitted successfully.` : `Your scholarship
                            application has been already submitted.`
        }
    }
})
</script>