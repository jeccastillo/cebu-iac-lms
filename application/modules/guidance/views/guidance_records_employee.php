<aside class="right-side" id="registration-container">    
<section class="content-header">
        <h1>
            Employee Guidance Records
        </h1>
        <hr />
    </section>
        <hr />
    <div class="content">  
        <div class="box box-primary">
            <div class="box-header">
                <h4>{{ employee.strLastname + " " + employee.strFirstname }}</h4>                
            </div>
            <div class="box-body">
                <h4>Guidance Records</h4>                                
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Consultation Type</th>
                            <th>Chief Complaint/Reason for the Visit</th>
                            <th>History of Present Illness</th>      
                            <th>Actions</th>                                                
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="guidance_records.length == 0">
                            <td colspan='8'>No Records Found</td>
                        </tr>
                        <tr v-else v-for="item in guidance_records">
                            <td>{{ item.consultation_date }}</td>
                            <td>{{ item.consultation_type }}</td>
                            <td>{{ item.chief_complaint }}</td>
                            <td>{{ item.history }}</td>
                            <td><button class="btn btn-danger" @click="deleteHealthRecord(item.id)">Delete</button></td>
                                                                                    
                        </tr>
                    </tbody>
                </table>      
                <hr />
                <a class="btn btn-success" href="#" data-toggle="modal" data-target="#record">Add Health Record</a>                        
            </div>        
        </div>
        
    </div>
    <div class="modal modal-md fade" id="record" role="dialog">         
        <div class="modal-content container">
            <form method="post" @submit.prevent="submitHealthRecord">
                <div class="modal-header">        
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Add Health Record</h4>
                </div>
                <div class="modal-body">                                
                    <div class="row">                        
                        <div class="col-sm-12 form-group">
                            <label>Kind of Consultation</label>
                            <select required class="form-control" v-model="request.consultation_type">
                                <option value="Face to face">Face to face</option>
                                <option value="Teleconsultation">Teleconsultation</option>
                            </select>
                        </div>
                        <div class="col-sm-12 form-group">
                            <label>Classification of Patient</label>
                            <input type="hidden" v-model="request.classification"/>
                            {{ stype }}
                        </div>
                        <div class="col-sm-12 form-group">
                            <label>Last Name</label>
                            <input type="hidden" v-model="request.last_name"  />
                            {{ employee.strLastname }}
                        </div>
                        <div class="col-sm-12 form-group">
                            <label>Firstname</label>
                            <input type="hidden" v-model="request.first_name"  />
                            {{ employee.strFirstname }}
                        </div>
                        <div class="col-sm-12 form-group">
                            <label>Chief Complaint/Reason for the Visit</label>
                            <textarea required class="form-control" v-model="request.chief_complaint"></textarea>
                        </div>
                        <div class="col-sm-12 form-group">
                            <label>History of Present Illness</label>
                            <textarea required class="form-control" v-model="request.history"></textarea>
                        </div>
                    </div>                                         
                </div>
                <div class=" modal-footer">        
                    <button type="submit" class="btn btn-primary">Submit</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </form>
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
        base_url: '<?php echo base_url(); ?>',    
        id: '<?php echo $id; ?>',
        guidance_records:[],
        employee: undefined,
        current_record: undefined,
        stype: undefined,
        request:{
            patient_id: <?php echo $id; ?>,
            consultation_type: undefined,
            last_name: undefined,
            first_name: undefined,
            classification: undefined,            
            chief_complaint: undefined,
            history: undefined,
        }
            
    },

    mounted() {

        let url_string = window.location.href;        
        if(this.id != 0){            
            //this.loader_spinner = true;
            axios.get(this.base_url + 'guidance/guidance_records_employee_data/'+this.id)
                .then((data) => {                                      
                    this.employee = data.data.employee
                    this.guidance_records = data.data.guidance_records;
                    this.stype = data.data.stype;
                    this.request.classification = this.stype;
                    this.request.last_name = this.employee.strLastname;
                    this.request.first_name = this.employee.strFirstname;
                })
            .catch((error) => {
                console.log(error);
                
            });
        }

    },

    methods: {      
        submitHealthRecord: function(){            
            Swal.fire({
                title: 'Add Guidance Record?',
                text: "Continue adding guidance record?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                preConfirm: (login) => {
                    var formdata= new FormData();
                    for (const [key, value] of Object.entries(this.request)) {
                        formdata.append(key,value);
                    }                                                              
                    return axios
                        .post('<?php echo base_url(); ?>guidance/add_guidance_record',formdata, {
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
        deleteHealthRecord: function(id){            
            Swal.fire({
                title: 'Delete Guidance Record?',
                text: "Continue deleting guidance record?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "warning",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                preConfirm: (login) => {
                    var formdata= new FormData();
                    formdata.append('id',id);                                                                                  
                    return axios
                        .post('<?php echo base_url(); ?>guidance/delete_guidance_record',formdata, {
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


