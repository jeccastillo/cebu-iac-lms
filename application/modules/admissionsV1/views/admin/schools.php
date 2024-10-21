<aside class="right-side" id="registration-container">    
    <section class="content-header">
        <h1>
            Schools
            <small>
                <a class="btn btn-app" 
                    href="#"
                    data-toggle="modal"                
                    data-target="#addSchool"
                    @click="setAddSchool"
                >
                    <i class="fa fa-plus"></i>
                    Add School
                </a> 
            </small>                       
            
        </h1>     
    </section>
    <hr />
    <div class="box box-primary">
        <div class="box-body">
            <div class="content">         
                <div class="row">
                    <div class="col-md-6">
                        <h4>Schools</h4>
                        <table v-if="schools.length > 0" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>City</th>
                                    <th>Province</th>
                                    <th>Country</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in schools">
                                    <td>{{ item.name }}</td>
                                    <td>{{ item.city }}</td>
                                    <td>{{ item.province }}</td>
                                    <td>{{ item.country }}</td>                            
                                    <td>
                                        <div class="btn-group"><button type="button" class="btn btn-default">Actions</button>
                                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span><span class="sr-only">Toggle Dropdown</span></button>
                                            <ul class="dropdown-menu" role="menu">
                                                <li>
                                                    <a href="#"                                         
                                                        data-toggle="modal"                
                                                        data-target="#addSchool"
                                                        @click="setEditSchool(item)"
                                                    >Edit</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div v-else>
                            <h3>No Data</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade"
        id="addSchool"
        role="dialog">
        <form class="modal-dialog modal-lg"
            @submit.prevent="addNewSchool">
            <div class="modal-content">
                <div class="modal-header">

                    <button type="button"
                        class="close"
                        data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">School</h4>
                </div>
                <div class="modal-body">                      
                    <div class="form-group">
                        <label>School Name</label>
                        <input type="text"
                            name="school_name"
                            v-model="request.school_name"
                            class="form-control"
                            required
                            required
                            placeholder="School Name">
                    </div>
                    <div class="form-group">
                        <label>City</label>
                        <input type="text"
                            name="school_city"
                            v-model="request.school_city"
                            class="form-control"
                            required
                            placeholder="City">
                    </div>
                    <div class="form-group">
                        <label>State/Province</label>
                        <input type="text"
                            name="school_province"
                            v-model="request.school_province"
                            class="form-control"
                            required
                            placeholder="State/Province">
                    </div>
                    <div class="form-group">
                        <label>Country</label>
                        <input type="text"
                            name="school_country"
                            v-model="request.school_country"
                            class="form-control"
                            required
                            placeholder="Country">
                    </div>
                </div>
                <div class=" modal-footer">
                    <button type="submit"
                        class="btn btn-primary">Submit</button>
                    <button type="button"
                        class="btn btn-default"
                        data-dismiss="modal">Close</button>
                </div>
            </div>

        </form>
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
        current_sem: '<?php echo $current_sem; ?>',
        schools: [],     
        edit_id: undefined,
        request:{            
            school_name: undefined,
            school_city: undefined,
            school_province: undefined,
            school_country: undefined,
        }
                
    },

    mounted() {

        let url_string = window.location.href;        
        if(this.id != 0){            
            //this.loader_spinner = true;
            
            axios.get(api_url + 'admissions/previous-schools')
            .then((data) => {                          
                this.schools = data.data;                                   
            })
            .catch((error) => {
                console.log(error);
            });
                
        }

    },

    methods: {      
        setAddSchool: function(){
            this.edit_id = undefined;
            this.request.school_name = undefined
            this.request.school_city = undefined
            this.request.school_province = undefined
            this.request.school_country = undefined
        },
        setEditSchool: function(item){            
            this.edit_id = item.id;
            this.request.school_name = item.name
            this.request.school_city = item.city
            this.request.school_province = item.province
            this.request.school_country = item.country
        },
        async addNewSchool(e) {
            if(!this.edit_school)
                var url = api_url + 'admissions/student-info/new-school';
            else
                var url = api_url + 'admissions/student-info/update-school/'+ this.edit_school;
            const {
                data
            } = await axios
                .post(url, this.request, {
                    headers: {
                        Authorization: `Bearer ${window.token}`
                    }
                })
            if (data.success) {
                Swal.fire({
                    showCancelButton: false,
                    showCloseButton: true,
                    allowEscapeKey: false,
                    title: 'Successfully Added New School',
                    text: 'Field Updated',
                    icon: 'success',
                });
                e.target.reset()
                this.getAllPrevSchool()
                $('#addSchool').modal('hide')
            } else {
                Swal.fire({
                    showCancelButton: false,
                    showCloseButton: true,
                    allowEscapeKey: false,
                    title: `${data.message}`,
                    text: 'Error',
                    icon: 'error',
                });
            }
        },
       
                                       
    }

})
</script>

