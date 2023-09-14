
<aside class="right-side">
    <div id="vue-container">
        <section class="content-header">
            <h1>
                Classlist
                <small>                                                                        
                </small>
            </h1>            
        </section>
        <hr />
        <div class="content">            
            <div class="box">                                
                <div class="box-header">
                    <h3 class="box-title">
                        {{ classlist.strCode + ' - ' + classlist.strClassName + ' ' + classlist.year + classlist.strSection + ' ' + classlist.sub_section}}
                        <small>
                            {{ classlist.enumSem + ' ' + classlist.term_label + ' ' + classlist.strYearStart + '-' + classlist.strYearEnd }}
                        </small>
                    </h3>                    
                </div>
                <div class="box-body">

                </div>
            </div>
        </div><!---content container--->
    </div><!---vue container--->
</aside>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@2.6.12"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
    integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>

<script>
new Vue({
    el: '#vue-container',
    data: {
        id: <?php echo $id; ?>,
        show_all: <?php echo $showAll; ?>,
        active_sem: undefined,
        cl: [],
        classlist:undefined,
        grading_items: [],
        grading_items_midterm:[],
        is_admin: false,
        is_registrar: false,
        is_super_admin: false,
        subject: undefined,

    },

    mounted() {

        let url_string = window.location.href;
        let url = new URL(url_string);

        this.loader_spinner = true;

        axios.get(base_url + 'unity/classlist_viewer_data/'+this.id+'/'+this.show_all)
        .then((data) => {
            this.active_sem = data.data.active_sem;
            this.cl = data.data.cl;
            this.classlist = data.data.classlist;
            this.grading_items =  data.data.grading_items;
            this.grading_items_midterm =  data.data.grading_items_midterm;
            this.is_admin = data.data.is_admin;
            this.is_registrar = data.data.is_registrar;
            this.is_super_admin = data.data.is_super_admin;
            this.show_all = data.data.showall;
            this.students = data.data.students;
            this.subject = data.data.subject;
            
        })
        .catch((error) => {
            console.log(error);
        })



    },

    methods: {                


    }

})
</script>