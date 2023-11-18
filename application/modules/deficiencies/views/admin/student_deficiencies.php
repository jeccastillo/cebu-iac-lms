<aside class="right-side" id="registration-container">    
<section class="content-header">
        <h1>
            Student Deficiencies
            <small>
                <a class="btn btn-app" :href="base_url + 'unity/student_viewer/' + student.intID"><i class="ion ion-arrow-left-a"></i>All Details</a>                 
            </small>
        </h1>
        <hr />
    </section>
        <hr />
    <div class="content">  
        <div class="box box-primary">
            <div class="box-header">
                <h4>{{ student.strLastname + " " + student.strFirstname }}</h4>
                <h5>{{ student.strStudentNumber.replace(/-/g, "") }}</h5>
            </div>
            <div class="box-body">
                <div class="row" style="margin-bottom:10px">                    
                    <div class="col-sm-4">
                        <label>Select Term</label>
                        <select class="form-control" @change="selectTerm($event)" v-model="sem">
                            <option v-for="term in terms" :value="term.intID">{{ term.enumSem + " " + term.term_label + " SY " + term.strYearStart + "-" + term.strYearEnd }}</option>
                        </select>
                    </div>
                </div>                
                <hr />
                <h4>Add Deficiency</h4>
                <form method="post" @submit.prevent="submitDeficiency">
                    <div class="row">
                        <div class="col-sm-6 form-group">
                            <label>Deficiency Details</label>
                            <input type="text" required class="form-control" v-model="request.details">                                                                        
                        </div>
                        <div class="col-sm-6 form-group">
                            <label>Remarks</label>
                            <textarea required class="form-control" v-model="request.remarks"></textarea>
                        </div>
                    </div>  
                    <hr />
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
                <hr />
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Term</th>
                            <th>Deficiency</th>
                            <th>Department</th>
                            <th>Remarks</th>
                            <th>Date Added</th>
                            <th>Added By</th>
                            <th>Date Resolved</th>
                            <th>Resolved By</th>
                            <th>Status</th>                             
                            <th>Actions</th>                                                       
                        </tr>
                    </thead>
                    
                </table>                              
            </div>        
        </div>
        
    </div>
    <div class="modal fade" id="temporaryResolve" role="dialog">
        <form ref="temp_resolve" @submit.prevent="tempResolveDeficiency" method="post"  class="modal-dialog modal-lg">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <!-- modal header  -->
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Credit Subject</h4>
                </div>
                <div class="modal-body">
                    <div class="row">                        
                        <div class="form-group col-sm-12">
                            <label>Enter End Date of Temporary Resolution for this deficiency</label>
                            <input required v-model="temp_resolve_date" type="date" class="form-control">
                        </div>                                               
                    </div>
                </div>
                <div class=" modal-footer">
                    <!-- modal footer  -->
                    <button type="submit" class="btn btn-primary">Submit</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>

        </form>
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
        active_sem: undefined,      
        deficiencies:[],              
        terms: [],    
        temp_resolve_id: undefined,
        temp_resolve_date: undefined,
        user: undefined,
        student: {
            strStudentNumber:'aaa-aaaa-aaa'
        },
        department: undefined,
        request:{
            added_by: undefined,
            details: undefined,
            department: undefined,
            remarks: undefined,
            syid: <?php echo $sem; ?>, 
            student_id: <?php echo $id; ?>,           
            status: 'active',
        }  
        
    },

    mounted() {

        let url_string = window.location.href;        
        if(this.id != 0){            
            //this.loader_spinner = true;
            axios.get(this.base_url + 'deficiencies/student_deficiencies_data/'+this.sem+'/'+this.id)
                .then((data) => {                                      
                    this.terms = data.data.sy;                                        
                    this.sem = data.data.active_sem.intID;                     
                    this.active_sem = data.data.active_sem;
                    this.student = data.data.student;
                    this.deficiencies = data.data.deficiencies;
                    this.department = data.data.department;
                    this.request.department = data.data.department;
                    this.request.added_by = data.data.name;
                    this.request.syid = this.sem;
                    this.user = data.data.user;
                    const date1 = new Date();

                    for(i in this.deficiencies){
                        if(this.deficiencies[i].temporary_resolve_date){                            
                            const date2 = new Date(this.deficiencies[i].temporary_resolve_date);                            
                            if(date1.getTime() < date2.getTime()){                                
                                this.deficiencies[i].status = "Temporarily Resolved until " + this.deficiencies[i].temporary_resolve_date;
                            }
                        }
                    }
                })
            .catch((error) => {
                console.log(error);
                
            });
        }

    },

    methods: {      
        selectTerm: function(event){
            document.location = base_url + 'deficiencies/student_deficiencies/'+this.student.intID+'/'+event.target.value;

        },
        setResolveID: function(id){
            this.temp_resolve_id = id;
            console.log(this.temp_resolve_id);
        },
        submitDeficiency: function(){            
            Swal.fire({
                title: 'Add Deficiency?',
                text: "Continue adding deficiency?",
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
                        .post('<?php echo base_url(); ?>deficiencies/add_deficiency',formdata, {
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
        tempResolveDeficiency: function(){
            var id = this.temp_resolve_id;
            console.log(id);
            Swal.fire({                
                title: 'Temporary Resolve Deficiency?',
                text: "Continue resolving deficiency? Once resolved you can not change the status back to active.",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                preConfirm: (inputData) => {
                    var formdata= new FormData();
                    formdata.append('id',id);     
                    formdata.append('temporary_resolve_date',this.temp_resolve_date);                       
                    return axios
                        .post('<?php echo base_url(); ?>deficiencies/temp_resolve_deficiency',formdata, {
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
        resolveDeficiency: function(id){
            Swal.fire({
                title: 'Resolve Deficiency?',
                text: "Continue resolving deficiency? Once resolved you can not change the status back to active.",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                preConfirm: (login) => {
                    var formdata= new FormData();
                    formdata.append('id',id);                                                                                  
                    formdata.append('resolved_by',this.request.added_by);   
                    formdata.append('status','resolved');   
                    return axios
                        .post('<?php echo base_url(); ?>deficiencies/resolve_deficiency',formdata, {
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
        }
       
                                       
    }

})
</script>

