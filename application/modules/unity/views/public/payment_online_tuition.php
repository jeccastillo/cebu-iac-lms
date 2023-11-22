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
                                <h4 class="box-title">PAY ONLINE</h4>
                            </div>
                            <div class="box-body">                                   
                                <hr />
                                <form @submit.prevent="submitPayment">                                                                                               
                                    <!-- <div class="form-group">
                                        <label>Select Payment Type</label>
                                        <select @change="selectDescription" class="form-control" v-model="payment_type">
                                            <option value="Tuition Full">Tuition Full</option>
                                            <option v-if="has_down" value="Tuition Partial">Tuition Partial</option>
                                            <option v-else value="Tuition Down Payment">Tuition Down Payment</option>
                                                                            
                                        </select>
                                    </div>     
                                                                                                -->
                                    <input type="hidden" value="Tuition Fee" v-model="desc" />                                                                                            
                                    <div>
                                        <h5 class="my-3">Select Mode of Payment ( Banks )</h5>
                                        <div class="d-flex flex-wrap" style="display:flex; flex:wrap;">
                                            <div v-for="t in payment_modes" style="border:1px solid #000" @click="selectPayment(t)"
                                                class="box_mode_payment d-flex align-items-center justify-content-center mr-3 my-3 p-1"
                                                style="display:flex; align-itenms:center;">
                                                <img :src="t.image_url" class="img-fluid d-block mx-auto" width="51px" alt="">
                                            </div>
                                        </div>

                                        <hr>
                                        <h5 class="my-3">Select Mode of Payment ( Non-Banks )</h5>
                                        <div class="d-flex flex-wrap" style="display:flex; flex:wrap;">
                                            <div v-for="t in payment_modes_nonbanks" style="border:1px solid #000" @click="selectPayment(t)"
                                                class="box_mode_payment d-flex align-items-center justify-content-center mr-3 my-3 p-1"
                                                style="display:flex; align-itenms:center;">
                                                <img class="img-fluid d-block mx-auto" width="51px" :src="t.image_url" alt="">
                                            </div>
                                        </div>                

                                        <hr />
                                        <h5 class="my-3">BDO PAY</h5>
                                        <div class="d-flex flex-wrap" style="display:flex; flex:wrap;">
                                            <div style="border:1px solid #000" @click="selectPayment(bdo_pay)"
                                                class="box_mode_payment d-flex align-items-center justify-content-center mr-3 my-3 p-1"
                                                style="display:flex; align-itenms:center;">
                                                <img class="img-fluid d-block mx-auto" width="51px" src="https://portalv2.iacademy.edu.ph/images/finance_online_payment/bdo.jpg" alt="">                                                
                                            </div>
                                        </div>                                                        
                                        <hr />
                                        <div class="d-flex flex-wrap my-5" style="margin-top:50px">
                                            <h5 class="mb-3"><strong>Breakdown of Fees</strong></h5>

                                            <table class="table" style="width:100%">
                                                <tbody>
                                                    <tr v-if="item">
                                                        <td> {{ desc }}
                                                        </td>
                                                        <td>₱ {{ item_details.price.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,') }}</td>
                                                    </tr>

                                                    <tr>
                                                        <td>Gateway Fee <span class="font-weight-bold"
                                                                v-if="selected_mode_of_payment.type == 'percentage'">(
                                                                {{ selected_mode_of_payment.charge}}% of the gross transaction amount or
                                                                Php
                                                                25.00 whichever is higher )</span> </td>
                                                        <td v-if="selected_mode_of_payment">
                                                            <span>
                                                                ₱ {{ new_charge.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,') }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style="border-top:1px solid #000">TOTAL AMOUNT DUE</td>
                                                        <td style="border-top:1px solid #000" class="text-nowrap w-[100px]" v-if="item"> <span
                                                                class="font-weight-bold">₱ {{ total_single_format }}</span> </td>
                                                        <td style="border-top:1px solid #000" class="text-nowrap w-[100px]" v-if="from_cart">
                                                            <span class="font-weight-bold">₱
                                                                {{ total_price_cart_with_charge_es }}</span>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>

                                            <div class="text-right mt-3">
                                                <div v-if="loading_spinner" class="lds-ring"><div></div><div></div><div></div><div></div></div> 
                                                <div v-else>
                                                    <button type="submit" :disabled="loading_spinner" v-if="selected_mode_of_payment.id"
                                                        class="btn btn-primary"
                                                        name="button">Submit 
                                                    </button>
                                                    <button type="button" disabled v-else
                                                        class="btn btn-default"
                                                        name="button">Submit</button>
                                                    <button type="button" onclick="window.history.back()"
                                                        class="btn btn-default"
                                                        name="button">Cancel</button>
                                                    <a :href="redirect_link" style="opacity:0" 
                                                        id="payment_link">{{ redirect_link }}</a>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
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
                    <form ref="bdo_form" action="https://secureacceptance.cybersource.com/pay" method="post">
                        <input type="hidden" v-for="(value, name, index) in request_bdo" :name="name" :value="value" />
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
        bdo_pay:{
            charge: 0,
            id: 99,
            is_nonbank: false,
            name: "BDO PAY",
            pchannel: "bdo_pay",
            pmethod: "onlinebanktransfer",            
            type: "none"
        },
        request_bdo:{
            access_key: undefined,
            amount: undefined,
            currency: undefined,
            locale: undefined,
            profile_id: undefined,
            reference_number: undefined,
            signature: undefined,
            signed_date_time: undefined,
            signed_field_names: undefined,
            transaction_type: undefined,
            transaction_uuid: undefined,
            unsigned_field_names: "",
            bill_to_address_line1: undefined,
            bill_to_address_city: undefined,
            bill_to_address_country: undefined,
            bill_to_email: undefined,
            bill_to_surname: undefined,
            bill_to_forename: undefined,
        }        
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
                            
            this.new_charge = new_charge.toFixed(2);

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

                        }
                        if(this.selected_mode_of_payment.pchannel == "bdo_pay"){                            
                            this.request_bdo = data.data.post_data;
                            setTimeout(() => {
                                this.$refs.bdo_form.submit();
                            }, 500);                        
                        }
                        else {
                            Swal.fire({
                                title: "Payment is Pending",
                                text: data.data.message,
                                icon: "success"
                            }).then(function() {
                                window.location = "https://iacademy.edu.ph";
                            });

                        }
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

