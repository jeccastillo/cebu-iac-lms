<aside class="right-side" id="registration-container">
<section class="content-header">
                    <h1>
                        Registrar
                        <small></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i>Register</a></li>
                        <li class="active">Register Student</li>
                    </ol>
                </section>
<div class="content">
    <div class="span10 box box-primary">
        <div class="box-header">
                <h3 class="box-title">Register Student</h3>
                
                
        </div>
       
        <div class="box box-solid">
            <div class="box-body">
                <?php if(!empty($reg_status)): ?>
                <?php if($message!=""): ?>
                    <div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <strong>Error</strong> <?php echo $message; ?></div>
                <?php endif; ?>
                <?php //print_r($student); ?>
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
                            <td><?php echo $student['strStudentNumber']; ?></td>
                            <td><?php echo $student['strLastname']; ?></td>
                            <td><?php echo $student['strFirstname']; ?></td>
                            <td><?php echo $student['strMiddlename']; ?></td>
                            <td><?php echo $student['strProgramCode']; ?></td>
                            <td><?php echo $reg_status; ?></td>
                        </tr>
                    </tbody>
                </table>
                <hr />
                <form id="validate-student" action="<?php echo base_url(); ?>registrar/submit_registration_old2" method="post" role="form" enctype="multipart/form-data">
                <input type="hidden" name="intProgramID" value="<?php echo $student['intProgramID']; ?>" id="addStudentCourse">
                <input type="hidden" value="<?php echo $student['intCurriculumID']; ?>" id="intCurriculumID">
                <input type="hidden" value="<?php echo $student['intID']; ?>" name="studentID" id="studentID">
                <input type="hidden" value="<?php echo $student['strStudentNumber']; ?>" name="studentNumber">
                <input type="hidden" value="<?php echo $active_sem['intID']; ?>" name="activeSem" id="activeSem">
                <input type="hidden" value="<?php echo $reg_status; ?>" name="regStatus" id="regStatus">
                <input type="hidden" value="0" id="total-units">
                
                    <div style="border:1px solid #d2d2d2;">
                    <div class="col-sm-8" style="padding:1rem;background:#f2f2f2;">
                    <h3>Registration Details</h3>
                Set Academic Year to Register
                <select class="form-control" id="strAcademicYear" name="strAcademicYear" v-model="request.strAcademicYear">
                    <option v-for="sy in school_years" :value="sy.intID">{{sy.enumSem + ' ' + term_type + ' ' + sy.strYearStart + '-' + sy.strYearEnd}}</option>                            
                </select>
                    <hr />
                <label for="enumRegistrationStatus">Academic Status</label>
                    <select class="form-control" v-model="request.enumRegistrationStatus">                        
                        <option value="regular">Regular</option>
                        <option value="irregular">Irregular</option>
                    </select>
                        <br />
                    <label for="enumScholarship">Scholarship Grant</label>
                    <select class="form-control" v-model="request.enumScholarship">                        
                        <option value="0">None</option>                                                
                        <option v-for="scholarship in scholarships" :value="scholarship.intID">{{scholarship.name}}</option>
                    </select>
                        <br />
                    <label for="enumStudentType">Student Type</label>
                    <select class="form-control" v-model="request.enumStudentType">                        
                         <option value="new">NEW</option>
                         <option value="old">RETURNING</option>
                         <option value="transferee">TRANSFEREE</option>
                        <option value="cross">CROSS REGISTRANT</option>
                    </select>
                    <br />                    
                    
                    <hr />
                    
                <div id="regular-option" class="row">
                        <div class="col-sm-12" style="margin-bottom:1rem">
                        <a href="#" id="load-subjects2" class="btn btn-default  btn-flat">Check Classlists Enlisted and Assess Fees <i class="fa fa-arrow-circle-down"></i></a>
                            </div>
                    </div>
                    <hr />
                    <div id="subject-list">
                    
                    </div>
                    </div>
                    <div class="col-sm-4" style="padding:1rem;">
                    
                        <h3>Accounting</h3>
                    <?php /*
                    <div id="accounting">
                        <div><p><strong>Tuition: </strong><span id="tuition-fee">0</span></p></div>
                        <div><p><strong>Misc: </strong>
                        <span id="misc-fee">
                            <?php echo ($student['enumScholarship'] == "paying")?$misc_fee:'0'; ?>
                        </span>
                        </p></div>
                        <hr />
                        <div><p><strong>Total: </strong><span id="total-fee"></span></p></div>
                    </div>
                    <hr />
                    */ ?>
                    <div id="tuitionContainer">
                    
                    </div>
                    <input type="submit" <?php echo ($reg_status!="For Registration")?'disabled':''; ?> value="Register" class="btn btn-default  btn-flat btn-block">
                    </div>
                    <div style="clear:both"></div>
                </div>
                </form>
                <?php else: ?>
                    <h3>Student Already Registered</h3>
                <?php endif; ?>
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
        id: '<?php echo $student['intID']; ?>',        
        request: {
            enumScholarship: 0,
            enumStudentType: 'new',
            enumRegistrationStatus: 'regular',
            strAcademicYear: undefined,
        },
        scholarships: [],
        school_years: [],
        term_type: 'Term',
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
            //this.loader_spinner = true;
            axios.get('<?php echo base_url(); ?>registrar/register_old_student_data/' + this.id)
                .then((data) => {                    
                    //this.request = data.data.data;                    
                    console.log(data.data.data);
                    this.scholarships = data.data.data.scholarships;
                    this.term_type = data.data.data.term_type;
                    this.school_years = data.data.data.sy;
                    //this.loader_spinner = false;
                })
                .catch((error) => {
                    console.log(error);
                })
        }

    },

    methods: {

        submitRegistration: function (type, name, data){
            Swal.fire({
                title: 'Add New Fee: '+ name,
                text: "Continue adding entry?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                preConfirm: (login) => {
                    var formdata= new FormData();
                    formdata.append("tuitionYearID",this.id);                    
                    for(const [key,value] of Object.entries(data)){                   
                        formdata.append(key,value);
                    }
                    
                    return axios
                        .post('<?php echo base_url(); ?>tuitionyear/submit_extra/'+type,formdata, {
                                headers: {
                                    Authorization: `Bearer ${window.token}`
                                }
                            })
                        .then(data => {
                            console.log(data.data);
                            if (data.data.success) {
                                Swal.fire({
                                    title: "Success",
                                    text: data.data.message,
                                    icon: "success"
                                }).then(function() {
                                    location.reload();
                                });
                            } else {
                                Swal.fire(
                                    'Failed!',
                                    data.data.message,
                                    'error'
                                )
                            }
                        });
                },
                allowOutsideClick: () => !Swal.isLoading()
            });

        },


    }

})
</script>