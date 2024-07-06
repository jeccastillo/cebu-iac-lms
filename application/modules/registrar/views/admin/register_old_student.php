<aside class="right-side" id="registration-container">    
    <section class="content-header">
        <h1>
            Registrar
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i>Registrar</a></li>
            <li class="active">Student Fee Assessment</li>
        </ol>
    </section>
    <div class="content">
        <div class="span10 box box-primary">
            <div class="box-header">
                <h3 class="box-title">Fee Assessment</h3>                                        
            </div>
        
            <div class="box box-solid">
                <div v-if="reg_status == 'For Registration'" class="box-body">                                                
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Student Number</th>
                                <th>Lastname</th>
                                <th>Firstname</th>
                                <th>Middlename</th>
                                <th>Course</th>
                                <th>Registration Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ student_data.strStudentNumber }}</td>
                                <td>{{ student_data.strLastname }}</td>
                                <td>{{ student_data.strFirstname }}</td>
                                <td>{{ student_data.strMiddlename }}</td>
                                <td>{{ student_data.strProgramCode }}</td>
                                <td>{{ reg_status }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <hr />
                    <form id="validate-student" @submit.prevent="submitRegistration" method="post" role="form" enctype="multipart/form-data">                
                        <input type="hidden" name="studentID" v-model="id" />                        
                        <div style="border:1px solid #d2d2d2;">
                            <div class="col-sm-8" style="padding:1rem;background:#f2f2f2;">
                            <h3>Registration Details</h3>
                            
                            Select Term
                            <select id="strAcademicYear" name="strAcademicYear" class="form-control" v-model="request.strAcademicYear">
                                <option v-for="sy in school_years" :value="sy.intID">{{sy.term_student_type + ' ' + sy.enumSem + ' ' + sy.term_label + ' ' + sy.strYearStart + '-' + sy.strYearEnd}}</option>                            
                            </select>
                                <hr />
                            <label for="enumRegistrationStatus">Academic Status</label>
                                <select id="enumRegistrationStatus" name="enumRegistrationStatus" class="form-control" v-model="request.enumRegistrationStatus">                        
                                    <option value="regular">Regular</option>
                                    <option value="irregular">Irregular</option>
                                </select>
                                <br />                                                    
                                <label for="enumStudentType">Student Type</label>
                                <select id="enumStudentType" class="form-control" name="enumStudentType" v-model="request.enumStudentType">                        
                                    <option value="new">New</option>
                                    <option value="continuing">Continuing</option>
                                    <option value="shiftee">Shiftee</option>                                    
                                    <option value="returning">Returning</option>
                                </select>
                                <br />       
                                <label for="intYearLevel">Year Level</label>
                                <select id="intYearLevel" name="intYearLevel" class="form-control" v-model="request.intYearLevel">                        
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                    <option value="6">6</option>
                                </select>
                                <br />                                                                    
                                <label for="type_of_class">Class Type</label>
                                <select id="type_of_class" class="form-control" name="enumStudentType" v-model="request.type_of_class">                        
                                    <option value="regular">Regular</option>
                                    <option value="online">Online</option>
                                    <option value="hyflex">Hyflex</option>
                                    <option value="hybrid">Hybrid</option>                                    
                                </select>
                                <br />                    
                                <label for="enumScholarship">Scholarship Grant</label><br />
                                <input type="hidden" model="request.enumScholarship">
                                {{ scholarship.intID != 0?scholarship.name:'None' }}
                                    <br />
                                <hr />
                                
                            <div id="regular-option" class="row">
                                    <div class="col-sm-12" style="margin-bottom:1rem">
                                    <a href="#" @click.prevent.stop='loadSubjects' class="btn btn-default  btn-flat">Check Classlists Enlisted and Assess Fees <i class="fa fa-arrow-circle-down"></i></a>
                                        </div>
                                </div>
                                <hr />
                                <div v-html="subjectList" id="subject-list">                        
                                </div>
                                </div>
                                <div class="col-sm-4" style="padding:1rem;">                                
                                <h3>Accounting</h3>                                
                                <div v-html="tuition_text" id="tuitionContainer">
                                
                                </div>
                                <input type="submit" :disabled="reg_status != 'For Registration' || !subjects_loaded || loader_spinner" value="Submit" class="btn btn-default  btn-flat btn-block">
                            </div>                             
                        </div>
                    </form>                           
                </div>
                <div v-else>
                    <h1>Not For Registration</h1>
                </div>
            </div>
        </div>    
    </div>
</aside>

<script src="<?php echo base_url(); ?>assets/themes/default/js/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script src="<?php echo base_url(); ?>assets/themes/default/js/vue.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
    integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="<?php echo base_url(); ?>assets/themes/default/js/axios.min.js"></script>

<script>
new Vue({
    el: '#registration-container',
    data: {
        id: '<?php echo $id; ?>',    
        sem: '<?php echo $sem; ?>',  
        student_data:{},    
        request: {
            studentID: '<?php echo $id; ?>',
            enumScholarship: 0,
            enumStudentType: undefined,
            enumRegistrationStatus: 'regular',
            strAcademicYear: undefined,
            type_of_class: 'regular',
            intYearLevel: 1,
        },
        scholarship: {
            intID: 0
        },
        school_years: [],
        term_type: 'Term',
        tuition_data: undefined,
        applicant_data: undefined,
        reservation_payment_amount: 0,
        reservation_or_number: "",
        total_units: 0,
        prev_registration: undefined,
        subjectList: '',
        reg_status: null,
        subjects_loaded: false,
        total_tuition: 0,
        tuition_text: '',
        subject_ids:[],
        misc: {
            name: undefined,
            miscRegular: undefined,            
            miscHybrid: undefined,
            miscOnline: undefined,
            miscHyflex: undefined,  
            type: 'regular',    
        },
        lab: {
            name: undefined,
            labRegular: undefined,
            labHybrid: undefined,
            labOnline: undefined,
            labHyflex: undefined,      
        },        
        update_text: "Tuition Year",
        loader_spinner: true,                        
    },

    mounted() {

        let url_string = window.location.href;
        let url = new URL(url_string);

        if(this.id != 0){
        
            this.header_title = 'Edit Tuition Year';
           

            axios.get('<?php echo base_url(); ?>registrar/register_old_student_data/' + this.id + '/' +this.sem)
                .then((data) => {                    
                    //this.request = data.data.data;                                        
                    
                    if(data.data.data.scholarship)
                        this.scholarship = data.data.data.scholarship;                                                
                    else
                        this.scholarship.intID = 0;

                    this.request.enumScholarship = this.scholarship.intID;
                    this.prev_registration = data.data.data.prev_reg;
                    if(this.prev_registration){
                        if(this.prev_registration.shifted_program)
                            this.request.enumStudentType = "shiftee";
                        else
                            this.request.enumStudentType = "continuing";

                        console.log(this.request.enumStudentType);
                    }
                    else
                        this.request.enumStudentType = "new";

                    

                    this.term_type = data.data.data.term_type;
                    this.school_years = data.data.data.sy;
                    this.request.strAcademicYear = data.data.data.active_sem.intID;
                    this.reg_status = data.data.data.reg_status;
                    this.student_data = data.data.data.student;
                    

                     //this.loader_spinner = true;
                    axios.get(api_url + 'admissions/student-info/' + this.student_data.slug)
                    .then((data) => {
                        this.applicant_data = data.data.data;
                        for(i in this.applicant_data.payments){
                            if(this.applicant_data.payments[i].description == "Reservation Payment" && this.applicant_data.payments[i].sy_reference == this.sem){
                                this.reservation_payment_amount = this.applicant_data.payments[i].subtotal_order;
                                this.reservation_or_number = this.applicant_data.payments[i].or_number;
                            }
                        }
                        this.loader_spinner = false;                                                
                    })
                    .catch((error) => {
                        console.log(error);
                    })
                })
                .catch((error) => {
                    console.log(error);
                })
        }

    },

    methods: {

        loadSubjects: function(){            
            // for(const [key,value] of Object.entries(data)){                   
            //         formdata.append(key,value);
            // }
            var formdata= new FormData();
            formdata.append("intStudentID",this.id);    
            formdata.append("sem",this.request.strAcademicYear);

            axios.post('<?php echo base_url(); ?>unity/load_subjects2', formdata, {
                        headers: {
                            Authorization: `Bearer ${window.token}`
                        }
                    })
                .then(data => {                    
                    var containerText = "";
                    if (data.data.subjects.length > 0) {
                        for (i in data.data.subjects) {
                            this.subject_ids.push(data.data.subjects[i].subjectID);
                            containerText +=
                                "<div><input type='hidden' class='subject-id' name='subjects-loaded[]' value='" +
                                data.data.subjects[i].subjectID +
                                "'><br> <div class='row'><div class='col-xs-3 subject-code'>" +
                                data.data.subjects[i].strCode +
                                "</div><div class='col-xs-3 subject-description'>" + data.data.subjects[i].strDescription +
                                "</div><div class='col-xs-3 subject-units'>" + data.data.subjects[i].strUnits +
                                "</div><div class='col-xs-3'><a class='btn remove-subject-loaded btn-default  btn-flat'>"
                                + "<i class='fa fa-minus'></i></a></div></div><hr /></div>";
                                this.total_units = parseInt(this.total_units) + parseInt(data.data.subjects[i]
                                .strUnits);


                        }
                        this.subjectList = containerText;

                        var formdata= new FormData();
                        formdata.append("studentID",this.id);
                        formdata.append("subjects_loaded",this.subject_ids);    
                        formdata.append("scholarship",this.request.enumScholarship);    
                        formdata.append("stype",this.request.enumStudentType);   
                        formdata.append("type_of_class",this.request.type_of_class);   
                        formdata.append("sem",this.sem);

                        axios.post('<?php echo base_url(); ?>unity/get_tuition_ajax', formdata, {
                            headers: {
                                Authorization: `Bearer ${window.token}`
                            }
                        })
                        .then(data => {                            
                            this.tuition_text = data.data.tuition;  
                            this.tuition_data = data.data.full_data;                            
                            this.subjects_loaded =  true;                          
                            
                        });

                    }
                    
                });

        },
        submitRegistration: function (){
            Swal.fire({
                title: 'Submit Fee Assessment',
                text: "Continue to register student?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,                
                preConfirm: (login) => {
                    this.loader_spinner = true;
                    var formdata= new FormData();                    
                    for(const [key,value] of Object.entries(this.request)){                   
                        formdata.append(key,value);
                    }

                    this.total_tuition = this.tuition_data.total_before_deductions;
                    
                    formdata.append("tuition",this.total_tuition);
                    formdata.append("scholarship_deductions",this.tuition_data.scholarship_deductions);
                    formdata.append("discount_deductions",this.tuition_data.discount_deductions);
                    formdata.append("discount",this.tuition_data.scholarship_discount);
                    formdata.append("reservation_payment_amount", this.reservation_payment_amount);
                    formdata.append("reservation_or_number", this.reservation_or_number);
                    
                    return axios
                        .post('<?php echo base_url(); ?>registrar/submit_registration_old2',formdata, {
                                headers: {
                                    Authorization: `Bearer ${window.token}`
                                }
                            })
                        .then(data => {                                                                                        
                                document.location = student_link;                                        
                        });
                },
                allowOutsideClick: () => !Swal.isLoading()
            });

        },


    }

})
</script>