
<aside class="right-side">
    <div id="vue-container">
        <section class="content-header">
            <h1>
                Grades
                <small>    
                    <a class="btn btn-app" :href="base_url + 'pdf/grading_sheet/' + id" >
                        <i class="ion ion-printer"></i>
                        Generate Report
                    </a>  
                                
                </small>
            </h1>


        </section>
        <hr />
        <div class="content">
            <div class="box box-primary">
                <div class="box-header">
                    <h3>Grades</h3>
                    
                </div>
                <div class="box-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th rowspan="2">#</th>
                                <th rowspan="2">Student Name</th>                                
                                <th rowspan="2">Midterm Grade</th>
                                <th rowspan="2">Final Grade</th>                                
                            </tr>                            
                        </thead>
                        <tbody>
                            <tr v-for="(item,index) in results">
                                <td>{{ index + 1 }}</td>
                                <td>{{ item.strLastname+" "+item.strFirstname+" "+item.strMiddlename }}</td>
                                <td v-if="classlist.intFinalized >= 1">{{ item.floatMidtermGrade }}</td>
                                <td v-else>NGS</td>
                                <td v-if="classlist.intFinalized >= 2">{{ item.floatFinalGrade }}</td>
                                <td v-else>NGS</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div><!---content container--->
    </div><!---vue container--->
</aside>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script src="<?php echo base_url(); ?>assets/themes/default/js/vue.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
    integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="<?php echo base_url(); ?>assets/themes/default/js/axios.min.js"></script>

<script>
new Vue({
    el: '#vue-container',
    data: {
        id:<?php echo $id; ?>,
        results:[],
        classlist: undefined,
        base_url: '<?php echo base_url(); ?>',
        
    },

    mounted() {

        let url_string = window.location.href;
        let url = new URL(url_string);

        this.loader_spinner = true;

        axios.get('<?php echo base_url(); ?>registrar/submitted_grades_data/'+this.id)
        .then((data) => {
           this.results = data.data.students;
           this.classlist = data.data.classlist;
                        
        })
        .catch((error) => {
            console.log(error);
        })



    },

    methods: {        
       
        

    }

})
</script>