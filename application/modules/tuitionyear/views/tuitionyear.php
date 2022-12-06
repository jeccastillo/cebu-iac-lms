<div class="content-wrapper " id="applicant-container">
    <section class="content-header container ">
        <h1>
            Student Applicants
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i>Student Applicant Details </a></li>
            <li class="active">Details</li>
        </ol>
    </section>
    <div class="content  container">
        <div action="">
            <div class="box ">
                <div class="box-header with-border font-weight-bold py-5" style="text-align:left; font-weight:bold">
                    <h3 class="box-title text-left text-primary " style="font-size:2rem">
                        {{ header_title }}
                    </h3>
                </div>
                <div class="box-body" style="padding:2rem">
                    <form @submit.prevent="updateData">    
                        <div class="row">                     
                            <div class="form-group col-xs-6">
                                <label for="year">Tuition Year</label>
                                <input type="text" name="year" required class="form-control" id="year" placeholder="Enter Year" v-model='request.year'>
                            </div>
                            <div class="form-group col-xs-6">
                                <label for="year">Price Per Unit Regular</label>
                                <input type="number" name="pricePerUnit" required class="form-control" id="pricePerUnit" placeholder="Enter Price per unit" v-model='request.pricePerUnit'>
                            </div>
                            <div class="form-group col-xs-6">
                                <label for="year">Price Per Unit Online</label>
                                <input type="number" name="pricePerUnitOnline" required class="form-control" id="pricePerUnitOnline" placeholder="Enter Price per unit" v-model='request.pricePerUnitOnline'>
                            </div>
                            <div class="form-group col-xs-6">
                                <label for="year">Price Per Unit Hyflex</label>
                                <input type="number" name="pricePerUnitHyflex" required class="form-control" id="pricePerUnitHyflex" placeholder="Enter Price per unit" v-model='request.pricePerUnitHyflex'>
                            </div>
                            <div class="form-group col-xs-6">
                                <label for="year">Price Per Unit Hybrid</label>
                                <input type="number" name="pricePerUnitHybrid" required class="form-control" id="pricePerUnitHybrid" placeholder="Enter Price per unit" v-model='request.pricePerUnitHybrid'>
                            </div> 
                            <div v-if="id != 0 && default_year != id" class="form-group col-xs-6">
                                <label for="isDefault">Default Tuition</label>
                                <select v-model="request.isDefault" class="form-control" name="isDefault" id="isDefault" >
                                    <option value="0">No</option>
                                    <option value="1">Yes</option>
                                </select>
                            </div>                       
                        </div>
                        
                        <div class="row">    
                            <div class="col-sm-6">
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </div>
                    </form>
                    <hr />
                    <h3>Miscellaneous Fees</h3>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Regular</th>
                                <th>Online</th>
                                <th>Hyflex</th>
                                <th>Hybrid</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item in request.misc">
                                <td>{{ item.name }}</td>
                                <td>{{ item.miscRegular }}</td>
                                <td>{{ item.miscOnline }}</td>
                                <td>{{ item.miscHybrid }}</td>
                                <td>{{ item.miscHyflex }}</td>
                                <td><a href="#" v-click="deleteMisc(item.intID)">Delete</a></td>
                            </tr>
                        </tbody>
                    </table>
                    <hr />
                    <p>Add new Miscellaneous Item</p>
                    <form @submit.prevent="addMisc">    
                        <div class="row">                     
                            <div class="form-group col-xs-8">
                                <label for="year">Name</label>
                                <input type="text" name="v" required class="form-control" id="name" placeholder="Enter Name" v-model='misc.name'>
                            </div>
                        </div>
                        <div class="row">                     
                            <div class="form-group col-sm-3">
                                <label for="year">Regular Fee</label>
                                <input type="number"  name="miscRegular" required class="form-control" id="miscRegular" placeholder="Enter Fee Amount" v-model='misc.miscRegular'>
                            </div>
                            <div class="form-group col-sm-3">
                                <label for="year">Online Fee</label>
                                <input type="number" name="miscOnline" required class="form-control" id="miscOnline" placeholder="Enter Fee Amount" v-model='misc.miscOnline'>
                            </div>
                            <div class="form-group col-sm-3">
                                <label for="year">Hyflex Fee</label>
                                <input type="number" name="miscHyflex" required class="form-control" id="miscHyflex" placeholder="Enter Fee Amount" v-model='misc.miscHyflex'>
                            </div>
                            <div class="form-group col-sm-3">
                                <label for="year">Hybrid Fee</label>
                                <input type="number" name="miscHybrid" required class="form-control" id="miscHybrid" placeholder="Enter Fee Amount" v-model='misc.miscHybrid'>
                            </div>
                                           
                        </div>
                        
                        <div class="row">    
                            <div class="col-sm-6">
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>

            </div>

        </div>
    </div>
</div>




<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@2.6.12"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
    integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>

<script>
new Vue({
    el: '#applicant-container',
    data: {
        id: <?php echo $this->uri->segment('3'); ?>,
        header_title: 'Add Tuition Year',
        request: {
            year: undefined,
            pricePerUnit: undefined,
            pricePerUnitOnline: undefined,
            pricePerUnitHyflex: undefined,
            pricePerUnitHybrid: undefined,
            misc: [],
            isDefault: 0,            
        },
        misc: {
            name: undefined,
            miscRegular: undefined,
            miscHybrid: undefined,
            miscOnline: undefined,
            miscHyflex: undefined,      
        },
        default_year: <?php echo $defaultYear; ?>,
        update_text: "Tuition Year",
        loader_spinner: true,                        
    },

    mounted() {

        let url_string = window.location.href;
        let url = new URL(url_string);

        if(this.id != 0){
        
            this.header_title = 'Edit Tuition Year';
            this.loader_spinner = true;
            axios.get('<?php echo base_url(); ?>tuitionyear/tuition_info/' + this.id)
                .then((data) => {                    
                    this.request = data.data.data;                    
                    this.loader_spinner = false;
                })
                .catch((error) => {
                    console.log(error);
                })

        }

    },

    methods: {

        addMisc: function (){
            Swal.fire({
                title: 'Add Miscellaneous',
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
                    formdata.append("name",this.misc.name);
                    formdata.append("tuitionYearID",this.id);
                    formdata.append("miscRegular",this.misc.miscRegular);
                    formdata.append("miscOnline",this.misc.miscOnline);
                    formdata.append("miscHyflex",this.misc.miscHyflex);
                    formdata.append("miscHybrid",this.misc.miscHybrid);
                    

                    return axios
                        .post('<?php echo base_url(); ?>tuitionyear/submit_misc/',formdata, {
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
        deleteMisc: function(miscId){
            Swal.fire({
                title: 'Delete Miscellaneous',
                text: "Continue deleting entry?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                preConfirm: (login) => {
                    var formdata= new FormData();
                    formdata.append("id",miscId);
                    return axios
                        .post('<?php echo base_url(); ?>tuitionyear/delete_misc/',formdata, {
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
        updateData: function() {
            Swal.fire({
                title: 'Update Status',
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
                    formdata.append("year",this.request.year);
                    formdata.append("pricePerUnit",this.request.pricePerUnit);
                    formdata.append("pricePerUnitOnline",this.request.pricePerUnitOnline);
                    formdata.append("pricePerUnitHybrid",this.request.pricePerUnitHybrid);
                    formdata.append("pricePerUnitHyflex",this.request.pricePerUnitHyflex);
                    formdata.append("isDefault",this.request.isDefault);

                    return axios
                        .post('<?php echo base_url(); ?>tuitionyear/submit_form/' + this.id,formdata, {
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