<aside class="right-side" id="registration-container">    
    
</aside>

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

        

    },

    methods: {        
        
    }

})
</script>

