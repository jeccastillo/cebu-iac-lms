<div class="custom-container max-w-[1080px] " id="scholarship-form" style="margin-top:4rem;">
    <h1 class="text-4xl font-[900] text-center color-primary"> iCSID Scholarship Program </h1>
    <div class="color-primary text-center">
        <h4 class="font-medium text-2xl mb-5"> Application Form (Cebu Campus) <br />
        </h4>
    </div>
    <form v-on:submit.prevent="validateVideo" class="">
        <div v-if="true" class=" mb-6 mt-10">
            <div class="mb-4">
                <div class="border-[1px] border-neutral-100  rounded-lg mt-5 py-2.5 pl-2.5 pr-2.5">
                    <label class="block color-primary font-bold mb-3 pr-4"> iACADEMY CEBU Scholars
                        for Innovation and Design: “iCSID” </label>
                    <label class="block color-primary italic text-sm mb-5"> (Submit a 1 minute video
                        introducing yourself and how you wish to create positive change through
                        unique and innovative ideas that address today’s challenges.) </label>
                    <input class="color-primary" @change="attachFile" type="file" accept="video/*" required />
                    <p class="mt-2 text-sm"> Deadline of application: Feb 28, 2026</p>
                </div>
            </div>
        </div>
        <hr class="my-5 bg-gray-400 h-[3px]" />
        <div class="text-right">
            <button type="submit">
                <img src="<?php echo $img_dir; ?>admissions/form/Asset 10.png">
            </button>
        </div>
    </form>
</div>
<!-- Start of HubSpot Embed Code -->
<script type="text/javascript" id="hs-script-loader" async defer src="//js.hs-scripts.com/45758391.js"></script>
<!-- End of HubSpot Embed Code -->
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
<!-- <script src="https://cdn.jsdelivr.net/npm/vue@2/dist/vue.js"></script> -->
<!--script src="https://unpkg.com/vue-select@3.0.0"></script-->
<!--link rel="stylesheet" href="https://unpkg.com/vue-select@3.0.0/dist/vue-select.css"-->
<script src="<?php echo $js_dir ?>dataExport.js"></script>
<script>
new Vue({
    el: "#scholarship-form",
    data: {
        showModal: true,
        email: '',
        selected: '',
        apiUrl: "http://cebuapi.iacademy.edu.ph/api/v1/",
        base_url: "<?php echo base_url(); ?>",
        loading_spinner: true,
        slug: '<?php echo $slug; ?>',
        attachment: ''
    },
    methods: {
        attachFile($event) {
            this.attachment = $event.target.files[0]
        },
        validateVideo() {
            if (!this.attachment.type.startsWith("video/")) {
                Swal.fire({
                    title: 'iACADEMY APPLICATION FORM',
                    html: 'Not a valid video file.',
                    confirmButtonText: "Ok",
                    imageWidth: 100,
                    icon: "error",
                    showCloseButton: false
                })
                return
            }
            this.submitVideo()
        },
        submitVideo() {
            Swal.fire({
                title: 'iACADEMY APPLICATION FORM ',
                html: `
                You're about to submit this video. 
            `,
                showCancelButton: true,
                confirmButtonText: "Submit Video",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                preConfirm: (login) => {
                    const formData = new FormData();
                    formData.append("field", "scholarship_video");
                    formData.append("value", this.attachment);
                    axios.post(
                        `${api_url}admissions/student-info/update-field/custom/${this.slug}`,
                        formData, {
                            headers: {
                                Authorization: `Bearer ${window.token}`
                            }
                        }).then(data => {
                        if (data.data.success) {
                            Swal.fire({
                                title: "SUCCESS",
                                text: data.data.message,
                                icon: "success"
                            }).then(function() {
                                location.href =
                                    'https://iacademy.edu.ph/'
                            });
                        } else {
                            Swal.fire('Failed!', data.data.message,
                                'error')
                        }
                    });
                }
            })
        },
    },
});
</script>
<style>
@import url("https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap");

* {
    font-family: "Roboto", sans-serif;
}
</style>