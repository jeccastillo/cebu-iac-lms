<aside class="right-side" id="vue-container">
    <section class="content-header">     
    <h1>
        Enlistment Form        
    </h1>  
        <ol class="breadcrumb">
            <li><a href="<?php echo base_url() ?>portal/dashboard"><i class="fa fa-home"></i> Home</a></li>
            <li class="active">Enlistment</li>
        </ol>
    </section>    
    <div class="content">
        <section class="section section_port relative">                 
        
            <div class="box box-widget widget-user-2">
                <!-- Add the bg color to the header using any of the bg-* classes -->
                <div class="widget-user-header bg-red">
                    <!-- /.widget-user-image -->
                    <h3 class="widget-user-username" style="text-transform:capitalize;margin-left:0;font-size:1.3em;">{{ student.strLastname }}, {{ student.strFirstname }} {{ student.strMiddlename }}</h3>                    
                    <h4 class="widget-user-desc" style="margin-left:0;">{{ student.strStudentNumber }}</h4>                   
                </div>                
                <div class="pull-right">
                <label>Select Term</label>
                    <select class="form-control" required @change="selectTerm($event)" v-model="sem">
                        <option v-for="term in sy" :value="term.intID">{{ term.enumSem + " " + term.term_label + " SY " + term.strYearStart + "-" + term.strYearEnd }}</option>
                    </select>
                </div>
            </div>         
                                                       
            
        </section>
    </div>
</aside>

<script src="<?php echo base_url(); ?>assets/themes/default/js/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script src="<?php echo base_url(); ?>assets/themes/default/js/vue.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
    integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="<?php echo base_url(); ?>assets/themes/default/js/axios.min.js"></script>

<style scoped="">

</style>


<script>
function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}    
new Vue({
    el: "#vue-container",
    data: {        
        id: '<?php echo $id; ?>',
        sem: '<?php echo $sem; ?>', 
        sy: [],
        student: {
            strFirstname:'',
            strLastname:'',
            strMiddlename:'',
            strProgramDescription: '',
            strMajor:'',

        },       
    },
    mounted() {        
        var amount = 0;

        axios
            .get(base_url + 'portal/enlistment_data/' + this.id + '/' + this.sem, {
                headers: {
                    Authorization: `Bearer ${window.token}`
                },
            })

            .then((data) => { 
                this.student = data.data.student;
                this.sy = data.data.sy;                                         
            });

   


    },

    methods: {            
        selectTerm: function($event){
            document.location = base_url + 'portal/enlistment/' + event.target.value;
        },
    }

})
</script>

