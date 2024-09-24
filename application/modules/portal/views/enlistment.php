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
                                        
                    <h4>Add Subject for Enlistment</h4>
                    <div class="row">
                        <div class="col-sm-6">
                            <select v-model="selected_subject" class="form-control">
                                <option v-for="subject in sortedSubjectsArray" :value="subject.intID">
                                    {{ subject.strCode + " " + subject.strClassName + subject.year + subject.strSection + subject.sub_section + " " + subject.sched_room + " " + subject.sched_day + " " + subject.sched_time }}
                                </option>
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <button @click="addSubjectForEnlistment" :disabled="selected_subject == undefined" class="btn btn-primary">Add</button>
                        </div>
                    </div>
                    <hr />
                    <h4>Subjects To Enlist</h4>
                    <div class="row">
                        <div class="col-sm-12">
                            <table v-if="selected_subjects.length > 0" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Subject</th>
                                        <th>Section</th>
                                        <th>Schedule</th>
                                        <th>Units</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="subject in selected_subjects">
                                        <td>{{ subject.strCode }}</td>
                                        <td>{{ subject.strClassName + subject.year + subject.strSection + subject.sub_section + subject.sub_section }}</td>
                                        <td>{{ subject.sched_room + " " + subject.sched_day + " " + subject.sched_time }}</td>
                                        <td>{{ subject.strUnits }}</td>
                                        <td><button @click="removeSubjectForEnlistment(subject.intID)" class="btn btn-danger">Remove</button></td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td class="text-right">Total Additional Units:</td>
                                        <td>{{ additional_units }}</td>
                                        <td></td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td colspan="2"><button class="btn btn-primary">Submit Subjects</button></td>
                                    </tr>
                                </tfoot>
                            </table>
                            <div v-else class="box">
                                <div class="box-body">
                                    <h4>No Subjects Selected</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <h4>Currently Enlisted Subjects</h4>
                    <div class="row">
                        <div class="col-sm-12">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Subject</th>
                                        <th>Section</th>
                                        <th>Schedule</th>                                        
                                        <th>Units</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="subject in my_classlists">
                                        <td>{{ subject.strCode }}</td>
                                        <td>{{ subject.strClassName + subject.year + subject.strSection + subject.sub_section + subject.sub_section }}</td>
                                        <td>{{ subject.sched_room + " " + subject.sched_day + " " + subject.sched_time }}</td>                                        
                                        <td>{{ subject.strUnits }}</td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td class="text-right">Total Units:</td>
                                        <td>{{ total_units }}</td>
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
        id: '<?php echo $id; ?>',
        sem: '<?php echo $sem; ?>', 
        sy: [],
        available_subjects: [],
        selected_subject: undefined,
        selected_subjects: [],
        my_classlists: [],
        total_units: 0,
        additional_units: 0,
        student: {
            strFirstname:'',
            strLastname:'',
            strMiddlename:'',
            strProgramDescription: '',
            strMajor:'',

        },       
    },
    computed: {
        sortedSubjectsArray() {
            let sortedSubjects = this.available_subjects;
            
            sortedSubjects = sortedSubjects.sort((a,b) => {
                let fa = a.strCode.toLowerCase(), fb = b.strCode.toLowerCase();
                if (fa < fb) {
                    return -1
                }
                if (fa > fb) {
                    return 1
                }
                return 0
            })

            return sortedSubjects
        }
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
                this.sem = data.data.active_sem.intID;   
                this.available_subjects = data.data.subject_offerings;  
                this.my_classlists = data.data.my_classlists;   
                this.total_units = data.data.total_units;                              
            });

   


    },

    methods: {            
        selectTerm: function($event){
            document.location = base_url + 'portal/enlistment/' + event.target.value;
        },
        addSubjectForEnlistment: function(){
            var formdata= new FormData();
            formdata.append('student',this.id);
            formdata.append('sem',this.sem);
            formdata.append('section_to_add',this.selected_subject);
            formdata.append('sections_to_add',JSON.stringify(this.selected_subjects));
            axios
            .post(base_url + 'portal/check_conflict/', formdata, {
                headers: {
                    Authorization: `Bearer ${window.token}`
                },
            })
            .then((data) => { 
                if(data.data.success){
                    let id = this.selected_subject;
                    let i = this.available_subjects.map(item => item.intID).indexOf(id) // find index of your object
                    this.selected_subjects.push(this.available_subjects[i]);
                    this.additional_units += parseInt(this.available_subjects[i].strUnits);
                    this.available_subjects.splice(i, 1) // remove it from array
                    this.selected_subject = undefined;            
                }
                else{
                    Swal.fire({
                        title: "Failed",
                        text: data.data
                            .message,
                        icon: "failed"
                    })
                }
            });

            
        },
        removeSubjectForEnlistment: function(id){            
            let i = this.selected_subjects.map(item => item.intID).indexOf(id) // find index of your object            
            this.available_subjects.push(this.selected_subjects[i]);
            this.additional_units -= parseInt(this.selected_subjects[i].strUnits);
            this.selected_subjects.splice(i, 1) // remove it from array
        }
    }

})
</script>


