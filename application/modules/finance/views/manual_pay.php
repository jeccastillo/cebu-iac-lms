
<aside class="right-side">
    <div id="vue-container">
        <section class="content-header">
            <h1>
                <small>
                    <a class="btn btn-app" :href="base_url + 'admissionsV1/view_all_leads'" ><i class="ion ion-arrow-left-a"></i>All Students Applicants</a>                                                                                                                     
                </small>
            </h1>
        </section>
        <hr />
        <div class="content">
            <div class="row">       
                <div class="col-sm-12">
                    <div v-if="cashier" class="box box-solid box-success">
                        <div class="box-header">                            
                            <h4 class="box-title">New Application Transaction</h4>
                        </div>
                        <div class="box-body">
                            <div class="row">                                
                                <form @submit.prevent="submitManualPayment" method="post">                                                
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Payment Type</label>
                                            <select @change="selectDescription" class="form-control" v-model="request.description">
                                                <option value="Reservation Payment">Reservation</option>
                                                <option value="Application Payment">Application</option>                                
                                            </select>
                                        </div>                                                
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>OR Number:</label>
                                            <input type="text" class="form-control" v-model="request.or_number" />
                                        </div>
                                    </div>                                    
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Contact Number:</label>
                                            <input type="text" required class="form-control" v-model="request.contact_number" />
                                        </div>
                                    </div>
                                    
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Remarks:</label>
                                            <textarea type="text" required class="form-control" v-model="request.remarks"></textarea>
                                        </div>                                    
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Amount to Pay:</label>
                                            {{ request.subtotal_order }}
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Email: {{ request.email_address }}</label>                                                    
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <button :disabled="!request.or_number" class="btn btn-primary btn-lg" type="submit">Submit Payment</button>
                                    </div>
                                </form>                                
                            </div>                            
                        </div>
                    </div>
                </div>                            
                <div class="col-sm-12">
                    <div class="box box-solid box-success">
                        <div class="box-header">                            
                            <h4 class="box-title">Transactions</h4>
                        </div>
                        <div class="box-body">
                            <table class="table table-bordered">
                                <tr>
                                    <th>OR Number</th>
                                    <th>Payment Type</th>
                                    <th>Amount Paid</th>
                                    <th>Online Payment Charge</th>
                                    <th>Total Due</th>
                                    <th>Status</th>
                                    <th>Online Response Message</th>
                                    <th>Date Updated</th>
                                    <th>Actions</th>
                                </tr>    
                                <tr v-if="application_payment">
                                    <td>{{ application_payment.or_number }}</td>
                                    <td>{{ application_payment.description }}</td>
                                    <td>{{ application_payment.subtotal_order }}</td>
                                    <td>{{ application_payment.charges }}</td>
                                    <td>{{ application_payment.total_amount_due }}</td>
                                    <td>{{ application_payment.status }}</td>                                            
                                    <td>{{ application_payment.response_message }}</td>
                                    <td>{{ application_payment.updated_at }}</td>            
                                    <td>
                                        <button v-if="!application_payment.or_number" data-toggle="modal"                                                
                                                @click="or_update.id = application_payment.id;" 
                                                data-target="#myModal" class="btn btn-primary">
                                                Update OR
                                        </button>
                                    </td>                                    
                                </tr> 
                                <tr v-if="reservation_payment">
                                    <td>{{ reservation_payment.or_number }}</td>
                                    <td>{{ reservation_payment.description }}</td>
                                    <td>{{ reservation_payment.subtotal_order }}</td>
                                    <td>{{ reservation_payment.charges }}</td>
                                    <td>{{ reservation_payment.total_amount_due }}</td>
                                    <td>{{ reservation_payment.status }}</td>
                                    <td>{{ reservation_payment.response_message }}</td>
                                    <td>{{ reservation_payment.updated_at }}</td>
                                    <td>
                                        <button v-if="!reservation_payment.or_number" data-toggle="modal"                                                
                                                @click="or_update.id = reservation_payment.id;" 
                                                data-target="#myModal" class="btn btn-primary">
                                                Update OR
                                        </button>
                                    </td>
                                </tr>
                                <tr>                                            
                            </table>
                            <hr />                                    
                        </div><!---box body--->
                    </div><!---box--->                      
                </div><!---column--->
            </div><!---row--->
        </div><!---content container--->
        <div class="modal fade" id="myModal" role="dialog">
            <form @submit.prevent="updateOR" class="modal-dialog modal-lg">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <!-- modal header  -->
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Add OR Number</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>OR Number <span class="text-danger">*</span> </label>
                            <input type="text" class="form-control" v-model="or_update.or_number" required></textarea>                        
                        </div>
                    </div>
                    <div class=" modal-footer">
                        <!-- modal footer  -->
                        <button type="submit" class="btn btn-primary">Submit</button>
                        <button type="button" :disabled="!or_update.or_number" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>

            </form>
        </div>
    </div><!---vue container--->
