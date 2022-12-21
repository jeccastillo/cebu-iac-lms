
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
                    <form @submit.prevent="submitManualPayment" method="post">                                                
                        <div class="form-group">
                            <label>Payment Type</label>
                            <select @change="selectDescription" class="form-control" v-model="request.description">
                                <option value="Reservation Payment">Reservation</option>
                                <option value="Application Payment">Application</option>                                
                            </select>
                        </div>                                                
                        <div class="form-group">
                            <label>OR Number:</label>
                            <input type="text" class="form-control" v-model="request.or_number" />
                        </div>
                        <div class="form-group">
                            <label>Amount to Pay:</label>
                            {{ request.subtotal_order }}
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
                                </tr>
                                <tr>                                            
                            </table>
                            <hr />                                    
                        </div><!---box body--->
                    </div><!---box--->                      
                </div><!---column--->
            </div><!---row--->
        </div><!---content container--->
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
        request:{
            first_name: '',
            slug: '',
            middle_name: '',
            last_name: '',
            contact_number: '',
            email_address: '',
            mode_of_payment_id: 26,
            description: 'Reservation Payment', 
            or_number:'',
            remarks:'',
            subtotal_order: 10000,
            convenience_fee: 0,
            total_amount_due: 10000,            
            charges: 0,            
            status: 'Paid',
        },
             
    },

    mounted() {

        let url_string = window.location.href;
        let url = new URL(url_string);

        this.loader_spinner = true;

        axios.get(this.base_url + 'finance/manualPay/' + this.slug)
        .then((data) => {
            this.student = data.data.data;                     
            this.request.slug = this.slug;
            this.request.first_name = this.student.strFirstname;
            this.request.middle_name = this.student.strMiddlename;
            this.request.last_name = this.student.strLastname;    
            this.request.contact_number = this.student.strMobileNumber;  
            this.request.email_address = this.student.strEmail;
            
            axios.get(api_url + 'finance/reservation/' + this.slug)
            .then((data) => {
                this.reservation_payment = data.data.data;    
                this.application_payment = data.data.application;                    
            })
            .catch((error) => {
                console.log(error);
            })
        })
        .catch((error) => {
            console.log(error);
        })
         


    },

    methods: {        

        submitManualPayment: function(){
            let url = api_url + 'finance/manual_payment';            
            this.loader_spinner = true;
            if(this.request.description == "Reservation Payment" && this.reservation_payment)
                Swal.fire({
                    title: "Failed",
                    text: "Reservation Payment already exists",
                    icon: "error"
                }).then(function() {
                    //location.reload();
                });
            else if(this.request.description == "Application Payment" && this.application_payment)
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