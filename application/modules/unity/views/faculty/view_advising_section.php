<aside class="right-side" id="registration-container">    
    <section class="content-header">
        <h1>
            <small>
                <a class="btn btn-app" :href="base_url + 'unity/view_classlist'"><i class="ion ion-arrow-left-a"></i>All Classes</a>                                     
            </small>
        </h1>
    </section>        
    <div class="content">        
        <div class="box box-primary">
            <div class="box-header">
                <h3>{{ section.name }}</h3>
            </div>
        </div>
    </div>
    
</aside>

<script src="<?php echo base_url(); ?>assets/themes/default/js/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script src="<?php echo base_url(); ?>assets/themes/default/js/vue.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
    integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="<?php echo base_url(); ?>assets/themes/default/js/axios.min.js"></script>

<script>
new Vue({
    el: '#registration-container',
    data: {
        id: '<?php echo $id; ?>',            
        sem: '<?php echo $sem; ?>',       
        section: undefined,
        students: [],
    },

    mounted() {

        let url_string = window.location.href;        
        if(this.id != 0){            
            //this.loader_spinner = true;
            axios.get(base_url + 'unity/advising_section_data/' + this.id + "/" + this.sem)
                .then((data) => {                                          
                    this.section = data.data.section;
                    this.students = data.data.students;
                })
                .catch((error) => {
                    console.log(error);
                })
        }

    },

    methods: {      
    }

})
</script>

