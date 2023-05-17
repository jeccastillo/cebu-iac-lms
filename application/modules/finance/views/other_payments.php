
<aside class="right-side">
    <div id="vue-container">
        <section class="content-header">
            <h1>                                
                Other Payments
            </h1>
        </section>
        <hr />
        <div class="content">            
            <div class="row">       
                <div class="col-sm-12">
                    <div v-if="cashier" class="box box-solid box-success">
                        <div class="box-header">                            
                            <h4 class="box-title">New Application Transaction - Cashier {{ cashier.intID }}</h4>
                            
                        </div>
                        <div class="box-body">
                            <div class="row">                                
                                <form @submit.prevent="submitManualPayment" method="post">                                                                                                                                
                                    <input type="hidden" required  class="form-control" v-model="request.description">                                                                                        
                                    <div class="col-sm-12">
                                        <label>Name:</label>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">                                            
                                            <input type="text" required class="form-control" placeholder="First Name" v-model="request.first_name" />
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">                                            
                                            <input type="text" required class="form-control" placeholder="Last Name" v-model="request.last_name" />
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">                                            
                                            <input type="text" class="form-control" placeholder="Middle Name" v-model="request.middle_name" />
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Payment for:</label>
                                            <input type="text" required class="form-control" v-model="description_other" />
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Enter amount to pay/refund:</label>
                                            <input type="text" required class="form-control" v-model="amount_to_pay" />
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
                                            <label>Payment Type</label>
                                            <select class="form-control" v-model="request.is_cash">
                                                <option value="1">Cash</option>
                                                <option value="0">Check</option>                                                        
                                                <option value="2">Credit Card</option>
                                                <option value="3">Debit Card</option>
                                                <option value="4">Online Payment</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Payment Status</label>
                                            <select class="form-control" v-model="request.status">
                                                <option value="Paid">Paid</option>
                                                <option value="Pending">Pending</option>                                                        
                                                <option value="Refunded">Refunded</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Check/Credit/Debit Number:</label>
                                            <input type="text" :disabled="request.is_cash == 1" required class="form-control" v-model="request.check_number" />
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
                                            <label>OR Number:</label>
                                            <div>{{ request.or_number }}</div>
                                            <input type="hidden" class="form-control" v-model="request.or_number" />
                                        </div>
                                    </div>  
                                    <!-- <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Amount to Pay:</label>
                                            {{ request.subtotal_order }}
                                        </div>
                                    </div> -->
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Email:</label>
                                            <input type="text" required class="form-control" v-model="request.email" />                                                    
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
                
            </div><!---row--->
        </div><!---content container--->
        <form ref="print_or" method="post" :action="base_url + 'pdf/print_or'" target="_blank">
            <input type="hidden" name="student_name" v-model="or_print.student_name">
            <input type="hidden" name="cashier_id" v-model="or_print.cashier_id">
            <input type="hidden" name="student_id" v-model="or_print.student_id">
            <input type="hidden" name="student_address" v-model="or_print.student_address">
            <input type="hidden" name="is_cash" v-model="or_print.is_cash">
            <input type="hidden" name="check_number" v-model="or_print.check_number">
            <input type="hidden" name="or_number" v-model="or_print.or_number" />
            <input type="hidden" name="remarks" v-model="or_print.remarks">
            <input type="hidden" name="description" v-model="or_print.description" />
            <input type="hidden" name="total_amount_due" v-model="or_print.total_amount_due" /> 
            <input type="hidden" name="name" v-model="or_print.student_name" />       
            <input type="hidden" name="transaction_date" v-model="or_print.transaction_date" />               
        </form>
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
                            <input type="hidden" class="form-control" v-model="or_update.or_number" required>                        
                            <h4>{{ String(or_update.or_number).padStart(5, '0') }}</h4>
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
        base_url: "<?php echo base_url(); ?>",   
        applicant_id: undefined,                
        refunded_payments: [],    
        amount_to_pay: 0,
        description_other: '', 
        cashier: undefined,
        request:{
            first_name: '',
            slug: '',
            middle_name: '',
            last_name: '',
            contact_number: '',
            email_address: '',
            mode_of_payment_id: 26,
            description: undefined, 
            sy_reference: undefined,
            or_number:undefined,
            remarks:'',
            subtotal_order: 0,
            convenience_fee: 0,
            total_amount_due: 0,            
            charges: 0,       
            cashier_id: undefined,     
            status: 'Paid',
            is_cash: 1,            
            check_number: undefined,
        },
        or_print: {
            or_number: undefined,
            description: undefined,
            total_amount_due: undefined,
            student_name: undefined,
            transaction_date: undefined,
            student_name: undefined,
            student_address: undefined,
            student_id: undefined,
            remarks: undefined,
            is_cash: undefined,
            cashier_id: undefined,
            check_number: undefined,
        },
        or_update:{
            id: undefined,
            or_number: undefined,
            cashier_id: undefined,
            sy_reference: undefined,
        },
             
    },

    mounted() {

        let url_string = window.location.href;
        let url = new URL(url_string);

        this.loader_spinner = true;
        
            
            axios.get(base_url + 'finance/other_payment_data/')
            .then((data) => {            
                this.cashier = data.data.cashier;
                this.request.sy_reference = data.data.current_sem;                
                if(this.cashier){
                    this.request.or_number = this.cashier.or_current;
                    this.or_update.or_number = this.cashier.or_current;
                    this.request.cashier_id = this.cashier.user_id;
                    this.or_update.cashier_id = this.cashier.user_id;
                }
            })
            .catch((error) => {
                console.log(error);
            })  
            
            

    },

    methods: {      
        printOR: function(payment){            
            this.or_print.or_number = payment.or_number;
            this.or_print.description = payment.description;
            this.or_print.total_amount_due = payment.subtotal_order;
            this.or_print.transaction_date = payment.updated_at;
            this.or_print.remarks = payment.remarks;
            this.or_print.student_name =  this.request.last_name+", "+this.request.first_name+", "+this.request.middle_name;    
            this.or_print.student_address = this.student.address;
            this.or_print.student_id = this.applicant_id;
            this.or_print.is_cash = payment.is_cash;
            this.or_print.check_number = payment.check_number;
            this.or_print.cashier_id = payment.cashier_id;
            this.$nextTick(() => {
                this.$refs.print_or.submit();
            });             
        },
        cashierDetails: function(id){
            axios.get(base_url + 'finance/cashier_details/' + id)
            .then((data) => {            
                var cashier_details = data.data.cashier_data;
                Swal.fire({
                    title: "Cashier",
                    text: cashier_details.strFirstname+" "+cashier_details.strLastname,
                    icon: "info"
                })
            })

        },  
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
                                    if(data.data.success){
                                        var formdata= new FormData();
                                        formdata.append('intID',this.cashier.intID);
                                        formdata.append('or_current',this.cashier.or_current);
                                        axios.post(base_url + 'finance/next_or', formdata, {
                                        headers: {
                                            Authorization: `Bearer ${window.token}`
                                        }
                                        })
                                        .then(function(data){
                                            if (data.data.send_notif) {                            
                                                let url = api_url + 'registrar/send_notif_enrolled/' + this.student_data.slug;                                                
                                                let payload = {'message': "This message serves as a notification that you have been officially enrolled."}
                                                
                                                Swal.fire({
                                                    showCancelButton: false,
                                                    showCloseButton: false,
                                                    allowEscapeKey: false,
                                                    title: 'Loading',
                                                    text: 'Processing Data do not leave page',
                                                    icon: 'info',
                                                })
                                                Swal.showLoading();
                                                axios.post(url, payload, {
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
                                                        location.reload();
                                                    });  
                                                });                                
                                            }
                                            else{
                                                Swal.fire({
                                                        title: "Success",
                                                        text: data.data.message,
                                                        icon: "success"
                                                    }).then(function() {
                                                        location.reload();
                                                    });                                                                                                                              

                                            }  
                                                  
                                        })
                                    }                                        
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


                            if(this.request.description == 'Other'){
                                this.request.description = this.description_other;                                
                            }

                            this.request.subtotal_order = this.amount_to_pay;
                            this.request.total_amount_due = this.amount_to_pay;

                            
                            return axios.post(url, this.request, {
                                        headers: {
                                            Authorization: `Bearer ${window.token}`
                                        }
                                    })
                                    .then(data => {
                                        this.loader_spinner = false;
                                        if(data.data.success){
                                            var formdata= new FormData();
                                            formdata.append('intID',this.cashier.intID);
                                            formdata.append('or_current',this.cashier.or_current);
                                            axios.post(base_url + 'finance/next_or', formdata, {
                                            headers: {
                                                Authorization: `Bearer ${window.token}`
                                            }
                                            })
                                            .then(function(){
                                                if (data.data.send_notif) {                            
                                                        let url = api_url + 'registrar/send_notif_enrolled/' + this.student_data.slug;                                                
                                                        let payload = {'message': "This message serves as a notification that you have been officially enrolled."}
                                                        
                                                        Swal.fire({
                                                            showCancelButton: false,
                                                            showCloseButton: false,
                                                            allowEscapeKey: false,
                                                            title: 'Loading',
                                                            text: 'Processing Data do not leave page',
                                                            icon: 'info',
                                                        })
                                                        Swal.showLoading();
                                                        axios.post(url, payload, {
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
                                                                location.reload();
                                                            });                                                                                                                              
                                                            
                                                        });                                
                                                    }
                                                    else{
                                                        Swal.fire({
                                                                title: "Success",
                                                                text: data.data.message,
                                                                icon: "success"
                                                            }).then(function() {
                                                                location.reload();
                                                            });                                                                                                                              

                                                    } 
                                                        
                                                })                                                     
                                        }                                            
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


    }

})
</script>