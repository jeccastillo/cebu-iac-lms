<section class="section section_port relative" id="adminssions-form">
    <div class="custom-container md:h-[500px] relative z-1">
        <img src="<?php echo $img_dir; ?>home-poly/blue-poly.png" class="absolute top-0 md:right-[25%] hidden md:block"
            alt="" data-scroll-speed="4" data-aos="zoom-in" />

        <img src="<?php echo $img_dir; ?>home-poly/yellow-poly.png"
            class="absolute top-[10%] md:left-[17%] hidden md:block" alt="" data-scroll-speed="4" data-aos="zoom-in" />
        <img src="<?php echo $img_dir; ?>home-poly/red-poly.png" class="absolute top-[30%] md:left-[0%] hidden md:block"
            alt="" data-scroll-speed="4" data-aos="zoom-in" />

        <img src="<?php echo $img_dir; ?>home-poly/peach-poly.png"
            class="absolute top-[25%] md:left-[33%] hidden md:block" alt="" data-scroll-speed="4" data-aos="zoom-in" />

        <img src="<?php echo $img_dir; ?>home-poly/lyellow-poly.png"
            class="absolute top-[50%] md:right-[0%] hidden md:block" alt="" data-scroll-speed="4" data-aos="zoom-in" />

        <img src="<?php echo $img_dir; ?>home-poly/lblue-poly.png"
            class="absolute top-[20%] md:right-[10%] hidden md:block" alt="" data-scroll-speed="4" data-aos="zoom-in" />

        <div class="custom-container relative h-full mb-[100px] md:mb-[10px]">
            <div class="md:flex mt-[00px] md:mt-0 h-full items-center justify-center">
                <div class="md:w-12/12 py-3">

                    <div class=" block mx-auto mt-[200px]" data-aos="fade-up">
                        <h1 class="text-4xl font-[900] text-center color-primary">
                            Admissions
                        </h1>
                        <h1 class="text-4xl uppercase text-center color-primary">

                            {{ payment_type == 'admissions_student_payment_reservation' ? 'Payment for Reservation Fee' : payment_type == 'admissions_student_payment' ? 'Payment for Application Fee' : '' }}
                        </h1>
                        <h4 class="text-center">(If you would like to pay on-site please ignore this step)</h4>
                    </div>
                    <p class="max-w-[800px] color-primary mt-[60px]">
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="custom-container" v-if="student.first_name">
        <form @submit.prevent="submitPayment">
            <div class="flex flex-wrap">
                <div class="mb-6 md:w-4/12 p-2">
                    <div>
                        <label class="block t color-primary font-bold  mb-3  pr-4" for="inline-full-name">
                            Email
                        </label>
                        <input disabled
                            class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="email" v-model="student.email" required>
                    </div>
                </div>

                <div class="mb-6 md:w-4/12 p-2">
                    <div>
                        <label class="block t color-primary font-bold  mb-3  pr-4" for="inline-full-name">
                            First Name
                        </label>
                        <input disabled
                            class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text" required v-model="student.first_name">
                    </div>
                </div>

                <div class="mb-6 md:w-4/12 p-2">
                    <div>
                        <label class="block t color-primary font-bold  mb-3  pr-4" for="inline-full-name">
                            Last Name
                        </label>
                        <input disabled
                            class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text" required v-model="student.last_name">
                    </div>
                </div>

                <div class="mb-6 md:w-4/12 p-2">
                    <div>
                        <label class="block t color-primary font-bold  mb-3  pr-4" for="inline-full-name">
                            Course
                        </label>
                        <input disabled
                            class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text" required v-model="student.student_type_title">
                    </div>
                </div>

                <div class="mb-6 md:w-4/12 p-2">
                    <div>
                        <label class="block t color-primary font-bold  mb-3  pr-4" for="inline-full-name">
                            Mobile Number
                        </label>
                        <input disabled
                            class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text" required v-model="student.mobile_number">
                    </div>
                </div>

                <div class="mb-6 md:w-4/12 p-2">
                    <div>
                        <label class="block t color-primary font-bold  mb-3  pr-4" for="inline-full-name">
                            Telephone Number
                        </label>
                        <input disabled
                            class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text" v-model="student.tel_number">
                    </div>
                </div>

            </div>
            <hr>
            <div class="md:w-1/2 w-full">
                <div>
                    <h3>Select Mode of Payment</h3>                          
                    <hr />
                    <div v-if="payment_type == 'admissions_student_payment_reservation'">
                        <h5 class="my-3">BDO PAY</h5>
                        <hr />  
                        <div class="d-flex flex-wrap" style="display:flex; flex:wrap;">
                            <div style="border:1px solid #000" @click="selectPayment(bdo_pay)"
                                class="box_mode_payment d-flex align-items-center justify-content-center mr-3 my-3 p-1"
                                style="display:flex; align-itenms:center;">
                                <img class="img-fluid d-block mx-auto" width="51px" src="https://portalv2.iacademy.edu.ph/images/finance_online_payment/bdo.jpg" alt="">                                                
                            </div>
                        </div>                                                        
                    </div>
                    <hr />                    
                    <h5>PAYNAMICS</h5>
                    <hr />
                    <h5 class="my-3">Banks</h5>
                    <div class="d-flex flex-wrap" style="display:flex; flex:wrap;">
                        <div v-for="t in payment_modes" style="border:1px solid #000" @click="selectPayment(t)"
                            class="box_mode_payment d-flex align-items-center justify-content-center mr-3 my-3 p-1"
                            style="display:flex; align-itenms:center;">
                            <img :src="t.image_url" class="img-fluid d-block mx-auto" width="51px" alt="">
                        </div>
                    </div>

                    <hr>
                    <h5 class="my-3">Non-Banks</h5>
                    <div class="d-flex flex-wrap" style="display:flex; flex:wrap;">
                        <div v-for="t in payment_modes_nonbanks" style="border:1px solid #000" @click="selectPayment(t)"
                            class="box_mode_payment d-flex align-items-center justify-content-center mr-3 my-3 p-1"
                            style="display:flex; align-itenms:center;">
                            <img class="img-fluid d-block mx-auto" width="51px" :src="t.image_url" alt="">
                        </div>
                    </div>
                </div>
                <hr />                
                

                <div class="d-flex flex-wrap my-5" style="margin-top:50px">
                    <h5 class="mb-3"><strong>Breakdown of Fees</strong></h5>

                    <table class="table" style="width:100%">
                        <tbody>
                            <tr v-if="item">
                                <td> {{payment_type == 'admissions_student_payment_reservation' ? 'Reservation Fee' :
                                    'Application Fee'
                                    }}
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
                        <div v-if="loading_spinner" class="lds-ring">
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                        </div>
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
                            <a :href="redirect_link" style="opacity:0"
                                id="payment_link">{{ redirect_link }}</a>
                        </div>
                    </div>
                </div>
                <hr />
                <p v-if="payment_type == 'admissions_student_payment_reservation'"><i>Reservation Fee is not refundable
                        and non-transferrable</i></p>

        </form>
        <form ref="bdo_form" action="https://secureacceptance.cybersource.com/pay" method="post">
            <input type="hidden" v-for="(value, name, index) in request_bdo" :name="name" :value="value" />
        </form>
    </div>
