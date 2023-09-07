
<aside class="right-side">
    <div id="vue-container">
        <section class="content-header">
            <h1>
                Grading Sheet
                <small>                
                </small>
            </h1>


        </section>
        <hr />
        <div class="content">
            <div class="box box-primary">
                <div class="box-header">
                    <h3>Search</h3>
                    <div class="row">
                        <div class="col-sm-2">
                            Department
                        </div>
                        <div class="col-sm-6">
                            <select class="form-control" @change="changeDept($event)">
                                <option value="college">College</option>
                                <option value="shs">SHS</option>
                            </select>
                        </div>
                    </div>
                    <hr />
                    <div class="row">
                        <div class="col-sm-2">
                            Term
                        </div>
                        <div class="col-sm-6">
                            <select class="form-control" v-model="selectedterm">
                                <option v-for="term in terms" :value="term.intID">{{ term.enumSem + " " + term.term_label + " SY " + term.strYearStart + "-" + term.strYearEnd }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="box-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th rowspan="2">#</th>
                                <th rowspan="2">Section</th>                                
                                <th rowspan="2">Subject Code</th>
                                <th rowspan="2">Description</th>
                                <th rowspan="2">Faculty</th>
                                <th colspan="2">Date Posted</th>
                                <th rowspan="2">Date Printed</th>
                            </tr>
                            <tr>                                
                                <th>Midterm</th>
                                <th>Final</th>
                            </tr>
                        </thead>
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
        terms:[],
        faculty:[],    
        selectedterm:  undefined, 
        
    },

    mounted() {

        let url_string = window.location.href;
        let url = new URL(url_string);

        this.loader_spinner = true;

        axios.get('<?php echo base_url(); ?>registrar/search_grading_data/college')
        .then((data) => {
            this.terms = data.data.terms;
                        
        })
        .catch((error) => {
            console.log(error);
        })



    },

    methods: {        
        changeDept: function(event){
            axios.get('<?php echo base_url(); ?>registrar/search_grading_data/'+event.target.value)
            .then((data) => {
                this.terms = data.data.terms;
                
                
            })
            .catch((error) => {
                console.log(error);
            })

        },

    }

})
</script>