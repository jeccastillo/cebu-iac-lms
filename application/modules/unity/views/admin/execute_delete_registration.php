<aside id="registration-container" class="right-side">
<section class="content-header">
                    <h1>
                        Reset Student Status
                        <small></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i>Registration</a></li>
                        <li class="active">Delete Registration</li>
                    </ol>
                </section>
<div class="content">
    <div class="span10 box box-primary">
        <div class="box-header">
            <h3 class="box-title">Reset Status</h3>
            <hr />
            <p>Name: <?php echo $student['strLastname'].", ".$student['strFirstname'].", ".$student['strMiddlename']; ?></p>
            <p>Student Number: <?php echo $student['strStudentNumber']; ?></p>
        </div>
       
            
            <form method="post" role="form">
                <div class="box-body">                         
                        <input type="hidden" v-model="student_id" name="studentid" class="form-control">
                        <input type="hidden" v-model="term" name="sem" class="form-control">
                        <div class="alert alert-warning alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <h4><i class="icon fa fa-warning"></i> Alert!</h4>
                            Warning This will delete registration data and all records from advising and classlist.
                            </div>
                        <div class="form-group col-xs-12">
                            <input type="submit" @click.prevent="deleteRegistration" value="Execute" class="btn btn-default  btn-flat">
                        </div>
                    <div style="clear:both"></div>
                </div>
            </form>
    </div>
    </div>
</aside>

<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
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
        term: "<?php echo $sem; ?>",
        student_id: <?php echo $student['intID']; ?>,    
        slug: "<?php echo $student['slug']; ?>"
    },

    mounted() {


    },

    methods: {      
        deleteRegistration: function(payment){        
            let url = base_url + "unity/delete_registration_confirm";
            Swal.fire({
                title: 'Continue with Reset',
                text: "Are you sure you want to continue?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                    preConfirm: (data) => {    
                        var formdata= new FormData();
                        formdata.append('studentid',this.student_id);
                        formdata.append('sem',this.term);                                    
                        return axios.post(url, formdata, {
                                    headers: {
                                        Authorization: `Bearer ${window.token}`
                                    }
                                })
                                .then(data => {
                                    
                                    axios.post(api_url + 'admissions/student-info/' + this.slug +
                                    '/update-status', {
                                        status: "Confirmed",
                                        remarks: "Automated Change Reset Enrollment",
                                        admissions_officer: "<?php echo $user['strFirstname'] . '  ' . $user['strLastname'] ; ?>"
                                    }, {
                                        headers: {
                                            Authorization: `Bearer ${window.token}`
                                        }
                                    })
                                    .then(function(data){
                                        Swal.fire({
                                            title: "Success",
                                            text: data.data.message,
                                            icon: "success"
                                        }).then(function() {
                                            document.location = base_url +"student/view_all_students";
                                        });                                                                                                                              

                                            
                                    });                                        
                                                                       
                                });                             
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                               
                    
            });  
        },
        
    }

})
</script>
