
<aside class="right-side">
    <div id="vue-container">
        <section class="content-header">
            <h1>
                Grades
                <small>                
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
                            <tr>                                
                                <th>Midterm</th>
                                <th>Final</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item,index) in results">
                                <td>{{ index + 1 }}</td>
                                <td>{{ item.strLastname+" "+item.strFirstname+" "+item.strMiddlename }}</td>
                                <td>{{ item.floatMidtermGrade }}</td>
                                <td>{{ item.floatFinalGrade }}</td>
                            </tr>
                        </tbody>
                    </table>
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
        id:<?php echo $id; ?>,
        results:[],
        
    },

    mounted() {

        let url_string = window.location.href;
        let url = new URL(url_string);

        this.loader_spinner = true;

        axios.get('<?php echo base_url(); ?>registrar/submitted_grades_data/'+this.id)
        .then((data) => {
           this.results = data.data.students;
                        
        })
        .catch((error) => {
            console.log(error);
        })



    },

    methods: {        
       
        

    }

})
</script>