</section>

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
    el: "#adminssions-form",
    data: {
        request: {
            mode_of_release: "",
            delivery_region_id: "",
            selected_location: "",
            mailing_fee: 0,
        },
        loading_spinner: false,
        student: {},
        payment_modes: [],
        mode_of_releases: [],
        area_delivery: [],
        city_delivery: [],
        payment_modes_nonbanks: [],
        selected_items: [],
        payment_type: "<?php echo $this->uri->segment('2'); ?>",
        item_details: {
            price: 0,
            hey: this.payment_type
        },
        item: {}, //single order
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
        slug: "<?php echo $this->uri->segment('3'); ?>",
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



        axios
            .get(api_url + 'payments/modes?count_content=100', {
                headers: {
                    Authorization: `Bearer ${window.token}`
                },
            })

            .then((data) => {
                this.payment_modes = _.filter(data.data.data, item => item.is_nonbank != true);
                this.payment_modes_nonbanks = _.filter(data.data.data, item => item.is_nonbank == true);

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

        axios.get(api_url + 'admissions/student-info/' + this.slug)
            .then((data) => {
                this.student = data.data.data;

                if (this.student.status != 'New' && this.student.status != "For Reservation") {
                    window.location.href = "/"
                }

                if (this.student.campus == "Cebu")
                    this.item_details.price = this.payment_type ==
                    'admissions_student_payment_reservation' ? 10000 : 500;
                else
                    this.item_details.price = this.payment_type ==
                    'admissions_student_payment_reservation' ? 10000 : 700;
            })
            .catch((error) => {
                console.log(error);
            })


    },

    methods: {

        selectPayment: function(mode_payment) {
            this.selected_mode_of_payment = mode_payment;

            var new_price = parseFloat(this.item_details.price);
            var new_charge = parseFloat(this.selected_mode_of_payment.charge);
            var qty = 1;


            if (this.selected_mode_of_payment.type == 'percentage') {
                var new_price_with_qty = new_price * qty;

                new_charge = ((new_charge / 100) * new_price_with_qty);
                if (new_charge < 28) {
                    new_charge = 28.00;
                }


            }

            this.total_single_without_charge = (new_price * qty);
            this.total_single = (new_price * qty) + new_charge;
            this.total_single_format = (this.total_single + parseFloat(this.request.mailing_fee))
                .toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');


            this.new_charge = new_charge.toFixed(2);

            console.log("total_single_format", this.total_single_format);
            console.log("new_charge", this.new_charge);

            let title = (this.payment_type == 'admissions_student_payment_reservation') ?
                'Reservation Payment' :
                'Application Payment';

            this.payload = {
                "description": title,
                "order_items": [{
                    "price_default": this.total_single_without_charge,
                    "title": title,
                    "qty": "1",
                    "id": 1
                }],
                "total_price_without_charge": this.total_single_without_charge,
                "first_name": this.student.first_name,
                "last_name": this.student.last_name,
                "contact_number": this.student.mobile_number.replace(/\D/g, ""),
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

                        if (!this.selected_mode_of_payment.is_nonbank && this.selected_mode_of_payment.pchannel != "bdo_pay") {
                            this.redirect_link = data.data.payment_link;
                            this.loading_spinner = false;

                            setTimeout(() => {
                                document.getElementById("payment_link")
                                    .click();
                            }, 500);

                        } 
                        else if(this.selected_mode_of_payment.pchannel == "bdo_pay"){                            
                            this.request_bdo = data.data.post_data;
                            setTimeout(() => {
                                this.$refs.bdo_form.submit();
                            }, 500);                        
                        }else {
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