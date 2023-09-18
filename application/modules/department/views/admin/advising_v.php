<aside class="right-side" id="registration-container">    
    <section class="content-header">
        <h1>
            <small>
                <a class="btn btn-app" :href="base_url + 'unity/student_viewer/' + id"><i class="ion ion-arrow-left-a"></i>All Details</a> 
                <!-- <a class="btn btn-app" :href="base_url + 'pdf/transcript/' + student.intID"><i class="fa fa-print"></i>Print Transcript</a>                                        -->
            </small>
        </h1>
        <hr />
    </section>
        <hr />
    <div class="content">        
        <div class="row">
            <div class="col-sm-12">
                <div v-if="student" class="box box-widget widget-user-2">
                    <!-- Add the bg color to the header using any of the bg-* classes -->
                    <div class="widget-user-header bg-red">
                        <!-- /.widget-user-image -->
                        <div class="pull-right">
                            <label>Select Term</label>
                            <select class="form-control" required @change="selectTerm($event)" v-model="sem">
                                <option v-for="term in sy" :value="term.intID">{{ term.enumSem + " " + term.term_label + " SY " + term.strYearStart + "-" + term.strYearEnd }}</option>
                            </select>
                        </div>
                        <h3 class="widget-user-username" style="text-transform:capitalize;margin-left:0;font-size:1.3em;">{{ student.strLastname.toUpperCase() }}, {{ student.strFirstname.toUpperCase() }} {{ student.strMiddlename?student.strMiddlename.toUpperCase():'' }}</h3>
                        <h5 class="widget-user-desc" style="margin-left:0;">{{ student.strProgramDescription }} {{ (student.strMajor != 'None')?'Major in '+student.strMajor:'' }}</h5>
                        
                    </div>
                    <div class="box-footer no-padding">
                        <ul class="nav nav-stacked">
                            <li><a href="#" style="font-size:13px;">Student Number <span class="pull-right text-blue">{{ student.strStudentNumber.replace(/-/g, '') }}</span></a></li>
                            <li><a href="#" style="font-size:13px;">Curriculum <span class="pull-right text-blue">{{ student.strName }}</span></a></li>
                            <li><a href="#" style="font-size:13px;">Academic Standing <span class="pull-right text-blue">{{ academic_standing.status }}</span></a></li>
                        </ul>
                    </div>
                </div>                
            </div>
        </div> 
        <div class="box box-primary">           
            <div class="box-header">
                <h3>Enlistment of Subjects</h3>
                <h4 class="text-center">Currently processing: {{ active_sem.enumSem + " " + active_sem.term_label + " " + active_sem.strYearStart + "-" + active_sem.strYearEnd }}</h4>
            </div>
            <div class="box-body">
                <table v-if="prev_sem" class="table table-bordered">
                    <thead>
                        <tr>
                            <th colspan="5" class="text-center">
                                Previous Record
                            </th>
                        </tr>
                        <tr>
                            <th colspan="5">
                                {{ prev_sem.enumSem + " " + prev_sem.term_label+ " "+prev_sem.strYearStart + "-" + prev_sem.strYearEnd }}
                            </th>
                        </tr>
                        <tr>
                            <th>Course Code</th>
                            <th class="text-center">Units</th>
                            <th class="text-center">Final Grade</th>
                            <th>Remarks</th>
                            <th>Faculty</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="rec in prev_records">
                            <td>{{ rec.strCode }}</td>
                            <td class="text-center">{{rec.strUnits }}</td>
                            <td class="text-center">{{ rec.v3 }}</td>
                            <td>{{rec.strRemarks }}</td>
                            <td>{{rec.strFirstname + " " + rec.strLastname }}</td>
                        </tr>
                    </tbody>
                </table>
                <hr />
                <div class="row">
                    <div class="col-sm-6">
                        <label>Year Level</label>
                        <div class="form-group">
                            <select v-model="year" class="form-control">
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                            </select>
                        </div>
                    </div>                    
                </div>                
                <hr />
                <div class="row">
                    <div class="col-md-5">
                        <h4>Suggested Subjects</h4>
                        <select style="height:300px" class="form-control" v-model="subject_selector" multiple>                            
                            <option v-for="sn in subjects_not_taken" :value="sn.intID">{{ sn.strCode }}</option>                            
                        </select>
                    </div>
                    <div class="col-md-2">
                        <br /><br />
                        <a href="#" class="btn btn-default  btn-flat btn-block" @click.prevent="autoload">Autoload <br /> Subjects </a>
                        <a href="#" id="load-advised" class="btn btn-default  btn-flat btn-block">Load <i class="ion ion-arrow-right-c"></i> </a>
                        <a href="#" id="unload-advised" class="btn btn-default  btn-flat btn-block"><i class="ion ion-arrow-left-c"></i> Remove</a>
                        <a href="#" id="save-advised" class="btn btn-default  btn-flat btn-block">Save</a>
                        
                    </div>
                    <div class="col-md-5">
                        <h4>Enlisted Subjects</h4>
                        <select style="height:300px" class="form-control" v-model="subject_selector_advised" multiple>
                            <option v-for="sn in advised_subjects" :value="sn.intSubjectID">{{ sn.strCode }}</option>                            
                        </select>
                    </div>
                </div>
            </div>
        </div>    
    </div>
    
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
        id: <?php echo $id; ?>,    
        sem: <?php echo $sem; ?>,
        base_url: '<?php echo base_url(); ?>',
        sy: [],
        student: undefined,
        year: 1,
        academic_standing: undefined,
        active_sem: undefined,
        prev_records: [],
        prev_sem: undefined,
        subjects_not_taken: [],
        advised_subjects: [],
        subject_selector:[],
        subject_selector_advised:[],
    },

    mounted() {

        let url_string = window.location.href;        
        if(this.id != 0){            
            //this.loader_spinner = true;
            axios.get(this.base_url + 'department/load_subjects_data/' + this.id + '/' + this.sem)
                .then((data) => {                                          
                    this.sy = data.data.sy;     
                    this.sem =  data.data.active_sem.intID;
                    this.student  = data.data.student;
                    this.academic_standing =  data.data.academic_standing;
                    this.active_sem = data.data.active_sem;
                    this.prev_sem = data.data.prev_sem;
                    this.prev_records = data.data.prev_records;
                    this.subjects_not_taken = data.data.subjects_not_taken;
                    this.advised_subjects = data.data.advised_subjects;
                })
                .catch((error) => {
                    console.log(error);
                })
        }

    },

    methods: {      
        selectTerm: function($event){
            document.location = base_url + 'department/load_subjects/' + this.id +'/'+ event.target.value;
        },
        autoload: function(){
            let url = base_url + 'unity/load_advised_subjects/'+this.academic_standing.status;
            var formdata= new FormData();
            formdata.append("year",this.year);
            formdata.append("sem",this.sem);
            formdata.append("sid",this.student.intID);
            formdata.append("cid",this.student.intCurriculumID);                                                            
            axios.post(url,formdata)
            .then((data) => {                                          
                this.advised_subjects = data.data;
            })
            .catch((error) => {
                console.log(error);
            })
        }
    }

})
</script>

