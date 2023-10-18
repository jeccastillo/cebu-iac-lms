<aside class="right-side" id="registration-container">    
    <section class="content-header">
        <h1>
            Student Grade Slip
            <small>
                <!-- <a class="btn btn-app" href="<?php echo base_url(); ?>registrar/registrar_reports" >
                    <i class="ion ion-arrow-left-a"></i>
                    All Reports
                </a>  -->
                <!-- <a class="btn btn-app" target="_blank" href="<?php echo $pdf_link; ?>" ><i class="fa fa-book"></i>Generate PDF</a> 
                <a class="btn btn-app" target="_blank" href="<?php echo $excel_link; ?>" ><i class="fa fa-book"></i>Generate Excel</a>  -->
            </small>
        </h1>     
    </section>
        <hr />
    <div class="content">  
        <div class="box box-primary">
            <div class="box-header">
                <h4>Student Grade Slip</h4>
            </div>
            <div class="box-body">
                <div class="row" style="margin-bottom:10px">                    
                    <div class="col-sm-4">
                        <label>Select Term</label>
                        <select class="form-control" required @change="selectTerm($event)" v-model="sem">
                            <option v-for="term in terms" :value="term.intID">{{ term.enumSem + " " + term.term_label + " SY " + term.strYearStart + "-" + term.strYearEnd }}</option>
                        </select>
                    </div>
                </div>
                <hr />
                <div class="row">
                    <div class="col-sm-4">
                        Student Number:<br />
                        {{ student.strStudentNumber }}
                    </div>
                    <div class="col-sm-4">
                        Name:<br />
                        {{ student.strLastname+", "+student.strFirstname }}
                    </div>
                    <div class="col-sm-4">
                        Course:<br />
                        {{ student.strProgramDescription }}
                    </div>
                </div>
                <hr />
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Course Code</th>
                            <th>Descriptive Title</th>
                            <th>Units</th>
                            <th>Midterm Grade</th>
                            <th>Final Grade</th>
                            <th>Units Earned</th>
                        </tr>                        
                    </thead>
                    <tbody>
                        <tr v-for="item in records">
                            <td>{{ item.strCode }}</td>
                            <td>{{ item.strDescription }}</td>
                            <td>{{ item.strUnits }}</td>
                            <td v-if="item.intFinalized >=1">{{ item.v2 }}</td>
                            <td v-else>NGS</td>
                            <td v-if="item.intFinalized >=2">{{ item.v3 }}</td>
                            <td v-else>NGS</td>
                            <td v-if="item.strRemarks =='Passed'">{{ item.strUnits }}</td>
                            <td v-else>0</td>
                        </tr>
                    </tbody>
                </table>
                <hr />
                <a target="_blank" href="#" @click.prevent="printGradeSlip('midterm')" class="btn btn-app"><i class="fa fa-print"></i> Print Midterm</a>
                <a target="_blank" href="#" @click.prevent="printGradeSlip('final')" class="btn btn-app"><i class="fa fa-print"></i> Print Final</a>
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
        base_url: '<?php echo base_url(); ?>',
        sem: '<?php echo $sem; ?>',        
        id: '<?php echo $id; ?>',    
        student: undefined,      
        registration: undefined,      
        terms: [],     
        records:[],   
        deficiencies: [],              
    },

    mounted() {

        let url_string = window.location.href;        
        if(this.id != 0){            
            //this.loader_spinner = true;
            axios.get(this.base_url + 'registrar/student_grade_slip_data/'+this.id+'/'+this.sem)
                .then((data) => {  
                  this.student = data.data.student;
                  this.registration = data.data.registration;
                  this.terms = data.data.sy;   
                  this.records = data.data.class_data;   
                  this.deficiencies = data.data.deficiencies;             
                })
            .catch((error) => {
                console.log(error);
                
            });
        }

    },

    methods: {      
        selectTerm: function(event){
            document.location = base_url + 'registrar/student_grade_slip/'+this.id+'/'+event.target.value;

        },
        printGradeSlip: function(type){    
            if(type == "midterm")
                var url = base_url + 'pdf/student_grade_slip/'+this.id+'/'+this.sem;
            else
                var url = base_url + 'pdf/student_grade_slip/'+this.id+'/'+this.sem+'/final';
            
            if(this.deficiencies.length > 0){                                
                Swal.fire({
                    icon: 'error',
                    title: 'Warning',
                    text: 'Failed to generate due to deficiencies',
                    cancelButtonText:'Close',
                    footer: '<a href="'+base_url + 'deficiencies/student_deficiencies/' + this.student.intID+'">View Deficiencies</a>'
                })                
            }
            else
                window.open(
                    url,
                    '_blank' // <- This is what makes it open in a new window.
                );
                    
        }
                                       
    }

})
</script>

