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
                            <div class="form-group col-sm-6">
                                <label for="year">Percent Increase for installment</label>
                                <input step="any" type="number" required class="form-control" placeholder="Enter Percentage" v-model='request.installmentIncrease'>
                            </div>                            
                            <div class="form-group col-sm-6">
                                <label for="year">Percent Down Payment for Installment</label>
                                <input step="any" type="number" required class="form-control" placeholder="Enter Percentage" v-model='request.installmentDP'>
                            </div> 
                            <div class="form-group col-sm-6">
                                <label for="year">Fixed Down Payment for Installment (if blank or set to 0 to choose percent down payment)</label>
                                <input step="any" type="number" class="form-control" placeholder="Enter Fixed Value" v-model='request.installmentFixed'>
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
                                <th>Type</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in request.misc">
                                <td>{{ item.name }}</td>
                                <td>{{ item.miscRegular }}</td>
                                <td>{{ item.miscOnline }}</td>
                                <td>{{ item.miscHyflex }}</td>
                                <td>{{ item.miscHybrid }}</td>
                                <td>{{ item.type }}</td>
                                <td><a href="#" @click.prevent.stop="deleteItem('misc',item.intID)">Delete</a></td>
                            </tr>
                        </tbody>
                    </table>
                    <hr />
                    <p>Add new Miscellaneous Item</p>
                    <form @submit.prevent="addExtra('misc','Miscellaneous',misc)">    
                        <div class="row">                     
                            <div class="form-group col-xs-8">
                                <label for="year">Name</label>
                                <input type="text" required class="form-control" placeholder="Enter Name" v-model='misc.name'>
                            </div>
                        </div>
                        <div class="row">                     
                            <div class="form-group col-sm-3">
                                <label for="year">Regular Fee</label>
                                <input step="any" type="number" required class="form-control" placeholder="Enter Fee Amount" v-model='misc.miscRegular'>
                            </div>
                            <div class="form-group col-sm-3">
                                <label for="year">Online Fee</label>
                                <input step="any" type="number" required class="form-control" placeholder="Enter Fee Amount" v-model='misc.miscOnline'>
                            </div>
                            <div class="form-group col-sm-3">
                                <label for="year">Hyflex Fee</label>
                                <input step="any" type="number" required class="form-control" placeholder="Enter Fee Amount" v-model='misc.miscHyflex'>
                            </div>
                            <div class="form-group col-sm-3">
                                <label for="year">Hybrid Fee</label>
                                <input step="any" type="number" required class="form-control" placeholder="Enter Fee Amount" v-model='misc.miscHybrid'>
                            </div>
                            <div class="form-group col-sm-3">
                                <label for="year">Type</label>
                                <select required class="form-control" @change="selectType($event)" placeholder="Enter Fee Amount" v-model='misc.type'>
                                    <option value="regular">Regular</option>
                                    <option value="nsf">New Student Misc</option>
                                    <option value="new_student">New Student Fees</option>
                                    <option value="thesis">Thesis</option>
                                    <option value="internship">Internship</option>
                                    <option value="nstp">NSTP/ROTC</option>
                                    <option value="isf">International Student Fee</option>                                    
                                    <option value="svf">Student Visa Fee</option>                                    
                                    <option value="other">Other</option>                                    
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
                    <h3>Lab Fees</h3>
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
                            <tr v-for="item in request.lab_fees">
                                <td>{{ item.name }}</td>
                                <td>{{ item.labRegular }}</td>
                                <td>{{ item.labOnline }}</td>
                                <td>{{ item.labHyflex }}</td>
                                <td>{{ item.labHybrid }}</td>
                                <td><a href="#" @click.prevent.stop="deleteItem('lab_fee',item.intID)">Delete</a></td>
                            </tr>
                        </tbody>
                    </table>
                    <hr />
                    <p>Add new Lab Fee Type</p>
                    <form @submit.prevent="addExtra('lab_fee','Laboratory',lab)">    
                        <div class="row">                     
                            <div class="form-group col-xs-8">
                                <label for="year">Name</label>
                                <input type="text" required class="form-control" placeholder="Enter Name" v-model='lab.name'>
                            </div>
                        </div>
                        <div class="row">                     
                            <div class="form-group col-sm-3">
                                <label for="year">Regular Fee</label>
                                <input step="any" type="number"  required class="form-control" placeholder="Enter Fee Amount" v-model='lab.labRegular'>
                            </div>
                            <div class="form-group col-sm-3">
                                <label for="year">Online Fee</label>
                                <input step="any" type="number" required class="form-control" placeholder="Enter Fee Amount" v-model='lab.labOnline'>
                            </div>
                            <div class="form-group col-sm-3">
                                <label for="year">Hyflex Fee</label>
                                <input step="any" type="number" required class="form-control" placeholder="Enter Fee Amount" v-model='lab.labHyflex'>
                            </div>
                            <div class="form-group col-sm-3">
                                <label for="year">Hybrid Fee</label>
                                <input step="any" type="number" required class="form-control" placeholder="Enter Fee Amount" v-model='lab.labHybrid'>
                            </div>                                                                                            
                        </div>
                        
                        <div class="row">    
                            <div class="col-sm-6">
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </div>
                    </form>
                    <hr />
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
            installmentIncrease: undefined,
            installmentDP: undefined,
            installmentFixed: undefined,
            misc: [],
            lab_fees: [],
            isDefault: 0,            
        },
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
        default_year: <?php echo $defaultYear; ?>,
        update_text: "Tuition Year",
        loader_spinner: true,                        
    },

    mounted() {

        let url_string = window.location.href;
        let url = new URL(url_string);

       
        
        this.header_title = 'Edit Tuition Year';
        //this.loader_spinner = true;
        axios.get('<?php echo base_url(); ?>tuitionyear/tuition_info/' + this.id)
            .then((data) => {                    
                this.request = data.data.data;                    
                //this.loader_spinner = false;
            })
            .catch((error) => {
                console.log(error);
            })
       

    },

    methods: {
        selectType: function(event){            
            if(event.target.value == "isf")
                this.misc.name = "International Student Fee";
            if(event.target.value == "svf")    
                this.misc.name = "Student Visa Fee";
            
        },
        addExtra: function (type, name, data){
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
        deleteItem: function(type,miscId){
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
                    formdata.append("type",type);                    
                    return axios
                        .post('<?php echo base_url(); ?>tuitionyear/delete_type/',formdata, {
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
                    formdata.append("installmentIncrease", this.request.installmentIncrease);
                    formdata.append("installmentDP", this.request.installmentDP);
                    formdata.append("installmentFixed", this.request.installmentFixed);
                    
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