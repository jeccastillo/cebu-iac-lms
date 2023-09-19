<aside class="right-side" id="registration-container">    
    <section class="content-header">
        <h1>
            Student Deficiencies
            <small>                
            </small>
        </h1>     
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
                    <tbody>
                        <tr v-if="deficiencies.length == 0">
                            <td colspan='8'>No Deficiencies for this term</td>
                        </tr>
                        <tr v-else v-for="item in deficiencies">
                            <td>{{ item.details }}</td>
                            <td>{{ item.department }}</td>
                            <td>{{ item.remarks }}</td>
                            <td>{{ item.date_added }}</td>
                            <td>{{ item.added_by }}</td>
                            <td>{{ item.date_resolved }}</td>
                            <td>{{ item.resolved_by }}</td>
                            <td>{{ item.status }}</td>
                            <td v-if="item.department == request.department && item.status != 'resolved'">
                                <a class="btn btn-primary" @click.prevent="resolveDeficiency(item.id)">Resolve</a>
                            </td>
                            <td v-else></td>
                        </tr>
                    </tbody>
                </table>                              
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
        active_sem: undefined,      
        deficiencies:[],              
        terms: [],    
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
        resolveDeficiency: function(id){
            Swal.fire({
                title: 'Resolve Deficiency?',
                text: "Continue resolving deficiency?",
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

