<aside class="right-side" id="registration-container">    
    <section class="content-header">
        <h1>
            <small>
                <a class="btn btn-app" :href="base_url + 'student/view_all_students'"><i class="ion ion-arrow-left-a"></i>All Students</a>                     
                <a class="btn btn-app" :href="base_url + 'student/edit_student/' + student.intID"><i class="ion ion-edit"></i> Edit</a> 
                <a class="btn btn-app" target="_blank" :href="base_url + 'pdf/student_viewer_registration_print/' + student.intID">
                    <i class="ion ion-printer"></i>Reg Form Print Preview
                </a>            
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
                        <h3 class="widget-user-username" style="text-transform:capitalize;margin-left:0;font-size:1.3em;">{{ student.strLastname }}, {{ student.strFirstname }} {{ student.strMiddlename }}</h3>
                        <h5 class="widget-user-desc" style="margin-left:0;">{{ student.strProgramCode }} Major in {{ student.strMajor }}</h5>
                    </div>
                    <div class="box-footer no-padding">
                        <ul class="nav nav-stacked">
                        <li><a href="#" style="font-size:13px;">Student Number <span class="pull-right text-blue">{{ student.strStudentNumber }}</span></a></li>
                        <li><a href="#" style="font-size:13px;">Curriculum <span class="pull-right text-blue">{{ student.strName }}</span></a></li>
                        <li><a style="font-size:13px;" href="#">Registration Status <span class="pull-right">{{ reg_status }}</span></a></li>
                        <li>
                            <a style="font-size:13px;" href="#">Date Registered <span class="pull-right">
                                <span style="color:#009000" v-if="registration" >{{ registration.dteRegistered }}</span>
                                <span style="color:#900000;" v-else>N/A</span>                                
                            </a>
                        </li>
                        <li><a style="font-size:13px;" href="#">Scholarship Type <span class="pull-right">{{ registration.scholarshipName }}</span></a></li>
                            
                        </ul>
                    </div>
                </div>                
            </div>
            <div class="col-sm-12">
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li>
                            <a :href="base_url + 'unity/student_viewer/' + student.intID + '/' + sem + '/tab_1'">
                                Personal Information
                            </a>
                        </li>
                        
                        <li v-if="advanced_privilages">
                            <a :href="base_url + 'unity/student_viewer/' + student.intID + '/' + sem + '/tab_2'">                            
                                Report of Grades
                            </a>
                        </li>
                        <li v-if="advanced_privilages">
                            <a :href="base_url + 'unity/student_viewer/' + student.intID + '/' + sem + '/tab_3'">                            
                                Assessment
                            </a>
                        </li>
                        
                        <li>
                            <a :href="base_url + 'unity/student_viewer/' + student.intID + '/' + sem + '/tab_5'">                            
                                Schedule
                            </a>
                        </li>
                        <li class="active"><a href="#tab_1" data-toggle="tab">Finance</a></li>
                        <li>
                            <a :href="base_url + 'unity/accounting/' + student.intID">                                
                                Accounting Summary
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="tab_1">    
                            <div class="box box-solid">
                                <div class="box-header">
                                    <h4 class="box-title">ACCOUNTING</h4>                                    
                                </div>                                    
                                <div class="box-body">
                                <h4 class="box-title">Payments</h4>
                                    <table class="table table-bordered table-striped">
                                        <tr>
                                            <th>OR Number</th>
                                            <th>Payment Type</th>
                                            <th>Amount Paid</th>
                                            <th>Online Payment Charge</th>
                                            <th>Total Due</th>
                                            <th>Status</th>
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
                                            <td>{{ application_payment.updated_at }}</td>
                                            <td>
                                                <button v-if="!application_payment.or_number && application_payment.status == 'Paid'" data-toggle="modal"                                                
                                                        @click="or_update.id = application_payment.id;" 
                                                        data-target="#myModal" class="btn btn-primary">
                                                        Update OR
                                                </button>
                                                <button v-if="application_payment.or_number"                                             
                                                        @click="printOR(application_payment)" 
                                                        class="btn btn-primary">
                                                        Print OR
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
                                            <td>{{ reservation_payment.updated_at }}</td>
                                            <td>
                                                <button v-if="!reservation_payment.or_number && reservation_payment.status == 'Paid'" data-toggle="modal"                                                
                                                        @click="or_update.id = reservation_payment.id;" 
                                                        data-target="#myModal" class="btn btn-primary">
                                                        Update OR
                                                </button>
                                                <button v-if="reservation_payment.or_number"                                             
                                                        @click="printOR(reservation_payment)" 
                                                        class="btn btn-primary">
                                                        Print OR
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th colspan="8">
                                            Other Payments:
                                            </th>
                                        </tr>  
                                        <tr v-for="payment in other_payments">
                                            <td>{{ payment.or_number }}</td>
                                            <td>{{ payment.description }}</td>
                                            <td>{{ payment.subtotal_order }}</td>
                                            <td>{{ payment.charges }}</td>
                                            <td>{{ payment.total_amount_due }}</td>
                                            <td>{{ payment.status }}</td>                                            
                                            <td>{{ payment.updated_at }}</td>
                                            <td>
                                                <button v-if="!payment.or_number && payment.status == 'Paid'" data-toggle="modal"                                                
                                                        @click="or_update.id = payment.id;" 
                                                        data-target="#myModal" class="btn btn-primary">
                                                        Update OR
                                                </button>
                                                <button v-if="payment.or_number"                                             
                                                        @click="printOR(payment)" 
                                                        class="btn btn-primary">
                                                        Print OR
                                                </button>
                                                <button v-if="payment.status == 'Pending' && payment.mode.name == 'MANUAL'"  class="btn btn-primary" @click="setToPaid(payment.id)">Set to paid</button>
                                                <button v-if="payment.status == 'Pending' && payment.mode.name == 'MANUAL'"  class="btn btn-danger" @click="deletePayment(payment.id)">Delete</button>
                                            </td>
                                        </tr>    
                                        <tr>
                                            <th colspan="8">
                                            Tuition Payments:
                                            </th>
                                        </tr>
                                        <tr v-for="payment in payments">
                                            <td>{{ payment.or_number }}</td>
                                            <td>{{ payment.description }}</td>
                                            <td>{{ payment.subtotal_order }}</td>
                                            <td>{{ payment.charges }}</td>
                                            <td>{{ payment.total_amount_due }}</td>
                                            <td>{{ payment.status }}</td>                                            
                                            <td>{{ payment.updated_at }}</td>
                                            <td>
                                                <button v-if="!payment.or_number && payment.status == 'Paid'" data-toggle="modal"                                                
                                                        @click="or_update.id = payment.id;" 
                                                        data-target="#myModal" class="btn btn-primary">
                                                        Update OR
                                                </button>
                                                <button v-if="payment.or_number"                                             
                                                        @click="printOR(payment)" 
                                                        class="btn btn-primary">
                                                        Print OR
                                                </button>
                                                <button v-if="payment.status == 'Pending' && payment.mode.name == 'MANUAL'" class="btn btn-primary" @click="setToPaid(payment.id)">Set to paid</button>
                                                <button v-if="payment.status == 'Pending' && payment.mode.name == 'MANUAL'"  class="btn btn-danger" @click="deletePayment(payment.id)">Delete</button>
                                            </td>
                                        </tr>                                                                           
                                        <tr>
                                            <td class="text-green" colspan="8">
                                            amount paid: P{{ amount_paid_formatted }}                                           
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-green" colspan="8">                                            
                                            remaining balance: P{{ remaining_amount_formatted }}
                                            </td>
                                        </tr>
                                    </table>
                                    <hr />
                                    <div class="row">
                                        <div v-html="tuition" class="col-sm-6"></div>   
                                        <div class="col-sm-6">
                                            <form @submit.prevent="submitManualPayment" method="post">                                                
                                                <div class="form-group">
                                                    <label>Payment Type</label>
                                                    <select @change="selectDescription" class="form-control" v-model="description">
                                                        <option value="Tuition Full">Tuition Full</option>
                                                        <option value="Tuition Down Payment">Tuition Down Payment</option>
                                                        <option value="Tuition Partial">Tuition Partial</option>
                                                        <option value="Tuition Specific">Tuition Specific</option>
                                                        <option value="Other">Other</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label>Payment Status</label>
                                                    <select class="form-control" v-model="request.status">
                                                        <option value="Paid">Paid</option>
                                                        <option value="Pending">Pending</option>                                                        
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label>Enter type if other is selected:</label>
                                                    <input type="text" :disabled="description != 'Other'" required class="form-control" v-model="description_other" />
                                                    
                                                    <label>Enter amount to pay:</label>
                                                    <input type="text" :disabled="description != 'Other' && description != 'Tuition Specific'" required class="form-control" v-model="amount_to_pay" />
                                                </div>
                                                <div class="form-group">
                                                    <label>OR Number:</label>
                                                    <input type="text" class="form-control" v-model="request.or_number" />
                                                </div>
                                                <div class="form-group">
                                                    <label>Contact Number:</label>
                                                    <input type="text" required class="form-control" v-model="request.contact_number" />
                                                </div>
                                                <div class="form-group">
                                                    <label>Email: {{ request.email_address }}</label>                                                    
                                                </div>
                                                <div class="form-group">
                                                    <label>Remarks:</label>
                                                    <textarea type="text" required class="form-control" v-model="request.remarks"></textarea>
                                                </div>
                                                <button class="btn btn-primary btn-lg" type="submit">Submit Payment</button>
                                            </form>
                                            <hr />                                            
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
        id: '<?php echo $id; ?>',    
        sem: '<?php echo $selected_ay; ?>',
        base_url: '<?php echo base_url(); ?>',
        slug: undefined,
        student:{},    
        request:{
            first_name: '',
            slug: '',
            middle_name: '',
            last_name: '',
            contact_number: '',
            email_address: '',
            mode_of_payment_id: 26,
            description: 'Tuition Full', 
            or_number:'',
            remarks:'',
            subtotal_order: 0,
            convenience_fee: 0,
            total_amount_due: 0,            
            charges: 0,
            sy_reference: '<?php echo $selected_ay; ?>',
            status: 'Paid',
        },
        or_update:{
            id: undefined,
            or_number: undefined,
        },
        amount_to_pay: 0,        
        advanced_privilages: false,      
        description: 'Tuition Full', 
        description_other: '',
        registration: {},
        other_payments:[],
        tuition:'',
        tuition_data: {},
        reservation_payment: undefined,
        application_payment: undefined,
        registration_status: 0,
        remaining_amount: 0,
        amount_paid: 0,
        amount_paid_formatted: 0,
        payments: [],
        remaining_amount_formatted: 0,
        has_partial: false,
        reg_status: undefined,        
        loader_spinner: true,                        
    },

    mounted() {

        let url_string = window.location.href;        
        if(this.id != 0){            
            //this.loader_spinner = true;
            axios.get(this.base_url + 'unity/registration_viewer_data/' + this.id + '/' + this.sem)
                .then((data) => {  
                    if(data.data.success){                                                                                           
                        this.registration = data.data.registration;            
                        this.registration_status = data.data.registration.intROG;
                        this.reg_status = data.data.reg_status;
                        this.student = data.data.student;         
                        this.slug = this.student.slug;
                        this.request.slug = this.slug;
                        this.request.first_name = this.student.strFirstname;
                        this.request.middle_name = this.student.strMiddlename;
                        this.request.last_name = this.student.strLastname;    
                        this.request.contact_number = this.student.strMobileNumber;  
                        this.request.email_address = this.student.strEmail;                  
                        this.advanced_privilages = data.data.advanced_privilages;       
                        this.tuition = data.data.tuition;
                        this.tuition_data = data.data.tuition_data;                                               
                        this.remaining_amount = data.data.tuition_data.total;

                        axios.get(api_url + 'finance/transactions/' + this.slug + '/' + this.sem)
                        .then((data) => {
                            this.payments = data.data.data;
                            this.other_payments = data.data.other;
                            
                            for(i in this.payments){
                                if(this.payments[i].status == "Paid"){
                                    if(this.payments[i].description == "Tuition Partial" || this.payments[i].description == "Tuition Down Payment")
                                        this.has_partial = true;
                                }
                            }

                            if(this.has_partial)
                                this.remaining_amount = this.tuition_data.total_installment;                            

                            for(i in this.payments){
                                if(this.payments[i].status == "Paid"){                              
                                    this.remaining_amount = this.remaining_amount - this.payments[i].subtotal_order;
                                    this.amount_paid = this.amount_paid + this.payments[i].subtotal_order;
                                }
                            }                        

                            axios.get(api_url + 'finance/reservation/' + this.slug)
                            .then((data) => {
                                this.reservation_payment = data.data.data;    
                                this.application_payment = data.data.application;
                                
                                if(this.reservation_payment.status == "Paid" && data.data.student_sy == this.sem){
                                        this.remaining_amount = this.remaining_amount - this.reservation_payment.subtotal_order;                                                                                            
                                        this.amount_paid = this.amount_paid + this.reservation_payment.subtotal_order;                                        
                                }
                                
                                this.remaining_amount = (this.remaining_amount < 0.02) ? 0 : this.remaining_amount;                                
                                this.remaining_amount_formatted = this.remaining_amount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                                this.amount_paid_formatted = this.amount_paid.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');                                
                                this.amount_to_pay = this.remaining_amount;
                                this.loader_spinner = false;
                            })
                            .catch((error) => {
                                console.log(error);
                            })
                        })
                        .catch((error) => {
                            console.log(error);
                        })      
                    }
                    else{
                        document.location = this.base_url + 'users/login';
                    }
                                  
                })
                .catch((error) => {
                    console.log(error);
                })
        }

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
        printOR: function(payment){
            console.log(payment);
        },
        deletePayment: function(payment_id){
            let url = api_url + 'finance/delete_payment';

            this.loader_spinner = true;
            
            Swal.fire({
                title: 'Continue with deleting Payment',
                text: "Are you sure you want to delete payment?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                    preConfirm: (login) => {
                        
                        let payload = {'id':payment_id}

                        return axios.post(url, payload, {
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
                                            icon: "error"
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
        setToPaid: function(payment_id){    
            let url = api_url + 'finance/set_paid';

            this.loader_spinner = true;
            
            Swal.fire({
                title: 'Continue with processing Payment',
                text: "Are you sure you want to process payment?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                    preConfirm: (login) => {
                        
                        let payload = {'id':payment_id}

                        return axios.post(url, payload, {
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
                        
                        if(this.description == 'Other')
                            this.request.description = this.description_other;

                        this.request.subtotal_order = this.amount_to_pay;
                        this.request.total_amount_due = this.amount_to_pay;
                        console.log(this.request);
                        
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
            if(this.description != 'Other'){                
                this.request.description = this.description;
                switch(this.description){
                    case 'Tuition Full':
                        this.amount_to_pay = this.remaining_amount;
                    break;
                    case 'Tuition Partial':
                        this.amount_to_pay = (this.tuition_data.installment_fee > this.remaining_amount) ? this.remaining_amount : this.tuition_data.installment_fee;
                    break;
                    case 'Tuition Down Payment':                        
                        this.amount_to_pay = (this.tuition_data.down_payment <= this.amount_paid) ? 0 : ( this.tuition_data.down_payment - this.amount_paid );
                    break;                    
                }
            }
            else{                                
                this.amount_to_pay = 0;
            }
        },
        changeRegStatus: function(){
            let url = this.base_url + 'unity/update_rog_status';
            var formdata= new FormData();
            formdata.append("intRegistrationID",this.registration.intRegistrationID);
            formdata.append("intROG",this.registration_status);
            var missing_fields = false;
            this.loader_spinner = true;
            
            //validate description
                      
            axios.post(url, formdata, {
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
           
            
            
        }
    }

})
</script>

