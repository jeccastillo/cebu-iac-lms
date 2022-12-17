<aside class="right-side">
    <div id="registration-container">
        <section class="content-header">
            <h1>
                <small>
                    <a class="btn btn-app" href="<?php echo base_url() ?>student/view_all_students" ><i class="ion ion-arrow-left-a"></i>All Students</a> 
                                    <a class="btn btn-app trash-student-record2" rel="<?php echo $student['intID']; ?>" href="#"><i class="ion ion-android-close"></i> Delete</a>   
                                    <a class="btn btn-app" href="<?php echo base_url()."student/edit_student/".$student['intID']; ?>"><i class="ion ion-edit"></i> Edit</a> 
                                    <a class="btn btn-app" href="<?php echo base_url()."pdf/student_viewer_registration_print/".$student['intID'] ."/". $active_sem['intID']; ?>">
                                        <i class="ion ion-printer"></i>Reg Form Print Preview</a> 
                                    
                                    
                </small>
            </h1>
            <div v-if="registration" class="pull-right">
                
                <label style="font-size:.6em;"> Registration Status</label>
                    
                <select v-model="registration_status" @change="changeRegStatus" class="form-control">
                    <option value="0">Registered</option>
                    <option value="1">Enrolled</option>
                    <option value="2">Cleared</option>
                </select>
                
            </div>
            <hr />
        </section>
            <hr />
        <div class="content">
            <div class="row">
                <div class="col-sm-12">
                    <div class="box box-widget widget-user-2">
                    <!-- Add the bg color to the header using any of the bg-* classes -->
                    <div class="widget-user-header bg-red">
                        <!-- /.widget-user-image -->
                        <h3 class="widget-user-username" style="text-transform:capitalize;margin-left:0;font-size:1.3em;"><?php echo strtolower($student['strLastname'].", ". $student['strFirstname']); ?>
                                <?php echo ($student['strMiddlename'] != "")?' '.strtolower($student['strMiddlename']):''; ?></h3>
                        <h5 class="widget-user-desc" style="margin-left:0;"><?php echo $student['strProgramCode']." Major in ".$student['strMajor']; ?></h5>
                    </div>
                    <div class="box-footer no-padding">
                        <ul class="nav nav-stacked">
                        <li><a href="#" style="font-size:13px;">Student Number <span class="pull-right text-blue"><?php echo $student['strStudentNumber']; ?></span></a></li>
                        <li><a style="font-size:13px;" href="#">Registration Status <span class="pull-right"><?php echo $reg_status; ?></span></a></li>
                        <li><a style="font-size:13px;" href="#">Date Registered <span class="pull-right"><?php echo ($registration)?'<span style="color:#009000;">'.$registration['dteRegistered'].'</span>':'<span style="color:#900000;">N/A</span>'; ?></span></a></li>
                            <li><a style="font-size:13px;" href="#">Scholarship Type <span class="pull-right"><?php echo $registration['enumScholarship']; ?></span></a></li>
                            
                        </ul>
                    </div>
                    </div>
                    
                </div>
                

                <div class="col-sm-12">
                    <div class="nav-tabs-custom">
                        <ul class="nav nav-tabs">
                        <li><a href="<?php echo base_url(); ?>unity/student_viewer/<?php echo $student['intID']; ?>/<?php echo $selected_ay; ?>/tab_1">Personal Information</a></li>
                        <?php if(in_array($user['intUserLevel'],array(2,4)) ): ?>
                            <li><a href="<?php echo base_url(); ?>unity/student_viewer/<?php echo $student['intID']; ?>/<?php echo $selected_ay; ?>/tab_2">Report of Grades</a></li>
                        <li><a href="<?php echo base_url(); ?>unity/student_viewer/<?php echo $student['intID']; ?>/<?php echo $selected_ay; ?>/tab_3">Assessment</a></li>
                        <?php endif; ?>
                        <li><a href="<?php echo base_url(); ?>unity/student_viewer/<?php echo $student['intID']; ?>/<?php echo $selected_ay; ?>/tab_5">Schedule</a></li>
                        <li class="active"><a href="#tab_1" data-toggle="tab">Statement of Account</a></li>
                        <li><a href="<?php echo base_url()."unity/accounting/".$student['intID']; ?>">Accounting Summary</a></li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane active" id="tab_1">    
                                <div class="box box-solid">
                                    <div class="box-header">
                                        <h4 class="box-title">ASSESSMENT OF FEES</h4>
                                    </div>
                                    <input type="hidden" id="intAYID" value="<?php echo $selected_ay; ?>">
                                    <div class="box-body">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <?php echo $tuition; ?>
                                            </div>                                    
                                        </div>
                                    </div>
                                </div>              
                            </div>        
                        </div>
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
        id: '<?php echo $id; ?>',    
        sem: '<?php echo $selected_ay; ?>',
        student_data:{},    
        request: {
            enumScholarship: 0,
            enumStudentType: 'new',
            enumRegistrationStatus: 'regular',
            strAcademicYear: undefined,
        },
        registration: {},
        registration_status: 0,
        
        loader_spinner: true,                        
    },

    mounted() {

        let url_string = window.location.href;        
        if(this.id != 0){            
            //this.loader_spinner = true;
            axios.get('<?php echo base_url(); ?>unity/registration_viewer_data/' + this.id + '/' + this.sem)
                .then((data) => {                                                                         
                    this.registration = data.data.registration;            
                    this.registration_status = data.data.registration.intROG;
                    this.loader_spinner = false;                    
                })
                .catch((error) => {
                    console.log(error);
                })
        }

    },

    methods: {        
        changeRegStatus: function(){
            let url = '<?php echo base_url(); ?>unity/update_rog_status';
            var formdata= new FormData();
            formdata.append("intRegistrationID",this.registration.intRegistrationID);
            formdata.append("intROG",this.registration_status);
            this.loader_spinner = true;
            axios.post(url, formdata, {
                headers: {
                    Authorization: `Bearer ${window.token}`
                }
            })
            .then(data => {
                this.loader_spinner = false;
                Swal.fire({
                    title: "Success",
                    text: data.data.message,
                    icon: "success"
                }).then(function() {
                    
                });
            });
            
            
        }
    }

})
</script>