</aside>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@2.6.12"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
    integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>

<script>
new Vue({
    el: '#vue-container',
    data: {
        type: "<?php echo $type; ?>",
        slug: "<?php echo $slug; ?>",
        base_url: "<?php echo base_url(); ?>",   
        reservation_payment: undefined,
        application_payment: undefined,
        cashier: undefined,
        request:{
            first_name: '',
            slug: '',
            middle_name: '',
            last_name: '',
            contact_number: '',
            email_address: '',
            mode_of_payment_id: 26,
            description: 'Reservation Payment', 
            or_number:undefined,
            remarks:'',
            subtotal_order: 10000,
            convenience_fee: 0,
            total_amount_due: 10000,            
            charges: 0,            
            status: 'Paid',
        },
        or_update:{
            id: undefined,
            or_number: undefined,
        },
             
    },

    mounted() {

        let url_string = window.location.href;
        let url = new URL(url_string);

        this.loader_spinner = true;
        axios.get(api_url + 'admissions/student-info/' + this.slug)
        .then((data) => {
            this.student = data.data.data;
            this.request.slug = this.slug;
            this.request.first_name = this.student.first_name;
            this.request.middle_name = this.student.middle_name;
            this.request.last_name = this.student.last_name;    
            this.request.contact_number = this.student.mobile_number;  
            this.request.email_address = this.student.email;   
            this.cashier = data.data.cashier;
            if(this.cashier){
                this.request.or_number = this.cashier.or_current;
                this.or_update.or_number = this.cashier.or_current;
            }
            for(i in this.student.payments){
                if(this.student.payments[i].description == "Application Payment")
                    this.application_payment = this.student.payments[i];
                if(this.student.payments[i].description == "Reservation Payment")
                    this.reservation_payment = this.student.payments[i];
            }
        })
        .catch((error) => {
            console.log(error);
        })

    },

    methods: {        
        updateOR: function(){
            let url = api_url + 'finance/update_or';

            this.loader_spinner = true;
            
            Swal.fire({
                title: 'Continue with the update',
                text: "Are you sure you want to update the payment?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                    preConfirm: (login) => {                                                

                        return axios.post(url, this.or_update, {
                                    headers: {
                                        Authorization: `Bearer ${window.token}`
                                    }
                                })
                                .then(data => {
                                    this.loader_spinner = false;
                                    if(data.data.success)
                                        Swal.fire({
                                            title: "Success",
                                            text: data.data.message,
                                            icon: "success"
                                        }).then(function() {
                                            location.reload();
                                        });
                                    else
                                        Swal.fire({
                                            title: "Failed",
                                            text: data.data.message,
                                            icon: "error"
                                        }).then(function() {
                                            //location.reload();
                                        });
                                });
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                
                })

        },
        submitManualPayment: function(){
            let url = api_url + 'finance/manual_payment';            
            this.loader_spinner = true;
            if(this.request.description == "Reservation Payment" && this.reservation_payment && this.reservation_payment.status == "Paid")
                Swal.fire({
                    title: "Failed",
                    text: "Reservation Payment already exists",
                    icon: "error"
                }).then(function() {
                    //location.reload();
                });
            else if(this.request.description == "Application Payment" && this.application_payment && this.application_payment.status == "Paid")
                Swal.fire({
                    title: "Failed",
                    text: "Application Payment already exists",
                    icon: "error"
                }).then(function() {
                    //location.reload();
                });
            else
                Swal.fire({
                    title: 'Continue with Payment',
                    text: "Are you sure you want to add payment?",
                    showCancelButton: true,
                    confirmButtonText: "Yes",
                    imageWidth: 100,
                    icon: "question",
                    cancelButtonText: "No, cancel!",
                    showCloseButton: true,
                    showLoaderOnConfirm: true,
                        preConfirm: (login) => {

                            return axios.post(url, this.request, {
                                        headers: {
                                            Authorization: `Bearer ${window.token}`
                                        }
                                    })
                                    .then(data => {
                                        this.loader_spinner = false;
                                        if(data.data.success)
                                            Swal.fire({
                                                title: "Success",
                                                text: data.data.message,
                                                icon: "success"
                                            }).then(function() {
                                                location.reload();
                                            });
                                        else
                                            Swal.fire({
                                                title: "Failed",
                                                text: data.data.message,
                                                icon: "error"
                                            }).then(function() {
                                                //location.reload();
                                            });
                                    });
                        },
                        allowOutsideClick: () => !Swal.isLoading()
                    }).then((result) => {
                    
                })
            
        },

        selectDescription: function(){
            if(this.request.description == "Reservation Payment"){
                this.request.subtotal_order = 10000;
                this.request.total_amount_due = 10000;
            }
            else{
                this.request.subtotal_order = 500;
                this.request.total_amount_due = 500;
            }
            
        }


    }

})
</script>