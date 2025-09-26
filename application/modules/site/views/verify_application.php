<section class="section section_port relative" id="verify-form">
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
                <div class="fixed top-2/4 left-2/4 -translate-x-1/2 -translate-y-1/2">
                    <div
                        class="bg-[white] px-[40px] py-8 rounded-[8px] max-w-[400px] h-[260px] w-[90%] [box-shadow:0_5px_15px_rgba(0,_0,_0,_0.3)] text-center">
                        <h2 class="color-primary font-bold text-xl">Email Verification</h2>
                        <p>Please enter your email address you used for application form:</p>
                        <form @submit.prevent="verifyEmail">
                            <input
                                class="text-center w-full p-2 rounded-md mt-2 border border-[#ccc]"
                                type="email" v-model="email" placeholder="email address" required />
                            <div>
                                <div v-if="loading_spinner" class="lds-ring">
                                    <div></div>
                                    <div></div>
                                    <div></div>
                                    <div></div>
                                </div>
                                <div v-else>
                                    <button
                                        class="mt-4 px-4 py-2 border-0 rounded bg-[#014FB3] text-white cursor-pointer"
                                        type="submit"> verify </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<style scoped="">
.lds-ring {
    width: 60px;
    height: 60px;
}
</style>
<script>
new Vue({
    el: "#verify-form",
    data: {
        email: '',
        imgPath: '<?php echo base_url() . 'assets/img/';?>',
        base_url: "<?php echo base_url(); ?>",
        campus: "<?php echo $campus; ?>",
        loading_spinner: false
    },
    methods: {
        async verifyEmail() {
            this.loading_spinner = true
            const {
                data
            } = await axios.get(
                `${api_url}scholarship/verification?email=${this.email}`, {
                    headers: {
                        Authorization: `Bearer ${window.token}`
                    }
                })
            this.checkApplicationExist(data)
        },
        async checkApplicationExist({
            success,
            message
        }) {
            if (!success) {
                const {
                    isConfirmed
                } = await Swal.fire({
                    title: `iACADEMY ${this.campus.toUpperCase()} CAMPUS`,
                    html: `${message}`,
                    confirmButtonText: "Ok",
                    imageWidth: 100,
                    icon: "error",
                    showCloseButton: false,
                    allowOutsideClick: false
                })
                if (isConfirmed) {
                    location.href = `${this.base_url}site/student_application`
                }
                return
            }
            const {
                isConfirmed
            } = await Swal.fire({
                title: `iACADEMY ${this.campus.toUpperCase()} CAMPUS`,
                html: `${message}`,
                confirmButtonText: "Ok",
                imageWidth: 100,
                icon: "success",
                showCloseButton: true
            })
            this.loading_spinner = false
        },
    }
})
</script>