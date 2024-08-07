
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
                    <form @submit.prevent="searchGrades" method="post">
                        <div class="row" style="margin-bottom:10px">
                            <div class="col-sm-2 text-right">
                                Department
                            </div>
                            <div class="col-sm-4">
                                <select class="form-control" @change="changeDept($event)">
                                    <option value="college">College</option>
                                    <option value="shs">SHS</option>
                                </select>
                            </div>
                        </div>                    
                        <div class="row" style="margin-bottom:10px">
                            <div class="col-sm-2 text-right">
                                Term
                            </div>
                            <div class="col-sm-4">
                                <select class="form-control" required @change="selectTerm($event)" v-model="request.term">
                                    <option v-for="term in terms" :value="term.intID">{{ term.enumSem + " " + term.term_label + " SY " + term.strYearStart + "-" + term.strYearEnd }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="row" style="margin-bottom:10px">
                            <div class="col-sm-2 text-right">
                                Faculty
                            </div>
                            <div class="col-sm-4">
                                <select class="form-control" v-model="request.faculty">
                                    <option v-for="fac in faculty" :value="fac.intID">{{ fac.strLastname + " " + fac.strFirstname }}</option>
                                </select>
                            </div>
                        </div>                       
                        <div v-if="request.term" class="row" style="margin-bottom:10px">
                            <div class="col-sm-2 text-right">
                                Section
                            </div>
                            <div class="col-sm-2">
                                <label>Class Name</label>
                                <select class="form-control" v-model="request.class_name">
                                    <option v-for="section in sections" :value="section.strClassName">{{ section.strClassName }}</option>
                                </select>
                            </div>
                            <div class="col-sm-2">
                                <label>Year Level</label>
                                <select class="form-control" v-model="request.year">
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                </select>
                            </div>
                            <div class="col-sm-2">
                                <label>Section Number</label>
                                <select class="form-control" v-model="request.section">
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                    <option value="6">6</option>
                                    <option value="7">7</option>
                                    <option value="8">8</option>
                                    <option value="9">9</option>
                                </select>
                            </div>
                            <div class="col-sm-2">
                                <label>Sub Section</label>
                                <select class="form-control" v-model="request.sub_section">
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                    <option value="6">6</option>
                                    <option value="7">7</option>
                                    <option value="8">8</option>
                                    <option value="9">9</option>
                                </select>
                            </div>                                                        
                        </div>                        
                        <div v-if="request.term" class="row" style="margin-bottom:10px">
                            <div class="col-sm-2 text-right">
                                Subject
                            </div>
                            <div class="col-sm-4">
                                <select class="form-control" v-model="request.subject">
                                    <option v-for="subject in subjects" :value="subject.intID">{{ subject.strCode }}</option>
                                </select>
                            </div>                            
                        </div>
                        <hr />
                        <div class="row">
                            <div class="col-sm-6">
                                <input type="submit" class="btn btn-default" value="Search" />                            
                                <button class="btn btn-default" @click.prevent="resetValues">Reset</button>
                            </div>
                        </div>
                    </form>
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
                        <tbody>
                            <tr v-for="(item,index) in results">
                                <td>{{ index + 1 }}</td>
                                <td><a target="_blank" :href="base_url + 'unity/classlist_viewer/' + item.intID">{{ item.strClassName + item.year + item.strSection + item.sub_section }}</a></td>
                                <td>{{ item.strCode }}</td>
                                <td>{{ item.strDescription }}</td>
                                <td>{{ item.strLastname+" "+item.strFirstname }}</td>
                                <td>{{ (item.intFinalized > 0 )? item.date_midterm_submitted: '' }}</td>
                                <td>{{ (item.intFinalized > 1 )? item.date_final_submitted: '' }}</td>
                                <td></td>
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
        terms:[],
        faculty:[],    
        sections:[],
        subjects:[],
        results:[],
        request: {
            faculty: undefined,
            term: undefined,
            section: undefined,
            subject: undefined,
            class_name:undefined,
            year: undefined,
            sub_section: undefined,
        }, 
        
    },

    mounted() {

        let url_string = window.location.href;
        let url = new URL(url_string);

        this.loader_spinner = true;

        axios.get('<?php echo base_url(); ?>registrar/search_grading_data/college')
        .then((data) => {
            this.terms = data.data.terms;
            this.faculty = data.data.faculty;
                        
        })
        .catch((error) => {
            console.log(error);
        })



    },

    methods: {        
        resetValues: function(){
            this.request.faculty = undefined;            
            this.request.section = undefined;
            this.request.sub_section = undefined;
            this.request.year = undefined;
            this.request.class_name = undefined;
            this.request.subject = undefined;
            this.results = [];
        },
        changeDept: function(event){
            axios.get('<?php echo base_url(); ?>registrar/search_grading_data/'+event.target.value)
            .then((data) => {
                this.terms = data.data.terms;                
                
            })
            .catch((error) => {
                console.log(error);
            })

        },
        selectTerm: function(event){
            axios.get('<?php echo base_url(); ?>registrar/search_grading_sections/'+event.target.value)
            .then((data) => {
                this.sections = data.data.sections;                
                this.request.section = undefined;
                this.subjects = data.data.subjects;
                this.request.subject = undefined;
                
            })
            .catch((error) => {
                console.log(error);
            })

        },
        searchGrades: function(){

            var formdata= new FormData();
            for (const [key, value] of Object.entries(this.request)) {
                formdata.append(key,value);
            }                                                    

            this.loader_spinner = true;
            axios.post(base_url + 'registrar/search_grading_results', formdata, {
                headers: {
                    Authorization: `Bearer ${window.token}`
                }
            })
            .then(data => {
                this.loader_spinner = false;
                this.results = data.data.results;
                for(i in this.results){
                    if(this.results[i].sub_section != null && this.results[i].sub_section != ""){
                        this.results[i].sub_section = "-"+this.results[i].sub_section;
                    }
                    else{
                        this.results[i].sub_section = "";
                    }
                }
            });
        },
        

    }

})
</script>