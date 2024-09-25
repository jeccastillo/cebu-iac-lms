<aside class="right-side" id="vue-container">
    <section class="content-header">     
    <h1>
        Advising        
    </h1>  
        <ol class="breadcrumb">
            <li><a href="<?php echo base_url() ?>portal/dashboard"><i class="fa fa-home"></i> Home</a></li>
            <li class="active">Advising</li>
        </ol>
    </section>    
    <div class="content">
        <section class="section section_port relative">                         
            <div class="box box-widget widget-user-2">
                <!-- Add the bg color to the header using any of the bg-* classes -->
                <div class="widget-user-header bg-red">
                    <!-- /.widget-user-image -->                    
                    <div class="row">
                        <div class="pull-right">
                            <label>Select Term</label>
                            <select class="form-control" required @change="selectTerm($event)" v-model="sem">
                                <option v-for="term in sy" :value="term.intID">{{ term.enumSem + " " + term.term_label + " SY " + term.strYearStart + "-" + term.strYearEnd }}</option>
                            </select>
                        </div>
                    </div>
                </div>      
                <div class="box-body">                   
                    <h4>For Advising</h4>
                    <div class="row">
                        <div class="col-sm-12">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Course/Program</th>
                                        <th>Status</th>                                                                                
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="enlistment in enlistments">
                                        <td><a :href="base_url + 'academics/enlistment/' + enlistment.student_id " target="_blank">{{ enlistment.strLastname + " " +enlistment.strFirstname }}</a></td>
                                        <td>{{ enlistment.strProgramCode }}</td>
                                        <td>{{ enlistment.status }}</td>                                        
                                    </tr>                                    
                                </tbody>
                            </table>
                        </div>
                    </div>
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
        sem: '<?php echo $sem; ?>',         
        sy: [],
        enlistments: [],
           
    },
    mounted() {             

        axios
            .get(base_url + 'academics/enlistments_data/' + this.sem, {
                headers: {
                    Authorization: `Bearer ${window.token}`
                },
            })

            .then((data) => { 
                if(data.data.success){
                    this.enlistments = data.data.enlistments;
                    this.sem = data.data.active_sem.intID;
                    this.sy = data.data.sy;
                }
                else
                    document.location = base_url + 'unity/faculty_dashboard';
            });

    },

    methods: {            
        selectTerm: function($event){
            document.location = base_url + 'academics/enlistments/' + event.target.value;
        },        
    }
})
</script>


