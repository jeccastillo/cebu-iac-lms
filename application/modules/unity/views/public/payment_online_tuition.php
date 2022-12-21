<div id="registration-container">    
    <div class="container">       
        <div class="content">
            <div class="row">
                <div class="col-sm-6">
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
                    <div v-html="tuition"></div>                                  
                </div>
                <div class="col-sm-6">
                    <div class="box box-solid">
                        <div class="box-header">
                            <h4 class="box-title">PAY ONLINE</h4>
                        </div>
                        <div class="box-body">                                                                                                  
                            <div class="form-group">
                                <label>Select Payment Type</label>
                                <select @change="selectDescription" class="form-control" v-model="payment_type">
                                    <option value="Tuition Full">Tuition Full</option>
                                    <option value="Tuition Down Payment">Tuition Down Payment</option>
                                    <option value="Tuition Partial">Tuition Partial</option>                                
                                </select>
                            </div>                                                                
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
                        </div>

                        <hr>

                        <div class="d-flex flex-wrap my-5" style="margin-top:50px">
                            <h5 class="mb-3"><strong>Breakdown of Fees</strong></h5>

                            <table class="table" style="width:100%">
                                <tbody>
                                    <tr v-if="item">
                                        <td> {{ payment_type }}
                                        </td>
                                        <td>₱ {{ item_details.price }}</td>
                                    </tr>

                                    <tr>
                                        <td>Gateway Fee <span class="font-weight-bold"
                                                v-if="selected_mode_of_payment.type == 'percentage'">(
                                                {{ selected_mode_of_payment.charge}}% of the gross transaction amount or
                                                Php
                                                25.00 whichever is higher )</span> </td>
                                        <td v-if="selected_mode_of_payment">
                                            <span>
                                                ₱ {{ new_charge }}
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
                                        class="inline-flex items-center py-2 px-3 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300"
                                        name="button">Submit 
                                    </button>
                                    <button type="button" disabled v-else
                                        class="inline-flex items-center py-2 px-3 text-sm font-medium text-center disabled:bg-blue-300 text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300"
                                        name="button">Submit</button>
                                    <button type="button" onclick="window.history.back()"
                                        class="inline-flex items-center py-2 px-3 text-sm font-medium text-center disabled:bg-red-300 text-white bg-red-700 rounded-lg hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300"
                                        name="button">Cancel</button>
                                    <a :href="redirect_link" style="opacity:0" target="_blank"
                                        id="payment_link">{{ redirect_link }}</a>
                                </div>
                            </div>
                        </div>
                    </div>      
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
        loading_spinner: false,
        student:{},            
        payment_modes: [],
        mode_of_releases: [],
        area_delivery: [],
        city_delivery: [],
        payment_modes_nonbanks: [],
        selected_items: [],
        payment_type: 'Tuition Full',
        item: {},
        item_details: {
            price: 0,
            hey: this.payment_type
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
                                this.item_details.price = this.remaining_amount;
                                this.loader_spinner = false;
                                Swal.close();
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
        selectDescription: function(){
            
            this.request.description = this.description;
            switch(this.description){
                case 'Tuition Full':
                    this.item_details.price = this.remaining_amount;
                break;
                case 'Tuition Partial':
                    this.item_details.price = (this.tuition_data.installment_fee > this.remaining_amount) ? this.remaining_amount : this.tuition_data.installment_fee;
                break;
                case 'Tuition Down Payment':                        
                    this.item_details.price = (this.tuition_data.down_payment <= this.amount_paid) ? 0 : ( this.tuition_data.down_payment - this.amount_paid );
                break;                    
            }
            
            
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

            console.log("total_single_format", this.total_single_format);
            console.log("new_charge", this.new_charge);

            let title = (this.payment_type == 'admissions_student_payment_reservation') ? 'Reservation Payment' :
                                    'Application Payment';

            this.payload = {
                "description": title,
                "order_items": [{
                    "price_default": "700",
                    "title": title,
                    "qty": "1",
                    "id": 1
                }],
                "total_price_without_charge": this.total_single_without_charge,
                "first_name": this.student.first_name,
                "last_name": this.student.last_name,
                "contact_number": this.student.mobile_number,
                "email": this.student.email,
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
                "student_information_id": this.student.id
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

