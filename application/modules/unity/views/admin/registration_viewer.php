<aside class="right-side" id="registration-container">    
    
</aside>

<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@2.6.12"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
    integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>

<script>
new Vue({
    el: '#registration-container',
    data: {
        base_url: '<?php echo base_url(); ?>',
        id: '<?php echo $id; ?>',    
        sem: '<?php echo $selected_ay; ?>',        
        student:{},    
        advanced_privilages: false,
        request: {
            enumScholarship: 0,
            enumStudentType: 'new',
            enumRegistrationStatus: 'regular',
            strAcademicYear: undefined,
        },
        registration: {},
        registration_status: 0,
        reg_status: undefined,
        
        loader_spinner: true,                        
    },

    mounted() {

        let url_string = window.location.href;        
        if(this.id != 0){            
            //this.loader_spinner = true;
            axios.get(this.base_url + 'unity/registration_viewer_data/' + this.id + '/' + this.sem)
                .then((data) => {  
                    if(data.data.success){                                                                                           
                        this.registration = data.data.registration;            
                        this.registration_status = data.data.registration.intROG;
                        this.reg_status = data.data.reg_status;
                        this.student = data.data.student;         
                        this.advanced_privilages = data.data.advanced_privilages;       
                    }
                    else{
                        document.location = this.base_url + 'users/login';
                    }

                    this.loader_spinner = false;                    
                })
                .catch((error) => {
                    console.log(error);
                })
        }

    },

    methods: {        
        changeRegStatus: function(){
            let url = this.base_url + 'unity/update_rog_status';
            var formdata= new FormData();
            formdata.append("intRegistrationID",this.registration.intRegistrationID);
            formdata.append("intROG",this.registration_status);
            this.loader_spinner = true;
            axios.post(url, formdata, {
                headers: {
                    Authorization: `Bearer ${window.token}`
                }
            })
            .then(data => {
                this.loader_spinner = false;
                Swal.fire({
                    title: "Success",
                    text: data.data.message,
                    icon: "success"
                }).then(function() {
                    
                });
            });
            
            
        }
    }

})
</script>

