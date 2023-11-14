<div id="registration-container">    
    <div class="container">       
        <div class="content">                        
            <h3>Name :{{ student.strFirstname }} {{ student.strLastname }} <br />
                Stud No :{{ student.strStudentNumber }}
            </h3>
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active">
                        <a href="#tab1" data-toggle="tab">
                            PAY ONLINE
                        </a>
                    </li>
                    <li>
                        <a href="#tab2" data-toggle="tab">
                            ASSESSMENT OF FEES
                        </a>
                    </li>
                    <li>
                        <a href="#tab3" data-toggle="tab">
                            MY PAYMENTS
                        </a>
                    </li>                    
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="tab1">    
                        <div class="box box-solid">
                            <div class="box-header">
                                <h4 class="box-title">BDO PAY</h4>
                            </div>
                            <div class="box-body">                                   
                                <hr />
                                <form id="payment_form" action="payment_confirmation.php" method="post">
                                    <input type="hidden" name="access_key" value="<REPLACE WITH ACCESS KEY>">
                                    <input type="hidden" name="profile_id" value="<REPLACE WITH PROFILE ID>">
                                    <input type="hidden" name="transaction_uuid" value="<?php echo uniqid() ?>">
                                    <input type="hidden" name="signed_field_names" value="access_key,profile_id,transaction_uuid,signed_field_names,unsigned_field_names,signed_date_time,locale,transaction_type,reference_number,amount,currency">
                                    <input type="hidden" name="unsigned_field_names">
                                    <input type="hidden" name="signed_date_time" value="<?php echo gmdate("Y-m-d\TH:i:s\Z"); ?>">
                                    <input type="hidden" value="sale" name="transaction_type" size="25"><br/><!-- set to sale -->
                                    <input type="hidden" name="locale" value="en">
                                    <fieldset>
                                        <legend>Payment Details</legend>
                                        <div id="paymentDetailsSection" class="section">                                            
                                            <input type="text" name="reference_number" size="25"><br/><!-- Generate Reference Number -->
                                            <span>amount:</span>{{ item_details.price.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,') }}<input type="hidden" v-model="item_details.price" name="amount" size="25"><br/>                                            
                                        </div>
                                    </fieldset>
                                    <input type="submit" id="submit" name="submit" value="Submit"/>                                    
                                </form>                            
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="tab2">                                                                    
                        <div class="row">
                            <div class="col-sm-12">                    
                                <div v-html="tuition"></div>                                  
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="tab3">
                        <div class="row">
                            <div class="col-sm-12">                          
                                <div class="box box-solid">
                                    <div class="box-header">
                                        <h4 class="box-title">MY PAYMENTS</h4>                                    
                                    </div>                                    
                                    <div class="box-body">
                                        <h4 class="box-title">Payments</h4>
                                        <table class="table table-bordered table-striped">
                                            <tr>                                    
                                                <th>Payment Type</th>
                                                <th>Amount Paid</th>
                                                <th>Online Payment Charge</th>
                                                <th>Total Due</th>
                                                <th>Status</th>
                                            </tr>     
                                            <tr v-if="application_payment">                                    
                                                <td>{{ application_payment.description }}</td>
                                                <td>{{ application_payment.subtotal_order }}</td>
                                                <td>{{ application_payment.charges }}</td>
                                                <td>{{ application_payment.total_amount_due }}</td>
                                                <td>{{ application_payment.status }}</td>                                                                                            
                                            </tr>
                                            <tr v-if="reservation_payment">                                    
                                                <td>{{ reservation_payment.description }}</td>
                                                <td>{{ reservation_payment.subtotal_order }}</td>
                                                <td>{{ reservation_payment.charges }}</td>
                                                <td>{{ reservation_payment.total_amount_due }}</td>
                                                <td>{{ reservation_payment.status }}</td>                                                                                           
                                            </tr>
                                            <tr>
                                                <th colspan="6">
                                                Other Payments:
                                                </th>
                                            </tr>  
                                            <tr v-for="payment in other_payments">                                    
                                                <td>{{ payment.description }}</td>
                                                <td>{{ payment.subtotal_order }}</td>
                                                <td>{{ payment.charges }}</td>
                                                <td>{{ payment.total_amount_due }}</td>
                                                <td>{{ payment.status }}</td>                                                                                            
                                            </tr>    
                                            <tr>
                                                <th colspan="6">
                                                Tuition Payments:
                                                </th>
                                            </tr>
                                            <tr v-for="payment in payments">                                    
                                                <td>{{ payment.description }}</td>
                                                <td>{{ payment.subtotal_order }}</td>
                                                <td>{{ payment.charges }}</td>
                                                <td>{{ payment.total_amount_due }}</td>
                                                <td>{{ payment.status }}</td>                                                                                            
                                            </tr>                                                                           
                                            <tr>
                                                <td class="text-green" colspan="5">
                                                amount paid: P{{ amount_paid_formatted }}                                           
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-green" colspan="5">                                            
                                                remaining balance: P{{ remaining_amount_formatted }}
                                                </td>
                                            </tr>
                                        </table>                                                                                                                                                                  
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
<!---
Additional Fields:

bill_to_forename=Joe
bill_to_surname=Smith
bill_to_email=joesmith@example.com
bill_to_address_line1=1 My Apartment
bill_to_address_city=Mountain View
bill_to_address_postal_code=94043
bill_to_address_state=CA
bill_to_address_country=US

Required Fields:
signature -> 

Merchant-generated Base64
signature. This is generated
using the signing method for the
access_key field supplied.

-->

<script src="https://code.jquery.com/jquery-1.7.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@2.6.12"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
    integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>

<style scoped="">
.box_mode_payment {
    border: 1px solid #000;
    height: 41px;
    width: 57px;
    margin: 4px;
    cursor: pointer;
}

.box_mode_payment.active {
    background: #1c54a5;
}

.spinner {
    animation-name: spin;
    animation-duration: 1000ms;
    animation-iteration-count: infinite;
    animation-timing-function: linear;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }
}
</style>
<script>
new Vue({
    el: '#registration-container',
    data: {
        id: '<?php echo $id; ?>',    
        sem: '<?php echo $selected_ay; ?>',
        base_url: '<?php echo base_url(); ?>',
        slug: undefined,
        loading_spinner: false,
        student:{},            
        student_api_data: {},
        desc: 'Tuition Fee',
        payment_modes: [],
        mode_of_releases: [],
        area_delivery: [],
        city_delivery: [],
        payment_modes_nonbanks: [],
        selected_items: [],
        payment_type: 'full',
        item: {},
        request: {
            mode_of_release: "",
            delivery_region_id: "",
            selected_location: "",
            mailing_fee: 0,
        },
        item_details: {
            price: 0,
            currency: 'PHP',
            hey: this.desc
        },                                             
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
        has_down: false,
        reg_status: undefined,        
        loader_spinner: true,     
        selected_mode_of_payment: {},
        total_single: 0,
        new_charge: 0,
        total_single_without_charge: 0,
        join_selected: '',
        redirect_link: '',
        qty_single: '',
        qty_global: 0,
        from_cart: false,
        total_single_format: 0,
        total_price_cart: 0,
        total_price_from_cart: 0,
        total_price_cart_with_charge_es: 0,
        total_price_cart_with_charge: 0,
        payload: {},         
        installments:[],          
    },

    mounted() {

        let url_string = window.location.href;        
        if(this.id != 0){            
            //this.loader_spinner = true;
            Swal.fire({
                showCancelButton: false,
                showCloseButton: false,
                allowEscapeKey: false,
                title: 'Loading',
                text: 'Loading data please wait',
                icon: 'info',
            })
            Swal.showLoading();
            axios.get(api_url + 'payments/modes?count_content=100', {
                headers: {
                    Authorization: `Bearer ${window.token}`
                },
            }).then((data) => {
                this.payment_modes = _.filter(data.data.data, item => item.is_nonbank != true);
                this.payment_modes_nonbanks = _.filter(data.data.data, item => item.is_nonbank == true);                                
                this.loadData();

                $(function() {
                    $(".box_mode_payment").click(function() {
                        $(".box_mode_payment").removeClass("active");
                        $(this).addClass("active");
                    })
                })
            })
            .catch((e) => {
                console.log("error");
            });        
            
            
        }

    },

    methods: {      
        loadData: function(){
            axios.get(this.base_url + 'unity/online_payment_data/' + this.id + '/' + this.sem)
                .then((data) => {  
                    if(data.data.success){                                                                                           
                        this.registration = data.data.registration;                                    
                        this.registration_status = data.data.registration.intROG;
                        this.reg_status = data.data.reg_status;
                        this.student = data.data.student;         
                        this.slug = this.student.slug;                           
                        this.advanced_privilages = data.data.advanced_privilages;       
                        this.tuition = data.data.tuition;
                        this.tuition_data = data.data.tuition_data;          
                        this.payment_type = this.registration.paymentType;
                        this.remaining_amount = data.data.tuition_data.total;
                        if(this.payment_type == "partial")                       
                            this.remaining_amount = data.data.tuition_data.total_installment;
                                      
                            

                        axios.get(api_url + 'finance/transactions/' + this.slug + '/' + this.sem)
                        .then((data) => {                                                 
                            this.payments = data.data.data;
                            for(i in this.payments){
                                if(this.payments[i].status == "Paid"){                              
                                    this.remaining_amount = this.remaining_amount - this.payments[i].subtotal_order;
                                    this.amount_paid = this.amount_paid + this.payments[i].subtotal_order;                                    
                                }
                            }                        
                            
                            
                            
                            this.other_payments = data.data.other;
                                   
                                                         

                            axios.get(api_url + 'finance/reservation/' + this.slug + '/' + this.sem)
                            .then((data) => {
                                this.reservation_payment = data.data.data;    
                                this.application_payment = data.data.application;
                                
                                if(this.reservation_payment.status == "Paid" && data.data.student_sy == this.sem){
                                        this.remaining_amount = this.remaining_amount - this.reservation_payment.subtotal_order;                                                                                            
                                        this.amount_paid = this.amount_paid + this.reservation_payment.subtotal_order;                                        
                                }                                
                                this.remaining_amount = (this.remaining_amount < 0.02) ? 0 : this.remaining_amount;
                                this.remaining_amount = Math.round(this.remaining_amount * 100) / 100;
                                this.remaining_amount_formatted = this.remaining_amount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                                this.amount_paid_formatted = this.amount_paid.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');                                
                                this.item_details.price = this.remaining_amount;
                                this.loader_spinner = false;

                                let down_payment = (this.tuition_data.down_payment <= this.amount_paid) ? 0 : ( this.tuition_data.down_payment - this.amount_paid );
                                
                                if(this.registration.downpayment == 1 || down_payment == 0){
                                    this.has_down = true;
                                    console.log(this.tuition_data.installment_fee);
                                    //installment amounts                                                                    
                                    var temp = (this.tuition_data.installment_fee * 5) - parseFloat(this.remaining_amount);
                                    console.log(temp);
                                    for(i=0; i < 5; i++){
                                        if(this.tuition_data.installment_fee > temp){
                                            val = this.tuition_data.installment_fee - temp;                                            
                                            this.item_details.price = val;
                                            break;
                                        }     
                                        else{
                                            temp = temp - this.tuition_data.installment_fee;
                                        }                                                                       
                                    }
                                    
                                    
                                }
                                else if(this.payment_type == "partial"){
                                    
                                    this.item_details.price = down_payment;
                                }                            
                                else{
                                    
                                    this.item_details.price = this.remaining_amount;
                                }      
                                axios.get(api_url + 'admissions/student-info/' + this.slug)
                                .then((data) => {
                                    this.student_api_data = data.data.data;
                                    Swal.close();
                                })
                                .catch((error) => {
                                    console.log(error);
                                })
                                
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
        },        
        selectPayment: function(mode_payment) {
            this.selected_mode_of_payment = mode_payment;

            var new_price = parseFloat(this.item_details.price);
            var new_charge = parseFloat(this.selected_mode_of_payment.charge);
            var qty = 1;


            if (this.selected_mode_of_payment.type == 'percentage') {
                var new_price_with_qty = new_price * qty;

                new_charge = ((new_charge / 100) * new_price_with_qty);
                if (new_charge < 25) {
                    new_charge = 25.00;
                }


            }

            this.total_single_without_charge = (new_price * qty);
            this.total_single = (new_price * qty) + new_charge;
            this.total_single_format = (this.total_single + parseFloat(this.request.mailing_fee))
                .toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');


            this.new_charge = new_charge;

            console.log("total_single_format", this.total_single_format);
            console.log("new_charge", this.new_charge);

            let title = this.desc;

            this.payload = {
                "description": title,
                "order_items": [{
                    "price_default": this.item_details.price,
                    "title": title,
                    "qty": "1",
                    "id": 1
                }],
                "total_price_without_charge": this.total_single_without_charge,
                "sy_reference": this.sem,
                "first_name": this.student.strFirstname,
                "last_name": this.student.strLastname,
                "contact_number": this.student.strMobileNumber.replace(/\D/g, ""),
                "email": this.student.strEmail,
                "remarks": "",
                "mode_of_payment_id": mode_payment.id,
                "delivery_region_id": null,
                "delivery_city_id": "",
                "country": "",
                "other_country": "",
                "total_price_with_charge": this.total_single,
                "charge": parseFloat(this.new_charge),
                "mode_of_release": null,
                "mailing_fee": 0,
                "student_information_id": this.student_api_data.id
            }


            // console.log(this.payload)

        },
        submitPayment: function() {
            // Swal.fire({
            //     title: "Submit Payment",
            //     text: "Are you sure you want to submit?",
            //     showCancelButton: true,
            //     confirmButtonText: "Yes",
            //     imageWidth: 100,
            //     icon: "question",
            //     cancelButtonText: "No, cancel!",
            //     showCloseButton: true,
            //     showLoaderOnConfirm: true,
            //     preConfirm: (login) => {
            //         return 
            //     },
            //     allowOutsideClick: () => !Swal.isLoading()
            // }).then((result) => {
            //     if (result.isConfirmed) {

            //     }
            // })

            this.loading_spinner = true;
            Swal.fire({
                showCancelButton: false,
                showCloseButton: false,
                allowEscapeKey: false,
                title: 'Loading',
                text: 'Processing Payment',
                icon: 'info',
            })
            Swal.showLoading();

            axios
                .post(api_url + 'payments', this.payload, {
                    headers: {
                        Authorization: `Bearer ${window.token}`
                    }
                })
                .then(data => {
                    this.is_done = true;

                    if (data.data.success) {

                        if (!this.selected_mode_of_payment.is_nonbank) {
                            this.redirect_link = data.data.payment_link;
                            this.loading_spinner = false;

                            setTimeout(() => {
                                document.getElementById("payment_link")
                                    .click();
                            }, 500);

                        } else {}
                    } else {
                        Swal.fire(
                            'Failed!',
                            data.data.message,
                            'error'
                        )
                    }
                });
        }
    }

})
</script>

