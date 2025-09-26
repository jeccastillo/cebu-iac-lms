<div class="custom-container max-w-[1080px] " id="scholarship-form" style="margin-top:4rem;">
    <h1 class="text-4xl font-[900] text-center color-primary"> iCSID Scholarship Program </h1>
    <div class="color-primary text-center">
        <h4 class="font-medium text-2xl mb-5"> Application Form <br />
        </h4>
    </div>
    <form v-on:submit.prevent="submitVideo">
        <div v-if="true" class=" mb-6 mt-10">
            <div class="mb-4">
                <div class="border-[1px] border-neutral-100  rounded-lg mt-5 py-2.5 pl-2.5 pr-2.5">
                    <label class="block color-primary font-bold mb-3 pr-4"> Video Introduction
                    </label>
                    <label class="block color-primary italic text-sm mb-5"> (Submit a creative
                        1-minute video introducing yourself, explaining why you should be accepted
                        into this program. Upload) </label>
                    <input class="color-primary" @change="attachFile" type="file" accept="video/*"
                        required />
                </div>
            </div>
        </div>
        <hr class="my-5 bg-gray-400 h-[3px]" />
        <div class=" text-right">
            <div v-if="loading_spinner" class="lds-ring">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
            <div v-else>
                <button type="submit">
                    <img src="<?php echo $img_dir; ?>admissions/form/Asset 10.png">
                </button>
            </div>
        </div>
    </form>
</div>
<style>
input::placeholder {
    text-align: center;
}

.parent-info::placeholder {
    text-align: center;
    font-size: 12px;
    font-style: italic;
}

select {
    text-align: center;
    text-align-last: center;
    color: #f5f5f5;
    background-color: #f5f5f5;
}
</style>
<script>
new Vue({
    el: "#scholarship-form",
    data: {
        showModal: true,
        email: '',
        selected: '',
        apiUrl: "http://cebuapi.iacademy.edu.ph/api/v1/",
        base_url: "<?php echo base_url(); ?>",
        loading_spinner: false,
        slug: "<?php echo $slug; ?>",
        attachment: ''
    },
    methods: {
        attachFile($event) {
            this.attachment = $event.target.files[0]
        },
        async submitVideo() {
            this.loading_spinner = true
            if (!this.attachment.type.startsWith("video/")) {
                const {
                    isConfirmed
                } = await Swal.fire({
                    title: 'iACADEMY CEBU CAMPUS',
                    html: 'Not a valid video file.',
                    confirmButtonText: "Ok",
                    imageWidth: 100,
                    icon: "error",
                    showCloseButton: false
                })
                if (isConfirmed) {
                    this.loading_spinner = false
                    return
                }
            }
            const formData = new FormData()
            formData.append('video', this.attachment)
            formData.append('slug', this.slug)
            axios.post(`${api_url}scholarship/application`, formData, {
                headers: {
                    Authorization: `Bearer ${window.token}`
                }
            }).then(data => {
                if (data.data.success) {
                    Swal.fire({
                        title: "SUCCESS",
                        icon: "success"
                    }).then(function() {
                        location.href = `${this.base_url}site/success/1`
                    });
                } else {
                    this.loading_spinner = false
                    Swal.fire('Failed!', data.data.message, 'error')
                }
            });
        },
    },
});
</script>